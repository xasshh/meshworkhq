<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CreditBundle extends Model
{
    protected $fillable = ['name', 'credits', 'price_kobo', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getPriceNgnAttribute(): float
    {
        return $this->price_kobo / 100;
    }

    public function getPricePerCreditAttribute(): float
    {
        return $this->credits > 0 ? $this->price_kobo / $this->credits / 100 : 0;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
