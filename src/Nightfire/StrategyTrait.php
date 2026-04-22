<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

/**
 * @phpstan-require-implements Strategy
 */
trait StrategyTrait
{
    /**
     * Validate an area against this strategy.
     *
     * @return list<ValidationError>
     */
    public function validate(
        Area $area,
        string $areaId
    ): array {
        $errors = [];
        $blockCount = count($area->blocks);

        // Check min blocks
        if ($this->minBlocks !== null && $blockCount < $this->minBlocks) {
            $errors[] = new ValidationError(
                areaId: $areaId,
                blockIndex: null,
                rule: 'minBlocks',
                message: sprintf(
                    'Area "%s" requires at least %d block%s but only has %d',
                    $areaId,
                    $this->minBlocks,
                    $this->minBlocks === 1 ? '' : 's',
                    $blockCount
                ),
            );
        }

        // Check max blocks
        if ($this->maxBlocks !== null && $blockCount > $this->maxBlocks) {
            $errors[] = new ValidationError(
                areaId: $areaId,
                blockIndex: null,
                rule: 'maxBlocks',
                message: sprintf(
                    'Area "%s" allows a maximum of %d block%s but has %d',
                    $areaId,
                    $this->maxBlocks,
                    $this->maxBlocks === 1 ? '' : 's',
                    $blockCount
                ),
            );
        }

        // Validate individual blocks
        foreach ($area->blocks as $index => $block) {
            $blockType = $block::defineTypeName();
            $blockRef = new BlockReference($block::class);

            // Check block blacklist
            if (
                !empty($this->blockBlacklist) &&
                in_array($blockType, $this->blockBlacklist)
            ) {
                $errors[] = new ValidationError(
                    areaId: $areaId,
                    blockIndex: $index,
                    rule: 'blockBlacklist',
                    message: sprintf(
                        'Block type "%s" is not allowed in area "%s"',
                        $blockType,
                        $areaId
                    ),
                );
            }

            // Check index blacklist
            if (
                isset($this->indexBlacklist[$index]) &&
                in_array($blockType, $this->indexBlacklist[$index])
            ) {
                $errors[] = new ValidationError(
                    areaId: $areaId,
                    blockIndex: $index,
                    rule: 'indexBlacklist',
                    message: sprintf(
                        'Block type "%s" is not allowed at position %d in area "%s"',
                        $blockType,
                        $index,
                        $areaId
                    ),
                );
            }

            // Check allowed collections
            if (!empty($this->allowedCollections)) {
                $blockCollections = $blockRef->collectionTypeNames;
                $hasAllowedCollection = false;

                foreach ($blockCollections as $collection) {
                    if (in_array($collection, $this->allowedCollections)) {
                        $hasAllowedCollection = true;
                        break;
                    }
                }

                if (!$hasAllowedCollection) {
                    $errors[] = new ValidationError(
                        areaId: $areaId,
                        blockIndex: $index,
                        rule: 'allowedCollections',
                        message: sprintf(
                            'Block type "%s" must belong to one of these collections: %s',
                            $blockType,
                            implode(', ', $this->allowedCollections)
                        ),
                    );
                }
            }

            // Check allowed categories
            if (!empty($this->allowedCategories)) {
                $blockCategories = $blockRef->categoryTypeNames;
                $hasAllowedCategory = false;

                foreach ($blockCategories as $category) {
                    if (in_array($category, $this->allowedCategories)) {
                        $hasAllowedCategory = true;
                        break;
                    }
                }

                if (!$hasAllowedCategory) {
                    $errors[] = new ValidationError(
                        areaId: $areaId,
                        blockIndex: $index,
                        rule: 'allowedCategories',
                        message: sprintf(
                            'Block type "%s" must belong to one of these categories: %s',
                            $blockType,
                            implode(', ', $this->allowedCategories)
                        ),
                    );
                }
            }
        }

        return $errors;
    }
}
