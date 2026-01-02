<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateTeamNameForm;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateTeamLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_admin_can_update_team_limit()
    {
        $this->actingAs($user = User::factory()->admin()->withPersonalTeam()->create());

        Livewire::test(UpdateTeamNameForm::class, ['team' => $user->currentTeam])
                    ->set(['state' => [
                        'name' => 'Test Team',
                        'max_member_teams' => 10,
                    ]])
                    ->call('updateTeamName');

        $this->assertEquals(10, $user->currentTeam->fresh()->max_member_teams);
    }

    public function test_regular_team_owner_cannot_update_team_limit()
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;
        $team->max_member_teams = 5;
        $team->save();

        $this->actingAs($user);

        Livewire::test(UpdateTeamNameForm::class, ['team' => $team])
                    ->set(['state' => [
                        'name' => 'Updated Name',
                        'max_member_teams' => 10,
                    ]])
                    ->call('updateTeamName');

        $this->assertEquals('Updated Name', $team->fresh()->name);
        $this->assertEquals(5, $team->fresh()->max_member_teams);
    }
}
