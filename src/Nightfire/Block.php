<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Nightfire\Data\Block as BlockData;
use DecodeLabs\Tagged\Markup;

/**
 * @extends DataInterchange<BlockData>
 */
interface Block extends DataInterchange
{
    public const string TypeName = '';
    public const int TypeWeight = 0;

    /**
     * @var list<string>
     */
    public const array Versions = [
        'initial'
    ];

    /**
     * @var list<string>
     */
    public const array Categories = [];

    public static function defineTypeName(): string;
    public static function defineTypeWeight(): int;
    public static function defineActiveVersion(): string;

    /**
     * @return list<string>
     */
    public static function defineCategoryTypeNames(): array;

    /**
     * @return array<string,mixed>
     */
    public function __serialize(): array;

    /**
     * @param array<string,mixed> $data
     */
    public function __unserialize(
        array $data
    ): void;

    public function export(): BlockData;

    public function render(): ?Markup;
}
