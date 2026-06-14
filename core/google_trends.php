<?php
/**
 * NeuralPress - Google Trends & AI Simulation Engine
 */

namespace NeuralPress\Core;

class GoogleTrends {

    /**
     * Fetch trending keywords and search scopes from Google Trends RSS.
     * Includes cURL + SimpleXMl, with complete Gemini cURL dynamic fallback.
     */
    public static function getTrendingTopics(): array {
        $topics = [];
        $rssUrl = 'https://trends.google.com/trends/trendingsearches/daily/rss?geo=US';

        // Advanced Stream Context setting realistic User-Agent headers
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36\r\n" .
                            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8\r\n" .
                            "Accept-Language: en-US,en;q=0.5\r\n",
                "timeout" => 4
            ]
        ];
        $context = stream_context_create($opts);

        // Fetch feed content gracefully
        $xmlContent = @file_get_contents($rssUrl, false, $context);
        if ($xmlContent) {
            $xml = @simplexml_load_string($xmlContent);
            if ($xml && isset($xml->channel->item)) {
                $count = 0;
                foreach ($xml->channel->item as $item) {
                    if ($count >= 10) break; // Return top 10 trends
                    $title = (string)$item->title;
                    $approxTraffic = '';
                    
                    // Decode Google ht:approx_traffic namespace
                    $namespaces = $item->getNameSpaces(true);
                    if (isset($namespaces['ht'])) {
                        $htChild = $item->children($namespaces['ht']);
                        if (isset($htChild->approx_traffic)) {
                            $approxTraffic = (string)$htChild->approx_traffic;
                        }
                    }

                    $topics[] = [
                        'title' => $title,
                        'traffic' => $approxTraffic ?: '20K+ searches',
                        'source' => 'Google Trends'
                    ];
                    $count++;
                }
            }
        }

        // AI Dynamic fallback if RSS was rate-limited or offline
        if (empty($topics)) {
            try {
                $gemini = new GeminiAPI();
                $curTime = date('Y-m-d H:i:s');
                $prompt = "Identify 10 currently high-traffic global news topics or search queries trending on Google Trends (Date context: {$curTime}). For each topic, construct a realistic news title. Provide your response as a strict JSON array of objects. Each object MUST have keys: 'title' (a realistic, specific search string or headline), 'traffic' (a estimated daily search traffic like '100K+', '200K+'), and 'source' ('Google Trends (AI Sim)'). Do not output any explanation, markdown backticks, or extra syntax outside the raw JSON array.";
                
                $res = $gemini->generate($prompt, "You are a factual trends metadata assistant. Always output valid JSON array.", true);
                if (is_array($res) && !empty($res)) {
                    if (isset($res[0]['title'])) {
                        $topics = array_slice($res, 0, 10);
                    } elseif (isset($res['topics']) && is_array($res['topics'])) {
                        $topics = array_slice($res['topics'], 0, 10);
                    }
                }
            } catch (\Exception $e) {
                error_log("[Trends AI Fallback Error] " . $e->getMessage());
            }
        }

        // Hardcoded static fallback index if internet/Gemini are both unreachable
        if (empty($topics)) {
            $topics = [
                ['title' => 'Federal Reserve Interest Rates Forecast', 'traffic' => '100K+ searches', 'source' => 'NeuralPress Index'],
                ['title' => 'SpaceX Starship Orbital Launch Capture', 'traffic' => '500K+ searches', 'source' => 'NeuralPress Index'],
                ['title' => 'Apple Worldwide Developer Conference AI Updates', 'traffic' => '300K+ searches', 'source' => 'NeuralPress Index'],
                ['title' => 'Global Renewable Energy Infrastructure Settle', 'traffic' => '100K+ searches', 'source' => 'NeuralPress Index'],
                ['title' => 'Cybersecurity Protocols for Autonomous Driving AI', 'traffic' => '80K+ searches', 'source' => 'NeuralPress Index'],
                ['title' => 'World Championship Tournament Highlights', 'traffic' => '1M+ searches', 'source' => 'NeuralPress Index'],
                ['title' => 'Quantum Computing Commercial Superconductor Trials', 'traffic' => '50K+ searches', 'source' => 'NeuralPress Index'],
                ['title' => 'Venture Capital Influx in Hydrogen Propulsion', 'traffic' => '40K+ searches', 'source' => 'NeuralPress Index']
            ];
        }

        return $topics;
    }
}
