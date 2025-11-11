<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use JsonSerializable;

interface Data extends JsonSerializable
{
    public string $hash { get; }

    /**
     * @param array<string,mixed> $data
     */
    public static function from(
        array $data
    ): static;

    public function checkHash(): bool;

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array;
}
