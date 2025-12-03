<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Exceptional;
use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Data\Layout as LayoutData;
use DecodeLabs\Nightfire\Layout\XmlTranslator;
use ReflectionClass;

/**
 * @phpstan-require-implements Layout
 */
trait LayoutTrait
{
    /**
     * @var array<string,Area>
     */
    public protected(set) array $areas = [];

    public static function defineTypeName(): string
    {
        $output = static::TypeName;

        if ($output === '') {
            $class = static::class;

            if (str_contains($class, '\\Layout\\')) {
                $parts = explode('\\Layout\\', $class);
                $output = array_pop($parts);
            } else {
                $output = new ReflectionClass(static::class)->getShortName();
            }
        }

        return $output;
    }

    public function addArea(
        Area $area
    ): void {
        $this->areas[$area->id] = $area;
    }

    public function hasArea(
        string|Area $area
    ): bool {
        $id = is_string($area) ? $area : $area->id;
        return isset($this->areas[$id]);
    }

    public function removeArea(
        string|Area $area
    ): void {
        $id = is_string($area) ? $area : $area->id;
        unset($this->areas[$id]);
    }

    public function export(): LayoutData
    {
        return new LayoutData(
            type: static::defineTypeName(),
            areas: array_values(array_map(fn (Area $area) => $area->export(), $this->areas)),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->export()->jsonSerialize();
    }

    public function deflateToJson(): string
    {
        $layoutData = $this->export();
        $output = json_encode($layoutData);

        if ($output === false) {
            throw Exceptional::UnexpectedValue(
                message: 'Failed to encode layout data',
                data: $layoutData,
            );
        }

        return $output;
    }

    public function deflateToXml(
        ?Writer $writer = null,
        ?XmlTranslator $translator = null
    ): string {
        $writer ??= Writer::create();

        if ($translator !== null) {
            $translator::writeXml($writer, $this);
        } else {
            $writer->startElement('layout', [
                'type' => static::defineTypeName(),
            ]);

            foreach ($this->areas as $area) {
                $area->deflateToXml($writer);
            }

            $writer->endElement();
        }

        return $writer->__toString();
    }
}
