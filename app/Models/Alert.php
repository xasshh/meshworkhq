<?php

namespace App\Models;

use App\Enums\AlertStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Alert extends Model
{
    protected $fillable = [
        'brief_id', 'professional_id', 'wave',
        'status', 'ai_relevance_score',
        'notified_at', 'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AlertStatus::class,
            'notified_at' => 'datetime',
            'viewed_at' => 'datetime',
        ];
    }

    public function brief(): BelongsTo
    {
        return $this->belongsTo(Brief::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professional_id');
    }

    public function unlock(): HasOne
    {
        return $this->hasOne(Unlock::class);
    }

    public function markViewed(): void
    {
        if ($this->status === AlertStatus::Notified) {
            $this->update([
                'status' => AlertStatus::Viewed,
                'viewed_at' => now(),
            ]);
        }
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', AlertStatus::Notified->value);
    }

    public function scopeForProfessional(Builder $query, int $professionalId): Builder
    {
        return $query->where('professional_id', $professionalId);
    }
}
