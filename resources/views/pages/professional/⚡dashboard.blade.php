<?php

use App\Enums\AlertStatus;
use App\Enums\Role;
use App\Exceptions\AlreadyUnlockedException;
use App\Exceptions\BriefNotAvailableException;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Alert;
use App\Models\CreditTransaction;
use App\Models\Unlock;
use App\Models\User;
use App\Services\UnlockService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('My Workspace')] class extends Component
{
    use WithFileUploads;

    public string $bio = '';

    public string $phone = '';

    public string $newPortfolioUrl = '';

    public string $newSkill = '';

    public bool $profileSaved = false;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $photo = null;

    /** @var array<int, string> */
    public array $portfolioUrls = [];

    /** @var array<int, string> */
    public array $skills = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->bio = $user->bio ?? '';
        $this->phone = $user->phone ?? '';
        $this->portfolioUrls = array_filter([$user->portfolio_url ?? '']);
        $this->skills = $user->skill_tags ?? [];
    }

    public function updatedPhoto(): void
    {
        $this->validate(['photo' => ['image', 'max:2048', 'mimes:jpeg,jpg,png']]);

        $path = $this->photo->store('avatars', 'public');
        auth()->user()->update(['avatar_path' => $path]);

        $this->photo = null;
        $this->profileSaved = true;
    }

    public function saveProfile(): void
    {
        $this->validate([
            'bio' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        auth()->user()->update([
            'bio' => $this->bio ?: null,
            'phone' => $this->phone ?: null,
            'portfolio_url' => collect($this->portfolioUrls)->first() ?? null,
            'skill_tags' => $this->skills,
        ]);

        $this->profileSaved = true;
        $this->dispatch('toast', variant: 'success', message: 'Profile saved successfully.');
    }

    public function addPortfolioUrl(): void
    {
        $url = trim($this->newPortfolioUrl);

        if ($url && ! in_array($url, $this->portfolioUrls) && count($this->portfolioUrls) < 5) {
            $this->portfolioUrls[] = $url;
        }

        $this->newPortfolioUrl = '';
    }

    public function removePortfolioUrl(int $index): void
    {
        unset($this->portfolioUrls[$index]);
        $this->portfolioUrls = array_values($this->portfolioUrls);
    }

    public function addSkill(): void
    {
        $skill = trim($this->newSkill);

        if ($skill === '' || in_array($skill, $this->skills) || count($this->skills) >= 15) {
            return;
        }

        $this->skills[] = $skill;
        $this->newSkill = '';
    }

    public function removeSkill(int $index): void
    {
        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
    }

    public function unlockAlert(int $alertId, UnlockService $unlockService): void
    {
        $alert = Alert::with('brief')
            ->where('id', $alertId)
            ->where('professional_id', auth()->id())
            ->firstOrFail();

        try {
            $unlockService->unlock(auth()->user(), $alert->brief);
            $this->dispatch('toast', variant: 'success', message: 'Brief unlocked! You can now pitch to the client.');
        } catch (InsufficientCreditsException $e) {
            $this->dispatch('toast', variant: 'error', message: "Insufficient credits. You need {$e->required} credit(s) but have {$e->available}.");
        } catch (AlreadyUnlockedException) {
            $this->dispatch('toast', variant: 'info', message: 'You have already unlocked this brief.');
        } catch (BriefNotAvailableException $e) {
            $this->dispatch('toast', variant: 'warning', message: $e->getMessage());
        }
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'newAlerts' => Alert::forProfessional(auth()->id())->unread()->count(),
            'credits' => (int) auth()->user()->credits,
            'pitches' => Unlock::where('professional_id', auth()->id())->count(),
            'completeness' => auth()->user()->profileCompleteness(),
        ];
    }

    /**
     * Engagement funnel for the last 30 days (spec §16.1).
     *
     * @return array<string, int>
     */
    #[Computed]
    public function funnel(): array
    {
        $base = Alert::forProfessional(auth()->id())
            ->where('notified_at', '>=', now()->subDays(30));

        return [
            'received' => (clone $base)->count(),
            'viewed' => (clone $base)->whereIn('status', [AlertStatus::Viewed, AlertStatus::Unlocked])->count(),
            'unlocked' => (clone $base)->where('status', AlertStatus::Unlocked)->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Alert>
     */
    #[Computed]
    public function recentAlerts(): \Illuminate\Database\Eloquent\Collection
    {
        return Alert::with(['brief.client', 'unlock'])
            ->forProfessional(auth()->id())
            ->latest('notified_at')
            ->take(5)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CreditTransaction>
     */
    #[Computed]
    public function recentTransactions(): \Illuminate\Database\Eloquent\Collection
    {
        return CreditTransaction::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function featuredClients(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('role', Role::Client)
            ->where(function ($q) {
                $q->whereNotNull('company_name')
                    ->orWhereNotNull('company_description');
            })
            ->latest()
            ->take(6)
            ->get();
    }
}; ?>

<div class="py-8 px-4 sm:px-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-8 animate-fade-in-up">
        <h1 class="font-display text-[28px] font-bold text-slate-main tracking-tight">My Workspace</h1>
        <p class="text-sm text-slate-400 mt-0.5">
            {{ auth()->user()->professional_title ? auth()->user()->professional_title.' · ' : '' }}Manage your profile and respond to matched briefs.
        </p>
    </div>

    {{-- ── PROFILE COMPLETENESS WARNING (spec §5.1: <70% pauses alerts) ── --}}
    @if ($this->stats['completeness'] < 70)
        <div class="flex items-start gap-3.5 px-5 py-4 bg-amber-50 border border-amber-200 rounded-2xl mb-6 animate-fade-in-up">
            <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-amber-800">Your profile is {{ $this->stats['completeness'] }}% complete — alerts are paused below 70%</p>
                <p class="text-xs text-amber-700/80 mt-0.5">Complete your bio, phone, portfolio link, and skill tags below to start receiving matched brief alerts.</p>
            </div>
        </div>
    @endif

    {{-- ── STATS ROW ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up" style="animation-delay: 40ms">
        @foreach ([
            ['label' => 'New Alerts', 'value' => $this->stats['newAlerts'], 'suffix' => '', 'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', 'accent' => true],
            ['label' => 'Credit Balance', 'value' => $this->stats['credits'], 'suffix' => '', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'accent' => false],
            ['label' => 'Pitches Sent', 'value' => $this->stats['pitches'], 'suffix' => '', 'icon' => 'M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5', 'accent' => false],
            ['label' => 'Profile Complete', 'value' => $this->stats['completeness'], 'suffix' => '%', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'accent' => false],
        ] as $stat)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 card-lift">
                <div class="w-9 h-9 rounded-lg {{ $stat['accent'] ? 'bg-emerald-main' : 'bg-emerald-soft border border-emerald-border' }} flex items-center justify-center mb-3">
                    <svg class="w-4.5 h-4.5 {{ $stat['accent'] ? 'text-white' : 'text-emerald-deep' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                    </svg>
                </div>
                <p class="font-display text-3xl font-bold text-slate-main leading-none">{{ number_format($stat['value']) }}{{ $stat['suffix'] }}</p>
                <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- ── COLUMN A: Profile Deck ── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Avatar Upload --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 animate-fade-in-up" style="animation-delay: 60ms">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Profile Photo</p>
                <div class="flex items-center gap-4">
                    @if (auth()->user()->avatar_path)
                        <img src="{{ Storage::url(auth()->user()->avatar_path) }}"
                             class="w-14 h-14 rounded-xl object-cover shrink-0 ring-2 ring-emerald-main/30"
                             alt="{{ auth()->user()->name }}" />
                    @elseif ($photo)
                        <img src="{{ $photo->temporaryUrl() }}"
                             class="w-14 h-14 rounded-xl object-cover shrink-0 ring-2 ring-emerald-main/30"
                             alt="Preview" />
                    @else
                        <div class="w-14 h-14 rounded-xl bg-emerald-main flex items-center justify-center text-white font-semibold text-lg shrink-0">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif

                    <div class="flex-1">
                        <label for="photo-upload" class="cursor-pointer text-sm font-semibold text-emerald-deep hover:text-emerald-main transition-colors">
                            {{ auth()->user()->avatar_path ? 'Change photo' : 'Upload photo' }}
                            <input
                                id="photo-upload"
                                type="file"
                                wire:model="photo"
                                accept="image/jpeg,image/jpg,image/png"
                                class="sr-only"
                            />
                        </label>
                        <p class="text-xs text-slate-400 mt-0.5">JPG or PNG · Max 2MB</p>
                        @error('photo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="photo" class="flex items-center gap-1.5 mt-1">
                            <svg class="w-3 h-3 animate-spin text-emerald-main" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span class="text-xs text-emerald-deep">Uploading...</span>
                        </div>
                    </div>
                </div>

                {{-- Completeness meter --}}
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Profile Completeness</p>
                        <p class="text-xs font-bold {{ $this->stats['completeness'] >= 70 ? 'text-emerald-deep' : 'text-amber-600' }}">{{ $this->stats['completeness'] }}%</p>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700 {{ $this->stats['completeness'] >= 70 ? 'bg-emerald-main' : 'bg-amber-400' }}"
                             style="width: {{ $this->stats['completeness'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1.5">70% required to receive brief alerts</p>
                </div>
            </div>

            {{-- Bio --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 animate-fade-in-up" style="animation-delay: 100ms">
                <label for="bio" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Bio Summary</label>
                <div class="relative">
                    <textarea
                        wire:model.live="bio"
                        id="bio"
                        rows="4"
                        placeholder="Describe your expertise, experience, and the type of work you do best..."
                        maxlength="500"
                        class="w-full px-3 py-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-main text-sm placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition resize-none"
                    ></textarea>
                    <span class="absolute bottom-2.5 right-3 text-[10px] text-slate-300">{{ strlen($bio) }}/500</span>
                </div>
                @error('bio') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Phone --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 animate-fade-in-up" style="animation-delay: 140ms">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Phone Number</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </span>
                    <input
                        wire:model="phone"
                        type="tel"
                        placeholder="+234 800 000 0000"
                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-main placeholder-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition"
                    />
                </div>
                @error('phone') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-2 text-[10px] text-slate-400">Needed for SMS alerts when they launch</p>
            </div>

            {{-- Portfolio URLs --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 animate-fade-in-up" style="animation-delay: 180ms">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Portfolio &amp; External Links</p>
                @if (count($portfolioUrls) > 0)
                    <div class="space-y-2 mb-3">
                        @foreach ($portfolioUrls as $idx => $url)
                            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <span class="text-xs text-slate-600 truncate flex-1">{{ $url }}</span>
                                <button wire:click="removePortfolioUrl({{ $idx }})" class="shrink-0 text-slate-300 hover:text-red-400 transition text-sm leading-none">×</button>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if (count($portfolioUrls) < 5)
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input
                                wire:model="newPortfolioUrl"
                                wire:keydown.enter.prevent="addPortfolioUrl"
                                type="url"
                                placeholder="https://yourportfolio.com"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-main placeholder-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition"
                            />
                        </div>
                        <button wire:click="addPortfolioUrl" class="shrink-0 px-3 py-2.5 bg-slate-main text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                            Add
                        </button>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Maximum 5 links added.</p>
                @endif
            </div>

            {{-- Skill Tags --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 animate-fade-in-up" style="animation-delay: 220ms">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Skill Tags</p>
                <div class="flex flex-wrap gap-1.5 mb-3 min-h-8">
                    @foreach ($skills as $index => $skill)
                        <span class="inline-flex items-center gap-1.5 bg-slate-main text-white text-xs font-medium px-3 py-1.5 rounded-full">
                            {{ $skill }}
                            <button wire:click="removeSkill({{ $index }})" class="text-white/50 hover:text-white transition leading-none">×</button>
                        </span>
                    @endforeach
                    @if (count($skills) === 0)
                        <p class="text-xs text-slate-400 italic">No skills added yet — these drive your brief matching.</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <input
                        wire:model="newSkill"
                        wire:keydown.enter.prevent="addSkill"
                        type="text"
                        placeholder="Add a skill..."
                        class="flex-1 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-main placeholder-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition"
                    />
                    <button wire:click="addSkill" class="px-3 py-2 bg-slate-main text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                        Add
                    </button>
                </div>
            </div>

            {{-- Save --}}
            <button
                wire:click="saveProfile"
                class="btn-lift w-full bg-emerald-main text-white font-semibold py-3 rounded-xl hover:bg-emerald-deep transition-colors text-sm shadow-sm animate-fade-in-up"
                style="animation-delay: 260ms"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="saveProfile">Save Profile</span>
                <span wire:loading wire:target="saveProfile">Saving...</span>
            </button>
        </div>

        {{-- ── COLUMN B: Wallet + Alerts + Analytics ── --}}
        <div class="lg:col-span-3 space-y-5">

            {{-- Credit Balance Card --}}
            <div class="bg-slate-main rounded-2xl p-6 animate-fade-in-up relative overflow-hidden" style="animation-delay: 80ms">
                <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse 80% 60% at 20% 0%, rgba(27, 80, 212, 0.5) 0%, transparent 70%);"></div>
                <div class="relative z-10 flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mb-2">Available Credit Balance</p>
                        <div class="flex items-end gap-3 mb-4">
                            <p class="font-display text-5xl font-bold text-white leading-none">{{ auth()->user()->credits }}</p>
                            <div class="pb-1">
                                <p class="text-white/60 text-sm font-medium">credits remaining</p>
                                <p class="text-white/30 text-xs mt-0.5">Each credit unlocks one client's full brief &amp; contact</p>
                            </div>
                        </div>
                        <div class="w-full h-1.5 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-main rounded-full transition-all duration-700"
                                 style="width: {{ min(100, auth()->user()->credits * 10) }}%"></div>
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-main/15 border border-emerald-main/25 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-emerald-main" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                            </svg>
                        </div>
                        <a href="{{ route('professional.wallet') }}" wire:navigate class="text-xs text-emerald-main font-bold hover:text-white transition whitespace-nowrap">Top up credits →</a>
                    </div>
                </div>
            </div>

            {{-- Matched Brief Alerts --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden animate-fade-in-up" style="animation-delay: 140ms">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-main">Matched Brief Alerts</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Briefs matched to your declared skill profile</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($this->stats['newAlerts'] > 0)
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-main animate-pulse"></div>
                                <span class="text-xs font-bold text-emerald-deep">{{ $this->stats['newAlerts'] }} new</span>
                            </div>
                        @endif
                        <a href="{{ route('professional.alerts') }}" wire:navigate class="text-xs font-semibold text-emerald-deep hover:text-emerald-main transition-colors">View all →</a>
                    </div>
                </div>

                @if ($this->recentAlerts->isNotEmpty())
                    <div class="divide-y divide-slate-50">
                        @foreach ($this->recentAlerts as $i => $alert)
                            <div class="p-5 hover:bg-slate-50/60 transition-colors animate-fade-in-up" style="animation-delay: {{ 180 + ($i * 60) }}ms">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $alert->status === App\Enums\AlertStatus::Notified ? 'bg-emerald-main animate-pulse' : 'bg-slate-300' }}"></span>
                                            <p class="text-sm font-semibold text-slate-main truncate">{{ $alert->brief->title }}</p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                            @if ($alert->unlock)
                                                <span class="font-semibold text-slate-600">{{ $alert->brief->client->name }}</span>
                                                <span>·</span>
                                            @endif
                                            <span>{{ $alert->brief->is_remote ? 'Remote' : ($alert->brief->location ?? 'On-site') }}</span>
                                            <span>·</span>
                                            <span class="font-semibold text-emerald-deep">₦{{ number_format($alert->brief->budget_max ?? 0) }}</span>
                                            <span>·</span>
                                            <span>{{ $alert->notified_at?->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-50 text-slate-400 border border-slate-200 shrink-0">Wave {{ $alert->wave }}</span>
                                </div>

                                @if ($alert->brief->skill_tags)
                                    <div class="flex flex-wrap gap-1.5 mb-4">
                                        @foreach (array_slice($alert->brief->skill_tags, 0, 5) as $tag)
                                            <span class="text-[10px] bg-slate-50 border border-slate-200 text-slate-500 px-2.5 py-1 rounded-full font-medium">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($alert->unlock)
                                    <div class="flex items-center gap-3">
                                        <div class="inline-flex items-center gap-1.5 text-xs text-emerald-deep font-semibold bg-emerald-soft border border-emerald-border px-3 py-1.5 rounded-lg">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                            Unlocked
                                        </div>
                                        <a href="{{ route('professional.brief.detail', ['ulid' => $alert->brief->ulid]) }}" wire:navigate
                                           class="text-xs text-emerald-deep font-semibold hover:underline ml-auto">View Brief &amp; Pitch →</a>
                                    </div>
                                @else
                                    <button
                                        wire:click="unlockAlert({{ $alert->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="unlockAlert({{ $alert->id }})"
                                        @if (auth()->user()->credits < 1) disabled title="Insufficient credits" @endif
                                        @class([
                                            'btn-lift w-full flex items-center justify-center gap-2 py-3 rounded-xl text-xs font-bold transition border',
                                            'bg-slate-main border-slate-main text-white hover:bg-slate-700' => auth()->user()->credits >= 1,
                                            'bg-slate-50 border-slate-200 text-slate-300 cursor-not-allowed' => auth()->user()->credits < 1,
                                        ])
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                        <span wire:loading.remove wire:target="unlockAlert({{ $alert->id }})">Spend 1 Credit to Reveal Details and Pitch</span>
                                        <span wire:loading wire:target="unlockAlert({{ $alert->id }})">Unlocking...</span>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm text-slate-400">No matched briefs yet.</p>
                        <p class="text-xs text-slate-300 mt-1">
                            @if ($this->stats['completeness'] < 70)
                                Complete your profile to at least 70% to start receiving alerts.
                            @else
                                You'll be alerted the moment a brief matches your skill tags.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            {{-- Engagement Funnel (30 days) --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6 animate-fade-in-up" style="animation-delay: 200ms">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-main">Engagement Funnel</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Last 30 days</p>
                    </div>
                </div>
                @php
                    $max = max(1, $this->funnel['received']);
                @endphp
                <div class="space-y-4">
                    @foreach ([
                        ['label' => 'Alerts Received', 'value' => $this->funnel['received'], 'color' => 'bg-slate-main'],
                        ['label' => 'Viewed', 'value' => $this->funnel['viewed'], 'color' => 'bg-emerald-main/70'],
                        ['label' => 'Unlocked', 'value' => $this->funnel['unlocked'], 'color' => 'bg-emerald-main'],
                    ] as $row)
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium text-slate-500">{{ $row['label'] }}</span>
                                <span class="text-xs font-bold text-slate-main">{{ $row['value'] }}</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $row['color'] }} rounded-full transition-all duration-700"
                                     style="width: {{ round($row['value'] / $max * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($this->funnel['received'] > 0)
                    <p class="text-[10px] text-slate-400 mt-4">
                        Unlock rate: <span class="font-bold text-emerald-deep">{{ round($this->funnel['unlocked'] / $max * 100) }}%</span> of matched alerts
                    </p>
                @endif
            </div>

            {{-- Recent Transactions --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden animate-fade-in-up" style="animation-delay: 240ms">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-main">Recent Credit Activity</h2>
                    <a href="{{ route('professional.wallet') }}" wire:navigate class="text-xs font-semibold text-emerald-deep hover:text-emerald-main transition-colors">Wallet →</a>
                </div>
                @if ($this->recentTransactions->isNotEmpty())
                    <div class="divide-y divide-slate-50">
                        @foreach ($this->recentTransactions as $tx)
                            <div class="px-5 py-3 flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-slate-main truncate">{{ $tx->description ?? ucfirst($tx->type->value) }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $tx->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="text-xs font-bold shrink-0 ml-3 {{ $tx->amount > 0 ? 'text-emerald-deep' : 'text-slate-500' }}">
                                    {{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-6 text-center">
                        <p class="text-xs text-slate-400">No credit activity yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── CLIENT DISCOVERY ── --}}
    <div class="mt-10 animate-fade-in-up" style="animation-delay: 300ms">
        <div class="flex items-center justify-between mb-5">
            <div>
                <p class="text-xs font-bold text-emerald-main uppercase tracking-widest mb-1">Discover</p>
                <h2 class="text-lg font-semibold text-slate-main">Clients on the Platform</h2>
                <p class="text-xs text-slate-400 mt-0.5">Companies and individuals actively commissioning creative and digital work</p>
            </div>
        </div>

        @if ($this->featuredClients->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($this->featuredClients as $client)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-emerald-main/40 hover:shadow-sm transition-all duration-200 group">
                        <div class="flex items-start gap-3 mb-3">
                            @if ($client->logo_path)
                                <img src="{{ Storage::url($client->logo_path) }}"
                                     class="w-11 h-11 rounded-xl object-cover shrink-0"
                                     alt="{{ $client->company_name ?? $client->name }}" />
                            @else
                                <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 font-semibold text-sm shrink-0 group-hover:bg-emerald-soft group-hover:border-emerald-border group-hover:text-emerald-deep transition-colors duration-200">
                                    {{ $client->initials() }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-main truncate">{{ $client->name }}</p>
                                @if ($client->company_name)
                                    <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $client->company_name }}</p>
                                @endif
                                @if ($client->company_size && $client->company_role)
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $client->company_size }} · {{ $client->company_role }}</p>
                                @endif
                            </div>
                        </div>
                        @if ($client->company_description)
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">{{ $client->company_description }}</p>
                        @else
                            <p class="text-xs text-slate-300 italic">No description yet</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-400">No clients with company profiles yet.</p>
                <p class="text-xs text-slate-300 mt-1">As clients join and complete their profiles, they'll appear here.</p>
            </div>
        @endif
    </div>
</div>
