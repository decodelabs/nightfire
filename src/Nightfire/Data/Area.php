<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Data;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Nightfire\Data;
use DecodeLabs\Nightfire\DataTrait;

final class Area implements Data
{
    use DataTrait;

    /**
     * @param array<string,mixed> $data
     */
    public static function from(
        array $data
    ): static {
        $id = $data['a'] ?? $data['area'] ?? null;
        $blocks = $data['bx'] ?? $data['blocks'] ?? null;

        if (
            $id === null ||
            $blocks === null
        ) {
            throw Exceptional::UnexpectedValue(
                message: 'Missing required area data',
                data: $data,
            );
        }

        $blockList = [];

        foreach (Coercion::asArray($blocks) as $block) {
            $blockList[] = Block::from(
                Coercion::asArray($block)
            );
        }

        return new static(
            id: Coercion::asString($id),
            blocks: $blockList,
        );
    }

    /**
     * @param list<Block> $blocks
     */
    public function __construct(
        public readonly string $id,
        public readonly array $blocks,
    ) {
    }

    /**
     * @return array<mixed>
     */
    private function getHashableData(): array
    {
        return $this->blocks;
    }

    /**
     * @return array{
     *   a:string,
     *   bx:list<array{
     *     b:string,
     *     v:string,
     *     h:string,
     *     d:array<string,mixed>
     *   }>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'a' => $this->id,
            'bx' => array_map(
                fn (Block $block) => $block->jsonSerialize(),
                $this->blocks,
            ),
        ];
    }
}
