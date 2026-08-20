<?php

namespace App\Models;

use App\Enums\BriefStatus;
use Database\Factories\BriefFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brief extends Model
{
    /** @use HasFactory<BriefFactory> */
    use HasFactory;

    protected $fillable = [
        'ulid', 'client_id', 'title', 'description',
        'budget_min', 'budget_max', 'skill_tags',
        'location', 'is_remote', 'language',
        'status', 'published_at', 'expires_at',
        'hired_professional_id', 'alert_wave',
        'total_alerts_sent', 'total_unlocks',
    ];

    protected function casts(): array
    {
        return [
            'skill_tags' => 'array',
            'is_remote' => 'boolean',
            'status' => BriefStatus::class,
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Brief $brief) {
            if (empty($brief->ulid)) {
                $brief->ulid = Str::ulid()->toString();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function hiredProfessional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hired_professional_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(Unlock::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BriefStatus::Published->value,
            BriefStatus::ReceivingPitches->value,
            BriefStatus::Shortlisting->value,
        ]);
    }

    public function isAvailableForUnlock(): bool
    {
        return $this->status->canReceivePitches();
    }

    // ── Wave timing ────────────────────────────────────────────
    //
    // The alert engine releases a brief in three waves: wave 1 immediately on
    // publication, wave 2 six hours later, wave 3 twenty four hours later.
    // These helpers expose that schedule to the UI so a professional can see
    // how much of a head start they still have.

    /** Hours after publication at which each wave opens. */
    private const WAVE_OFFSETS_IN_HOURS = [1 => 0, 2 => 6, 3 => 24];

    /** Cumulative audience size once each wave has been notified. */
    private const WAVE_AUDIENCE = [1 => 10, 2 => 25, 3 => 50];

    /**
     * Which wave this brief is currently in, derived from elapsed time rather
     * than the stored alert_wave column so the countdown stays truthful even
     * when a delayed wave job has not run yet.
     */
    public function currentWave(): int
    {
        if ($this->published_at === null) {
            return 1;
        }

        $hoursLive = $this->published_at->diffInHours(now());

        return match (true) {
            $hoursLive < self::WAVE_OFFSETS_IN_HOURS[2] => 1,
            $hoursLive < self::WAVE_OFFSETS_IN_HOURS[3] => 2,
            default => 3,
        };
    }

    /** How many professionals can see this brief in the current wave. */
    public function waveAudience(): int
    {
        return self::WAVE_AUDIENCE[$this->currentWave()];
    }

    /** When the next wave opens, or null once the brief is in the final wave. */
    public function nextWaveAt(): ?\DateTimeInterface
    {
        if ($this->published_at === null) {
            return null;
        }

        $next = $this->currentWave() + 1;

        if (! isset(self::WAVE_OFFSETS_IN_HOURS[$next])) {
            return null;
        }

        return $this->published_at->addHours(self::WAVE_OFFSETS_IN_HOURS[$next]);
    }

    /**
     * How far through the current wave window we are, from 0 to 1. Drives the
     * fill on the wave rail. Returns 1 for the final wave, which has no end.
     */
    public function waveProgress(): float
    {
        $nextWaveAt = $this->nextWaveAt();

        if ($nextWaveAt === null || $this->published_at === null) {
            return 1.0;
        }

        $wave = $this->currentWave();
        $windowOpenedAt = $this->published_at->addHours(self::WAVE_OFFSETS_IN_HOURS[$wave]);
        $windowSeconds = $windowOpenedAt->diffInSeconds($nextWaveAt);

        if ($windowSeconds <= 0) {
            return 1.0;
        }

        $elapsed = $windowOpenedAt->diffInSeconds(now());

        return max(0.0, min(1.0, $elapsed / $windowSeconds));
    }
}
