<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateVacationPreferencesForm extends Component
{
    public $state = [];

    protected $rules = [
        'state.vacation_calculation_type' => 'required|string|in:natural,working',
        'state.vacation_working_days' => 'required_if:state.vacation_calculation_type,working|nullable|integer|min:1|max:365',
    ];

    protected $messages = [
        'state.vacation_calculation_type.required' => 'Debe seleccionar un tipo de cálculo.',
        'state.vacation_calculation_type.in' => 'El tipo de cálculo seleccionado no es válido.',
        'state.vacation_working_days.required_if' => 'Debe especificar el número de días hábiles.',
        'state.vacation_working_days.integer' => 'El número de días debe ser un número entero.',
        'state.vacation_working_days.min' => 'El número de días debe ser al menos 1.',
        'state.vacation_working_days.max' => 'El número de días no puede exceder 365.',
    ];

    public function mount()
    {
        $this->state = [
            'vacation_calculation_type' => Auth::user()->vacation_calculation_type ?? 'natural',
            'vacation_working_days' => Auth::user()->vacation_working_days ?? 22,
        ];
    }

    public function updateVacationPreferences()
    {
        $this->validate();

        Auth::user()->forceFill([
            'vacation_calculation_type' => $this->state['vacation_calculation_type'],
            'vacation_working_days' => $this->state['vacation_working_days'],
        ])->save();

        $this->emit('saved');

        session()->flash('status', 'vacation-preferences-updated');
    }

    public function render()
    {
        return view('livewire.profile.update-vacation-preferences-form');
    }
}
