<x-layouts::auth :title="__('Join as a Professional')">
    <div class="flex flex-col gap-5"
         x-data="{
             skills: [],
             skillInput: '',
             addSkill(val) {
                 val = val.trim();
                 if (val && !this.skills.includes(val) && this.skills.length < 10) {
                     this.skills.push(val);
                 }
                 this.skillInput = '';
             },
             removeSkill(i) { this.skills.splice(i, 1); },
             get skillsJson() { return JSON.stringify(this.skills); }
         }">

        <div class="text-center">
            <p class="font-data text-[10px] uppercase tracking-[0.18em] text-ink-faint mb-3">Professional Account</p>
            <h1 class="font-display text-2xl text-ink">Create your professional profile</h1>
            <p class="text-sm text-ink-soft mt-2">Receive matched client briefs, pitch directly, no commission taken.</p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="role" value="professional">
            <input type="hidden" name="skill_tags" :value="skillsJson">

            <div class="grid grid-cols-2 gap-3">
                <flux:input
                    name="name"
                    :label="__('Full name')"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Your full name"
                />
                <flux:input
                    name="professional_title"
                    :label="__('Professional title')"
                    :value="old('professional_title')"
                    type="text"
                    required
                    placeholder="e.g. Brand Designer"
                />
            </div>
            @error('professional_title')
                <p class="-mt-2 text-xs text-red-500">{{ $message }}</p>
            @enderror

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="you@example.com"
            />

            {{-- Skill category tags --}}
            <div>
                <p class="text-sm font-medium text-slate-700 mb-2">Primary skill categories <span class="text-red-400">*</span></p>

                {{-- Preset categories --}}
                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach (['Brand & Identity', 'UI/UX Design', 'Web Development', 'Mobile Dev', 'Photography', 'Video & Motion', 'Copywriting', 'Marketing', 'Data Analysis', 'Finance', 'Legal'] as $cat)
                        <button
                            type="button"
                            @click="addSkill('{{ $cat }}')"
                            :class="skills.includes('{{ $cat }}') ? 'bg-ink text-white border-ink' : 'bg-white text-ink-soft border-line hover:border-ink hover:text-ink'"
                            class="text-[11px] font-semibold px-2.5 py-1 rounded-full border transition-colors"
                        >{{ $cat }}</button>
                    @endforeach
                </div>

                {{-- Custom tag input --}}
                <div class="flex gap-2">
                    <input
                        type="text"
                        x-model="skillInput"
                        @keydown.enter.prevent="addSkill(skillInput)"
                        placeholder="Add a custom skill..."
                        class="flex-1 px-3 py-2  border border-line bg-chalk-soft text-ink placeholder-ink-faint text-xs focus:outline-none focus:ring-2 focus:ring-ink/20 focus:border-ink transition"
                    />
                    <button type="button" @click="addSkill(skillInput)"
                        class="px-3 py-2 bg-ink text-white text-xs font-semibold  hover:bg-ink-deep transition">
                        Add
                    </button>
                </div>

                {{-- Selected tags display --}}
                <div class="flex flex-wrap gap-1.5 mt-2 min-h-6">
                    <template x-for="(skill, i) in skills" :key="i">
                        <span class="inline-flex items-center gap-1 bg-ink text-white text-[11px] font-medium px-2.5 py-1 rounded-full">
                            <span x-text="skill"></span>
                            <button type="button" @click="removeSkill(i)" class="text-white/50 hover:text-white leading-none ml-0.5">×</button>
                        </span>
                    </template>
                </div>
                @error('skill_tags')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
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
                Create Professional Account →
            </flux:button>
        </form>

        <p class="text-sm text-center text-ink-faint">
            Already have an account?
            <a href="{{ route('professional.login') }}" wire:navigate class="font-semibold text-ink hover:text-brand-deep transition-colors">Sign in</a>
            &nbsp;·&nbsp;
            <a href="{{ route('client.register') }}" wire:navigate class="font-semibold text-ink-soft hover:text-ink transition-colors">Join as client</a>
        </p>
    </div>
</x-layouts::auth>
