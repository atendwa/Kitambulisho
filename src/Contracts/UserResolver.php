<?php

declare(strict_types=1);

namespace Atendwa\Kitambulisho\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface UserResolver
{
    public function dataService(string $username): Authenticatable;

    public function ldap(string $username): Authenticatable;
}
