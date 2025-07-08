<?php

declare(strict_types=1);

namespace Atendwa\Kitambulisho\Services;

use Atendwa\Kitambulisho\Contracts\Authenticator;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MasqueradeAuthenticator implements Authenticator
{
    /**
     * @param  array<string, mixed>  $credentials
     *
     * @return array<string, bool|string|null>
     *
     * @throws Throwable
     */
    public function authenticate(array $credentials): array
    {
        throw_if(app()->isProduction(), 'Masquerade is not allowed in production!');

        $user = User::where($this->identifier(), $credentials[$this->identifier()])->first();

        throw_if(blank($user), 'Masquerade user not found!');

        auth()->loginUsingId($user->id, true);

        return ['authenticated' => auth()->check(), 'message' => null];
    }

    /**
     * @param  array<string, string>  $credentials
     */
    public function confirm(array $credentials): bool
    {
        return Auth::guard('web')->validate(collect($credentials)->only([$this->identifier(), 'password'])->all());
    }

    public function identifier(): string
    {
        return 'username';
    }

    public function fetchLabel(string $message, string $identifier): array
    {
        return [null, null];
    }
}
