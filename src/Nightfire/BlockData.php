<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use JsonSerializable;

final class BlockData implements JsonSerializable
{
    public string $hash {
        get {
            if (!isset($this->hash)) {
                $this->hash = $this->generateHash();
            }

            return $this->hash;
        }
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function from(
        array $data
    ): static {
        $type = $data['t'] ?? $data['type'] ?? null;
        $version = $data['v'] ?? $data['version'] ?? null;
        $hash = $data['h'] ?? $data['hash'] ?? null;
        $data = $data['d'] ?? $data['data'] ?? null;

        if (
            $type === null ||
            $version === null ||
            $hash === null ||
            $data === null
        ) {
            throw Exceptional::UnexpectedValue(
                message: 'Missing required block data',
                data: $data,
            );
        }

        return new static(
            type: Coercion::asString($type),
            version: Coercion::asString($version),
            hash: Coercion::asString($hash),
            data: Coercion::asArray($data),
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(
        public readonly string $type,
        public readonly string $version,
        public readonly array $data,
        ?string $hash = null,
    ) {
        if ($hash !== null) {
            $this->hash = $hash;
        }
    }

    private function generateHash(): string
    {
        if (false === ($json = json_encode($this->data))) {
            throw Exceptional::UnexpectedValue(
                message: 'Failed to encode block data',
                data: $this->data,
            );
        }

        return hash('xxh3', $json);
    }

    public function checkHash(): bool
    {
        return $this->hash === $this->generateHash();
    }

    /**
     * @return array{
     *   t:string,
     *   v:string,
     *   h:string,
     *   d:array<string,mixed>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            't' => $this->type,
            'v' => $this->version,
            'h' => $this->hash,
            'd' => $this->data,
        ];
    }
}
