<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Strategy;

use DecodeLabs\Nightfire\Strategy;
use DecodeLabs\Nightfire\StrategyTrait;

class Generic implements Strategy
{
    use StrategyTrait;

    public const string TypeName = 'Generic';

    public ?int $maxBlocks = null;
    public ?int $minBlocks = null;

    /**
     * @var ?list<string>
     */
    public ?array $allowedCollections = null;

    /**
     * @var ?list<string>
     */
    public ?array $allowedCategories = null;

    /**
     * @var ?list<string>
     */
    public ?array $blockBlacklist = null;

    /**
     * @var ?array<int,list<string>>
     */
    public ?array $indexBlacklist = null;

    public static function defineTypeName(): string
    {
        return static::TypeName;
    }
}
