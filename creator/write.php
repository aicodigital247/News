<?php
/**
 * NeuralPress - Creator Article Composition Panel
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Core\SlugGenerator;

Auth::checkRole(['admin', 'editor', 'journalist']);
$currentUser = Auth::getCurrentUser();
$userId = intval($currentUser['id']);
$db = Database::getInstance();

// Fetch dynamic categories
$catListRes = $db->query("SELECT name FROM categories ORDER BY name ASC");
$allCategories = [];
if ($catListRes) {
    while ($crow = $catListRes->fetch_assoc()) {
        $allCategories[] = $crow['name'];
    }
}
if (empty($allCategories)) {
    $allCategories = ['World', 'Business', 'Technology', 'Sports'];
}

$postId = intval($_GET['id'] ?? 0);
$isEdit = ($postId > 0);

$title = trim($_GET['seed_topic'] ?? '');
$summary = '';
$content = '';
$category = 'World';
$status = 'draft';
$seoTitle = '';
$seoDescription = '';
$seoKeywords = '';
$error = '';
$success = '';

// Load existing post if in edit mode
if ($isEdit) {
    // SECURITY: Verify that this user owns the post or is admin/editor
    $res = $db->query("SELECT * FROM posts WHERE id = ?", "i", [$postId]);
    if ($res && $post = $res->fetch_assoc()) {
        if ($post['author_id'] != $userId && !in_array($currentUser['role'], ['admin', 'editor'])) {
            die("Access Denied: You are not authorized to edit this article.");
        }
        
        $title = $post['title'];
        $summary = $post['summary'];
        $content = $post['content'];
        $category = $post['category'];
        $status = $post['status'];
        $seoTitle = $post['seo_title'];
        $seoDescription = $post['seo_description'];
        $seoKeywords = $post['seo_keywords'];
    } else {
        $isEdit = false;
        $postId = 0;
        $error = "The requested article draft was not found. Composing a new draft instead.";
    }
}

/**
 * Helper to extract the first image src from WYSIWYG HTML content
 */
