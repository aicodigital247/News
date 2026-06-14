<?php
/**
 * NeuralPress - Professional Internal Linker
 * Automates context-aware SEO internal linking and related article callouts.
 *
 * @package Core
 */

namespace NeuralPress\Core;

class InternalLinker {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Automatically discover and link published articles based on keyword phrases in content,
     * protecting HTML tags, attributes, and existing links from corruption.
     *
     * @param string $content HTML or plain text article content
     * @param int $currentPostId Current article ID to avoid self-linking
     * @return string Parsed and linked content
     */
    public function autoLink(string $content, int $currentPostId): string {
        // Fetch all other published posts to find candidate words/phrases
        $res = $this->db->query(
            "SELECT id, title, slug, category, seo_keywords FROM posts WHERE status = 'published' AND id != ? ORDER BY LENGTH(title) DESC",
            "i",
            [$currentPostId]
        );

        if (!$res) {
            return $content;
        }

        $candidates = [];
        while ($row = $res->fetch_assoc()) {
            $candidates[] = $row;
        }

        if (empty($candidates)) {
            return $content;
        }

        // Tokenize/Protect HTML tags to avoid replacing attributes or existing links
        $htmlTokens = [];
        $tokenIndex = 0;
        
        // Find all HTML tags (such as <a>, <p>, <img ...>, etc.)
        $protectedContent = preg_replace_callback(
            '/<[^>]+>/i',
            function($matches) use (&$htmlTokens, &$tokenIndex) {
                $token = "%%HTML_TOKEN_{$tokenIndex}%%";
                $htmlTokens[$token] = $matches[0];
                $tokenIndex++;
                return $token;
            },
            $content
        );

        // Keep track of which candidate slugs we have already linked to avoid duplicate listing
        $linkedSlugs = [];

        foreach ($candidates as $cand) {
            $slug = $cand['slug'];
            if (isset($linkedSlugs[$slug])) {
                continue;
            }

            // Candidate phrases can be:
            // 1. The full title (exact match)
            // 2. Specific keywords from seo_keywords
            $phrases = [trim($cand['title'])];
            if (!empty($cand['seo_keywords'])) {
                $words = explode(',', $cand['seo_keywords']);
                foreach ($words as $w) {
                    $w = trim($w);
                    if (strlen($w) > 3) {
                        $phrases[] = $w;
                    }
                }
            }

            // Remove duplicates and keep longest first
            $phrases = array_values(array_unique($phrases));
            usort($phrases, function($a, $b) {
                return strlen($b) - strlen($a);
            });

            foreach ($phrases as $phrase) {
                if (empty($phrase)) continue;

                // Escape for safe regex match
                $escapedPhrase = preg_quote($phrase, '/');
                $pattern = '/\b(' . $escapedPhrase . ')\b/i';

                // We count occurrences and replace only the first match to avoid over-linking
                $replaced = false;
                $url = \UrlManager::getArticleUrl($slug);

                $protectedContent = preg_replace_callback(
                    $pattern,
                    function($m) use (&$replaced, $url) {
                        if ($replaced) {
                            return $m[0]; // Stay plain text if already linked once
                        }
                        $replaced = true;
                        // Elegant link styled specifically to harmonise with the existing typography
                        return '<a href="' . htmlspecialchars($url) . '" class="text-[#bb1919] font-medium border-b border-[#bb1919]/15 hover:border-[#bb1919] transition-all duration-150" title="Read related article: ' . htmlspecialchars($m[0]) . '">' . htmlspecialchars($m[0]) . '</a>';
                    },
                    $protectedContent
                );

                if ($replaced) {
                    $linkedSlugs[$slug] = true;
                    // Limit to 3 internal autolinks per article to avoid spamming the reader
                    if (count($linkedSlugs) >= 3) {
                        break 2;
                    }
                    break; // Move to the next candidate article
                }
            }
        }

        // Restore protected HTML tags
        $finalContent = str_replace(array_keys($htmlTokens), array_values($htmlTokens), $protectedContent);

        return $finalContent;
    }

