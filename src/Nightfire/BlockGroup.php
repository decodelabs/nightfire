<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Exceptional;
use DecodeLabs\Nightfire\BlockGroup\Descriptor as BlockGroupDescriptor;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;

/**
 * @template TDescriptor of BlockGroupDescriptor
 */
class BlockGroup implements Dumpable
{
    public string $id {
        get => $this->descriptor->id;
    }

    public string $name {
        get => $this->descriptor->name;
    }

    public int $weight {
        get => $this->descriptor->weight;
    }

    /**
     * @var array<string,BlockReference>
     */
    public array $blocks {
        get {
            if (!$this->sorted) {
                uasort(
                    $this->rawBlocks,
                    fn (
                        BlockReference $a,
                        BlockReference $b
                    ): int => $a->weight <=> $b->weight
                );

                $this->sorted = true;
            }

            return $this->rawBlocks;
        }
    }

    /**
     * @var array<string,BlockReference>
     */
    private array $rawBlocks = [];

    private bool $sorted = false;


    /**
     * @param TDescriptor $descriptor
     */
    public function __construct(
        public readonly BlockGroupDescriptor $descriptor,
    ) {
    }

    public function add(
        Block|BlockReference $block
    ): void {
        if ($block instanceof Block) {
            $block = new BlockReference($block::class);
        }

        if (!$this->descriptor::acceptsBlock($block)) {
            throw Exceptional::UnexpectedValue(
                message: 'Block not accepted by group',
                data: $block,
            );
        }

        $this->rawBlocks[$block->type] = $block;
        $this->sorted = false;
    }

    public function has(
        string|Block|BlockReference $block
    ): bool {
        if ($block instanceof Block) {
            $block = $block::defineTypeName();
        } elseif ($block instanceof BlockReference) {
            $block = $block->type;
        }

        return isset($this->rawBlocks[$block]);
    }

    public function remove(
        string|Block|BlockReference $block
    ): void {
        if ($block instanceof Block) {
            $block = $block::defineTypeName();
        } elseif ($block instanceof BlockReference) {
            $block = $block->type;
        }

        unset($this->rawBlocks[$block]);
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->values = $this->blocks;

        $entity->setProperty(
            name: 'descriptor',
            value: $this->descriptor,
            readOnly: true,
        );

        return $entity;
    }
}