if (!function_exists('extractFirstImage')) {
    function extractFirstImage($html) {
        if (empty($html)) {
            return null;
        }
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $matches[1];
        }
        return null;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!\NeuralPress\Core\CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'World');
    
    // For creators, status can only be 'draft' or 'pending_review' unless they are admin/editor
    $reqStatus = trim($_POST['status'] ?? 'draft');
    if (!in_array($currentUser['role'], ['admin', 'editor'])) {
        if (!in_array($reqStatus, ['draft', 'pending_review'])) {
            $reqStatus = 'pending_review';
        }
    }
    $status = $reqStatus;
    
    // SEO fields manual overrides
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDescription = trim($_POST['seo_description'] ?? '');
    $seoKeywords = trim($_POST['seo_keywords'] ?? '');

    if (empty($title) || empty($content)) {
        $error = "Please fill in all required fields (Headline & Full Content).";
    } else {
        // Fallbacks for SEO fields if left empty
        if (empty($seoTitle)) {
            $seoTitle = $title;
        }
        if (empty($seoDescription)) {
            $seoDescription = mb_substr(strip_tags($content), 0, 160);
        }
        if (empty($seoKeywords)) {
            $seoKeywords = strtolower($category) . ", creator, neuralpress";
        }

        // Extracted post thumbnail
        $thumbnailUrl = extractFirstImage($content);

        if ($isEdit) {
            // Update existing post
            $sql = "UPDATE posts SET title = ?, summary = ?, content = ?, category = ?, status = ?, seo_title = ?, seo_description = ?, seo_keywords = ?, thumbnail_url = ? WHERE id = ?";
            $ok = $db->query($sql, "sssssssssi", [
                $title,
                $summary ?: mb_substr(strip_tags($content), 0, 150),
                $content,
                $category,
                $status,
                $seoTitle,
                $seoDescription,
                $seoKeywords,
                $thumbnailUrl,
                $postId
            ]);
            
            if ($ok) {
                $_SESSION['message'] = "Article '" . htmlspecialchars($title) . "' has been updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: /creator/dashboard");
                exit;
            } else {
                $error = "Failed to update the database record.";
            }
        } else {
            // Create new post
            require_once NP_DIR . '/core/slug_generator.php';
            $slug = SlugGenerator::create($title, $db->getConnection());

            $sql = "INSERT INTO posts (author_id, title, slug, summary, content, category, status, seo_title, seo_description, seo_keywords, thumbnail_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $ok = $db->query($sql, "issssssssss", [
                $userId,
                $title,
                $slug,
                $summary ?: mb_substr(strip_tags($content), 0, 150),
                $content,
                $category,
                $status,
                $seoTitle,
                $seoDescription,
                $seoKeywords,
                $thumbnailUrl
            ]);

            if ($ok) {
                $_SESSION['message'] = "New article successfully recorded! Awaiting moderator reviews.";
                $_SESSION['message_type'] = "success";
                header("Location: /creator/dashboard");
                exit;
            } else {
                $error = "Failed to insert new post into the database.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $isEdit ? 'Modify Article' : 'Compose News Article'; ?> - Creator Panel</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Load jQuery and Summernote Lite -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
        
        .note-editor.note-frame {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            background: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .note-toolbar {
            background-color: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top-left-radius: 6px !important;
            border-top-right-radius: 6px !important;
            padding: 8px !important;
        }
        .note-btn {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            color: #334155 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .note-btn:hover {
            background-color: #f1f5f9 !important;
        }
        .note-editable {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            font-size: 14px !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">
    <header class="bg-black text-white h-14 flex items-center justify-between px-6 shrink-0 shadow-md">
        <span class="font-black tracking-tighter text-sm flex items-center gap-1.5 select-none">
            <span class="bg-[#bb1919] text-white px-1 leading-none font-bold">C</span> Creator Panel
        </span>
        <div class="flex items-center gap-4 text-xs">
            <span>Logged in as: <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong></span>
            <span class="text-gray-700">|</span>
            <a href="/creator/dashboard" class="text-red-400 hover:underline">Back to Dashboard</a>
        </div>
    </header>

    <div class="flex-grow flex flex-col md:flex-row max-w-7xl mx-auto w-full px-6 py-8 gap-8">
        <!-- Sidebar Navigation -->
        <nav class="w-full md:w-56 shrink-0 space-y-1 bg-white border border-gray-200 p-4 rounded text-xs font-bold uppercase tracking-wider h-fit">
            <a href="/creator/dashboard" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Dashboard</a>
            <a href="/creator/write" class="block py-2 px-3 bg-red-50 text-[#bb1919] rounded font-bold">Compose Bulletin</a>
            <a href="/creator/withdrawals" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Withdrawals Log</a>
            <a href="/creator/promotions" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">My Promotions</a>
            <div class="pt-6 border-t border-dashed mt-4">
                <a href="/" class="block text-center bg-[#bb1919] text-white py-2 select-none text-[10px] tracking-widest font-extrabold hover:bg-[#801111]">VIEW PUBLIC SITE</a>
            </div>
        </nav>

        <!-- Main Form Workspace -->
        <main class="flex-1 space-y-6">
            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <h1 class="font-bold text-lg text-slate-900"><?php echo $isEdit ? 'Modify Authored Article' : 'Compose Independent News Article'; ?></h1>
                    <p class="text-xs text-slate-500 font-light font-sans">Draft facts-first content; optimized headlines and metadata earn premium traffic payouts.</p>
                </div>
                <a href="/creator/dashboard" class="text-xs border border-gray-300 bg-white hover:bg-gray-50 px-3 py-1.5 rounded shadow-sm text-slate-700">Cancel & Go Back</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="p-4 rounded text-xs bg-red-50 text-red-700 border border-red-200 font-medium">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6 bg-white border p-6 rounded shadow-sm">
                <?php echo \NeuralPress\Core\CSRF::renderField(); ?>
                
                <!-- Primary Editorial Inputs -->
                <div class="space-y-4">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-[#bb1919] border-b pb-1.5">// PRIMARY EDITORIAL INPUTS</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2 space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-gray-500">Headline Title <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                name="title"
                                required
                                value="<?php echo htmlspecialchars($title); ?>"
                                placeholder="Enter BBC-style neutral headline..."
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded focus:outline-none focus:border-red-700"
                            />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-gray-500">Category Select <span class="text-red-500">*</span></label>
                            <select
                                name="category"
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded focus:outline-none bg-white font-sans text-gray-800"
                            >
                                <?php foreach ($allCategories as $catNameOption): ?>
                                    <option value="<?php echo htmlspecialchars($catNameOption); ?>" <?php echo ($category === $catNameOption) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($catNameOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-mono uppercase text-gray-500">Teaser / Core Summary</label>
                        <textarea
                            name="summary"
                            rows="2"
                            placeholder="Enter immediate article teaser sentence to grip user feed flow..."
                            class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded focus:outline-none"
                        ><?php echo htmlspecialchars($summary); ?></textarea>
                    </div>

                    <!-- Summernote Body -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-mono uppercase text-gray-500">Body Narrative (WYSIWYG Editor) <span class="text-red-500">*</span></label>
                        <textarea
                            id="content"
                            name="content"
                            required
                        ><?php echo htmlspecialchars($content); ?></textarea>
                    </div>

                    <div class="w-full md:w-1/3 space-y-1">
                        <label class="block text-[11px] font-mono uppercase text-gray-500">Article Flow Status</label>
                        <select
                            name="status"
                            class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded focus:outline-none bg-white font-medium"
                        >
                            <option value="draft" <?php echo ($status === 'draft') ? 'selected' : ''; ?>>Draft (Save internal copy)</option>
                            <option value="pending_review" <?php echo ($status === 'pending_review' || $status === 'published') ? 'selected' : ''; ?>>Submit For Moderator Review</option>
                        </select>
                    </div>
                </div>

                <!-- Manual SEO Custom overrides -->
                <div class="pt-6 border-t border-dashed mt-6 space-y-4">
                    <div class="flex items-center justify-between border-b pb-1.5 cursor-pointer select-none" onclick="toggleSEOPanel()">
                        <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-emerald-700 flex items-center gap-1.5">
                            🔒 // MANUAL SEO CONFIGURATION PANEL
                        </h3>
                        <span id="seo_toggle_indicator" class="text-xs font-bold font-mono text-emerald-700">[ EXPAND + ]</span>
                    </div>

                    <div id="seo_panel_body" class="hidden space-y-4 bg-slate-50/50 p-4 rounded border border-slate-200">
                        <p class="text-[11px] text-gray-500 italic max-w-2xl leading-relaxed">
                            Define custom search engine parameters below to optimize your bulletin's keyword search ranking.
                        </p>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold">Meta Title Override</label>
                            <input
                                type="text"
                                name="seo_title"
                                value="<?php echo htmlspecialchars($seoTitle); ?>"
                                placeholder="E.g., custom search engine meta title tag..."
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded bg-white focus:outline-none"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold">Meta Description Override</label>
                            <textarea
                                name="seo_description"
                                rows="3"
                                placeholder="E.g., optimized search engine snippet summary..."
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded bg-white focus:outline-none"
                            ><?php echo htmlspecialchars($seoDescription); ?></textarea>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold">Meta Keywords Override</label>
                            <input
                                type="text"
                                name="seo_keywords"
                                value="<?php echo htmlspecialchars($seoKeywords); ?>"
                                placeholder="E.g., world, technology, customized, tag, string"
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded bg-white focus:outline-none"
                            />
                        </div>

                        <!-- Google Live Search Outcome Preview Card -->
                        <div class="mt-6 border border-slate-200 bg-white p-5 rounded-lg shadow-sm space-y-1.5 max-w-2xl font-sans text-left">
                            <span class="text-[10px] uppercase font-mono font-extrabold tracking-wider text-slate-400 block mb-2">// GOOGLE LIVE SEARCH OUTCOME PREVIEW</span>
                            
                            <!-- Search Meta / Breadcrumbs -->
                            <div class="flex items-center gap-2 text-[12px] text-[#202124] leading-normal truncate">
                                <div class="bg-gray-100 rounded-full p-1 flex items-center justify-center w-5 h-5 shrink-0">
                                    <span class="font-black text-[9px] text-[#bb1919]">N</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[12px] text-[#202124] font-medium leading-none">NeuralPress Network</span>
                                    <span id="google_preview_url" class="text-[11px] text-[#5f6368] leading-none mt-0.5">https://www.neuralpress.com › category › article-slug</span>
                                </div>
                            </div>

                            <!-- Styled Blue Headline Link -->
                            <div class="pt-1">
                                <a href="#" onclick="return false;" id="google_preview_title" class="text-[#1a0dab] text-[19px] font-normal leading-tight tracking-normal block hover:underline truncate">
                                    NeuralPress Search Outcome Headline Title
                                </a>
                            </div>

                            <!-- Meta Description -->
                            <div class="pt-0.5">
                                <p id="google_preview_description" class="text-[#4d5156] text-xs leading-relaxed break-words line-clamp-2">
                                    Provide descriptive overrides inside metadata inputs to review and compile instant Google Search snippet previews before publishing drafts...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Action Controls -->
                <div class="pt-4 border-t flex justify-end gap-3">
                    <a href="/creator/dashboard" class="px-5 py-2 text-xs border border-slate-300 rounded hover:bg-slate-50 transition text-slate-700">Discard Changes</a>
                    <button
                        type="submit"
                        class="bg-[#bb1919] text-white font-bold text-xs px-6 py-2 rounded hover:bg-[#801111] transition shadow cursor-pointer font-mono"
                    >
                        Save Bulletin Parameters
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Init Summernote lite
        $(document).ready(function() {
            $('#content').summernote({
                placeholder: 'Start typing the article body and story paragraphs...',
                tabsize: 2,
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        for (let i = 0; i < files.length; i++) {
                            uploadImage(files[i]);
                        }
                    }
                }
            });

            <?php if (!empty($seoTitle) || !empty($seoDescription) || !empty($seoKeywords)): ?>
                toggleSEOPanel();
            <?php endif; ?>

            // Google Live Search Preview
            function updateGooglePreview() {
                var title = $('input[name="title"]').val().trim();
                var seoTitle = $('input[name="seo_title"]').val().trim();
                var category = $('select[name="category"]').val();
                var seoDesc = $('textarea[name="seo_description"]').val().trim();
                var summary = $('textarea[name="summary"]').val().trim();
                
                var contentText = "";
                var contentRaw = $('#content').val();
                if (contentRaw) {
                    try {
                        contentText = $('<div>').html(contentRaw).text().trim();
                    } catch(e) {}
                }

                var displayTitle = seoTitle || title || "Untitled Article Bulletin";
                if (displayTitle.length > 60) {
                    displayTitle = displayTitle.substring(0, 57) + "...";
                }
                $('#google_preview_title').text(displayTitle);

                var slug = "article-slug";
                if (title) {
                    slug = title.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim();
                }
                $('#google_preview_url').text("https://www.neuralpress.com › " + category.toLowerCase() + " › " + (slug || "article-slug"));

                var displayDesc = seoDesc;
                if (!displayDesc) {
                    displayDesc = summary || contentText || "Provide custom descriptive overrides inside metadata fields to preview professional Google Search snippet layout outputs...";
                }
                if (displayDesc.length > 155) {
                    displayDesc = displayDesc.substring(0, 152) + "...";
                }
                $('#google_preview_description').text(displayDesc);
            }

            $('input[name="title"], input[name="seo_title"], textarea[name="seo_description"], textarea[name="summary"], select[name="category"]').on('input change', updateGooglePreview);
            $('#content').on('summernote.change', updateGooglePreview);
            updateGooglePreview();
        });

        // AJAX Image uploader logic
        function uploadImage(file) {
            let data = new FormData();
            data.append("image", file);
            $.ajax({
                url: "/admin/upload_image.php",
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: "POST",
                success: function(response) {
                    try {
                        let res = typeof response === 'string' ? JSON.parse(response) : response;
                        if (res.success && res.url) {
                            $('#content').summernote('insertImage', res.url);
                        } else {
                            alert("Upload failed: " + (res.error || "Unknown error"));
                        }
                    } catch (e) {
                        alert("Error parsing server upload response.");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Image upload failed: " + textStatus);
                }
            });
        }

        function toggleSEOPanel() {
            var panel = document.getElementById('seo_panel_body');
            var indicator = document.getElementById('seo_toggle_indicator');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                indicator.innerText = '[ COLLAPSE - ]';
            } else {
                panel.classList.add('hidden');
                indicator.innerText = '[ EXPAND + ]';
            }
        }
    </script>
</body>
</html>
