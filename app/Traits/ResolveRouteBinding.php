<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait ResolveRouteBinding
{
    public function resolveRouteBinding($value, $field = null)
    {
        if (!in_array("api", request()->route()->getAction('middleware'))) {
            return $this->where('id', Crypt::decrypt($value))->firstOrFail();
        }
        return $this->where('id', $value)->firstOrFail();
    }
}
