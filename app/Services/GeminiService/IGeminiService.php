<?php

namespace App\Services\GeminiService;

interface IGeminiService
{
    public function sendPrompt(string $prompt);
}
