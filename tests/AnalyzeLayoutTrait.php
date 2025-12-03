<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Tests;

use DecodeLabs\Nightfire\Layout;
use DecodeLabs\Nightfire\LayoutTrait;

class AnalyzeLayoutTrait implements Layout
{
    use LayoutTrait;

    public static function defineAreas(): array
    {
        return [];
    }
}
