<?php

namespace App\Response;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function successResponse($statusCode = 200, $message = 'Success')
    {
        return [
            'status_code' => $statusCode,
            'message' => $message,
        ];
    }

    public static function errorResponse($statusCode = 400, $message = "error")
    {
        return [
            'status_code' => $statusCode,
            'message' => $message
        ];
    }

    public static function successWData($statusCode = 200, $message = 'Success', $data)
    {
        return [
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function getStatusResponse(array $response): JsonResponse
    {
        $statusCode = $response['status_code'] ?? 400;
        $data = $response['data'] ?? null;

        return match ($statusCode) {
            200 => response()->json($response, 200),
            204 => response()->json(null, 204),
            400 => response()->json($response, 400),
            401 => response()->json($response, 401),
            403 => response()->json($response, 403),
            404 => response()->json($response, 404),
            409 => response()->json($response, 409),
            500 => response()->json($response, 500),
            default => response()->json($response, 400),
        };
    }
}
