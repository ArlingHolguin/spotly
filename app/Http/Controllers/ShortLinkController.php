<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;

class ShortLinkController extends Controller
{
    //
    public function show($short_code)
    {
        $url = Url::where('short_code', $short_code)->firstOrFail();

        // Verificar si está activa y no ha expirado
        if (!$url->is_active || ($url->expires_at && now()->greaterThan($url->expires_at))) {
            return response()->json(['message' => 'La URL ha expirado o no está activa.'], 410);
        }

        // Incrementar el contador de clics
        $url->increment('clicks');

        if (!$url) {
            // Si no existe, puedes retornar 404 o una página especial
            abort(404);
        }

        // Si existe, simplemente redireccionas
        return redirect($url->original_url);
    }
}
