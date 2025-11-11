<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Exceptional;

/**
 * @phpstan-require-implements Data
 */
trait DataTrait
{
    public string $hash {
        get => $this->hash ??= $this->generateHash();
    }

    private function generateHash(): string
    {
        if (false === ($json = json_encode($this->getHashableData()))) {
            throw Exceptional::UnexpectedValue(
                message: 'Failed to encode data',
                data: $this->getHashableData(),
            );
        }

        return hash('xxh3', $json);
    }

    /**
     * @return array<mixed>
     */
    abstract private function getHashableData(): array;

    public function checkHash(): bool
    {
        return $this->hash === $this->generateHash();
    }
}
