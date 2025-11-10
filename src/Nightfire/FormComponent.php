<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Tagged\Markup;

interface FormComponent
{
    public function render(): Markup;
    public function apply(): Block;
}
