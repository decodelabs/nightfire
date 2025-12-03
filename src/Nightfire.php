<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Exemplar\Element;
use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;
use DecodeLabs\Nightfire\Area;
use DecodeLabs\Nightfire\Area\XmlTranslator as AreaXmlTranslator;
use DecodeLabs\Nightfire\Block;
use DecodeLabs\Nightfire\Block\XmlTranslator as BlockXmlTranslator;
use DecodeLabs\Nightfire\BlockGroup;
use DecodeLabs\Nightfire\BlockReference;
use DecodeLabs\Nightfire\Category;
use DecodeLabs\Nightfire\Category\Uncategorized;
use DecodeLabs\Nightfire\Collection;
use DecodeLabs\Nightfire\Data\Area as AreaData;
use DecodeLabs\Nightfire\Data\Block as BlockData;
use DecodeLabs\Nightfire\Data\Layout as LayoutData;
use DecodeLabs\Nightfire\Layout;
use DecodeLabs\Nightfire\Layout\XmlTranslator as LayoutXmlTranslator;
use DecodeLabs\Nightfire\LayoutReference;
use DecodeLabs\Nightfire\LayoutValidationResult;
use DecodeLabs\Nightfire\Strategy;
use DecodeLabs\Nightfire\ValidationError;
use Generator;
use ReflectionClass;

class Nightfire implements Service
{
    use ServiceTrait;

    public function __construct(
        protected Archetype $archetype
    ) {
    }

    /**
     * @return ?class-string<Block>
     */
    public function resolveBlockClass(
        string $type
    ): ?string {
        return $this->archetype->tryResolve(Block::class, $type);
    }

    /**
     * @return Generator<BlockReference>
     */
    public function loadAllBlockReferences(): Generator
    {
        foreach ($this->archetype->scanClasses(Block::class) as $class) {
            yield new BlockReference($class);
        }
    }

    /**
     * @param string|array<string,mixed>|Element|BlockData $data
     */
    public function inflateBlock(
        string|array|Element|BlockData $data
    ): Block {
        $blockData = $this->inflateBlockData($data);

        if (
            !$data instanceof BlockData &&
            !$blockData->checkHash()
        ) {
            throw Exceptional::UnexpectedValue(
                message: 'Block data hash mismatch',
                data: $data,
            );
        }

        if (!$blockClass = $this->resolveBlockClass($blockData->type)) {
            throw Exceptional::UnexpectedValue(
                message: 'Block class not found for type: ' . $blockData->type,
                data: $blockData->type,
            );
        }

        $ref = new ReflectionClass($blockClass);
        $block = $ref->newInstanceWithoutConstructor();
        $block->__unserialize($blockData->data);

        return $block;
    }


    /**
     * @param string|array<string,mixed>|Element|BlockData $data
     */
    public function inflateBlockData(
        string|array|Element|BlockData $data
    ): BlockData {
        if ($data instanceof BlockData) {
            return $data;
        }

        if (is_string($data)) {
            if (str_starts_with($data, '<')) {
                $data = Element::fromXmlString($data);
            } elseif (str_starts_with($data, '{')) {
                /** @var array<string,mixed> $decoded */
                $decoded = Coercion::asArray(json_decode($data, true));
                return BlockData::from($decoded);
            } else {
                throw Exceptional::UnexpectedValue(
                    message: 'Invalid block data string',
                    data: $data,
                );
            }
        }

        if (is_array($data)) {
            return BlockData::from($data);
        }

        return $this->translateXmlToBlockData($data);
    }

    public function translateXmlToBlockData(
        Element $element
    ): BlockData {
        $type = $element->getAttribute('type');

        if (empty($type)) {
            throw Exceptional::UnexpectedValue(
                message: 'Block type not found in element',
                data: $element,
            );
        }

        if (!$blockClass = $this->resolveBlockClass($type)) {
            throw Exceptional::UnexpectedValue(
                message: 'Block class not found for type: ' . $type,
                data: $type,
            );
        }

        if (is_a($blockClass, BlockXmlTranslator::class, true)) {
            $data = $blockClass::readXml($element);
        } else {
            if (null === ($dataString = $element->getAttribute('data'))) {
                $data = [];
            } else {
                /** @var array<string,mixed> $data */
                $data = Coercion::asArray(json_decode($dataString, true));
            }
        }

        return new BlockData(
            type: $type,
            version: $element->getAttribute('version') ?? $blockClass::defineActiveVersion(),
            data: $data,
            hash: $element->getAttribute('hash'),
        );
    }





