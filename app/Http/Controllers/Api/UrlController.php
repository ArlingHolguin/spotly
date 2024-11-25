<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Url;
use App\Models\User;
use App\Traits\ShortCodeGenerator;
use App\Traits\TokenValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UrlController extends Controller
{
    use ShortCodeGenerator;
    use TokenValidator;

    // Doc para sagwr 
    /**
     * @OA\PathItem(
     * @OA\Info(
     *             title="Api Rest - Shortener", 
     *             version="1.0",
     *             description="Listado de urls api Url"
     * )
     *
     * @OA\Server(url="http://spotly.test")
     * description="Servidor local"
     */

    /**
     * Listado de las ultimas urls acortadas
     * @OA\Get (
     *     path="/api/v1/urls",
     *     tags={"Urls"},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 type="array",
     *                 property="rows",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="number",
     *                         example="12"
     *                     ),
     *                     @OA\Property(
     *                         property="user_id",
     *                         type="number",
     *                         example="1"
     *                     ),
     *                     @OA\Property(
     *                         property="original_url",
     *                         type="string",
     *                         example="https://example.com/"
     *                     ),
     *                     @OA\Property(
     *                         property="short_code",
     *                         type="string",
     *                         example="short_code"
     *                     ),
     *                     @OA\Property(
     *                         property="is_active",
     *                         type="number",
     *                         example="1"
     *                     ),
     *                      @OA\Property(
     *                         property="clicks",
     *                         type="number",
     *                         example="0"
     *                     ),
     *                          @OA\Property(
     *                         property="expires_at",
     *                         type="string",
     *                         example="2024-12-24 11:20:19"
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         example="2023-02-23T00:09:16.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         example="2023-02-23T12:33:45.000000Z"
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     * )
     */

    public function index(Request $request)
    {
        $userId = $this->getUserIdFromToken($request->bearerToken());
        // Si está autenticado
        if ($userId) {
            $user = User::find($userId);

            if ($user->role === 'admin') {
                $urls = Url::orderBy('created_at', 'desc')->paginate(15);
                return response()->json(['data' => $urls], 200);
            }

            $urls = Url::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(15);
            return response()->json(['data' => $urls], 200);
        }

        $urls = Url::whereNull('user_id')->orderBy('created_at', 'desc')->take(10)->get();
        return response()->json(['data' => $urls], 200);
    }




    /**
     * Store a newly created resource in storage.
     */
    // Crear una nueva URL
    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_url' => 'required|url|max:2048',
        ]);

        if (Str::startsWith($validated['original_url'], 'javascript:')) {
            abort(400, 'URL no válida.');
        }

        if ($request->input('honeypot') !== null) {
            abort(400, 'Detección de bot.');
        }

        $userId = $this->getUserIdFromToken($request->bearerToken());

        $url = Url::create([
            'original_url' => $validated['original_url'],
            'short_code' => $this->generateUniqueShortCode(),
            'user_id' => $userId,
            'expires_at' => $userId ? null : now()->addDays(30),
        ]);

        return response()->json(['data' => $url], 201);
    }



    /**
     * Display the specified resource.
     */
    // Mostrar una URL específica
    public function show($short_code)
    {
        // Buscar la URL por su código corto
        $url = Url::where('short_code', $short_code)->firstOrFail();

        // Verificar si la URL está activa y no ha expirado
        if (!$url->is_active || ($url->expires_at && now()->greaterThan($url->expires_at))) {
            return response()->json(['message' => 'La URL ha expirado o no está activa'], 410); // HTTP 410 Gone
        }

        // Incrementar el conteo de clics
        $url->increment('clicks');

        // Redireccionar a la URL original
        return redirect($url->original_url);
    }


    /**
     * Update the specified resource in storage.
     */
    // Editar una URL (solo autenticado)
    public function update(Request $request, $short_code)
    {
        $validated = $request->validate([
            'original_url' => 'required|url|max:2048',
        ]);

        // Obtener usuario autenticado (si existe)
        $userId = $this->getUserIdFromToken($request->bearerToken());

        // Buscar la URL por el código corto
        $url = Url::where('short_code', $short_code)->firstOrFail();

        // Validar permisos: solo el dueño o un administrador pueden editar
        if ($url->user_id !== $userId) {
            $user = User::find($userId);
            if (!$user || $user->role !== 'admin') {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        }

        // Actualizar la URL
        $url->update(['original_url' => $validated['original_url']]);

        return response()->json(['message' => 'URL actualizada correctamente', 'data' => $url], 200);
    }


    /**
     * Remove the specified resource from storage.
     */

    public function destroy($short_code, Request $request)
    {
        $url = Url::where('short_code', $short_code)->firstOrFail();

        // Obtener el ID del usuario autenticado
        $userId = $this->getUserIdFromToken($request->bearerToken());

        // Validar si el usuario es el dueño de la URL
        // if ($url->user_id !== $userId) {
        //     $user = User::find($userId);
        //     if (!$user || $user->role !== 'admin') {
        //         return response()->json(['error' => 'No autorizado'], 403);
        //     }
        // }

        $url->delete();

        return response()->json(['message' => 'URL eliminada correctamente'], 200);
    }
}
