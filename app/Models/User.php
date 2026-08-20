<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\VerificationStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
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
    'show_hire_count', 'show_engagement_count',
    'avatar_path',
    'company_name', 'company_size', 'company_role', 'company_description', 'company_services',
    'logo_path',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Verification columns are deliberately absent from the fillable list: only
     * VerificationService may write them, and it force fills. That way no
     * request payload can ever hand someone a verified badge.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'verification_status' => 'unverified',
    ];

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
            'show_hire_count' => 'boolean',
            'show_engagement_count' => 'boolean',
            'verification_status' => VerificationStatus::class,
            'verification_submitted_at' => 'datetime',
            'verification_reviewed_at' => 'datetime',
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

    /**
     * Fields that count toward profile completeness, mapped to the label shown
     * to the professional when one is missing.
     *
     * @var array<string, string>
     */
    /**
     * The completeness a professional must reach before the matcher will alert
     * them. The dashboard and profile page both quote this figure.
     */
    private const PROFILE_READY_PERCENT = 70;

    private const PROFILE_FIELDS = [
        'name' => 'Your name',
        'email' => 'Email address',
        'professional_title' => 'Professional title',
        'phone' => 'Phone number',
        'bio' => 'Short bio',
        'portfolio_url' => 'Portfolio link',
        'skill_tags' => 'Skills',
    ];

    public function profileCompleteness(): int
    {
        $filled = collect(array_keys(self::PROFILE_FIELDS))
            ->filter(fn (string $field): bool => ! empty($this->{$field}))
            ->count();

        return (int) round($filled / count(self::PROFILE_FIELDS) * 100);
    }

    /**
     * Human readable labels for the profile fields still to be filled in, so
     * the profile page can tell a professional exactly what is holding their
     * alerts back rather than just showing a percentage.
     *
     * @return array<int, string>
     */
    public function missingProfileFields(): array
    {
        return collect(self::PROFILE_FIELDS)
            ->reject(fn (string $label, string $field): bool => ! empty($this->{$field}))
            ->values()
            ->all();
    }

    public function isProfileReady(): bool
    {
        return $this->profileCompleteness() >= self::PROFILE_READY_PERCENT;
    }

    /**
     * The SQL twin of isProfileReady().
     *
     * The matcher selects candidates in a single query with a limit, so the
     * readiness rule has to be expressible in the database rather than applied
     * afterwards in PHP, which would silently shrink the wave. Both sides
     * derive their threshold from minimumFilledProfileFields(), and
     * ProfileReadinessTest asserts they agree for every profile shape, so the
     * gate the dashboard promises cannot drift from the one the matcher runs.
     */
    public function scopeProfileReady(Builder $query): Builder
    {
        $filled = collect(array_keys(self::PROFILE_FIELDS))
            ->map(function (string $field): string {
                // skill_tags is a json column on MySQL. Comparing one against a
                // bare '' raises "invalid JSON text", so read it as text first;
                // that also lets the same expression run on SQLite in tests.
                if ($field === 'skill_tags') {
                    return "CASE WHEN `skill_tags` IS NOT NULL AND CAST(`skill_tags` AS CHAR) NOT IN ('', '[]') THEN 1 ELSE 0 END";
                }

                return "CASE WHEN `{$field}` IS NOT NULL AND `{$field}` <> '' THEN 1 ELSE 0 END";
            })
            ->implode(' + ');

        return $query->whereRaw("({$filled}) >= ?", [self::minimumFilledProfileFields()]);
    }

    /**
     * The fewest filled fields that still round up to the readiness threshold.
     * Derived from the same formula profileCompleteness() uses, so adding a
     * profile field or moving the threshold updates both sides at once.
     */
    private static function minimumFilledProfileFields(): int
    {
        $total = count(self::PROFILE_FIELDS);

        for ($filled = 0; $filled <= $total; $filled++) {
            if ((int) round($filled / $total * 100) >= self::PROFILE_READY_PERCENT) {
                return $filled;
            }
        }

        return $total;
    }

    // ── Track record ───────────────────────────────────────────
    //
    // The only trust signal available before reviews exist: what actually
    // happened on the platform. Counts are derived, never stored, so they
    // cannot drift from the records they describe.

    /** Briefs where this professional was the one hired. */
    public function hiresCount(): int
    {
        return Brief::where('hired_professional_id', $this->id)->count();
    }

    /**
     * Distinct professionals who spent a credit on one of this client's
     * briefs. Paying to reach someone is the strongest available signal that
     * a client's briefs are worth answering.
     */
    public function engagementCount(): int
    {
        return Unlock::whereIn('brief_id', Brief::where('client_id', $this->id)->select('id'))
            ->distinct('professional_id')
            ->count('professional_id');
    }

    // ── Verification ───────────────────────────────────────────

    public function isVerified(): bool
    {
        return $this->verification_status === VerificationStatus::Verified;
    }

    /** The track record this user has chosen to show, or null when hidden. */
    public function publicTrackRecord(): ?int
    {
        if ($this->isProfessional()) {
            return $this->show_hire_count ? $this->hiresCount() : null;
        }

        return $this->show_engagement_count ? $this->engagementCount() : null;
    }
}
