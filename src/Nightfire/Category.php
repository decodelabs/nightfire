<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Nuance\Dumpable;

interface Category extends Dumpable
{
    public const string TypeName = '';

    public string $id { get; }
    public string $name { get; }
    public int $weight { get; }

    /**
     * @var array<string,BlockReference>
     */
    public array $blocks { get; }

    public static function defineTypeName(): string;
    public function __construct();

    public function addBlock(
        Block|BlockReference $block
    ): void;
}
