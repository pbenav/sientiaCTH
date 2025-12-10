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
        $this->actingAs($user = User::factory()->create(['max_owned_teams' => 2]));

        // Create 1st team (user already has personal team if factory creates it, but let's assume standard factory)
        // Note: User factory usually creates a personal team if withPersonalTeam() is called.
        // Let's create teams manually to be sure.
        
        $user->ownedTeams()->create(['name' => 'Team 1', 'personal_team' => false]);
        $user->ownedTeams()->create(['name' => 'Team 2', 'personal_team' => false]);

        // Try to create 3rd team via Livewire component
        Livewire::test(CreateTeamForm::class)
            ->set(['state' => ['name' => 'Team 3']])
            ->call('createTeam')
            ->assertHasErrors(['name']);
            
        $this->assertCount(2, $user->fresh()->ownedTeams);
    }

    public function test_global_admin_can_exceed_team_limit()
    {
        $this->actingAs($admin = User::factory()->create(['max_owned_teams' => 1, 'is_admin' => true]));

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
            'title' => 'Test Event',
            'start' => now(),
            'end' => now()->addHour(),
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
