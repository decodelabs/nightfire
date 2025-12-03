<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Layout;

use Closure;
use DecodeLabs\Exemplar\Element;
use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Data\Area as AreaData;
use DecodeLabs\Nightfire\Data\Layout as LayoutData;
use DecodeLabs\Nightfire\Layout;

interface XmlTranslator
{
    /**
     * @param Closure(Element):AreaData $areaInflater
     */
    public static function readXml(
        Element $element,
        Closure $areaInflater
    ): LayoutData;

    public static function writeXml(
        Writer $writer,
        Layout $layout
    ): void;
}
