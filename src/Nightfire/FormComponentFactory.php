<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

interface FormComponentFactory
{
    public function createFormComponent(
        Block $block
    ): FormComponent;
}
