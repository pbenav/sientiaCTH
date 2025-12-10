<?php

namespace App\Http\Livewire\Teams;

use App\Models\WorkCenter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;
use Livewire\Component;

/**
 * A Livewire component for managing work centers for a team.
 *
 * This component provides functionality for creating, updating, and deleting
 * work centers.
 */
class WorkCenterManager extends Component
{
    use AuthorizesRequests;

    public int $teamId;
    public $team = null;
    public bool $managingWorkCenters = true;

    public bool $confirmingWorkCenterRemoval = false;
    public ?int $workCenterIdBeingRemoved = null;

    public bool $confirmingWorkCenterManagement = false;
    public ?int $workCenterBeingUpdatedId = null;

    public array $currentNFCContent = [];

    public array $state = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:work_centers,code,' . ($this->workCenterBeingUpdatedId ? $this->workCenterBeingUpdatedId : 'NULL'),
            'nfc_tag_description' => 'nullable|string|max:500',
            'enable_nfc' => 'boolean',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ];
    }

    /**
     * Mount the component.
     *
     * @param mixed $team
     * @return void
     */
    public function mount($teamId): void
    {
        $this->teamId = (int)$teamId;
        $this->team = \App\Models\Team::findOrFail($this->teamId);
    }

    /**
     * Show the form for creating a new work center.
     *
     * @return void
     */
    public function confirmWorkCenterCreation(): void
    {
        $this->resetErrorBag();
        $this->state = [
            'enable_nfc' => false,
        ];
        $this->currentNFCContent = [];
        $this->workCenterBeingUpdatedId = null;
        $this->confirmingWorkCenterManagement = true;
    }

    /**
     * Create a new work center.
     *
     * @return void
     */
    public function createWorkCenter(): void
    {
        $this->resetErrorBag();

        Validator::make($this->state, $this->rules())->validate();

        $team = \App\Models\Team::findOrFail($this->teamId);
        $workCenter = $team->workCenters()->create([
            'name' => $this->state['name'],
            'code' => $this->state['code'],
            'address' => $this->state['address'] ?? null,
            'city' => $this->state['city'] ?? null,
            'postal_code' => $this->state['postal_code'] ?? null,
            'state' => $this->state['state'] ?? null,
            'country' => $this->state['country'] ?? null,
        ]);

        // Generar NFC si está habilitado
        if ($this->state['enable_nfc'] ?? false) {
            $workCenter->enableNFC($this->state['nfc_tag_description'] ?? null);
        }

        $this->confirmingWorkCenterManagement = false;
        $this->currentNFCContent = [];
        $this->emit('saved');
    }

    /**
     * Show the form for updating a work center.
     *
     * @param \App\Models\WorkCenter $workCenter
     * @return void
     */
    public function confirmWorkCenterUpdate($workCenterId): void
    {
        \Log::info('confirmWorkCenterUpdate called', ['raw' => $workCenterId]);
        $this->resetErrorBag();

        // Coerce flexible payloads (in case the caller accidentally passed the model/array)
        if (is_array($workCenterId)) {
            $workCenterId = (int) ($workCenterId['id'] ?? $workCenterId[0] ?? 0);
        } else {
            $workCenterId = (int) $workCenterId;
        }

        if ($workCenterId <= 0) {
            \Log::warning('confirmWorkCenterUpdate received invalid id', ['id' => $workCenterId]);
            $this->addError('work_center', 'Invalid work center id provided');
            return;
        }

        // Cargar el modelo internamente para evitar serialización pesada al pasar el modelo desde la vista
        $workCenter = WorkCenter::findOrFail($workCenterId);
        $this->workCenterBeingUpdatedId = $workCenter->id;
        $this->state = $workCenter->toArray();
        $this->state['enable_nfc'] = $workCenter->hasNFC();
        \Log::info('confirmWorkCenterUpdate state', ['state' => $this->state]);
        // Generar contenido NFC actual para mostrar en el modal
        $this->currentNFCContent = $workCenter->generateNFCTagContent();
        \Log::info('confirmWorkCenterUpdate NFC', ['currentNFCContent' => $this->currentNFCContent]);
        $this->confirmingWorkCenterManagement = true;
    }

    /**
     * Save the work center being updated.
     *
     * @return void
     */
    public function updateWorkCenter(): void
    {
        $this->resetErrorBag();
        try {
            Validator::make($this->state, $this->rules())->validate();

            $workCenter = WorkCenter::findOrFail($this->workCenterBeingUpdatedId);
            $workCenter->update([
                'name' => $this->state['name'],
                'code' => $this->state['code'],
                'address' => $this->state['address'] ?? null,
                'city' => $this->state['city'] ?? null,
                'postal_code' => $this->state['postal_code'] ?? null,
                'state' => $this->state['state'] ?? null,
                'country' => $this->state['country'] ?? null,
            ]);

            // Gestionar estado de NFC
            if ($this->state['enable_nfc'] ?? false) {
                if (!$workCenter->hasNFC()) {
                    $workCenter->enableNFC($this->state['nfc_tag_description'] ?? null);
                } else {
                    // Actualizar descripción si ha cambiado
                    if (($this->state['nfc_tag_description'] ?? null) !== $workCenter->nfc_tag_description) {
                        $workCenter->update([
                            'nfc_tag_description' => $this->state['nfc_tag_description'] ?? null
                        ]);
                    }
                }
            } else {
                $workCenter->disableNFC();
            }

            $this->confirmingWorkCenterManagement = false;
            $this->currentNFCContent = [];
            $this->workCenterBeingUpdatedId = null;
            $this->emit('saved');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        } catch (\Exception $e) {
            $this->addError('code', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Confirm the removal of a work center.
     *
     * @param int $workCenterId
     * @return void
     */
    public function confirmWorkCenterRemoval($workCenterId): void
    {
        $workCenterId = is_array($workCenterId) ? (int) ($workCenterId['id'] ?? $workCenterId[0] ?? 0) : (int) $workCenterId;
        if ($workCenterId <= 0) {
            $this->addError('work_center', 'Invalid work center id provided');
            return;
        }

        $this->confirmingWorkCenterRemoval = true;
        $this->workCenterIdBeingRemoved = $workCenterId;
    }

    /**
     * Remove a work center.
     *
     * @return void
     */
    public function removeWorkCenter(): void
    {
        WorkCenter::find($this->workCenterIdBeingRemoved)->delete();
        $this->confirmingWorkCenterRemoval = false;
    }

    /**
     * Generate a new NFC tag ID for a work center.
     *
     * @param WorkCenter $workCenter
     * @return void
     */
    public function regenerateNFCTag($workCenterId): void
    {
        $workCenterId = is_array($workCenterId) ? (int) ($workCenterId['id'] ?? $workCenterId[0] ?? 0) : (int) $workCenterId;
        $workCenter = WorkCenter::findOrFail($workCenterId);
        if ($workCenter->hasNFC()) {
            $workCenter->disableNFC();
        }
        $workCenter->enableNFC($workCenter->nfc_tag_description);
        $this->emit('saved');
    }

    /**
     * Copy NFC tag ID to clipboard (handled by frontend).
     *
     * @param WorkCenter $workCenter
     * @return void
     */
    public function copyNFCTagId($workCenterId): void
    {
        $workCenterId = is_array($workCenterId) ? (int) ($workCenterId['id'] ?? $workCenterId[0] ?? 0) : (int) $workCenterId;
        $workCenter = WorkCenter::findOrFail($workCenterId);
        $this->emit('nfcTagCopied', $workCenter->nfc_tag_id);
    }

    /**
     * Regenerate NFC content for the work center being edited
     *
     * @return void
     */
    public function regenerateNFCContentInModal(): void
    {
        if ($this->workCenterBeingUpdatedId) {
            $workCenter = WorkCenter::find($this->workCenterBeingUpdatedId);
            if ($workCenter) {
                \Log::info('regenerateNFCContentInModal for workCenter', ['id' => $this->workCenterBeingUpdatedId]);
                $this->currentNFCContent = $workCenter->generateNFCTagContent();
            }
        }
    }

    /**
     * Generate NFC content for a new work center (preview)
     *
     * @return void
     */
    public function generateNFCContentForNew(): void
    {
        $team = \App\Models\Team::findOrFail($this->teamId);
        $tempWorkCenter = new WorkCenter([
            'name' => $this->state['name'] ?? 'New Work Center',
            'code' => $this->state['code'] ?? 'TEMP',
            'team_id' => $team->id,
        ]);
        $tempWorkCenter->id = 999999;
        $this->currentNFCContent = $tempWorkCenter->generateNFCTagContent(
            $this->state['nfc_tag_description'] ?? null
        );
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $team = \App\Models\Team::findOrFail($this->teamId);
        \Log::info('WorkCenterManager render', [
            'teamId' => $this->teamId,
            'workCenterBeingUpdatedId' => $this->workCenterBeingUpdatedId,
            'state' => $this->state,
            'confirmingWorkCenterManagement' => $this->confirmingWorkCenterManagement
        ]);
        return view('livewire.teams.work-center-manager', [
            'workCenters' => $team->workCenters()->paginate(5)
        ]);
    }
}
