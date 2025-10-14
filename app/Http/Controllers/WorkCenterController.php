<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\WorkCenter;
use Illuminate\Http\Request;

class WorkCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function index(Team $team)
    {
        // This view will be integrated into the team settings page.
        // We pass the team and its work centers to the view.
        $workCenters = $team->workCenters;
        return view('teams.show', compact('team', 'workCenters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function create(Team $team)
    {
        return view('work_centers.create', compact('team'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:work_centers,code',
            'address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
        ]);

        $team->workCenters()->create($request->all());

        return redirect()->route('teams.show', $team);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkCenter  $workCenter
     * @return \Illuminate\Http\Response
     */
    public function show(WorkCenter $workCenter)
    {
        // Not used
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkCenter  $workCenter
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkCenter $workCenter)
    {
        return view('work_centers.edit', compact('workCenter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkCenter  $workCenter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WorkCenter $workCenter)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:work_centers,code,' . $workCenter->id,
            'address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
        ]);

        $workCenter->update($request->all());

        return redirect()->route('teams.show', $workCenter->team);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkCenter  $workCenter
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkCenter $workCenter)
    {
        $team = $workCenter->team;
        $workCenter->delete();

        return redirect()->route('teams.show', $team);
    }
}
