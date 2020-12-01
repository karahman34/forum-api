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

    /**
     * Model not found response.
     *
     * @param   string  $modelName
     *
     * @return  JsonResponse
     */
    public static function modelNotFound(string $modelName = 'Model')
    {
        $message = "{$modelName} not found.";

        return response()->json(
            self::meta(false, $message),
            404
        );
    }

    /**
     * Authorization failed json response.
     *
     * @param   string  $message
     *
     * @return  JsonResponse
     */
    public static function authorizationFailed(string $message = null)
    {
        if (is_null($message)) {
            $message = 'User does not permitted to do this action.';
        }

        return response()->json(
            self::meta(false, $message),
            403
        );
    }
}
