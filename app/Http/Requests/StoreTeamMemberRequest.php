<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->providerProfile;
    }

    public function rules(): array
    {
        $profileId = $this->user()?->providerProfile?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('team_members', 'email')->where(fn (Builder $query) => $query->where('provider_profile_id', $profileId)),
            ],
            'role' => ['required', 'string', 'in:admin,profissional,recepcionista'],
        ];
    }
}
