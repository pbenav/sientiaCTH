<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserMetaController extends Controller
{
    /**
     * Muestra el formulario para gestionar los metadatos de un usuario.
     * GET /users/{user}/meta
     */
    public function index(User $user)
    {
        if (!(Auth::id() === $user->id || Auth::user()?->isTeamAdmin())) {
            abort(403);
        }
        // Se cargan todos los metadatos del usuario para mostrarlos en una vista.
        $metaData = $user->meta()->get();

        return view('profile.meta.index', compact('user', 'metaData'));
    }

    /**
     * Almacena o actualiza un metadato para un usuario.
     * POST /users/{user}/meta
     */
    public function store(Request $request, User $user)
    {
        if (!(Auth::id() === $user->id || Auth::user()?->isTeamAdmin())) {
            abort(403);
        }
        // 1. Validar la entrada
        $validator = Validator::make($request->all(), [
            'meta_key' => 'required|string|max:255',
            'meta_value' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Buscar si el metadato ya existe y actualizarlo, o crearlo si no.
        $userMeta = $user->meta()->updateOrCreate(
            ['meta_key' => $request->meta_key],
            ['meta_value' => $request->meta_value]
        );

        return back()->with('success', 'Metadato guardado correctamente.');
    }

    /**
     * Elimina un metadato específico para un usuario.
     * DELETE /users/{user}/meta/{meta}
     */
    public function destroy(User $user, UserMeta $meta)
    {
        if (!(Auth::id() === $user->id || Auth::user()?->isTeamAdmin())) {
            abort(403);
        }

        // 1. Verificar si el metadato pertenece al usuario.
        if ($meta->user_id !== $user->id) {
            return back()->with('error', 'Metadato no encontrado o no pertenece al usuario.');
        }

        // 2. Eliminar el metadato.
        $meta->delete();

        return back()->with('success', 'Metadato eliminado correctamente.');
    }
}