@use(Filament\Support\Facades\FilamentView)
@use(Filament\View\PanelsRenderHook)

<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ FilamentView::renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
        />

        @if($this->canUseMasquerade())
            <div class="flex justify-center">
                <button class="p-3 -mb-6" type="button" wire:click.prevent="authenticate(true)" >Masquerade</button>
            </div>
        @endif

        @if ($this->linkLabel)
            <div class="flex justify-center">
                <a
                        class="group -mb-2 flex w-full items-center justify-between gap-2 pt-2 text-slate-600 hover:text-blue-800 focus:text-blue-800"
                        href="{{ $this->resetLink }}"
                        target="_blank"
                        hreflang="en"
                >
                    <span class="underline-offset-4">{{ $this->linkLabel }}</span>

                    <svg
                            class="size-5 text-slate-400 transition-all duration-500 group-hover:rotate-45 group-hover:text-blue-800 group-focus:rotate-45 group-focus:text-blue-800"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            aria-hidden="true"
                            fill="none"
                    >
                        <path
                                d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"
                                stroke-linejoin="round"
                                stroke-linecap="round"
                        />
                    </svg>
                </a>
            </div>
        @endif
    </x-filament-panels::form>

    {{ FilamentView::renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
