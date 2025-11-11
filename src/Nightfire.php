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
use DecodeLabs\Nightfire\BlockReference;
use DecodeLabs\Nightfire\Category;
use DecodeLabs\Nightfire\Category\Uncategorized;
use DecodeLabs\Nightfire\Data\Area as AreaData;
use DecodeLabs\Nightfire\Data\Block as BlockData;
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
                return BlockData::from(
                    Coercion::asArray(json_decode($data, true))
                );
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
                return AreaData::from(
                    Coercion::asArray(json_decode($data, true))
                );
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



    public function loadCategory(
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
     * @return array<string,Category>
     */
    public function getBlockSelectionList(
        iterable $blocks
    ): array {
        $output = $categories = [];

        foreach ($blocks as $block) {
            if ($block instanceof BlockReference) {
                $categoryNames = $block->categoryTypeNames;
            } else {
                $categoryNames = $block::defineCategoryTypeNames();
            }

            foreach ($categoryNames as $catName) {
                if (isset($categories[$catName])) {
                    $categories[$catName]->addBlock($block);
                    continue;
                }

                $category = $this->loadCategory($catName) ?? new Uncategorized();
                $category->addBlock($block);
                $categories[$catName] = $category;
            }
        }

        uasort($categories, fn (Category $a, Category $b): int => $a->weight <=> $b->weight);

        foreach ($categories as $category) {
            $output[$category->id] = $category;
        }

        return $output;
    }
}