    /**
     * @param string|array<string,mixed>|Element|AreaData $data
     */
    public function inflateArea(
        string|array|Element|AreaData $data
    ): Area {
        $areaData = $this->inflateAreaData($data);

        if (
            !$data instanceof AreaData &&
            !$areaData->checkHash()
        ) {
            throw Exceptional::UnexpectedValue(
                message: 'Area data hash mismatch',
                data: $data,
            );
        }

        return new Area(
            id: $areaData->id,
            blocks: array_map(fn (BlockData $blockData) => $this->inflateBlock($blockData), $areaData->blocks),
        );
    }

    /**
     * @param string|array<string,mixed>|Element|AreaData $data
     */
    public function inflateAreaData(
        string|array|Element|AreaData $data
    ): AreaData {
        if ($data instanceof AreaData) {
            return $data;
        }

        if (is_string($data)) {
            if (str_starts_with($data, '<')) {
                $data = Element::fromXmlString($data);
            } elseif (str_starts_with($data, '{')) {
                /** @var array<string,mixed> $decoded */
                $decoded = Coercion::asArray(json_decode($data, true));
                return AreaData::from($decoded);
            } else {
                throw Exceptional::UnexpectedValue(
                    message: 'Invalid area data string',
                    data: $data,
                );
            }
        }

        if (is_array($data)) {
            return AreaData::from($data);
        }

        return $this->translateXmlToAreaData($data);
    }

    public function translateXmlToAreaData(
        Element $element
    ): AreaData {
        $name = $element->getTagName();

        if ($name === 'area') {
            $blocks = [];

            foreach ($element->getChildrenOfType('block') as $blockElement) {
                $blocks[] = $this->translateXmlToBlockData($blockElement);
            }

            return new AreaData(
                id: Coercion::tryString($element->getAttribute('id')) ?? 'default',
                blocks: $blocks
            );
        }


        if (null === ($translatorClass = $this->archetype->tryResolve(AreaXmlTranslator::class, ucfirst($name)))) {
            throw Exceptional::UnexpectedValue(
                message: 'Area translator class not found for type: ' . $name,
                data: $name,
            );
        }

        return $translatorClass::readXml($element, $this->translateXmlToBlockData(...));
    }



    public function tryLoadCategory(
        string $id
    ): ?Category {
        if (null === ($class = $this->archetype->tryResolve(Category::class, $id))) {
            return null;
        }

        return new $class();
    }

    /**
     * @return array<string,Category>
     */
    public function loadAllCategories(): array
    {
        $output = [];

        foreach ($this->archetype->scanClasses(Category::class) as $class) {
            $category = new $class();

            if (
                isset($output[$category->id]) &&
                $output[$category->id]->weight < $category->weight
            ) {
                continue;
            }

            $output[$category->id] = $category;
        }

        uasort($output, fn (Category $a, Category $b): int => $a->weight <=> $b->weight);
        return $output;
    }

    /**
     * @param iterable<Block|BlockReference> $blocks
     * @return array<string,BlockGroup<Category>>
     */
    public function buildBlockSelectionList(
        iterable $blocks
    ): array {
        $output = $groups = [];

        foreach ($blocks as $block) {
            if ($block instanceof BlockReference) {
                $categoryNames = $block->categoryTypeNames;
            } else {
                $categoryNames = $block::defineCategoryTypeNames();
            }

            foreach ($categoryNames as $catName) {
                if (isset($groups[$catName])) {
                    $groups[$catName]->add($block);
                    continue;
                }

                $category = $this->tryLoadCategory($catName) ?? new Uncategorized();
                $catName = $category->defineTypeName();

                $group = new BlockGroup(
                    descriptor: $category,
                );

                $group->add($block);
                $groups[$catName] = $group;
            }
        }

        uasort(
            $groups,
            fn (
                BlockGroup $a,
                BlockGroup $b
            ): int =>
                $a->weight <=> $b->weight
        );

        foreach ($groups as $group) {
            $output[$group->id] = $group;
        }

        return $output;
    }




    public function tryLoadCollection(
        string $id
    ): ?Collection {
        if (null === ($class = $this->archetype->tryResolve(Collection::class, $id))) {
            return null;
        }

        return new $class();
    }

