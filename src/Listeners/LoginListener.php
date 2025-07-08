<?php

declare(strict_types=1);

namespace Atendwa\Kitambulisho\Listeners;

use Atendwa\Kitambulisho\Contracts\AuthUser;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use Throwable;

class LoginListener
{
    /**
     * @throws Throwable
     */
    public function handleUserLogin(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof AuthUser) {
            return;
        }

        if (! $user->canLogIn()) {
            auth()->logout();

            throw_if(true, 'You are not allowed to log in.');
        }

        $data = ['last_login_at' => now()];
        $data['login_ip_address'] = request()->ip();

        $model = $user->asModel();

        if (blank($model->getAttribute('first_login_at'))) {
            $data['first_login_at'] = now();
        }

        $model->update($data);

        $this->log($user, true);
    }

    public function handleUserLogout(Logout $event): void
    {
        $user = $event->user;

        if (! $user instanceof AuthUser) {
            return;
        }

        $user->asModel()->update(['last_logged_out' => now()]);

        $this->log($user, false);
    }

    /**
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return match (filled($events)) {
            true => [
                Logout::class => 'handleUserLogout',
                Login::class => 'handleUserLogin',
            ],
            false => []
        };
    }

    private function log(AuthUser $user, bool $isLogin): void
    {
        $user = $user->asModel();

        activity()->useLog('Authentication')->causedBy($user)
            ->event([true => 'Login', false => 'Log Out'][$isLogin])->performedOn($user)
            ->log([true => 'User logged in.', false => 'User logged out.'][$isLogin]);
    }
}
