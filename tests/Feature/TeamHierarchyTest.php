<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;
use Livewire\Livewire;
use Tests\TestCase;

class TeamHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_exceed_team_limit()
    {
        $user = User::factory()->create();
        // Create a team that the user belongs to, which defines their limit
        $team = Team::factory()->create(['max_member_teams' => 2]);
        $user->teams()->attach($team, ['role' => 'admin']);
        $user->switchTeam($team);

        // Ensure user has permission to create teams
        $permission = \App\Models\Permission::firstOrCreate(
            ['name' => 'teams.create'], 
            ['display_name' => 'Create Teams', 'is_system' => true]
        );
        $role = \App\Models\Role::firstOrCreate(
            ['team_id' => $team->id, 'name' => 'admin'], 
            ['display_name' => 'Admin', 'is_system' => true]
        );
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        
        \DB::table('team_user')
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->update(['custom_role_id' => $role->id]);

        $this->actingAs($user);

        // Create 2 teams (user now owns 2 teams)
        $user->ownedTeams()->create(['name' => 'Owned 1', 'personal_team' => false, 'max_member_teams' => 2]);
        $user->ownedTeams()->create(['name' => 'Owned 2', 'personal_team' => false, 'max_member_teams' => 2]);

        $this->assertEquals(2, $user->ownedTeams()->count());
        $this->assertFalse($user->canCreateTeam());

        // Try to create 3rd team via Livewire component
        Livewire::test(CreateTeamForm::class)
            ->set(['state' => ['name' => 'Team 3']])
            ->call('createTeam')
            ->assertHasErrors(['name']);
            
        $this->assertCount(2, $user->fresh()->ownedTeams);
    }

    public function test_global_admin_can_exceed_team_limit()
    {
        $this->actingAs($admin = User::factory()->create(['is_admin' => true]));

        $admin->ownedTeams()->create(['name' => 'Team 1', 'personal_team' => false]);
        
        // Try to create 2nd team
        Livewire::test(CreateTeamForm::class)
            ->set(['state' => ['name' => 'Team 2']])
            ->call('createTeam')
            ->assertHasNoErrors();
            
        $this->assertCount(2, $admin->fresh()->ownedTeams);
    }

    public function test_user_transfer_migrates_events()
    {
        $user = User::factory()->create();
        $sourceTeam = Team::factory()->create();
        $targetTeam = Team::factory()->create();
        
        // Add user to source team
        $sourceTeam->users()->attach($user, ['role' => 'admin']);
        $user->switchTeam($sourceTeam);
        
        // Create event in source team
        $event = Event::create([
            'user_id' => $user->id,
            'team_id' => $sourceTeam->id,
            'start' => now(),
            'end' => now()->addHour(),
            'is_open' => false,
        ]);

        // Perform transfer
        $action = new \App\Actions\Admin\TransferUserToTeam();
        $action->transfer($user, $targetTeam, 'editor');
        
        // Assertions
        $this->assertEquals($targetTeam->id, $user->fresh()->current_team_id);
        $this->assertTrue($targetTeam->users->contains($user));
        $this->assertFalse($sourceTeam->users->contains($user));
        
        // Check event migration
        $this->assertEquals($targetTeam->id, $event->fresh()->team_id);
    }
}
