<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

interface TypeNameProvider
{
    public const string TypeName = '';

    public static function defineTypeName(): string;
}
