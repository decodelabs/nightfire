<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;

class LayoutReference implements Dumpable
{
    public string $type {
        get => $this->class::defineTypeName();
    }

    /**
     * @var array<string,Strategy>
     */
    public array $areaStrategies {
        get => $this->class::defineAreas();
    }

    /**
     * @param class-string<Layout> $class
     */
    public function __construct(
        public readonly string $class
    ) {
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->itemName = $this->type;

        $entity->meta = [
            'type' => $this->type,
            'class' => $this->class,
            'areaStrategies' => array_map(
                fn (Strategy $strategy) => $strategy::defineTypeName(),
                $this->areaStrategies
            ),
        ];

        return $entity;
    }
}
