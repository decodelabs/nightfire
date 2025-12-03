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

final class Layout implements Data
{
    use DataTrait;

    /**
     * @param array<string,mixed> $data
     */
    public static function from(
        array $data
    ): static {
        $type = $data['l'] ?? $data['layout'] ?? null;
        $areas = $data['ax'] ?? $data['areas'] ?? null;

        if (
            $type === null ||
            $areas === null
        ) {
            throw Exceptional::UnexpectedValue(
                message: 'Missing required layout data',
                data: $data,
            );
        }

        $areaList = [];

        foreach (Coercion::asArray($areas) as $area) {
            /** @var array<string,mixed> $areaData */
            $areaData = Coercion::asArray($area);
            $areaList[] = Area::from($areaData);
        }

        return new static(
            type: Coercion::asString($type),
            areas: $areaList,
        );
    }

    /**
     * @param array<string,Area>|list<Area> $areas
     */
    public function __construct(
        public readonly string $type,
        public readonly array $areas,
    ) {
    }

    /**
     * @return array<mixed>
     */
    private function getHashableData(): array
    {
        return $this->areas;
    }

    /**
     * @return array{
     *   l:string,
     *   ax:list<array{
     *     a:string,
     *     bx:list<array{
     *       b:string,
     *       v:string,
     *       h:string,
     *       d:array<string,mixed>
     *     }>
     *   }>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'l' => $this->type,
            'ax' => array_values(array_map(
                fn (Area $area) => $area->jsonSerialize(),
                $this->areas,
            )),
        ];
    }
}
