<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;

class BlockReference implements Dumpable
{
    public string $type {
        get => $this->class::defineTypeName();
    }

    public int $weight {
        get => $this->class::defineTypeWeight();
    }

    public string $version {
        get => $this->class::defineActiveVersion();
    }

    /**
     * @var list<string>
     */
    public array $categoryTypeNames {
        get => $this->class::defineCategoryTypeNames();
    }

    /**
     * @var list<string>
     */
    public array $collectionTypeNames {
        get => $this->class::defineCollectionTypeNames();
    }

    public function __construct(
        public readonly string $class,
    ) {
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->itemName = $this->type;

        $entity->meta = [
            'type' => $this->type,
            'class' => $this->class,
            'weight' => $this->weight,
            'version' => $this->version,
            'categoryTypeNames' => $this->categoryTypeNames,
        ];

        return $entity;
    }
}
