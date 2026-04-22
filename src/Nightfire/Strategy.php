<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

interface Strategy extends TypeNameProvider
{
    public ?int $maxBlocks { get; }
    public ?int $minBlocks { get; }

    /**
     * @var list<string>
     */
    public ?array $allowedCollections { get; }

    /**
     * @var list<string>
     */
    public ?array $allowedCategories { get; }

    /**
     * @var list<string>
     */
    public ?array $blockBlacklist { get; }

    /**
     * @var array<int,list<string>>
     */
    public ?array $indexBlacklist { get; }

    /**
     * Validate an area against this strategy.
     *
     * @return list<ValidationError>
     */
    public function validate(
        Area $area,
        string $areaId
    ): array;
}
