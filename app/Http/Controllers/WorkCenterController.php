<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WorkCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $workCenters = \App\Models\WorkCenter::all();
        return view('work_centers.index', compact('workCenters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('work_centers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:work_centers',
            'address' => 'required',
        ]);

        \App\Models\WorkCenter::create($request->all());

        return redirect()->route('work_centers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(\App\Models\WorkCenter $workCenter)
    {
        return view('work_centers.edit', compact('workCenter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, \App\Models\WorkCenter $workCenter)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:work_centers,code,' . $workCenter->id,
            'address' => 'required',
        ]);

        $workCenter->update($request->all());

        return redirect()->route('work_centers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(\App\Models\WorkCenter $workCenter)
    {
        $workCenter->delete();

        return redirect()->route('work_centers.index');
    }
}
