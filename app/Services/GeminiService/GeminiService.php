<?php

namespace App\Services\GeminiService;

use Illuminate\Support\Facades\Http;

class GeminiService implements IGeminiService
{
    protected $apiKey;
    protected $baseUrl;
    protected $model;

    public function __construct()
    {
        // $this->apiKey = env('GEMINI_API_KEY'); // your Gemini API key
        // $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent';
        $this->apiKey = env('OPENROUTER_API_KEY');
        $this->model = env('OPENROUTER_MODEL', 'gpt-4o-mini');
    }

    // public function sendPrompt(string $prompt)
    // {
    //     $response = Http::withHeaders([
    //         'x-goog-api-key' => $this->apiKey,
    //         'Content-Type' => 'application/json',
    //     ])->post($this->baseUrl, [
    //         "contents" => [
    //             [
    //                 "parts" => [
    //                     ["text" => $prompt]
    //                 ]
    //             ]
    //         ]
    //     ]);

    //     if (!$response->successful()) {
    //         return [
    //             'error' => true,
    //             'message' => $response->body()
    //         ];
    //     }

    //     return $response->json();
    // }

    public function sendPrompt(string $prompt)
    {

        if (!env('AI_GENERATION_ENABLED', true)) {
            return "AI generation is temporarily disabled.";
        }

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => env('OPENROUTER_MODEL', 'gpt-4o-mini'),
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ]
                    ],
                    'max_tokens' => 600,
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            return $result['choices'][0]['message']['content'] ?? null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $error = $e->getResponse()->getBody()->getContents();
            return "OpenRouter API error: " . $error;
        }
    }



    function parseAiResponse(string $aiText): array
    {
        $data = [];

        // Extract title
        if (preg_match('/title:\s*(.+)/', $aiText, $matches)) {
            $data['title'] = trim($matches[1]);
        }

        // Extract summary
        if (preg_match('/summary:\s*(.+)/', $aiText, $matches)) {
            $data['summary'] = trim($matches[1]);
        }

        // Extract insights (assumes '- "..."' format)
        if (preg_match_all('/-\s*"(.*?)"/', $aiText, $matches)) {
            $data['insights'] = $matches[1];
        } else {
            $data['insights'] = [];
        }

        // Extract confidence
        if (preg_match('/confidence:\s*(\d+)/', $aiText, $matches)) {
            $data['confidence'] = (int)$matches[1];
        }

        // Extract generatedAt
        if (preg_match('/generatedAt:\s*(.+)/', $aiText, $matches)) {
            $data['generated_at'] = $matches[1];
        } else {
            $data['generated_at'] = now();
        }

        return $data;
    }
}
