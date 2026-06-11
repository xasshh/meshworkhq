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
}
