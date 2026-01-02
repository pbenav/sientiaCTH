<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Permissions\PermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_team_admin_with_permission_can_update_team_limit()
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;

        PermissionMatrix::syncTeamRoles($team->id, $user->id);

        $this->actingAs($user);

        Livewire::test(UpdateTeamNameForm::class, ['team' => $team])
                    ->set(['state' => [
                        'name' => 'Updated Name',
                        'max_member_teams' => 10,
                    ]])
                    ->call('updateTeamName');

        $this->assertEquals('Updated Name', $team->fresh()->name);
        $this->assertEquals(10, $team->fresh()->max_member_teams);
    }

    public function test_team_admin_without_permission_cannot_update_team_limit()
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;

        PermissionMatrix::syncTeamRoles($team->id, $user->id);

        $adminRoleId = DB::table('roles')
            ->where('team_id', $team->id)
            ->where('name', PermissionMatrix::roleName('administrador', $team->id))
            ->value('id');

        $permissionId = DB::table('permissions')
            ->where('name', 'teams.limits.manage')
            ->value('id');

        DB::table('permission_role')
            ->where('role_id', $adminRoleId)
            ->where('permission_id', $permissionId)
            ->delete();

        $team->update(['max_member_teams' => 5]);

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
