<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

interface Strategy
{
    public const string TypeName = '';

    public int $maxBlocks { get; }

    public static function defineTypeName(): string;
}
