<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Data\Layout as LayoutData;
use DecodeLabs\Nightfire\Layout\XmlTranslator;

/**
 * @extends DataInterchange<LayoutData>
 */
interface Layout extends
    DataInterchange,
    TypeNameProvider
{
    public const string TypeName = '';

    /**
     * @var array<string,Area>
     */
    public array $areas { get; }

    /**
     * Define the areas that belong to this layout and their strategies.
     *
     * @return array<string,Strategy>
     */
    public static function defineAreas(): array;

    public function addArea(
        Area $area
    ): void;

    public function hasArea(
        string|Area $area
    ): bool;

    public function removeArea(
        string|Area $area
    ): void;

    public function export(): LayoutData;

    public function deflateToJson(): string;

    public function deflateToXml(
        ?Writer $writer = null,
        ?XmlTranslator $translator = null
    ): string;
}
