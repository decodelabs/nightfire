<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
use ReflectionClass;

use function array_pop;
use function explode;
use function str_contains;

/**
 * @phpstan-require-implements Collection
 */
trait CollectionTrait
{
    public string $id {
        get => $this->defineTypeName();
    }

    public static function defineTypeName(): string
    {
        $output = static::TypeName;

        if ($output === '') {
            $class = static::class;

            if (str_contains($class, '\\Collection\\')) {
                $parts = explode('\\Collection\\', $class);
                $output = array_pop($parts);
            } else {
                $output = new ReflectionClass(static::class)->getShortName();
            }
        }

        return $output;
    }

    public function __construct()
    {
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->meta = [
            'name' => $this->name,
            'type' => $this->defineTypeName(),
        ];

        return $entity;
    }
}
