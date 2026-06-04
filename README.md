# 📰 NeuralPress AI News Network - Core Codebase

A modular ultra-high performance PHP 8 automated newsroom CMS and trust analysis engine.

## 📁 System Architecture

### 🧠 `/core`
Core drivers including Gemini API connection protocols, MySQLi singleton, CSRF and sanitization filters, clickbait detectors, and trust score engines.

### 🔌 `/api`
Rest JSON endpoints facilitating interaction between administrative review cues, content translators, and the public UI layer.

### 👨💼 `/admin`
Administrative dashboards managing article reviews, flags, monetizations, user accounts, and visual parameter logs.

### 🧩 `/includes` & `/templates`
Highly optimized layouts and responsive grids supporting multi-lingual interfaces and real-time ad placements.

## ⚙️ Requirements
- PHP 8.1+ with curl and mysqli extensions
- MySQL 8.0+
- `.htaccess` enabled Apache webserver
