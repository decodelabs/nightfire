<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Category;

use DecodeLabs\Nightfire\Category;
use DecodeLabs\Nightfire\CategoryTrait;

class Uncategorized implements Category
{
    use CategoryTrait;

    public string $name { get => 'Uncategorized'; }
    public int $weight { get => 1000; }
}
