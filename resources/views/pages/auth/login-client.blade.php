<x-layouts::auth :title="__('Client Sign In')">
    <div class="flex flex-col gap-6">

        <div class="text-center">
            <p class="font-data text-[10px] uppercase tracking-[0.18em] text-ink-faint mb-3">Client Portal</p>
            <h1 class="font-display text-2xl text-ink">Welcome back</h1>
            <p class="text-sm text-ink-soft mt-2">Sign in to manage your briefs and review matched professionals.</p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                name="email"
                :label="__('Work email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@company.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Your password"
                    viewable
                />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="absolute top-0 end-0 text-xs font-semibold text-ink hover:text-brand-deep transition-colors">
                        Forgot password?
                    </a>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Keep me signed in')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full mt-1">
                Sign in to Dashboard →
            </flux:button>
        </form>

        <div class="grid gap-2 text-center text-sm">
            <p class="text-ink-faint">
                New to Meshwork HQ?
                <a href="{{ route('client.register') }}" wire:navigate class="font-semibold text-ink hover:text-brand-deep transition-colors">Create an account</a>
            </p>
            <p>
                <a href="{{ route('professional.login') }}" wire:navigate class="font-semibold text-ink-soft hover:text-ink transition-colors">Sign in as a professional instead</a>
            </p>
        </div>
    </div>
</x-layouts::auth>
