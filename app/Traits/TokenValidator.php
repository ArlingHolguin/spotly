<?php

namespace App\Traits;

use Laravel\Sanctum\PersonalAccessToken;

trait TokenValidator
{
    /**
     * Valida si el token es válido y retorna el user_id asociado.
     *
     * @param string|null $token
     * @return int|null
     */
    public function getUserIdFromToken(?string $token): ?int
    {
        if (!$token) {
            return null;
        }

        $personalAccessToken = PersonalAccessToken::findToken($token);

        if (
            $personalAccessToken &&
            $personalAccessToken->tokenable_type === \App\Models\User::class &&
            (!$personalAccessToken->expires_at || now()->lessThan($personalAccessToken->expires_at))
        ) {
            return $personalAccessToken->tokenable_id; // Retorna el user_id
        }

        return null; // Token no válido o expirado
    }
}
