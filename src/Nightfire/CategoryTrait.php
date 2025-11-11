<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
use ReflectionClass;

use function array_pop;
use function str_contains;

/**
 * @phpstan-require-implements Category
 */
trait CategoryTrait
{
    /**
     * @var array<string,BlockReference>
     */
    public array $blocks {
        get {
            if (!$this->sorted) {
                uasort($this->rawBlocks, fn (BlockReference $a, BlockReference $b): int => $a->weight <=> $b->weight);
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

    public static function defineTypeName(): string
    {
        $output = static::TypeName;

        if ($output === '') {
            $class = static::class;

            if (str_contains($class, '\\Category\\')) {
                $parts = explode('\\Category\\', $class);
                $output = array_pop($parts);
            } else {
                $output = new ReflectionClass(static::class)->getShortName();
            }
        }

        return $output;
    }

    public function __construct()
    {
    }

    public function addBlock(
        Block|BlockReference $block
    ): void {
        if ($block instanceof Block) {
            $block = new BlockReference($block::class);
        }

        $this->rawBlocks[$block->type] = $block;
        $this->sorted = false;
    }

    public function hasBlock(
        string|Block|BlockReference $block
    ): bool {
        if ($block instanceof Block) {
            $block = $block::defineTypeName();
        } elseif ($block instanceof BlockReference) {
            $block = $block->type;
        }

        return isset($this->rawBlocks[$block]);
    }

    public function removeBlock(
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
        $entity->meta = [
            'id' => $this->id,
            'name' => $this->name,
            'weight' => $this->weight,
            'type' => $this->defineTypeName(),
        ];

        return $entity;
    }
}
