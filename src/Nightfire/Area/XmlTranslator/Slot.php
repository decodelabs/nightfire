<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

namespace DecodeLabs\Nightfire\Area\XmlTranslator;

use Closure;
use DecodeLabs\Coercion;
use DecodeLabs\Exemplar\Element;
use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Area;
use DecodeLabs\Nightfire\Area\XmlTranslator;
use DecodeLabs\Nightfire\Data\Area as AreaData;

class Slot implements XmlTranslator
{
    public static function readXml(
        Element $element,
        Closure $blockInflater
    ): AreaData {
        $blocks = [];

        foreach ($element->getChildrenOfType('block') as $blockElement) {
            $blocks[] = $blockInflater($blockElement);
        }

        return new AreaData(
            id: Coercion::tryString($element->getAttribute('id')) ?? 'primary',
            blocks: $blocks
        );
    }

    public static function writeXml(
        Writer $writer,
        Area $area
    ): void {
        $writer->startElement('slot', [
            'id' => $area->id,
        ]);

        foreach ($area->blocks as $block) {
            $block->deflateToXml($writer);
        }

        $writer->endElement();
    }
}
