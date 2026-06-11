<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\Role;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private readonly CreditService $creditService,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $isProfessional = ($input['role'] ?? '') === 'professional';

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'role' => ['required', 'string', Rule::in(['client', 'professional'])],
            'professional_title' => ['required_if:role,professional', 'nullable', 'string', 'max:255'],
            'skill_tags' => [
                'required_if:role,professional',
                'string',
                // Ensure professionals have at least one skill selected.
                function (string $attribute, mixed $value, \Closure $fail) use ($isProfessional): void {
                    if ($isProfessional) {
                        $tags = is_string($value) ? json_decode($value, true) : null;
                        if (! is_array($tags) || count($tags) === 0) {
                            $fail('Please select at least one skill category.');
                        }
                    }
                },
            ],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_size' => ['nullable', 'string', Rule::in(['solo', '2-10', '11-50', '51-200', '200+'])],
            'company_role' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $role = $isProfessional ? Role::Professional : Role::Client;

        $attributes = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $role,
        ];

        if ($role === Role::Professional) {
            $attributes['professional_title'] = $input['professional_title'] ?? null;
            $attributes['skill_tags'] = json_decode($input['skill_tags'], true);
        }

        if ($role === Role::Client) {
            $attributes['company_name'] = $input['company_name'] ?? null;
            $attributes['company_size'] = $input['company_size'] ?? null;
            $attributes['company_role'] = $input['company_role'] ?? null;
        }

        $user = User::create($attributes);

        // Issue welcome bonus via the ledger so the credit history is correct.
        if ($role === Role::Professional) {
            $this->creditService->issueWelcomeBonus($user);
        }

        return $user;
    }
}
