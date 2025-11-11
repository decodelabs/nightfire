<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

namespace DecodeLabs\Nightfire\Area;

use Closure;
use DecodeLabs\Exemplar\Element;
use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Area;
use DecodeLabs\Nightfire\Data\Area as AreaData;
use DecodeLabs\Nightfire\Data\Block as BlockData;

interface XmlTranslator
{
    /**
     * @param Closure(Element):BlockData $blockInflater
     */
    public static function readXml(
        Element $element,
        Closure $blockInflater
    ): AreaData;

    public static function writeXml(
        Writer $writer,
        Area $area
    ): void;
}
