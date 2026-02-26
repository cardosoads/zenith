<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRequest;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function store(StoreTeamMemberRequest $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless($profile, 404);

        $profile->teamMembers()->create($request->validated());

        return back();
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless($profile, 404);
        abort_unless($teamMember->provider_profile_id === $profile->id, 404);

        $teamMember->update($request->validated());

        return back();
    }

    public function destroy(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless($profile, 404);
        abort_unless($teamMember->provider_profile_id === $profile->id, 404);

        $teamMember->delete();

        return back();
    }
}
