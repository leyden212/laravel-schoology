<?php

namespace Leyden\Schoology\Resources\Traits;

use Illuminate\Support\Str;

trait ReadWriteRealmTrait
{
    use CanListResourceTrait,
        CanViewResourceTrait,
        CanCreateResourceTrait,
        CanUpdateResourceTrait,
        CanDeleteResourceTrait;
}
