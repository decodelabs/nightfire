<?php

/**
 * Nightfire
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Nightfire;

use DecodeLabs\Nightfire\BlockGroup\Descriptor as BlockGroupDescriptor;
use DecodeLabs\Nuance\Dumpable;

interface Collection extends
    BlockGroupDescriptor,
    TypeNameProvider,
    Dumpable
{
    public function __construct();
}
