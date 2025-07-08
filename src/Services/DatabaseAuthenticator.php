<?php

declare(strict_types=1);

namespace Atendwa\Kitambulisho\Services;

use Atendwa\Kitambulisho\Contracts\Authenticator;
use Illuminate\Support\Facades\Auth;
use Throwable;

class DatabaseAuthenticator implements Authenticator
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
        $credentials = collect($credentials);

        auth()->attempt(
            $credentials->only([$this->identifier(), 'password'])->all(),
            (bool) $credentials->get('remember')
        );

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
        return 'email';
    }

    public function fetchLabel(string $message, string $identifier): array
    {
        return [null, null];
    }
}
