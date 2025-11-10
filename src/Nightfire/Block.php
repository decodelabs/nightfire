<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Tagged\Markup;
use JsonSerializable;
use Stringable;

interface Block extends JsonSerializable, Stringable
{
    public const string TypeName = '';

    /**
     * @var list<string>
     */
    public const array Versions = [
        'initial'
    ];

    public static function getTypeName(): string;
    public static function getActiveVersion(): string;

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

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array;


    public function render(): ?Markup;
}
