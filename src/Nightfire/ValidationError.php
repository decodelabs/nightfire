<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

class ValidationError
{
    public function __construct(
        public readonly string $areaId,
        public readonly ?int $blockIndex,
        public readonly string $rule,
        public readonly string $message,
    ) {
    }
}
