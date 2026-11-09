<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AdminDashboardComponent extends Component
{
    public function render()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isGlobalAdmin = (bool) $currentUser->is_admin;
        $administeredTeamIds = $currentUser->administeredTeamIds();

        if ($isGlobalAdmin) {
            // 1. Total statistics (Global)
            $totalUsers = User::count();
            $totalTeams = Team::count();
            $totalEvents = Event::count();

            // 2. Team distribution
            $personalTeams = Team::where('personal_team', true)->count();
            $sharedTeams = $totalTeams - $personalTeams;

            // 3. Most active teams (by number of events)
            $mostActiveTeams = Team::with('owner')
                ->withCount('events')
                ->orderByDesc('events_count')
                ->take(5)
                ->get();

            // 4. Orphan or problematic teams (no owner)
            $orphanTeamsCount = Team::whereNull('user_id')
                ->orWhereNotIn('user_id', User::select('id'))
                ->count();

            // 5. User roles distribution
            $globalAdmins = User::where('is_admin', true)->count();

            // 6. Recent system activity (last 7 days events)
            $recentActivity = Event::where('created_at', '>=', now()->subDays(7))->count();
        } else {
            // Scoped to administered teams
            $totalTeams = count($administeredTeamIds);

            $totalUsers = User::where(function ($q) use ($administeredTeamIds) {
                $q->whereHas('teams', function ($sub) use ($administeredTeamIds) {
                    $sub->whereIn('teams.id', $administeredTeamIds);
                })->orWhereIn('id', function ($sub) use ($administeredTeamIds) {
                    $sub->select('user_id')->from('teams')->whereIn('id', $administeredTeamIds)->whereNotNull('user_id');
                });
            })->count();

            $totalEvents = Event::whereIn('team_id', $administeredTeamIds)->count();

            $personalTeams = Team::whereIn('id', $administeredTeamIds)->where('personal_team', true)->count();
            $sharedTeams = $totalTeams - $personalTeams;

            $mostActiveTeams = Team::whereIn('id', $administeredTeamIds)
                ->with('owner')
                ->withCount('events')
                ->orderByDesc('events_count')
                ->take(5)
                ->get();

            $orphanTeamsCount = 0;
            $globalAdmins = 0;
            $recentActivity = Event::whereIn('team_id', $administeredTeamIds)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
        }

        return view('livewire.admin.admin-dashboard-component', [
            'isGlobalAdmin' => $isGlobalAdmin,
            'totalUsers' => $totalUsers,
            'totalTeams' => $totalTeams,
            'totalEvents' => $totalEvents,
            'personalTeams' => $personalTeams,
            'sharedTeams' => $sharedTeams,
            'mostActiveTeams' => $mostActiveTeams,
            'orphanTeamsCount' => $orphanTeamsCount,
            'globalAdmins' => $globalAdmins,
            'recentActivity' => $recentActivity,
        ])->layout('layouts.app');
    }
}
