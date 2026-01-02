<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardPreferencesController extends Controller
{
    /**
     * Store the dashboard widget preferences (order and visibility).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|string',
            'hidden' => 'sometimes|array',
            'hidden.*' => 'required|string',
        ]);

        $user = Auth::user();
        
        // Store the widget preferences in user_meta
        $user->setDashboardWidgetOrder([
            'order' => $request->input('order'),
            'hidden' => $request->input('hidden', []),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Dashboard layout saved successfully'),
        ]);
    }

    /**
     * Reset the dashboard widget order to default.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset()
    {
        $user = Auth::user();
        
        // Remove the dashboard_widget_order meta
        $user->metas()->where('meta_key', 'dashboard_widget_order')->delete();

        return response()->json([
            'success' => true,
            'message' => __('Dashboard layout reset to default'),
        ]);
    }
}
