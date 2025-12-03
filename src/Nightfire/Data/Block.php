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

final class Block implements Data
{
    use DataTrait;

    /**
     * @param array<string,mixed> $data
     */
    public static function from(
        array $data
    ): static {
        $type = $data['b'] ?? $data['block'] ?? null;
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

        /** @var array<string,mixed> $dataArray */
        $dataArray = Coercion::asArray($data);

        return new static(
            type: Coercion::asString($type),
            version: Coercion::asString($version),
            hash: Coercion::asString($hash),
            data: $dataArray,
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

    /**
     * @return array<mixed>
     */
    private function getHashableData(): array
    {
        return $this->data;
    }

    /**
     * @return array{
     *   b:string,
     *   v:string,
     *   h:string,
     *   d:array<string,mixed>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'b' => $this->type,
            'v' => $this->version,
            'h' => $this->hash,
            'd' => $this->data,
        ];
    }
}
