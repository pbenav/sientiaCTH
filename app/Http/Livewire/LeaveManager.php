<?php
namespace App\Http\Livewire;

use App\Models\Leave;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

namespace App\Http\Livewire;

use App\Models\Leave;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LeaveManager extends Component
{
    public $leaves;
    public $type;
    public $fromdate;
    public $todate;
    public $description;
    public $leaveId;
    public $user;

    protected $rules = [
        'type' => 'required|string',
        'fromdate' => 'required|date',
        'todate' => 'required|date|after_or_equal:fromdate',
        'description' => 'nullable|string',
    ];

    public function mount()
    {
        $this->type = __('Holidays');
        $this->fetchLeaves();
        $this->user = Auth::user();
        $this->fromdate = date('Y-m-01');
        $this->todate = date('Y-m-d');
    }

    public function fetchLeaves()
    {
        $this->leaves = Leave::where('user_id', Auth::id())->get();
        //dd($this->leaves);
    }

    public function save()
    {
        $this->validate();

        Leave::updateOrCreate(['id' => $this->leaveId], [
            'user_id' => Auth::id(),
            'type' => $this->type,
            'fromdate' => $this->fromdate,
            'todate' => $this->todate,
            'description' => $this->description,
        ]);

        $this->resetInputFields();
        $this->fetchLeaves();
        session()->flash('message', $this->leaveId ? 'Leave updated successfully.' : 'Leave created successfully.');

        $this->dispatchBrowserEvent('leaves-updated', ['leaves' => $this->leaves]);
    }

    public function edit($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->user_id != Auth::id()) {
            abort(403);
        }

        $this->leaveId = $id;
        $this->type = $leave->type;
        $this->fromdate = $leave->fromdate;
        $this->todate = $leave->todate;
        $this->description = $leave->description;
    }

    public function delete($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->user_id != Auth::id()) {
            abort(403);
        }

        $leave->delete();
        $this->fetchLeaves();
        session()->flash('message', 'Leave deleted successfully.');

        $this->dispatchBrowserEvent('leaves-updated', ['leaves' => $this->leaves]);
    }

    private function resetInputFields()
    {
        $this->type = __('Holidays');
        $this->fromdate = date('Y-m-01');
        $this->todate = date('Y-m-d');
        $this->description = '';
        $this->leaveId = '';
    }

    public function render()
    {
        return view('livewire.leaves.leave-manager');
    }
}
