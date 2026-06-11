<x-layouts::auth :title="__('Sign in')">
    <div class="flex flex-col gap-7 animate-fade-in-up">
        <div>
            <h1 class="font-display text-[26px] font-bold text-slate-main tracking-tight">Welcome back</h1>
            <p class="text-sm text-slate-400 mt-1.5">Enter your credentials to access your workspace.</p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Your password')"
                    viewable
                />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="absolute top-0 end-0 text-xs font-semibold text-emerald-deep hover:text-emerald-main transition-colors duration-150">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Keep me signed in')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full mt-1 btn-lift" data-test="login-button">
                {{ __('Sign in') }}
            </flux:button>
        </form>

        <div class="flex items-center gap-3">
            <div class="h-px flex-1 bg-slate-100"></div>
            <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">New here?</span>
            <div class="h-px flex-1 bg-slate-100"></div>
        </div>

        <p class="text-sm text-center text-slate-400 -mt-2">
            <a href="{{ route('register') }}" wire:navigate
               class="font-semibold text-emerald-deep hover:text-emerald-main transition-colors duration-150">Create a free account</a>
            — no commission, no hidden fees.
        </p>
    </div>
</x-layouts::auth>
