<?php
/**
 * NeuralPress - standard API response sender
 */

namespace NeuralPress\API;

class Response {
    public static function json($data, int $statusCode = 200): void {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function error(string $message, int $statusCode = 400): void {
        self::json(['error' => $message, 'success' => false], $statusCode);
    }

    public static function success($payload = []): void {
        self::json(array_merge(['success' => true], $payload));
    }
}
