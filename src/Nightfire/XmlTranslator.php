<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Exemplar\Element;
use DecodeLabs\Exemplar\Writer;

interface XmlTranslator
{
    /**
     * @return array<string,mixed>
     */
    public static function readXml(
        Element $element
    ): array;

    /**
     * @param array<string,mixed> $data
     */
    public static function writeXml(
        Writer $writer,
        array $data
    ): void;
}
