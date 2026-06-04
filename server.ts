import express from "express";
import path from "path";
import fs from "fs/promises";
import { fileURLToPath } from "url";
import { createServer as createViteServer } from "vite";
import { GoogleGenAI, Type } from "@google/genai";
import dotenv from "dotenv";

dotenv.config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = 3000;

app.use(express.json());

// Path to JSON persistent store
const articlesDbPath = path.join(process.cwd(), "src", "data", "articles.json");

// Ensure data directory exists
async function ensureDb() {
  try {
    await fs.mkdir(path.dirname(articlesDbPath), { recursive: true });
    try {
      await fs.access(articlesDbPath);
    } catch {
      // Create empty db if not present
      await fs.writeFile(articlesDbPath, JSON.stringify([], null, 2));
    }
  } catch (e) {
    console.error("Database initialization error:", e);
  }
}

// Instantiate Gemini API Gateway (server-side ONLY)
// Utilizes process.env.GEMINI_API_KEY from Settings > Secrets
let aiClient: GoogleGenAI | null = null;
function getGeminiClient(): GoogleGenAI {
  if (!aiClient) {
    const key = process.env.GEMINI_API_KEY;
    if (!key) {
      console.warn("GEMINI_API_KEY is not configured. AI features will fallback to deterministic simulations.");
    }
    aiClient = new GoogleGenAI({
      apiKey: key || "PLACEHOLDER",
      httpOptions: {
        headers: {
          "User-Agent": "aistudio-build",
        },
      },
    });
  }
  return aiClient;
}

// Helper: Read posts helper
async function readArticles(): Promise<any[]> {
  try {
    const raw = await fs.readFile(articlesDbPath, "utf-8");
    return JSON.parse(raw);
  } catch (e) {
    console.error("Failed to read articles:", e);
    return [];
  }
}

// Helper: Write posts helper
async function writeArticles(articles: any[]): Promise<void> {
  await fs.writeFile(articlesDbPath, JSON.stringify(articles, null, 2), "utf-8");
}

/* ==============================================
   API ENDPOINTS
   ============================================== */

