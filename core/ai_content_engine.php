<?php
/**
 * NeuralPress - AI Content Generator Engine
 */

namespace NeuralPress\Core;

class AIContentEngine {
    private GeminiAPI $gemini;

    public function __construct() {
        $this->gemini = new GeminiAPI();
    }

    public function draftInvestigativePiece(string $topic, string $category): array {
        $system = "You are a lead international journalist. Write a balanced, deeply factual, un-hyped article mimicking the high quality of BBC News.";
        $prompt = "Write an extensive investigative piece focusing on: '{$topic}'. Category: {$category}. Define standard search metadata alongside.";
        
        return $this->gemini->generate($prompt, $system, true);
    }
}
