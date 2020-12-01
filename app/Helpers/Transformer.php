<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class Transformer
{
    /**
     * Meta
     *
     * @param   bool    $ok
     * @param   string  $message
     *
     * @return  array
     */
    public static function meta(bool $ok, string $message)
    {
        return [
            'ok' => $ok,
            'message' => $message
        ];
    }

    /**
     * Success response json.
     *
     * @param   string  $message
     * @param   mixed   $data
     * @param   int     $status
     * @param   array   $headers
     *
     * @return  JsonResponse
     */
    public static function ok(string $message, $data = null, int $status = 200, array $headers = [])
    {
        return response()->json(
            array_merge(
                self::meta(true, $message),
                ['data' => $data]
            ),
            $status,
            $headers
        );
    }

    /**
     * Failed response json.
     *
     * @param   string  $message
     * @param   mixed   $data
     * @param   int     $status
     * @param   array   $headers
     *
     * @return  JsonResponse
     */
    public static function fail(string $message, $data = null, int $status = 500, array $headers = [])
    {
        return response()->json(
            array_merge(
                self::meta(false, $message),
                ['data' => $data]
            ),
            $status,
            $headers
        );
    }
}
