<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

class LayoutValidationResult
{
    public readonly bool $valid;

    /**
     * @var array<string,list<ValidationError>>
     */
    public readonly array $areaErrors;

    /**
     * @param array<string,list<ValidationError>> $areaErrors
     */
    public function __construct(
        bool $valid,
        array $areaErrors = [],
    ) {
        $this->valid = $valid;
        $this->areaErrors = $areaErrors;
    }
}
