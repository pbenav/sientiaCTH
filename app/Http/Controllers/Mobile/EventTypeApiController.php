<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EventTypeApiController extends Controller
{
    /**
     * Devuelve la lista de tipos de evento con nombre traducido según el idioma.
     */
    public function index(Request $request)
    {
        $locale = $request->get('locale', App::getLocale());
        $eventTypes = EventType::all()->map(function ($type) use ($locale) {
            // Si tienes una tabla de traducciones, aquí deberías buscar el nombre traducido
            $translatedName = $type->getTranslation('name', $locale) ?? $type->name;
            return [
                'id' => $type->id,
                'name' => $translatedName,
                'is_workday_type' => $type->is_workday_type,
                'is_break_type' => $type->is_break_type,
                'color' => $type->color,
            ];
        });
        return response()->json(['event_types' => $eventTypes]);
    }
}
