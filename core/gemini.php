<?php
/**
 * NeuralPress - Gemini AI Gateway for PHP
 * Connects directly to Gemini models using pure PHP cURL.
 * No dependencies, high performance, and proxy ready.
 */

namespace NeuralPress\Core;

class GeminiAPI {
    private string $apiKey;
    private string $apiVersion = 'v1beta';
    private string $model = 'gemini-3.5-flash';

    public function __construct(string $apiKey = "") {
        // Auto-resolve API keys from environments
        if (empty($apiKey)) {
            $this->apiKey = getenv('GEMINI_API_KEY') ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '');
        } else {
            $this->apiKey = $apiKey;
        }

        if (defined('GEMINI_MODEL')) {
            $this->model = GEMINI_MODEL;
        }
    }

    /**
     * Executes a generation call to Gemini
     *
     * @param string $prompt Plaintext instruction or query string
     * @param string $systemInstruction Contextual core rules for the model run
     * @param bool $asJson If true, triggers structure output schemas
     * @return array Response dataset parsed from standard JSON output
     */
    public function generate(string $prompt, string $systemInstruction = "", bool $asJson = false): array {
        if (empty($this->apiKey)) {
            error_log("[Gemini Error] GEMINI_API_KEY is null or missing.");
            return ["error" => "Gemini API key is unconfigured on the server-side.", "status" => 500];
        }

        $url = "https://generativelanguage.googleapis.com/{$this->apiVersion}/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.2, // Low temperature for high deterministic accuracy (trust rating)
                "topK" => 32,
                "topP" => 0.95
            ]
        ];

        if (!empty($systemInstruction)) {
            $payload["systemInstruction"] = [
                "parts" => [
                    ["text" => $systemInstruction]
                ]
            ];
        }

        if ($asJson) {
            $payload["generationConfig"]["responseMimeType"] = "application/json";
        }

        $headers = [
            "Content-Type: application/json",
            "User-Agent: aistudio-build-php-client"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Prevent PHP blocking delays

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log("[Gemini PHP cURL Error] " . $curlError);
            return ["error" => "cURL communication failure.", "details" => $curlError, "status" => 500];
        }

        if ($httpCode !== 200) {
            error_log("[Gemini HTTP Error] Status {$httpCode}. Response: " . $response);
            return [
                "error" => "Gemini returned error statuscode {$httpCode}",
                "raw_response" => $response,
                "status" => $httpCode
            ];
        }

        $parsedResponse = json_decode($response, true);
        $responseText = $parsedResponse["candidates"][0]["content"]["parts"][0]["text"] ?? "";

        if (empty($responseText)) {
            return ["error" => "Null text returned from Gemini candidate tree.", "status" => 500];
        }

        if ($asJson) {
            $jsonParsed = json_decode(trim($responseText), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonParsed;
            }
            // If failed to parse, attempt manual JSON-clean regex fallback
            preg_match('/\{.*\}/s', $responseText, $matches);
            if (!empty($matches)) {
                $jsonParsed = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $jsonParsed;
                }
            }
            return ["error" => "Failed to format Gemini response as rigid JSON", "raw" => $responseText];
        }

        return ["text" => $responseText];
    }
}
