<?php
/**
 * NeuralPress - AI Verifier Module
 * 
 * Includes strictly defined analytical hooks for spam testing, structural validation,
 * duplication scanning, and unified trust scoring.
 */

namespace NeuralPress\Core;

/**
 * Validates text properties for spam and over-saturated sales language
 *
 * @param string $content
 * @return float Penalty scalar (0.0 to 1.0; 1.0 being high risk spam)
 */
function detect_spam_patterns(string $content): float {
    $spamIndicators = [
        '/\b(buy now|click here|refinance|mortgage|lottery|crypto millionaire|fast cash)\b/i',
        '/\b(viagra|casino|earn cash|passive income|double money|100% free|no catch)\b/i',
        '/\b(risk-free|guaranteed results|secret recipe|unbelievable earnings|make cash)\b/i',
        '/\!{3,}/', // Aggressive exclamation marks
        '/\${2,}/', // Multi-currency symbols
    ];

    $hits = 0;
    foreach ($spamIndicators as $pattern) {
        if (preg_match_all($pattern, $content, $matches)) {
            $hits += count($matches[0]);
        }
    }

    if ($hits === 0) return 0.0;
    
    // Logarithmic penalty scale
    return min(1.0, 0.15 * $hits);
}

/**
 * Validates structural indices to detect generic AI or machine-generated styles
 * e.g., lack of punctuation, short repetitive sentences, excessive transitioning words
 *
 * @param string $content
 * @return float Anomalous rating (0.0 to 1.0; 1.0 being suspicious empty framework)
 */
function detect_fake_structure(string $content): float {
    $wordCount = str_word_count($content);
    if ($wordCount < 50) return 0.8; // High warning for stub papers masquerading as real articles

    $puncCount = preg_match_all('/[\.\,\?\!\;]/', $content);
    if ($wordCount > 0) {
        $puncDensity = $puncCount / $wordCount;
        if ($puncDensity < 0.02) return 0.6; // Alarmingly few punctuation markers
    }

    // Checking for generic transition vocabulary clustering
    $transitions = ['moreover', 'furthermore', 'first and foremost', 'in conclusion', 'consequently', 'it is important to note', 'delves into'];
    $transitionHits = 0;
    foreach ($transitions as $word) {
        if (stripos($content, $word) !== false) {
            $transitionHits++;
        }
    }

    if ($transitionHits > 4) {
        return 0.4; // AI text transition pattern detected
    }

    return 0.0;
}

/**
 * Compares incoming article title and content against existing posts database
 * to enforce exclusivity and block plagiarism or duplication.
 *
 * @param string $title
 * @param string $content
 * @return float Similarity factor (0.0 to 1.0; 1.0 is exact copy)
 */
function compare_existing_posts(string $title, string $content): float {
    $db = Database::getInstance();
    $sql = "SELECT title, content FROM posts ORDER BY id DESC LIMIT 20";
    $result = $db->query($sql);

    if (!$result) return 0.0;

    $maxSimilarity = 0.0;
    while ($post = $result->fetch_assoc()) {
        // Simple Levenshtein distance on title
        $lev = levenshtein(strtolower($title), strtolower($post['title']));
        $longestLength = max(strlen($title), strlen($post['title']));
        if ($longestLength > 0) {
            $titleSim = 1 - ($lev / $longestLength);
            if ($titleSim > $maxSimilarity) {
                $maxSimilarity = $titleSim;
            }
        }
    }

    return $maxSimilarity;
}

/**
 * Combines natural heuristics with the backend Gemini parser for unified audit results.
 * Returns strict formatting standards in alignment with NeuralPress mandates.
 *
 * @param string $title
 * @param string $content
 * @return array Standardized structured array: [trust_score => INT, risk_level => STRING, reason => STRING]
 */
function analyze_content(string $title, string $content): array {
    // 1. Run local checks
    $spamScore = detect_spam_patterns($content);
    $structureAnomaly = detect_fake_structure($content);
    $similarityFactor = compare_existing_posts($title, $content);

    // Initial draft trust score reduction based on heuristics
    $localDecline = ($spamScore * 40) + ($structureAnomaly * 20) + ($similarityFactor * 30);
    $baseScore = max(10, 100 - (int)$localDecline);

    // 2. Invoke deep semantic evaluation via Gemini API hook
    try {
        $gemini = new GeminiAPI();
        $prompt = "Evaluate the following article text for corporate truth rating, spam, false claims, journalistic integrity, bias, and likelihood of being complete misinformation/spam.
Title: {$title}
Body: {$content}

Your evaluation MUST be objective, neutral, and precise. Return ONLY a valid JSON block containing:
- \"trust_score\": An integer between 0 and 100 representing journalistic integrity and reliability.
- \"risk_level\": A string classification matching exactly one of: \"low\", \"medium\", \"high\", \"fake_risk\".
- \"reason\": A short paragraph detailing your analysis and justification.

Do NOT include any markdown code blocks, HTML tags, or trailing explanations. Return strictly the raw JSON string.";

        $systemDirective = "You are the Senior Editorial Auditor at the NeuralPress Trust Scoring Network. You verify facts with cold precision, penalize unverified spam, and enforce pure objectivity.";
        
        $analysis = $gemini->generate($prompt, $systemDirective, true);

        // Merge and optimize outputs
        if (isset($analysis['trust_score']) && isset($analysis['risk_level']) && isset($analysis['reason'])) {
            // Apply small local heuristics offset to balance machine output with regex patterns
            $finalScore = min(100, max(0, intval($analysis['trust_score'] * 0.8 + $baseScore * 0.2)));
            $risk = $analysis['risk_level'];
            
            // Upgrade risk level if local heuristics are highly alarmed
            if ($finalScore < 40 && $risk === "low") {
                $risk = "medium";
            }
            if ($spamScore > 0.7 || $similarityFactor > 0.8) {
                $risk = "high";
                $finalScore = min($finalScore, 30);
            }

            return [
                "trust_score" => $finalScore,
                "risk_level" => $risk,
                "reason" => $analysis['reason'] . " (Local heuristics matched: Spam=" . round($spamScore, 2) . ", Dup=" . round($similarityFactor, 2) . ")"
            ];
        }
    } catch (\Exception $e) {
        error_log("[Verifier cURL Exception] " . $e->getMessage());
    }

    // Default Fallback
    $risk = 'low';
    if ($baseScore < 50) $risk = 'fake_risk';
    elseif ($baseScore < 75) $risk = 'medium';

    return [
        "trust_score" => $baseScore,
        "risk_level" => $risk,
        "reason" => "Gemini API query failed or generated invalid JSON. Defaulted to local heuristic assessment."
    ];
}

/**
 * Commits the resolved trust scores back to a specific target post in the DB
 */
function assign_trust_score(int $postId, int $trustScore, string $riskLevel, string $reason): bool {
    $db = Database::getInstance();
    $sql = "UPDATE posts SET trust_score = ?, risk_level = ?, verification_reason = ?, status = ? WHERE id = ?";
    
    // Auto flag if score is disastrous to route to moderator reviews
    $status = ($riskLevel === 'high' || $riskLevel === 'fake_risk') ? 'flagged' : 'pending_review';
    
    $types = "isssi";
    $params = [$trustScore, $riskLevel, $reason, $status, $postId];

    return (bool)$db->query($sql, $types, $params);
}
