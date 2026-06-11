<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'name', 'email', 'password', 'role', 'credits',
    'professional_title', 'phone', 'bio', 'portfolio_url', 'skill_tags',
    'avatar_path',
    'company_name', 'company_size', 'company_role', 'company_description', 'company_services',
    'logo_path',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'skill_tags' => 'array',
        ];
    }

    public function isClient(): bool
    {
        return $this->role === Role::Client;
    }

    public function isProfessional(): bool
    {
        return $this->role === Role::Professional;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // ── Relationships ──────────────────────────────────────────

    public function briefs(): HasMany
    {
        return $this->hasMany(Brief::class, 'client_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'professional_id');
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(Unlock::class, 'professional_id');
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function clientConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'client_id');
    }

    public function professionalConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'professional_id');
    }

    // ── Credit helpers ─────────────────────────────────────────

    public function hasCredits(int $amount = 1): bool
    {
        return $this->credits >= $amount;
    }

    public function profileCompleteness(): int
    {
        $fields = [
            'name', 'email', 'professional_title',
            'phone', 'bio', 'portfolio_url', 'skill_tags',
        ];
        $filled = collect($fields)->filter(fn ($f) => ! empty($this->{$f}))->count();

        return (int) round($filled / count($fields) * 100);
    }

    public function isProfileReady(): bool
    {
        return $this->profileCompleteness() >= 70;
    }
}
