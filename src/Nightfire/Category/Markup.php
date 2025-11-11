<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Category;

use DecodeLabs\Nightfire\Category;
use DecodeLabs\Nightfire\CategoryTrait;

class Markup implements Category
{
    use CategoryTrait;

    public string $id { get => 'markup'; }
    public string $name { get => 'Markup editors'; }
    public int $weight { get => 1; }
}
