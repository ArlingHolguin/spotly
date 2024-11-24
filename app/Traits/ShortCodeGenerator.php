<?php

namespace App\Traits;

use Hashids\Hashids;
use Exception;

trait ShortCodeGenerator
{
    protected function generateShortCode($minLength = 4, $maxLength = 8)
    {
        $alphabet = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789';
        $salt = config('app.key');
        $hashids = new Hashids($salt, $minLength, $alphabet);

        $randomNumber = random_int(1, 9999999);
        $shortCode = $hashids->encode($randomNumber);

        while (strlen($shortCode) > $maxLength) {
            $randomNumber = random_int(1, 9999999);
            $shortCode = $hashids->encode($randomNumber);
        }

        return $shortCode;
    }

    protected function generateUniqueShortCode($minLength = 4, $maxLength = 8)
    {
        $attempts = 0;
        $maxAttempts = 100; // Límite para evitar bucles infinitos

        do {
            $shortCode = $this->generateShortCode($minLength, $maxLength);
            $attempts++;

            // Si los intentos superan el límite, lanzar una excepción
            if ($attempts > $maxAttempts) {
                throw new Exception('No se pudo generar un código único después de varios intentos.');
            }
        } while (\App\Models\Url::where('short_code', $shortCode)->exists());

        return $shortCode;
    }
}
