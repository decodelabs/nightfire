<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Category;

use DecodeLabs\Nightfire\Category;
use DecodeLabs\Nightfire\CategoryTrait;

class Media implements Category
{
    use CategoryTrait;

    public string $id { get => 'media'; }
    public string $name { get => 'Media'; }
    public int $weight { get => 10; }
}
