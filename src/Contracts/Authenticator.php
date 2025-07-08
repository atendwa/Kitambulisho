<?php

declare(strict_types=1);

namespace Atendwa\Kitambulisho\Contracts;

interface Authenticator
{
    /**
     * @param  array<string, mixed>  $credentials
     *
     * @return array<string, string|bool|null>
     */
    public function authenticate(array $credentials): array;

    /**
     * @param  array<string, string>  $credentials
     */
    public function confirm(array $credentials): bool;

    public function identifier(): string;

    /**
     * @return string[]|null[]
     */
    public function fetchLabel(string $message, string $identifier): array;
}
