<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    /**
     * Start impersonating a user
     */
    public function impersonate(Request $request, $userId)
    {
        // Verificar permiso de impersonalización
        if (!userCan('users.impersonate')) {
            abort(403, __('Unauthorized action'));
        }

        $userToImpersonate = User::findOrFail($userId);

        // No permitir impersonar al mismo usuario
        if ($userToImpersonate->id === Auth::id()) {
            return redirect()->back()->with('error', __('Cannot impersonate yourself'));
        }

        // Guardar el ID del admin original
        Session::put('impersonator_id', Auth::id());
        Session::put('impersonated_at', now());

        // Login como el usuario a impersonar usando el guard web
        Auth::guard('web')->login($userToImpersonate, true); // true = remember
        
        // Regenerar la sesión para evitar problemas de expiración
        $request->session()->regenerate();

        return redirect()->route('inicio')->with('success', __('Now viewing as :name', ['name' => $userToImpersonate->name]));
    }

    /**
     * Stop impersonating and return to original user
     */
    public function leave(Request $request)
    {
        if (!Session::has('impersonator_id')) {
            return redirect()->route('inicio');
        }

        $impersonatorId = Session::get('impersonator_id');
        $originalUser = User::findOrFail($impersonatorId);

        // Limpiar la sesión de impersonación
        Session::forget('impersonator_id');
        Session::forget('impersonated_at');

        // Volver al usuario original usando el guard web
        Auth::guard('web')->login($originalUser, true); // true = remember
        
        // Regenerar la sesión
        $request->session()->regenerate();

        return redirect()->route('inicio')->with('success', __('Returned to your account'));
    }

    /**
     * Check if currently impersonating
     */
    public static function isImpersonating()
    {
        return Session::has('impersonator_id');
    }

    /**
     * Get the original user (impersonator)
     */
    public static function getImpersonator()
    {
        if (!self::isImpersonating()) {
            return null;
        }

        return User::find(Session::get('impersonator_id'));
    }
}
