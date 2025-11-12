<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Category;

use DecodeLabs\Nightfire\Category;
use DecodeLabs\Nightfire\CategoryTrait;

class Document implements Category
{
    use CategoryTrait;

    public string $name { get => 'Document structure'; }
    public int $weight { get => 20; }
}
