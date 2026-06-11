<x-layouts::auth :title="__('Create account')">
    <div class="flex flex-col gap-6 animate-fade-in-up">
        <div>
            <h1 class="font-display text-[26px] font-bold text-slate-main tracking-tight">Create your account</h1>
            <p class="text-sm text-slate-400 mt-1.5">Free to start — no commission on completed work.</p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4"
              x-data="{
                  role: '{{ old('role', 'client') }}',
                  skills: @json(old('skill_tags') ? json_decode(old('skill_tags'), true) : []),
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
            @csrf
            <input type="hidden" name="role" :value="role">

            {{-- Role segmented control --}}
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">I am joining as</p>
                <div class="relative grid grid-cols-2 bg-slate-100/90 rounded-xl p-1">
                    {{-- Sliding pill --}}
                    <div class="absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] bg-white rounded-lg shadow-sm ring-1 ring-slate-200/70 transition-transform duration-300 ease-out"
                         :class="role === 'professional' ? 'translate-x-full' : 'translate-x-0'"></div>

                    <button type="button" @click="role = 'client'"
                        class="relative z-10 py-2.5 px-3 text-center transition-colors duration-200 rounded-lg">
                        <span class="block text-xs font-bold leading-tight"
                              :class="role === 'client' ? 'text-slate-main' : 'text-slate-400'">Hiring Client</span>
                        <span class="block text-[10px] mt-0.5 font-medium"
                              :class="role === 'client' ? 'text-slate-500' : 'text-slate-300'">Post briefs, find talent</span>
                    </button>

                    <button type="button" @click="role = 'professional'"
                        class="relative z-10 py-2.5 px-3 text-center transition-colors duration-200 rounded-lg">
                        <span class="block text-xs font-bold leading-tight"
                              :class="role === 'professional' ? 'text-emerald-main' : 'text-slate-400'">Professional</span>
                        <span class="block text-[10px] mt-0.5 font-medium"
                              :class="role === 'professional' ? 'text-slate-500' : 'text-slate-300'">Receive leads, pitch clients</span>
                    </button>
                </div>
                @error('role')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <flux:input
                name="name"
                :label="__('Full name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Your full name')"
            />

            {{-- Professional title — professionals only --}}
            <div x-show="role === 'professional'" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <flux:input
                    name="professional_title"
                    :label="__('Professional title')"
                    :value="old('professional_title')"
                    type="text"
                    placeholder="e.g. Brand Designer"
                />
                @error('professional_title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="you@example.com"
            />

            {{-- Skill categories — professionals only --}}
            <div x-show="role === 'professional'" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <input type="hidden" name="skill_tags" :value="skillsJson">
                <p class="text-sm font-medium text-slate-700 mb-2">Primary skill categories <span class="text-red-400">*</span></p>

                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach (['Brand & Identity', 'UI/UX Design', 'Web Development', 'Mobile Dev', 'Photography', 'Video & Motion', 'Copywriting', 'Marketing', 'Data Analysis', 'Finance', 'Legal'] as $cat)
                        <button
                            type="button"
                            @click="addSkill('{{ $cat }}')"
                            :class="skills.includes('{{ $cat }}') ? 'bg-emerald-main text-white border-emerald-main shadow-sm shadow-emerald-main/25' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-main hover:text-emerald-deep'"
                            class="text-[11px] font-semibold px-2.5 py-1 rounded-full border transition-all duration-150"
                        >{{ $cat }}</button>
                    @endforeach
                </div>

                <div class="flex gap-2">
                    <input
                        type="text"
                        x-model="skillInput"
                        @keydown.enter.prevent="addSkill(skillInput)"
                        placeholder="Add a custom skill..."
                        class="flex-1 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-main placeholder-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition"
                    />
                    <button type="button" @click="addSkill(skillInput)"
                        class="px-3 py-2 bg-slate-main text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition-colors duration-150">
                        Add
                    </button>
                </div>

                <div class="flex flex-wrap gap-1.5 mt-2 min-h-6">
                    <template x-for="(skill, i) in skills" :key="i">
                        <span class="inline-flex items-center gap-1 bg-slate-main text-white text-[11px] font-medium px-2.5 py-1 rounded-full animate-fade-in-up">
                            <span x-text="skill"></span>
                            <button type="button" @click="removeSkill(i)" class="text-white/50 hover:text-white leading-none ml-0.5 transition-colors">×</button>
                        </span>
                    </template>
                </div>
                @error('skill_tags')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Create a password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm your password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <flux:button type="submit" variant="primary" class="w-full mt-1 btn-lift" data-test="register-user-button">
                <span x-text="role === 'professional' ? 'Create Professional Account' : 'Create Account'"></span>
            </flux:button>
        </form>

        <p class="text-sm text-center text-slate-400">
            Already have an account?
            <a href="{{ route('login') }}" wire:navigate class="font-semibold text-emerald-deep hover:text-emerald-main transition-colors duration-150">Sign in</a>
        </p>
    </div>
</x-layouts::auth>