    /**
     * Finds related posts by category or word overlap
     */
    public function getRelatedPosts(int $currentPostId, string $category, int $limit = 3): array {
        // Query same category first
        $res = $this->db->query(
            "SELECT id, title, slug, category, summary, thumbnail_url, created_at, views FROM posts WHERE status = 'published' AND id != ? AND category = ? ORDER BY created_at DESC LIMIT ?",
            "isi",
            [$currentPostId, $category, $limit]
        );

        $related = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $related[] = $row;
            }
        }

        // If not enough same category, fetch any other trending published posts
        if (count($related) < $limit) {
            $needed = $limit - count($related);
            $excludeIds = [$currentPostId];
            foreach ($related as $r) {
                $excludeIds[] = $r['id'];
            }
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $types = str_repeat('i', count($excludeIds)) . 'i';
            $params = array_merge($excludeIds, [$needed]);

            $sql = "SELECT id, title, slug, category, summary, thumbnail_url, created_at, views FROM posts WHERE status = 'published' AND id NOT IN ($placeholders) ORDER BY views DESC LIMIT ?";
            
            $resBack = $this->db->query($sql, $types, $params);
            if ($resBack) {
                while ($row = $resBack->fetch_assoc()) {
                    $related[] = $row;
                }
            }
        }

        return array_slice($related, 0, $limit);
    }

    /**
     * Inject an engaging callout inline inside the post content beautifully
     */
    public function injectInlineRelatedCallout(string $content, int $currentPostId, string $category): string {
        $related = $this->getRelatedPosts($currentPostId, $category, 1);
        if (empty($related)) {
            return $content;
        }

        $firstRelated = $related[0];
        $url = \UrlManager::getArticleUrl($firstRelated['slug']);
        $thumbnail = $firstRelated['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=400&q=80';
        
        $calloutHtml = '
        <div class="my-8 flex flex-col sm:flex-row gap-4 bg-slate-50 border border-slate-250/70 rounded-md overflow-hidden hover:shadow-md hover:border-[#bb1919]/40 transition-all duration-300 group">
            <div class="sm:w-1/3 h-36 sm:h-auto relative overflow-hidden shrink-0">
                <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="' . htmlspecialchars($thumbnail) . '" alt="' . htmlspecialchars($firstRelated['title']) . '" referrerPolicy="no-referrer">
                <span class="absolute top-2 left-2 bg-[#bb1919] text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-xs">' . htmlspecialchars($firstRelated['category']) . '</span>
            </div>
            <div class="p-5 flex flex-col justify-center space-y-2">
                <span class="text-[9px] font-mono text-slate-400 font-extrabold uppercase tracking-wider block">RECOMMENDED READ</span>
                <a href="' . htmlspecialchars($url) . '" class="text-slate-900 font-extrabold text-base tracking-tight hover:text-[#bb1919] transition-colors leading-snug block">
                    ' . htmlspecialchars($firstRelated['title']) . '
                </a>
                <p class="text-slate-500 text-xs line-clamp-2 font-light leading-relaxed">' . htmlspecialchars($firstRelated['summary']) . '</p>
                <div class="flex items-center gap-3 pt-1 text-[10px] font-mono text-slate-400">
                    <span>Reads: ' . intval($firstRelated['views']) . '</span>
                    <span>•</span>
                    <span>' . date('j M Y', strtotime($firstRelated['created_at'])) . '</span>
                </div>
            </div>
        </div>';

        // Check if there are paragraph tags to inject inside
        if (stripos($content, '</p>') !== false) {
            // Find all paragraph ends
            $offset = 0;
            $pCount = 0;
            $insertPos = false;

            while (($pos = stripos($content, '</p>', $offset)) !== false) {
                $pCount++;
                $offset = $pos + 4; // Length of </p>
                if ($pCount === 2) {
                    $insertPos = $offset;
                    break;
                }
            }

            if ($insertPos !== false) {
                return substr_replace($content, $calloutHtml, $insertPos, 0);
            }
        }

        // Fallback: Append it at the end
        return $content . $calloutHtml;
    }
}