// PUBLIC api: get all posts
app.get("/api/posts", async (req, res) => {
  try {
    const articles = await readArticles();
    // Filter out unpublished notes for normal viewer queries unless requested otherwise
    const filter = req.query.all === "true";
    const filtered = filter ? articles : articles.filter(a => a.status === "published");
    res.json(filtered);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// PUBLIC api: get specific article by slug
app.get("/api/post", async (req, res) => {
  try {
    const { slug } = req.query;
    if (!slug) {
      return res.status(400).json({ error: "Slug parameter is required" });
    }
    const articles = await readArticles();
    const article = articles.find(a => a.slug === slug);
    if (!article) {
      return res.status(404).json({ error: "Article not found" });
    }
    
    // Increment views
    article.views = (article.views || 0) + 1;
    await writeArticles(articles);

    res.json(article);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// PRIVATE api: create post manually
app.post("/api/posts", async (req, res) => {
  try {
    const { title, summary, content, category, author } = req.body;
    if (!title || !content || !category) {
      return res.status(400).json({ error: "Missing required fields: title, content, category" });
    }

    const articles = await readArticles();
    const slug = title
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, "")
      .replace(/\s+/g, "-")
      .substring(0, 60);

    const newArticle = {
      id: Date.now(),
      title,
      slug,
      summary: summary || content.substring(0, 150) + "...",
      content,
      category,
      language: "en",
      thumbnail_url: null,
      status: "draft", // Starts as draft per guidelines
      trust_score: 100,
      risk_level: "low",
      verification_reason: "Pre-review draft. Pending Trust Engine evaluation.",
      seo_title: title,
      seo_description: summary || content.substring(0, 150),
      seo_keywords: category.toLowerCase(),
      views: 0,
      created_at: new Date().toISOString(),
      translations: {}
    };

    articles.unshift(newArticle);
    await writeArticles(articles);
    res.status(201).json(newArticle);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// API endpoint to change status (workflow control)
app.post("/api/posts/status", async (req, res) => {
  try {
    const { id, status } = req.body;
    if (!id || !status) {
      return res.status(400).json({ error: "ID and Status are required" });
    }

    const validStatuses = ["draft", "pending_review", "approved", "published", "rejected", "flagged"];
    if (!validStatuses.includes(status)) {
      return res.status(400).json({ error: "Invalid status state" });
    }

    const articles = await readArticles();
    const idx = articles.findIndex(a => a.id === parseInt(id));
    if (idx === -1) {
      return res.status(404).json({ error: "Article not found" });
    }

    // Block publishing of flagged risk content
    if (status === "published" && (articles[idx].risk_level === "high" || articles[idx].risk_level === "fake_risk")) {
      return res.status(400).json({ error: "CRITICAL SECURITY BLOCK: Cannot publish articles flagged with high misinformation risk." });
    }

    articles[idx].status = status;
    await writeArticles(articles);
    res.json(articles[idx]);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// API: Verify article using Gemini Truth Score Engine + Spambot Heuristics
app.post("/api/verify_post", async (req, res) => {
  try {
    const { id } = req.body;
    if (!id) {
       return res.status(400).json({ error: "Article ID is required" });
    }

    const articles = await readArticles();
    const idx = articles.findIndex(a => a.id === parseInt(id));
    if (idx === -1) {
      return res.status(404).json({ error: "Article not found" });
    }

    const article = articles[idx];
    
    // Heuristic Scan: Spam patterns
    const spamWords = ["buy now", "earn cash", "passive income", "100% free", "viagra", "crypto millionaire", "anti-gravity propulsion"];
    let spamCount = 0;
    spamWords.forEach(w => {
      const regex = new RegExp("\\b" + w + "\\b", "gi");
      const matches = article.content.match(regex);
      if (matches) spamCount += matches.length;
    });

    const bodyWordCount = article.content.split(/\s+/).length;
    let fallbackTrust = 100 - (spamCount * 15);
    if (bodyWordCount < 50) fallbackTrust -= 30; // Penalize short stubs
    fallbackTrust = Math.max(10, fallbackTrust);

    // Call Gemini for advanced fact analysis
    let trustScore = fallbackTrust;
    let riskLevel = "low";
    let reason = "Evaluated via local heuristic scans. Content displays normal lexical attributes.";

    const apikey = process.env.GEMINI_API_KEY;
    if (apikey) {
      try {
        const client = getGeminiClient();
        const prompt = `Evaluate the following news article for factual integrity, objectivity, neutrality, and presence of AI-generated fluff or unverified scams.
Title: ${article.title}
Body: ${article.content}

Rate it on a scale from 0 to 100 (where 100 is absolute bulletproof truth/objectivity, 0 is dangerous fake news or spam).
Assign a risk_level rating ('low', 'medium', 'high', 'fake_risk') based on these boundaries:
- 80-100: low
- 60-79: medium
- 30-59: high
- 0-29: fake_risk

Provide a definitive short explanation paragraph in 'reason'.
Return ONLY a valid JSON object matching the following outline:
{
  "trust_score": 90,
  "risk_level": "low",
  "reason": "Clear explanation of facts."
}`;

        const response = await client.models.generateContent({
          model: "gemini-3.5-flash",
          contents: prompt,
          config: {
            responseMimeType: "application/json",
            responseSchema: {
               type: Type.OBJECT,
               properties: {
                 trust_score: { type: Type.INTEGER, description: "Journalistic trust percentage from 0 to 100" },
                 risk_level: { type: Type.STRING, description: "Must be low, medium, high, or fake_risk" },
                 reason: { type: Type.STRING, description: "A detailed paragraph explaining why this score was assigned." }
               },
               required: ["trust_score", "risk_level", "reason"]
            }
          }
        });

        if (response.text) {
          const parsed = JSON.parse(response.text);
          trustScore = parsed.trust_score !== undefined ? parseInt(parsed.trust_score) : fallbackTrust;
          riskLevel = parsed.risk_level || (trustScore < 40 ? "high" : "low");
          reason = parsed.reason || "Processed successfully by NeuralPress.";
        }
      } catch (geminiError: any) {
        console.error("Gemini connection error during verification:", geminiError);
        reason = `Local heuristic analysis. (Gemini offline check fallback: ${geminiError.message})`;
      }
    }

    // Commit verification details
    article.trust_score = trustScore;
    article.risk_level = riskLevel;
    article.verification_reason = reason;

    // RULE: IF risk_level = HIGH or FAKE_RISK: BLOCK publishing, flag immediately
    if (riskLevel === "high" || riskLevel === "fake_risk") {
      article.status = "flagged";
    } else {
      article.status = "approved"; // Automatically greenlights content to approved stage for human final publish!
    }

    await writeArticles(articles);
    res.json(article);
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// PRIVATE: AI Generation system (GEMINI PHP equivalence proxy)
app.post("/api/ai_generate", async (req, res) => {
  try {
    const { topic, category } = req.body;
    if (!topic || !category) {
       return res.status(400).json({ error: "Missing required generation fields: topic and category" });
    }

    const client = getGeminiClient();
    const apikey = process.env.GEMINI_API_KEY;

    let generatedArticle = {
      title: `Automated investigation into ${topic}`,
      summary: `Pre-release bulletin touching upon the industrial themes surrounding ${topic}.`,
      content: `This bulletin represents a mock content item generated in local developer offline mode. Configure a valid GEMINI_API_KEY in active settings to retrieve professional articles from live model layers.`,
      seo_title: `Audit - ${topic}`,
      seo_description: `Discussion outlining the primary findings of ${topic} inside ${category}.`,
      seo_keywords: `ai news, ${topic.toLowerCase()}, global bulletins`,
      image_keywords: "editorial, financial, press"
    };

    if (apikey) {
      try {
        const prompt = `Act as an elite BBC Investigative Reporter. Write an engaging, balanced, highly readable, objective, and detailed article covering this topic: "${topic}".
The article category must be: "${category}".

Follow BBC guidelines: standard typography tone, no exclamation marks or sensory hyperbole, rich paragraphs citing factual insights or industry statistics.

Return strictly a JSON object with this shape:
{
  "title": "A highly descriptive, engaging BBC-style headline",
  "summary": "A 2-sentence rich summary/teaser",
  "content": "A detailed multi-paragraph article body (at least 200 words)",
  "seo_title": "Search engine optimized target title",
  "seo_description": "Search engine meta summary",
  "seo_keywords": "comma,separated,search,tags",
  "image_keywords": "3 keywords useful for Unsplash search"
}`;

        const response = await client.models.generateContent({
          model: "gemini-3.5-flash",
          contents: prompt,
          config: {
            responseMimeType: "application/json",
            responseSchema: {
               type: Type.OBJECT,
               properties: {
                 title: { type: Type.STRING },
                 summary: { type: Type.STRING },
                 content: { type: Type.STRING },
                 seo_title: { type: Type.STRING },
                 seo_description: { type: Type.STRING },
                 seo_keywords: { type: Type.STRING },
                 image_keywords: { type: Type.STRING }
               },
               required: ["title", "summary", "content", "seo_title", "seo_description", "seo_keywords", "image_keywords"]
            }
          }
        });

        if (response.text) {
          generatedArticle = JSON.parse(response.text);
        }
      } catch (gemException: any) {
        console.error("Gemini generate call faulted:", gemException);
        return res.status(500).json({ error: "Gemini AI generation failure: " + gemException.message });
      }
    }

    // Save article directly into Draft database block
    const articles = await readArticles();
    const slug = generatedArticle.title
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, "")
      .replace(/\s+/g, "-")
      .substring(0, 60);

    const newArticle = {
      id: Date.now(),
      title: generatedArticle.title,
      slug,
      summary: generatedArticle.summary,
      content: generatedArticle.content,
      category,
      language: "en",
      thumbnail_url: `https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=800&q=80`, // Placeholder
      status: "draft", // Starts as Draft per strict workflow
      trust_score: 100,
      risk_level: "low",
      verification_reason: "Awaiting mandatory post-fact verification step.",
      seo_title: generatedArticle.seo_title,
      seo_description: generatedArticle.seo_description,
      seo_keywords: generatedArticle.seo_keywords,
      views: 0,
      created_at: new Date().toISOString(),
      translations: {}
    };

    articles.unshift(newArticle);
    await writeArticles(articles);
    res.json(newArticle);
  } catch (err: any) {
    res.status(500).json({ error: err.message });
  }
});

// API: Translate article on-demand via Gemini translation pipeline (Arabic, Hausa, Yoruba, Igbo, French, Spanish)
app.post("/api/translate", async (req, res) => {
  try {
    const { id, language } = req.body;
    if (!id || !language) {
      return res.status(400).json({ error: "Article ID and target language are required" });
    }

    const languagesMap: Record<string, string> = {
      fr: "French",
      es: "Spanish",
      ar: "Arabic",
      ha: "Hausa",
      yo: "Yoruba",
      ig: "Igbo"
    };

    const targetLangName = languagesMap[language];
    if (!targetLangName) {
      return res.status(400).json({ error: `Language code "${language}" is unsupported.` });
    }

    const articles = await readArticles();
    const article = articles.find(a => a.id === parseInt(id));
    if (!article) {
      return res.status(404).json({ error: "Article not found" });
    }

    // Use translation cache if already present
    if (article.translations && article.translations[language]) {
      return res.json({ cached: true, translation: article.translations[language] });
    }

    const apikey = process.env.GEMINI_API_KEY;
    let translated = {
      title: `[${targetLangName}] ` + article.title,
      summary: `[Translated Summary in ${targetLangName}] ` + article.summary,
      content: `[Translated Content in ${targetLangName}] ` + article.content
    };

    if (apikey) {
      try {
        const client = getGeminiClient();
        const prompt = `Translate the following article details perfectly into native, authentic literary ${targetLangName}. Preserve journalistic style.
Title: ${article.title}
Summary: ${article.summary}
Body: ${article.content}

Return ONLY a JSON object:
{
  "title": "Translated title",
  "summary": "Translated summary",
  "content": "Translated long body text"
}`;

        const response = await client.models.generateContent({
          model: "gemini-3.5-flash",
          contents: prompt,
          config: {
            responseMimeType: "application/json"
          }
        });

        if (response.text) {
          translated = JSON.parse(response.text);
        }
      } catch (gemErr) {
        console.error("Gemini translation error:", gemErr);
      }
    }

    // Save translation in DB cache layer
    if (!article.translations) {
      article.translations = {};
    }
    article.translations[language] = translated;
    await writeArticles(articles);

    res.json({ cached: false, translation: translated });
  } catch (error: any) {
    res.status(500).json({ error: error.message });
  }
});

// Active mock ads configuration state
interface AdSlot {
  id: number;
  name: string;
  slot: string;
  impressions: number;
  clicks: number;
  ctr: number;
  status: "active" | "paused";
}

let activeAds: AdSlot[] = [
  { id: 101, name: "Premium Enterprise Cloud Server Hosting", slot: "header_banner", impressions: 4230, clicks: 54, ctr: 0.0127, status: "active" },
  { id: 102, name: "Global Financial Markets Daily Newsletter", slot: "sidebar", impressions: 3820, clicks: 88, ctr: 0.023, status: "active" },
  { id: 103, name: "Premium Journalistic Subscription Bundle", slot: "in_article", impressions: 5100, clicks: 121, ctr: 0.0237, status: "active" }
];

// Increment ad statistics live!
app.post("/api/ad_interact", (req, res) => {
  const { id, type } = req.body;
  const ad = activeAds.find(a => a.id === parseInt(id));
  if (!ad) return res.status(404).json({ error: "Ad not found" });

  if (type === "impression") {
    ad.impressions++;
  } else if (type === "click") {
    ad.clicks++;
  }
  ad.ctr = ad.impressions > 0 ? ad.clicks / ad.impressions : 0;
  res.json(ad);
});

// Fetch all ads config
app.get("/api/ads", (req, res) => {
  res.json(activeAds);
});

// Optimize ads automatically (remove lower performing variants)
app.post("/api/ads/optimize", (req, res) => {
  // Let's sort ads, slightly adjust parameters or toggle status to optimize high yielding streams
  activeAds.forEach(ad => {
    if (ad.ctr < 0.015) {
      // Simulate replacement with freshly optimized copy to bump performance metrics!
      ad.name = ad.name + " (AI copy optimised A/B v2)";
      ad.clicks += Math.floor(Math.random() * 5); // Simulating optimized bump
      ad.ctr = ad.clicks / ad.impressions;
    }
  });
  res.json({ status: "optimized", ads: activeAds });
});

/* ==============================================
   DEV/PROD VITE INTEGRATION RUNTIME
   ============================================== */

async function start() {
  await ensureDb();

  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), "dist");
    app.use(express.static(distPath));
    app.get("*", (req, res) => {
      res.sendFile(path.join(distPath, "index.html"));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`NeuralPress running on http://localhost:${PORT}`);
  });
}

start();
