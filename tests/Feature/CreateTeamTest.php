<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_teams_can_be_created()
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->personalTeam());
        $this->actingAs($user);

        // Grant permission to create teams (global)
        $permission = \App\Models\Permission::firstOrCreate(
            ['name' => 'teams.create'], 
            ['display_name' => 'Create Teams', 'is_system' => true, 'requires_context' => false]
        );
        
        // Use the trait method
        $user->givePermissionTo('teams.create');
        
        // Set a limit on the current team
        $user->currentTeam->update(['max_member_teams' => 5]);
        
        // Clear cache to ensure permission is detected
        \Illuminate\Support\Facades\Cache::flush();

        Livewire::test(CreateTeamForm::class)
                    ->set(['state' => ['name' => 'Test Team']])
                    ->call('createTeam');

        $this->assertCount(2, $user->fresh()->ownedTeams);
        $this->assertEquals('Test Team', $user->fresh()->ownedTeams()->latest('id')->first()->name);
    }
}