    /**
     * @return array<string,Collection>
     */
    public function loadAllCollections(): array
    {
        $output = [];

        foreach ($this->archetype->scanClasses(Collection::class) as $class) {
            $collection = new $class();

            if (
                isset($output[$collection->id]) &&
                $output[$collection->id]->weight < $collection->weight
            ) {
                continue;
            }

            $output[$collection->id] = $collection;
        }

        uasort($output, fn (Collection $a, Collection $b): int => $a->weight <=> $b->weight);
        return $output;
    }

    /**
     * @return BlockGroup<Collection>
     */
    public function buildCollectionGroup(
        string|Collection $collection
    ): BlockGroup {
        if (is_string($collection)) {
            $collection = $this->tryLoadCollection($collection);
        }

        if ($collection === null) {
            throw Exceptional::UnexpectedValue(
                message: 'Collection not found',
                data: $collection,
            );
        }

        $collectionId = $collection->defineTypeName();

        $group = new BlockGroup(
            descriptor: $collection,
        );

        foreach ($this->loadAllBlockReferences() as $blockReference) {
            if (
                in_array($collectionId, $blockReference->collectionTypeNames) &&
                $collection::acceptsBlock($blockReference)
            ) {
                $group->add($blockReference);
            }
        }

        return $group;
    }



    /**
     * @return ?class-string<Layout>
     */
    public function resolveLayoutClass(
        string $type
    ): ?string {
        return $this->archetype->tryResolve(Layout::class, $type);
    }

    /**
     * @return Generator<LayoutReference>
     */
    public function loadAllLayoutReferences(): Generator
    {
        foreach ($this->archetype->scanClasses(Layout::class) as $class) {
            yield new LayoutReference($class);
        }
    }

    /**
     * @param string|array<string,mixed>|Element|LayoutData $data
     */
    public function inflateLayout(
        string|array|Element|LayoutData $data
    ): Layout {
        $layoutData = $this->inflateLayoutData($data);

        if (
            !$data instanceof LayoutData &&
            !$layoutData->checkHash()
        ) {
            throw Exceptional::UnexpectedValue(
                message: 'Layout data hash mismatch',
                data: $data,
            );
        }

        if (!$layoutClass = $this->resolveLayoutClass($layoutData->type)) {
            throw Exceptional::UnexpectedValue(
                message: 'Layout class not found for type: ' . $layoutData->type,
                data: $layoutData->type,
            );
        }

        $ref = new ReflectionClass($layoutClass);
        $layout = $ref->newInstanceWithoutConstructor();

        foreach ($layoutData->areas as $areaData) {
            $area = $this->inflateArea($areaData);
            $layout->addArea($area);
        }

        return $layout;
    }

    /**
     * @param string|array<string,mixed>|Element|LayoutData $data
     */
    public function inflateLayoutData(
        string|array|Element|LayoutData $data
    ): LayoutData {
        if ($data instanceof LayoutData) {
            return $data;
        }

        if (is_string($data)) {
            if (str_starts_with($data, '<')) {
                $data = Element::fromXmlString($data);
            } elseif (str_starts_with($data, '{')) {
                /** @var array<string,mixed> $decoded */
                $decoded = Coercion::asArray(json_decode($data, true));
                return LayoutData::from($decoded);
            } else {
                throw Exceptional::UnexpectedValue(
                    message: 'Invalid layout data string',
                    data: $data,
                );
            }
        }

        if (is_array($data)) {
            return LayoutData::from($data);
        }

        return $this->translateXmlToLayoutData($data);
    }

    public function translateXmlToLayoutData(
        Element $element
    ): LayoutData {
        $name = $element->getTagName();

        if ($name === 'layout') {
            $type = $element->getAttribute('type');

            if (empty($type)) {
                throw Exceptional::UnexpectedValue(
                    message: 'Layout type not found in element',
                    data: $element,
                );
            }

            $areas = [];

            foreach ($element->getChildrenOfType('area') as $areaElement) {
                $areas[] = $this->translateXmlToAreaData($areaElement);
            }

            return new LayoutData(
                type: $type,
                areas: $areas
            );
        }

        if (null === ($translatorClass = $this->archetype->tryResolve(LayoutXmlTranslator::class, ucfirst($name)))) {
            throw Exceptional::UnexpectedValue(
                message: 'Layout translator class not found for type: ' . $name,
                data: $name,
            );
        }

        return $translatorClass::readXml($element, $this->translateXmlToAreaData(...));
    }

