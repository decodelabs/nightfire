<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

namespace DecodeLabs\Nightfire;

use DecodeLabs\Exemplar\Writer;
use JsonSerializable;

/**
 * @template T of Data
 */
interface DataInterchange extends JsonSerializable
{
    /**
     * @return T
     */
    public function export(): Data;

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array;
    public function deflateToJson(): string;

    public function deflateToXml(
        ?Writer $writer = null
    ): string;
}
