<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Url;
use App\Models\User;
use App\Traits\ShortCodeGenerator;
use App\Traits\TokenValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     title="Api Rest - Shortener",
 *     version="1.0",
 *     description="API para acortar URLs"
 * )
 * 
 * @OA\Server(
 *     url="http://spotly.test",
 *     description="Servidor local"
 * )
 */




class UrlController extends Controller
{
    use ShortCodeGenerator;
    use TokenValidator;





    /**
     * Listado de las últimas URLs acortadas
     * 
     * @OA\Get(
     *     path="/api/v1/urls",
     *     tags={"Urls"},
     *     summary="Obtener las últimas URLs acortadas",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de URLs",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 type="array",
     *                 property="data",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="original_url", type="string", example="https://example.com/"),
     *                     @OA\Property(property="short_code", type="string", example="short_code"),
     *                     @OA\Property(property="is_active", type="integer", example=1),
     *                     @OA\Property(property="clicks", type="integer", example=0),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-12-24 11:20:19"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-02-23T00:09:16.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-02-23T12:33:45.000000Z")
     *                 )
     *             )
     *         )
     *     )
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
     * Crear una nueva URL acortada
     * 
     * @OA\Post(
     *     path="/api/v1/urls",
     *     tags={"Urls"},
     *     summary="Crear una URL acortada",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="original_url", type="string", example="https://example.com/")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="URL creada con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Url")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos inválidos"
     *     )
     * )
     */


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
     * Eliminar una URL acortada
     * 
     * @OA\Delete(
     *     path="/api/v1/urls/{short_code}",
     *     tags={"Urls"},
     *     summary="Eliminar una URL acortada",
     *     description="Permite eliminar una URL acortada. Solo el dueño de la URL o un administrador puede realizar esta acción.",
     *     @OA\Parameter(
     *         name="short_code",
     *         in="path",
     *         required=true,
     *         description="El código corto de la URL a eliminar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="URL eliminada con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="URL eliminada correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No autorizado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="URL no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="URL no encontrada")
     *         )
     *     )
     * )
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
