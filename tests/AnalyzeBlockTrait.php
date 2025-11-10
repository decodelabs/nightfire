<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire\Tests;

use DecodeLabs\Nightfire\Block;
use DecodeLabs\Nightfire\BlockTrait;
use DecodeLabs\Tagged\Markup;

class AnalyzeBlockTrait implements Block
{
    use BlockTrait;

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }

    public function render(): ?Markup
    {
        return null;
    }
}
