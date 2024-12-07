<?php

namespace App\Traits;

use Hashids\Hashids;
use Exception;

trait ShortCodeGenerator
{
    /**
     * Genera un código corto basado en una longitud mínima y máxima.
     *
     * @param int $minLength Longitud mínima del código.
     * @param int $maxLength Longitud máxima del código.
     * @return string
     */
    protected function generateShortCode($minLength = 4, $maxLength = 8)
    {
        $length = random_int($minLength, $maxLength); // Longitud aleatoria dentro del rango
        $randomString = bin2hex(random_bytes(ceil($length / 2))); // Genera bytes aleatorios

        return substr($randomString, 0, $length); // Ajusta la longitud al valor exacto
    }

    /**
     * Genera un código único garantizado verificando en la base de datos.
     *
     * @param int $minLength Longitud mínima del código.
     * @param int $maxLength Longitud máxima del código.
     * @return string
     * @throws Exception Si no se puede generar un código único después de varios intentos.
     */
    protected function generateUniqueShortCode($minLength = 4, $maxLength = 8)
    {
        $attempts = 0;
        $maxAttempts = 10; // Limitar a un número razonable de intentos

        while ($attempts < $maxAttempts) {
            $shortCode = $this->generateShortCode($minLength, $maxLength);

            // Verifica si el código ya existe en la base de datos
            if (!\App\Models\Url::where('short_code', $shortCode)->exists()) {
                return $shortCode; // Devuelve el código único
            }

            $attempts++;
        }

        // Lanza una excepción si no se encuentra un código único
        throw new Exception('No se pudo generar un código único después de varios intentos.');
    }
}
