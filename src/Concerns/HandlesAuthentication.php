<?php

namespace Atendwa\Kitambulisho\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Model;

trait HandlesAuthentication
{
    public function canLogin(): bool
    {
        return true;
    }

    /**
     * @throws Exception
     */
    public function asModel(): Model
    {
        return asInstanceOf($this, Model::class);
    }
}
