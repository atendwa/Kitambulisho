<?php

declare(strict_types=1);

namespace Atendwa\Kitambulisho\Livewire;

use Atendwa\Kitambulisho\Actions\AttemptAuthentication;
use Atendwa\Kitambulisho\Contracts\Authenticator;
use Atendwa\Kitambulisho\Services\MasqueradeAuthenticator;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Throwable;

class LoginPage extends BaseLogin
{
    protected ?string $subheading = 'Use your university credentials to login';

    protected static string $view = 'authentication::pages.login';

    private Authenticator $authenticator;

    private ?string $linkLabel = null;

    private ?string $resetLink = null;

    /**
     * @throws Throwable
     */
    public function boot(): void
    {
        $this->authenticator = app(AttemptAuthentication::class)->getDriver();
    }

    #[Computed]
    public function linkLabel(): ?string
    {
        return $this->linkLabel;
    }

    #[Computed]
    public function resetLink(): ?string
    {
        return $this->resetLink;
    }

    #[Computed]
    public function canUseMasquerade(): bool
    {
        return app()->isLocal();
    }

    public function authenticate(bool $masquerade = false): ?LoginResponse
    {
        try {
            if ($masquerade) {
                $authenticator = app(MasqueradeAuthenticator::class);

                app(AttemptAuthentication::class)->driver($authenticator::class)->execute([
                    $authenticator->identifier() => asString(config('authentication.masquerade_username')),
                ]);

                return app(LoginResponse::class);
            }

            $this->attemptAuth();

            return app(LoginResponse::class);
        } catch (Throwable $throwable) {
            $this->fetchLabel($throwable->getMessage());

            notify($throwable->getMessage())->error('Login failed!');

            $identifier = collect($this->form->getState())->get($this->authenticator->identifier());

            Log::warning('Authentication attempt failed for ' . asString($identifier), [
                'message' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @throws Throwable
     */
    public function form(Form $form): Form
    {
        $attribute = $this->authenticator->identifier();

        return $form->schema([
            textInput($attribute)->autocomplete($attribute)->autofocus()->email($attribute === 'email'),
            textInput('password')->password()->revealable(),
            $this->getRememberFormComponent(),
        ]);
    }

    public function getHeading(): string
    {
        return config('app.name');
    }

    /**
     * @throws Throwable
     */
    protected function attemptAuth(): void
    {
        $data = $this->form->getState();

        $rule = match ($this->authenticator->identifier()) {
            default => 'string|max:255|min:1|required',
            'email' => 'email|string|max:50|required',
        };

        $validator = Validator::make($data, [
            'password' => 'string|required|max:50|min:8',
            $this->authenticator->identifier() => $rule,
            'remember' => 'boolean|nullable',
        ]);

        throw_if($validator->fails(), validatorErrorString($validator));

        app(AttemptAuthentication::class)->execute(asMixedArray($validator->validated()));
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('login')->submit('authenticate')->color('success')
                ->iconPosition('after')->icon('heroicon-o-arrow-long-right'),
        ];
    }

    private function fetchLabel(string $message): void
    {
        $identifier = collect($this->form->getState())->get($this->authenticator->identifier());

        [$this->linkLabel, $this->resetLink] = $this->authenticator->fetchLabel($message, asString($identifier));
    }
}
