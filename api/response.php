<?php

class Response
{
    public static function success($data = [], string $message = 'Success', int $status = 200): void
    {
        http_response_code($status);

        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_OPTIONS);

        exit;
    }

    public static function error(string $message = 'Error', int $status = 400, $errors = []): void
    {
        http_response_code($status);

        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_OPTIONS);

        exit;
    }

    public static function notFound(string $message = 'Not Found'): void
    {
        self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    public static function serverError(string $message = 'Internal Server Error'): void
    {
        self::error($message, 500);
    }
}