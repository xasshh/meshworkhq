<x-layouts::auth :title="__('Professional Sign In')">
    <div class="flex flex-col gap-6">

        <div>
            <div class="inline-flex items-center gap-2 bg-emerald-soft border border-emerald-border text-emerald-deep text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-main"></span>
                Professional Portal
            </div>
            <h1 class="font-display text-[26px] font-bold text-slate-main tracking-tight">Welcome back</h1>
            <p class="text-sm text-slate-400 mt-1">Sign in to your workspace and respond to matched briefs.</p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
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
                    placeholder="Your password"
                    viewable
                />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="absolute top-0 end-0 text-xs font-semibold text-emerald-deep hover:text-emerald-main transition-colors">
                        Forgot password?
                    </a>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Keep me signed in')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full mt-1">
                Sign in to Workspace →
            </flux:button>
        </form>

        <p class="text-sm text-center text-slate-400">
            New to Meshwork HQ?
            <a href="{{ route('professional.register') }}" wire:navigate class="font-semibold text-emerald-deep hover:text-emerald-main transition-colors">Create professional account</a>
            &nbsp;·&nbsp;
            <a href="{{ route('client.login') }}" wire:navigate class="font-semibold text-slate-500 hover:text-slate-700 transition-colors">Client sign in</a>
        </p>
    </div>
</x-layouts::auth>
