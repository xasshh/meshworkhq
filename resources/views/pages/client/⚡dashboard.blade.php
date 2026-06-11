<?php

use App\Enums\Role;
use App\Models\Brief;
use App\Models\Conversation;
use App\Models\Unlock;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Client Dashboard')] class extends Component
{
    use WithFileUploads;

    public string $companyDescription = '';

    public string $companyServices = '';

    public bool $profileSaved = false;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $logo = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->companyDescription = $user->company_description ?? '';
        $this->companyServices = $user->company_services ?? '';
    }

    public function updatedLogo(): void
    {
        $this->validate(['logo' => ['image', 'max:2048', 'mimes:jpeg,jpg,png']]);

        $path = $this->logo->store('logos', 'public');
        auth()->user()->update(['logo_path' => $path]);

        $this->logo = null;
        $this->profileSaved = true;
    }

    public function saveCompanyProfile(): void
    {
        $this->validate([
            'companyDescription' => ['nullable', 'string', 'max:1000'],
            'companyServices' => ['nullable', 'string', 'max:500'],
        ]);

        auth()->user()->update([
            'company_description' => $this->companyDescription ?: null,
            'company_services' => $this->companyServices ?: null,
        ]);

        $this->profileSaved = true;
        $this->dispatch('toast', variant: 'success', message: 'Company profile saved.');
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        $briefIds = Brief::where('client_id', auth()->id())->pluck('id');

        return [
            'activeBriefs' => Brief::where('client_id', auth()->id())->active()->count(),
            'totalPitches' => Unlock::whereIn('brief_id', $briefIds)->count(),
            'alertsSent' => (int) Brief::where('client_id', auth()->id())->sum('total_alerts_sent'),
            'conversations' => Conversation::where('client_id', auth()->id())->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Brief>
     */
    #[Computed]
    public function activeBriefs(): \Illuminate\Database\Eloquent\Collection
    {
        return Brief::where('client_id', auth()->id())
            ->active()
            ->latest('published_at')
            ->take(6)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Unlock>
     */
    #[Computed]
    public function recentPitches(): \Illuminate\Database\Eloquent\Collection
    {
        return Unlock::with(['professional', 'brief', 'conversation'])
            ->whereIn('brief_id', Brief::where('client_id', auth()->id())->pluck('id'))
            ->latest('unlocked_at')
            ->take(6)
            ->get();
    }

    #[Computed]
    public function featuredProfessionals(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('role', Role::Professional)
            ->latest()
            ->take(6)
            ->get();
    }
}; ?>

<div class="py-8 px-4 sm:px-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8 animate-fade-in-up">
        <div>
            <h1 class="font-display text-[28px] font-bold text-slate-main tracking-tight">Client Dashboard</h1>
            <p class="text-sm text-slate-400 mt-0.5">Welcome back, {{ auth()->user()->name }}
                @if (auth()->user()->company_name)
                    · {{ auth()->user()->company_name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <div class="w-2 h-2 rounded-full bg-emerald-main animate-pulse"></div>
            <span class="font-semibold text-emerald-deep">Matching engine active</span>
        </div>
    </div>

    {{-- ── STATS ROW ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up" style="animation-delay: 40ms">
        @foreach ([
            ['label' => 'Active Briefs', 'value' => $this->stats['activeBriefs'], 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'accent' => true],
            ['label' => 'Pitches Received', 'value' => $this->stats['totalPitches'], 'icon' => 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z', 'accent' => false],
            ['label' => 'Professionals Alerted', 'value' => $this->stats['alertsSent'], 'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', 'accent' => false],
            ['label' => 'Conversations', 'value' => $this->stats['conversations'], 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'accent' => false],
        ] as $stat)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 card-lift">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 rounded-lg {{ $stat['accent'] ? 'bg-emerald-main' : 'bg-emerald-soft border border-emerald-border' }} flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 {{ $stat['accent'] ? 'text-white' : 'text-emerald-deep' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                        </svg>
                    </div>
                </div>
                <p class="font-display text-3xl font-bold text-slate-main leading-none">{{ number_format($stat['value']) }}</p>
                <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── POST A BRIEF CTA ── --}}
    <a href="{{ route('client.brief.create') }}" wire:navigate
       class="group cursor-pointer bg-white border border-slate-200 hover:border-emerald-main rounded-2xl p-6 flex items-center justify-between transition-all duration-200 hover:shadow-sm mb-6 animate-fade-in-up" style="animation-delay: 80ms">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-soft border border-emerald-border flex items-center justify-center shrink-0 group-hover:bg-emerald-main group-hover:border-emerald-main transition-colors">
                <svg class="w-5 h-5 text-emerald-deep group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-main">Post a Brief</p>
                <p class="text-xs text-slate-400 mt-0.5">Describe your project, confirm skill tags, and matched professionals get alerted instantly.</p>
            </div>
        </div>
        <span class="text-xs font-bold text-emerald-deep group-hover:text-emerald-main transition-colors shrink-0 ml-4">Get matched →</span>
    </a>

    {{-- ── MAIN GRID ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- ── COMPANY PROFILE ── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Logo Upload --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 animate-fade-in-up" style="animation-delay: 120ms">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Company Logo / Profile Image</p>
                <div class="flex items-center gap-4">
                    @if (auth()->user()->logo_path)
                        <img src="{{ Storage::url(auth()->user()->logo_path) }}"
                             class="w-14 h-14 rounded-xl object-cover shrink-0 ring-2 ring-slate-200"
                             alt="{{ auth()->user()->company_name ?? auth()->user()->name }}" />
                    @elseif ($logo)
                        <img src="{{ $logo->temporaryUrl() }}"
                             class="w-14 h-14 rounded-xl object-cover shrink-0 ring-2 ring-emerald-main/30"
                             alt="Preview" />
                    @else
                        <div class="w-14 h-14 rounded-xl bg-slate-main flex items-center justify-center text-white font-semibold text-lg shrink-0">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif

                    <div class="flex-1">
                        <label for="logo-upload" class="cursor-pointer text-sm font-semibold text-emerald-deep hover:text-emerald-main transition-colors">
                            {{ auth()->user()->logo_path ? 'Change logo' : 'Upload logo' }}
                            <input
                                id="logo-upload"
                                type="file"
                                wire:model="logo"
                                accept="image/jpeg,image/jpg,image/png"
                                class="sr-only"
                            />
                        </label>
                        <p class="text-xs text-slate-400 mt-0.5">PNG, JPG · Max 2MB · Shown to matched professionals</p>
                        @error('logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="logo" class="flex items-center gap-1.5 mt-1">
                            <svg class="w-3 h-3 animate-spin text-emerald-main" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span class="text-xs text-emerald-deep">Uploading...</span>
                        </div>
                    </div>
                </div>

                @if (auth()->user()->company_name)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs text-slate-400">Company</p>
                        <p class="text-sm font-semibold text-slate-main mt-0.5">{{ auth()->user()->company_name }}</p>
                        @if (auth()->user()->company_size)
                            <p class="text-xs text-slate-400 mt-0.5">{{ auth()->user()->company_size }} people · {{ auth()->user()->company_role }}</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Business Background --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 animate-fade-in-up" style="animation-delay: 160ms">
                <label for="company_desc" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Business Background</label>
                <div class="relative">
                    <textarea
                        wire:model.live="companyDescription"
                        id="company_desc"
                        rows="4"
                        placeholder="Describe your company, industry, and what kinds of projects you typically commission..."
                        maxlength="1000"
                        class="w-full px-3 py-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-main text-sm placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition resize-none"
                    ></textarea>
                    <span class="absolute bottom-2.5 right-3 text-[10px] text-slate-300">{{ strlen($companyDescription) }}/1000</span>
                </div>
                @error('companyDescription') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Services Sought --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 animate-fade-in-up" style="animation-delay: 200ms">
                <label for="company_services" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Services Regularly Sought</label>
                <textarea
                    wire:model="companyServices"
                    id="company_services"
                    rows="3"
                    placeholder="e.g. Brand design, web development, content creation, video production..."
                    maxlength="500"
                    class="w-full px-3 py-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-main text-sm placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition resize-none"
                ></textarea>
                @error('companyServices') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Save --}}
            <button
                wire:click="saveCompanyProfile"
                class="btn-lift w-full bg-slate-main text-white font-semibold py-3 rounded-xl hover:bg-slate-700 transition-colors text-sm animate-fade-in-up shadow-sm"
                style="animation-delay: 240ms"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="saveCompanyProfile">Save Company Profile</span>
                <span wire:loading wire:target="saveCompanyProfile">Saving...</span>
            </button>
        </div>

        {{-- ── ACTIVITY COLUMN ── --}}
        <div class="lg:col-span-3 space-y-5">

            {{-- Active Briefs --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden animate-fade-in-up" style="animation-delay: 140ms">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-main">Active Briefs</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $this->activeBriefs->count() }} running through the matching engine</p>
                    </div>
                    <a href="{{ route('client.briefs') }}" wire:navigate class="text-xs font-semibold text-emerald-deep hover:text-emerald-main transition-colors">View all →</a>
                </div>

                @if ($this->activeBriefs->isNotEmpty())
                    <div class="grid grid-cols-12 px-5 py-2 bg-slate-50 border-b border-slate-100">
                        <div class="col-span-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Brief</div>
                        <div class="col-span-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">Budget</div>
                        <div class="col-span-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">Alerts</div>
                        <div class="col-span-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">Pitches</div>
                        <div class="col-span-1"></div>
                    </div>

                    <div class="divide-y divide-slate-50">
                        @foreach ($this->activeBriefs as $brief)
                            <a href="{{ route('client.brief.detail', ['ulid' => $brief->ulid]) }}" wire:navigate
                               class="grid grid-cols-12 items-center px-5 py-3.5 hover:bg-slate-50/60 transition-colors">
                                <div class="col-span-5 flex items-center gap-2.5 min-w-0">
                                    <div class="w-1.5 h-1.5 rounded-full shrink-0 bg-emerald-main animate-pulse"></div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-main truncate">{{ $brief->title }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $brief->published_at?->diffForHumans() ?? 'Draft' }}</p>
                                    </div>
                                </div>
                                <div class="col-span-2 text-right">
                                    <p class="text-xs font-semibold text-slate-main">₦{{ number_format($brief->budget_max ?? 0) }}</p>
                                </div>
                                <div class="col-span-2 text-right">
                                    <p class="text-xs font-medium text-emerald-deep">{{ $brief->total_alerts_sent }}</p>
                                </div>
                                <div class="col-span-2 text-right">
                                    <p class="text-xs font-medium text-slate-600">{{ $brief->total_unlocks }}</p>
                                </div>
                                <div class="col-span-1 flex justify-end">
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-full bg-emerald-soft text-emerald-deep border border-emerald-border whitespace-nowrap">
                                        {{ str_replace('_', ' ', $brief->status->value) }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm text-slate-400">No active briefs yet.</p>
                        <a href="{{ route('client.brief.create') }}" wire:navigate class="inline-block mt-2 text-xs font-semibold text-emerald-deep hover:text-emerald-main transition-colors">Post your first brief →</a>
                    </div>
                @endif
            </div>

            {{-- Professionals Who Pitched --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden animate-fade-in-up" style="animation-delay: 200ms">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-main">Professionals Who Pitched</h2>
                    <p class="text-xs text-slate-400 mt-0.5">These professionals spent credits to unlock your briefs and are ready to talk</p>
                </div>

                @if ($this->recentPitches->isNotEmpty())
                    <div class="divide-y divide-slate-50">
                        @foreach ($this->recentPitches as $pitch)
                            <div class="px-5 py-4 flex items-center gap-3 hover:bg-slate-50/60 transition-colors">
                                @if ($pitch->professional->avatar_path)
                                    <img src="{{ Storage::url($pitch->professional->avatar_path) }}"
                                         class="w-10 h-10 rounded-full object-cover shrink-0"
                                         alt="{{ $pitch->professional->name }}" />
                                @else
                                    <div class="w-10 h-10 rounded-full bg-emerald-main flex items-center justify-center text-white font-semibold text-xs shrink-0">
                                        {{ $pitch->professional->initials() }}
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-main truncate">{{ $pitch->professional->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $pitch->professional->professional_title ?? 'Professional' }}</p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-semibold text-slate-500">{{ $pitch->unlocked_at?->diffForHumans() }}</span>
                                        <span class="text-[10px] text-slate-300">·</span>
                                        <span class="text-[10px] text-slate-400 truncate">re: {{ $pitch->brief->title }}</span>
                                    </div>
                                </div>
                                @if ($pitch->conversation)
                                    <a href="{{ route('client.conversation', ['id' => $pitch->conversation->id]) }}" wire:navigate
                                       class="shrink-0 w-8 h-8 rounded-lg border border-slate-200 bg-white hover:bg-emerald-main hover:border-emerald-main text-slate-400 hover:text-white transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-slate-400">No professionals have pitched yet.</p>
                        <p class="text-xs text-slate-300 mt-1">Once professionals unlock your briefs, they'll appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── PROFESSIONAL DISCOVERY ── --}}
    <div class="mt-10 animate-fade-in-up" style="animation-delay: 300ms">
        <div class="flex items-center justify-between mb-5">
            <div>
                <p class="text-xs font-bold text-emerald-main uppercase tracking-widest mb-1">Discover</p>
                <h2 class="text-lg font-semibold text-slate-main">Professionals on the Platform</h2>
                <p class="text-xs text-slate-400 mt-0.5">Verified talent available to unlock and pitch on your briefs</p>
            </div>
        </div>

        @if ($this->featuredProfessionals->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($this->featuredProfessionals as $professional)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-emerald-main/40 hover:shadow-sm transition-all duration-200 group">
                        <div class="flex items-start gap-3 mb-3">
                            @if ($professional->avatar_path)
                                <img src="{{ Storage::url($professional->avatar_path) }}"
                                     class="w-11 h-11 rounded-xl object-cover shrink-0"
                                     alt="{{ $professional->name }}" />
                            @else
                                <div class="w-11 h-11 rounded-xl bg-emerald-main flex items-center justify-center text-white font-semibold text-sm shrink-0 group-hover:bg-emerald-deep transition-colors duration-200">
                                    {{ $professional->initials() }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-main truncate">{{ $professional->name }}</p>
                                @if ($professional->professional_title)
                                    <p class="text-xs text-emerald-deep font-medium mt-0.5">{{ $professional->professional_title }}</p>
                                @endif
                            </div>
                        </div>
                        @if ($professional->bio)
                            <p class="text-xs text-slate-500 leading-relaxed mb-3 line-clamp-2">{{ $professional->bio }}</p>
                        @endif
                        @if ($professional->skill_tags)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach (array_slice($professional->skill_tags, 0, 4) as $tag)
                                    <span class="text-[10px] font-medium bg-slate-50 border border-slate-200 text-slate-500 px-2 py-0.5 rounded-full">{{ $tag }}</span>
                                @endforeach
                                @if (count($professional->skill_tags) > 4)
                                    <span class="text-[10px] text-slate-400 self-center">+{{ count($professional->skill_tags) - 4 }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center">
                <div class="w-10 h-10 rounded-xl bg-emerald-soft border border-emerald-border flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-emerald-deep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-400">No professionals with complete profiles yet.</p>
                <p class="text-xs text-slate-300 mt-1">As professionals join and build their profiles, they'll appear here.</p>
            </div>
        @endif
    </div>
</div>
