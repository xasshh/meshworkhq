<x-layouts::auth :title="__('Join as a Hiring Client')">
    <div class="flex flex-col gap-5">

        <div class="text-center">
            <p class="font-data text-[10px] uppercase tracking-[0.18em] text-ink-faint mb-3">Client Account</p>
            <h1 class="font-display text-2xl text-ink">Find and hire top professionals</h1>
            <p class="text-sm text-ink-soft mt-2">Post briefs, get matched, hire directly. No platform commission.</p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="role" value="client">

            <flux:input
                name="name"
                :label="__('Your full name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="First and last name"
            />

            <flux:input
                name="email"
                :label="__('Work email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="you@company.com"
            />

            {{-- Company / Business Details --}}
            <div class="bg-chalk-soft border border-line  p-4 flex flex-col gap-3">
                <p class="text-xs font-bold text-ink-faint uppercase tracking-wider">Company / Business Details</p>

                <flux:input
                    name="company_name"
                    :label="__('Company or business name')"
                    :value="old('company_name')"
                    type="text"
                    placeholder="e.g. Apex Holdings Ltd"
                />

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Team size</label>
                        <select name="company_size"
                            class="w-full px-3 py-2.5  border border-line bg-white text-ink text-sm focus:outline-none focus:ring-2 focus:ring-ink/20 focus:border-ink transition appearance-none">
                            <option value="">Select size</option>
                            <option value="solo" @selected(old('company_size') === 'solo')>Just me</option>
                            <option value="2-10" @selected(old('company_size') === '2-10')>2 to 10 people</option>
                            <option value="11-50" @selected(old('company_size') === '11-50')>11 to 50 people</option>
                            <option value="51-200" @selected(old('company_size') === '51-200')>51 to 200 people</option>
                            <option value="200+" @selected(old('company_size') === '200+')>200+ people</option>
                        </select>
                    </div>
                    <flux:input
                        name="company_role"
                        :label="__('Your role')"
                        :value="old('company_role')"
                        type="text"
                        placeholder="e.g. Founder, HR Manager"
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Create a password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />
                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />
            </div>

            <flux:button type="submit" variant="primary" class="w-full mt-1">
                Create Client Account →
            </flux:button>
        </form>

        <p class="text-sm text-center text-ink-faint">
            Already have an account?
            <a href="{{ route('client.login') }}" wire:navigate class="font-semibold text-ink hover:text-brand-deep transition-colors">Sign in</a>
            &nbsp;·&nbsp;
            <a href="{{ route('professional.register') }}" wire:navigate class="font-semibold text-ink-soft hover:text-ink transition-colors">Join as professional</a>
        </p>
    </div>
</x-layouts::auth>
