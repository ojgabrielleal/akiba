<?php

namespace App\Http\Controllers\Public\Pages;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Inertia\Inertia;

class TeamPageController extends Controller
{
    public function render()
    {
        return Inertia::render('public/Team', [
            'members' => $this->indexMembers(),
        ]);
    }

    private function indexMembers()
    {
        return UserResource::collection(
            User::query()
                ->active()
                ->where('is_virtual', false)
                ->whereHas('roles')
                ->with([
                    'preferences',
                    'roles' => fn ($query) => $query->orderByDesc('weight'),
                    'socials',
                    'topAnimes',
                ])
                ->get()
                ->sortByDesc(fn (User $user) => $user->roles->max('weight'))
                ->values()
        )->format('team');
    }
}
