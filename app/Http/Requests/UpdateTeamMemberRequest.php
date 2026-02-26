<?php

namespace App\Http\Requests;

use App\Models\TeamMember;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $teamMember = $this->route('teamMember');

        if (! $teamMember instanceof TeamMember) {
            return false;
        }

        return (int) $teamMember->provider_profile_id === (int) $this->user()?->providerProfile?->id;
    }

    public function rules(): array
    {
        $teamMember = $this->route('teamMember');
        $profileId = $this->user()?->providerProfile?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('team_members', 'email')
                    ->ignore($teamMember)
                    ->where(fn (Builder $query) => $query->where('provider_profile_id', $profileId)),
            ],
            'role' => ['required', 'string', 'in:admin,profissional,recepcionista'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