    public function validateLayout(
        Layout $layout
    ): LayoutValidationResult {
        $areaErrors = [];
        $valid = true;

        // Get area strategies from layout definition
        $areaStrategies = $layout::defineAreas();

        foreach ($layout->areas as $area) {
            // If no strategy defined for this area, skip validation (anything goes)
            if (!isset($areaStrategies[$area->id])) {
                continue;
            }

            $strategy = $areaStrategies[$area->id];
            $errors = [];

            // Check min blocks
            if (count($area->blocks) < $strategy->minBlocks) {
                $errors[] = new ValidationError(
                    areaId: $area->id,
                    blockIndex: null,
                    rule: 'minBlocks',
                    message: sprintf(
                        'Area "%s" requires at least %d block%s but only has %d',
                        $area->id,
                        $strategy->minBlocks,
                        $strategy->minBlocks === 1 ? '' : 's',
                        count($area->blocks)
                    ),
                );
            }

            // Check max blocks
            if (count($area->blocks) > $strategy->maxBlocks) {
                $errors[] = new ValidationError(
                    areaId: $area->id,
                    blockIndex: null,
                    rule: 'maxBlocks',
                    message: sprintf(
                        'Area "%s" allows a maximum of %d block%s but has %d',
                        $area->id,
                        $strategy->maxBlocks,
                        $strategy->maxBlocks === 1 ? '' : 's',
                        count($area->blocks)
                    ),
                );
            }

            // Validate individual blocks
            foreach ($area->blocks as $index => $block) {
                $blockType = $block::defineTypeName();
                $blockRef = new BlockReference($block::class);

                // Check block blacklist
                if (
                    !empty($strategy->blockBlacklist) &&
                    in_array($blockType, $strategy->blockBlacklist)
                ) {
                    $errors[] = new ValidationError(
                        areaId: $area->id,
                        blockIndex: $index,
                        rule: 'blockBlacklist',
                        message: sprintf(
                            'Block type "%s" is not allowed in area "%s"',
                            $blockType,
                            $area->id
                        ),
                    );
                }

                // Check index blacklist
                if (
                    isset($strategy->indexBlacklist[$index]) &&
                    in_array($blockType, $strategy->indexBlacklist[$index])
                ) {
                    $errors[] = new ValidationError(
                        areaId: $area->id,
                        blockIndex: $index,
                        rule: 'indexBlacklist',
                        message: sprintf(
                            'Block type "%s" is not allowed at position %d in area "%s"',
                            $blockType,
                            $index,
                            $area->id
                        ),
                    );
                }

                // Check allowed collections
                if (!empty($strategy->allowedCollections)) {
                    $blockCollections = $blockRef->collectionTypeNames;
                    $hasAllowedCollection = false;

                    foreach ($blockCollections as $collection) {
                        if (in_array($collection, $strategy->allowedCollections)) {
                            $hasAllowedCollection = true;
                            break;
                        }
                    }

                    if (!$hasAllowedCollection) {
                        $errors[] = new ValidationError(
                            areaId: $area->id,
                            blockIndex: $index,
                            rule: 'allowedCollections',
                            message: sprintf(
                                'Block type "%s" must belong to one of these collections: %s',
                                $blockType,
                                implode(', ', $strategy->allowedCollections)
                            ),
                        );
                    }
                }

                // Check allowed categories
                if (!empty($strategy->allowedCategories)) {
                    $blockCategories = $blockRef->categoryTypeNames;
                    $hasAllowedCategory = false;

                    foreach ($blockCategories as $category) {
                        if (in_array($category, $strategy->allowedCategories)) {
                            $hasAllowedCategory = true;
                            break;
                        }
                    }

                    if (!$hasAllowedCategory) {
                        $errors[] = new ValidationError(
                            areaId: $area->id,
                            blockIndex: $index,
                            rule: 'allowedCategories',
                            message: sprintf(
                                'Block type "%s" must belong to one of these categories: %s',
                                $blockType,
                                implode(', ', $strategy->allowedCategories)
                            ),
                        );
                    }
                }
            }

            if (!empty($errors)) {
                $areaErrors[$area->id] = $errors;
                $valid = false;
            }
        }

        return new LayoutValidationResult(
            valid: $valid,
            areaErrors: $areaErrors,
        );
    }
}
