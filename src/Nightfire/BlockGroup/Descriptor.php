<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\BlockGroup;

use DecodeLabs\Nightfire\BlockReference;

interface Descriptor
{
    public string $id { get; }
    public string $name { get; }
    public int $weight { get; }

    public static function acceptsBlock(
        BlockReference $blockReference
    ): bool;
}
