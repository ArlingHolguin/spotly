<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Url",
 *     type="object",
 *     description="Modelo de una URL acortada",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="original_url", type="string", example="https://example.com"),
 *     @OA\Property(property="short_code", type="string", example="abc123"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="clicks", type="integer", example=0),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-25T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-25T12:00:00Z"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-12-25T10:00:00Z")
 * )
 */

class Url extends Model
{
    /** @use HasFactory<\Database\Factories\UrlFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    //relacion uno s muchos inversa con la tabla users
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
