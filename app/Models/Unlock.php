<?php

namespace App\Models;

use App\Enums\UnlockStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unlock extends Model
{
    protected $fillable = [
        'brief_id', 'professional_id', 'alert_id',
        'credits_spent', 'status', 'ai_fit_score',
        'unlocked_at', 'pitch_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => UnlockStatus::class,
            'unlocked_at' => 'datetime',
            'pitch_sent_at' => 'datetime',
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

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }
}
