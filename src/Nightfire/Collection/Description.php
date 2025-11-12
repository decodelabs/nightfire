<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Collection;

use DecodeLabs\Nightfire\BlockReference;
use DecodeLabs\Nightfire\Collection;
use DecodeLabs\Nightfire\CollectionTrait;

class Description implements Collection
{
    use CollectionTrait;

    public string $name {
        get => 'Descriptive text markup blocks';
    }

    public int $weight {
        get => 5;
    }

    public static function acceptsBlock(
        BlockReference $blockReference
    ): bool {
        return in_array('Markup', $blockReference->categoryTypeNames);
    }
}
