<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Exceptional;
use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Data\Block as BlockData;
use ReflectionClass;

use function array_first;

/**
 * @phpstan-require-implements Block
 */
trait BlockTrait
{
    private const string DefaultVersion = 'initial';

    public static function defineTypeName(): string
    {
        $output = static::TypeName;

        if ($output === '') {
            $class = static::class;

            if (str_contains($class, '\\Block\\')) {
                $parts = explode('\\Block\\', $class);
                $output = array_pop($parts);
            } else {
                $output = new ReflectionClass(static::class)->getShortName();
            }
        }

        return $output;
    }

    public static function defineTypeWeight(): int
    {
        return static::TypeWeight;
    }

    public static function defineActiveVersion(): string
    {
        $versions = static::Versions;

        if (empty($versions)) {
            return self::DefaultVersion;
        }

        return array_first($versions);
    }

    /**
     * @return list<string>
     */
    public static function defineCategoryTypeNames(): array
    {
        $output = static::Categories;

        if (empty($output)) {
            $output = ['Uncategorized'];
        }

        return $output;
    }

    public function export(): BlockData
    {
        return new BlockData(
            type: static::defineTypeName(),
            version: static::defineActiveVersion(),
            data: $this->__serialize(),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->export()->jsonSerialize();
    }

    public function __toString(): string
    {
        $blockData = $this->export();

        if (!$this instanceof XmlTranslator) {
            $output = json_encode($blockData);

            if ($output === false) {
                throw Exceptional::UnexpectedValue(
                    message: 'Failed to encode block data',
                    data: $blockData,
                );
            }

            return $output;
        }

        $writer = Writer::create();

        $writer->startElement('block', [
            'type' => $blockData->type,
            'version' => $blockData->version,
            'hash' => $blockData->hash,
        ]);

        $this::writeXml($writer, $blockData->data);

        $writer->endElement();
        return $writer->__toString();
    }
}
