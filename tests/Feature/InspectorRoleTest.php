<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Http\Livewire\EditEvent;
use App\Http\Livewire\ReportsComponent;

class InspectorRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_inspector_cannot_modify_events()
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());
        $team = $user->currentTeam;
        
        $inspector = User::factory()->create();
        $team->users()->attach($inspector, ['role' => 'inspect']);
        $inspector->switchTeam($team);

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'start' => now(),
            'end' => now()->addHour(),
            'is_open' => true, 
        ]);

        $this->actingAs($inspector);

        Livewire::test(EditEvent::class)
            ->call('edit', $event->id)
            ->assertSet('canBeModified', false);
    }

    public function test_inspector_can_access_audit_reports()
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());
        $team = $user->currentTeam;
        
        $inspector = User::factory()->create();
        $team->users()->attach($inspector, ['role' => 'inspect']);
        $inspector->switchTeam($team);

        $this->actingAs($inspector);

        Livewire::test(ReportsComponent::class)
            ->set('report_source', 'history')
            ->set('worker', '%')
            ->set('fromdate', now()->subDays(30)->format('Y-m-d'))
            ->set('todate', now()->format('Y-m-d'))
            ->set('event_type_id', 'All')
            ->set('rtype', 'XLS')
            ->call('export')
            ->assertStatus(200); // Verificar que la exportación se completó sin errores
    }
}
