<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Layout;

use Closure;
use DecodeLabs\Coercion;
use DecodeLabs\Exemplar\Element;
use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Data\Area as AreaData;
use DecodeLabs\Nightfire\Data\Layout as LayoutData;
use DecodeLabs\Nightfire\Layout;
use DecodeLabs\Nightfire\LayoutTrait;
use DecodeLabs\Nightfire\Strategy;
use DecodeLabs\Nightfire\Strategy\Generic as GenericStrategy;

class Generic implements Layout, XmlTranslator
{
    use LayoutTrait;

    public const string TypeName = 'Generic';

    /**
     * @return array<string,Strategy>
     */
    public static function defineAreas(): array
    {
        return [
            'primary' => new GenericStrategy(),
        ];
    }

    /**
     * @param Closure(Element):AreaData $areaInflater
     */
    public static function readXml(
        Element $element,
        Closure $areaInflater
    ): LayoutData {
        $type = $element->getAttribute('type') ?? static::TypeName;

        $areas = [];

        foreach ($element->getChildrenOfType('area') as $areaElement) {
            $areas[] = $areaInflater($areaElement);
        }

        return new LayoutData(
            type: $type,
            areas: $areas,
        );
    }

    public static function writeXml(
        Writer $writer,
        Layout $layout
    ): void {
        $writer->startElement('layout', [
            'type' => $layout::defineTypeName(),
        ]);

        foreach ($layout->areas as $area) {
            $area->deflateToXml($writer);
        }

        $writer->endElement();
    }
}
