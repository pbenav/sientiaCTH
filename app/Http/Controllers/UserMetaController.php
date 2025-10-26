<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * Handles the logic for managing user metadata.
 *
 * This controller is responsible for displaying, storing, and deleting user
 * metadata.
 */
class UserMetaController extends Controller
{
    /**
     * Display the form for managing a user's metadata.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\View\View
     */
    public function index(User $user)
    {
        if (!(Auth::id() === $user->id || Auth::user()?->isTeamAdmin())) {
            abort(403);
        }
        // Load all of the user's metadata to display in a view.
        $metaData = $user->meta()->get();

        return view('profile.meta.index', compact('user', 'metaData'));
    }

    /**
     * Store or update a piece of metadata for a user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, User $user)
    {
        if (!(Auth::id() === $user->id || Auth::user()?->isTeamAdmin())) {
            abort(403);
        }
        // 1. Validate the input
        $validator = Validator::make($request->all(), [
            'meta_key' => 'required|string|max:255',
            'meta_value' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Find and update the metadata, or create it if it doesn't exist.
        $userMeta = $user->meta()->updateOrCreate(
            ['meta_key' => $request->meta_key],
            ['meta_value' => $request->meta_value]
        );

        return back()->with('success', 'Metadata saved successfully.');
    }

    /**
     * Delete a specific piece of metadata for a user.
     *
     * @param \App\Models\User $user
     * @param \App\Models\UserMeta $meta
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user, UserMeta $meta)
    {
        if (!(Auth::id() === $user->id || Auth::user()?->isTeamAdmin())) {
            abort(403);
        }

        // 1. Verify that the metadata belongs to the user.
        if ($meta->user_id !== $user->id) {
            return back()->with('error', 'Metadata not found or does not belong to the user.');
        }

        // 2. Delete the metadata.
        $meta->delete();

        return back()->with('success', 'Metadata deleted successfully.');
    }
}