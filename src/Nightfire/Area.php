<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Exceptional;
use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Area\XmlTranslator as AreaXmlTranslator;
use DecodeLabs\Nightfire\Data\Area as AreaData;

/**
 * @implements DataInterchange<AreaData>
 */
class Area implements DataInterchange
{
    /**
     * @var list<Block>
     */
    public protected(set) array $blocks = [];

    /**
     * @param ?list<Block> $blocks
     */
    public function __construct(
        public readonly string $id,
        ?array $blocks = null,
    ) {
        if ($blocks !== null) {
            foreach ($blocks as $block) {
                $this->addBlock($block);
            }
        }
    }

    public function addBlock(
        Block $block
    ): void {
        $this->blocks[] = $block;
    }

    public function hasBlock(
        Block $block
    ): bool {
        return in_array($block, $this->blocks, true);
    }

    public function removeBlock(
        Block $block
    ): void {
        $this->blocks = array_values(array_filter($this->blocks, fn (Block $b) => $b !== $block));
    }



    public function export(): AreaData
    {
        return new AreaData(
            id: $this->id,
            blocks: array_map(fn (Block $block) => $block->export(), $this->blocks),
        );
    }


    public function jsonSerialize(): array
    {
        return $this->export()->jsonSerialize();
    }

    public function deflateToJson(): string
    {
        $areaData = $this->export();
        $output = json_encode($areaData);

        if ($output === false) {
            throw Exceptional::UnexpectedValue(
                message: 'Failed to encode area data',
                data: $areaData,
            );
        }

        return $output;
    }

    public function deflateToXml(
        ?Writer $writer = null,
        ?AreaXmlTranslator $translator = null
    ): string {
        $writer ??= Writer::create();

        if ($translator !== null) {
            $translator::writeXml($writer, $this);
        } else {
            $writer->startElement('area', [
                'id' => $this->id,
            ]);

            foreach ($this->blocks as $block) {
                $block->deflateToXml($writer);
            }

            $writer->endElement();
        }

        return $writer->__toString();
    }
}
