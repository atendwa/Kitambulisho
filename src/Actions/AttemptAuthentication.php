<?php

declare(strict_types=1);

namespace Atendwa\Kitambulisho\Actions;

use Atendwa\Kitambulisho\Contracts\Authenticator;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttemptAuthentication
{
    private ?string $driver = null;

    private string $message;

    private string $field;

    public function driver(string $class): self
    {
        $this->driver = $class;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $credentials
     *
     * @throws Throwable
     */
    public function execute(array $credentials): void
    {
        (new Timebox())->call(function () use ($credentials): void {
            $this->field = $this->getDriver()->identifier();

            $this->ensureIsNotRateLimited($credentials);

            $this->message = trans('auth.failed');

            $key = $this->throttleKey($credentials);

            if (! $this->attemptLogin($credentials)) {
                $this->handleFailedLogin($key, $credentials);
            }

            RateLimiter::clear($key);
        }, microseconds: asInteger(config('authentication.timebox_duration')));
    }

    /**
     * @param  array<string, mixed>  $credentials
     *
     * @throws Throwable
     */
    public function ensureIsNotRateLimited(array $credentials): void
    {
        $key = $this->throttleKey($credentials);

        if (RateLimiter::tooManyAttempts($key, asInteger(config('authentication.max_login_failures')))) {
            $this->handleRateLimited($key);
        }
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function throttleKey(array $credentials): string
    {
        return Str::transliterate(mb_strtolower(asString($credentials[$this->field])) . '|' . request()->ip());
    }

    /**
     * @throws Throwable
     */
    public function getDriver(): Authenticator
    {
        $driver = app($this->driver ?? Authenticator::class);

        throw_if(! $driver instanceof Authenticator, 'Invalid authentication driver');

        return $driver;
    }

    private function handleRateLimited(string $key): void
    {
        event(new Lockout(request()));

        $time = [];
        $time['seconds'] = RateLimiter::availableIn($key);
        $time['minutes'] = ceil($time['seconds'] / 60);

        throw ValidationException::withMessages([$this->field => trans('auth.throttle', $time)]);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function attemptLogin(array $credentials): bool
    {
        try {
            return DB::transaction(function () use ($credentials) {
                $response = $this->getDriver()->authenticate($credentials);
                $this->message = asString($response['message']);

                return (bool) $response['authenticated'];
            });
        } catch (Throwable $exception) {
            $this->message = $exception->getMessage();

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function handleFailedLogin(string $key, array $credentials): void
    {
        RateLimiter::hit($key);

        $properties = array_merge($credentials, ['user_agent' => request()->userAgent(), 'ip' => request()->ip()]);

        activity('Failed Logins')->withProperties($properties)->log('Failed login attempt');

        $message = [true => $this->message, false => 'Invalid credentials.'][filled($this->message)];

        throw ValidationException::withMessages([$this->field => $message]);
    }
}
