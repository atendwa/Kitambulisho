<?php

namespace Atendwa\Kitambulisho\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuthUser
{
    public function canLogIn(): bool;

    public function asModel(): Model;
}
