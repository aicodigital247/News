<?php
/**
 * NeuralPress - Create & Edit Article Compose Panel
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Core\SlugGenerator;

Auth::checkRole(['admin', 'editor']);
$currentUser = Auth::getCurrentUser();
$db = Database::getInstance();

$postId = intval($_GET['id'] ?? 0);
$isEdit = ($postId > 0);

$title = '';
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
    $res = $db->query("SELECT * FROM posts WHERE id = ?", "i", [$postId]);
    if ($res && $post = $res->fetch_assoc()) {
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
        $error = "The requested article was not found. Composing a new draft instead.";
    }
}

/**
 * Helper to extract the first image src from WYSIWYG HTML content
 */
function extractFirstImage($html) {
    if (empty($html)) {
        return null;
    }
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
        return $matches[1];
    }
    return null;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'World');
    $status = trim($_POST['status'] ?? 'draft');
    
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
            $seoKeywords = strtolower($category) . ", neuralpress, news";
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
                $_SESSION['message'] = "Article '" . htmlspecialchars($title) . "' has been successfully updated natively!";
                $_SESSION['message_type'] = "success";
                header("Location: /admin/posts");
                exit;
            } else {
                $error = "Failed to update the database record. Ensure database integrity.";
            }
        } else {
            // Create new post
            $authorId = intval($currentUser['id'] ?? 1);
            require_once NP_DIR . '/core/slug_generator.php';
            $slug = SlugGenerator::create($title, $db->getConnection());

            $sql = "INSERT INTO posts (author_id, title, slug, summary, content, category, status, seo_title, seo_description, seo_keywords, thumbnail_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $ok = $db->query($sql, "issssssssss", [
                $authorId,
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
                $_SESSION['message'] = "New article draft successfully committed to persistent storage!";
                $_SESSION['message_type'] = "success";
                header("Location: /admin/posts");
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
    <title><?php echo $isEdit ? 'Edit Article' : 'Compose Article'; ?> - NeuralPress CMS</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    
    <!-- Load jQuery and Summernote Lite (Bootstrap-free WYSIWYG) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
        
        /* Harmonize Summernote styles with Tailwind Slate canvas */
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
            <span class="bg-white text-black px-1 leading-none font-bold">N</span> NeuralPress CMS
        </span>
        <div class="flex items-center gap-4 text-xs">
            <span>Logged in as: <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong></span>
            <span class="text-gray-700">|</span>
            <a href="/admin/posts" class="text-red-400 hover:underline">Back to Archives</a>
        </div>
    </header>

    <div class="flex-grow flex flex-col md:flex-row max-w-7xl mx-auto w-full px-6 py-8 gap-8">
        <!-- Sidebar Navigation -->
        <nav class="w-full md:w-56 shrink-0 space-y-1 bg-white border border-gray-200 p-4 rounded text-xs font-bold uppercase tracking-wider h-fit">
            <a href="/admin/dashboard" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Overview</a>
            <a href="/admin/posts" class="block py-2 px-3 bg-red-50 text-[#bb1919] rounded">Post Archives</a>
            <a href="/admin/review_queue" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Review Queue</a>
            <a href="/admin/users" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Users & Roles</a>
            <a href="/admin/ads" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Ad Monetisation</a>
            <a href="/admin/settings" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Global Settings</a>
            <div class="pt-6">
                <a href="/" class="block text-center bg-[#bb1919] text-white py-2 select-none text-[10px] tracking-widest font-extrabold hover:bg-[#801111]">VIEW PUBLIC SITE</a>
            </div>
        </nav>

        <!-- Main Form Workspace -->
        <main class="flex-1 space-y-6">
            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <h1 class="font-bold text-lg text-slate-900"><?php echo $isEdit ? 'Modify Existing Article Draft' : 'Compose News Bulletin Record'; ?></h1>
                    <p class="text-xs text-slate-500 font-light">Prepare verified real-time feeds inside Node database persistence streams.</p>
                </div>
                <a href="/admin/posts" class="text-xs border border-gray-300 bg-white hover:bg-gray-50 px-3 py-1.5 rounded shadow-sm text-slate-700">Cancel & Go Back</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="p-4 rounded text-xs bg-red-50 text-red-700 border border-red-200">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6 bg-white border p-6 rounded shadow-sm">
                
                <!-- Primary Article Information -->
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
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded focus:outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700"
                            />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-gray-500">Category Select <span class="text-red-500">*</span></label>
                            <select
                                name="category"
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded focus:outline-none focus:border-red-700 bg-white"
                            >
                                <option value="World" <?php echo ($category === 'World') ? 'selected' : ''; ?>>World</option>
                                <option value="Business" <?php echo ($category === 'Business') ? 'selected' : ''; ?>>Business</option>
                                <option value="Technology" <?php echo ($category === 'Technology') ? 'selected' : ''; ?>>Technology</option>
                                <option value="Sports" <?php echo ($category === 'Sports') ? 'selected' : ''; ?>>Sports</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-mono uppercase text-gray-500">Teaser / Core Summary</label>
                        <textarea
                            name="summary"
                            rows="2"
                            placeholder="Enter immediate article teaser sentence to grip user feed flow..."
                            class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded focus:outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700"
                        ><?php echo htmlspecialchars($summary); ?></textarea>
                    </div>

                    <!-- Summernote integrated Narrative Body -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-mono uppercase text-gray-500">Factual Body Narrative (WYSIWYG Editor) <span class="text-red-500">*</span></label>
                        <textarea
                            id="content"
                            name="content"
                            required
                        ><?php echo htmlspecialchars($content); ?></textarea>
                    </div>

                    <div class="w-full md:w-1/3 space-y-1">
                        <label class="block text-[11px] font-mono uppercase text-gray-500">Workflow Draft Gate</label>
                        <select
                            name="status"
                            class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#bb1919] bg-white font-medium"
                        >
                            <option value="draft" <?php echo ($status === 'draft') ? 'selected' : ''; ?>>Draft (Internal stage only)</option>
                            <option value="pending_review" <?php echo ($status === 'pending_review') ? 'selected' : ''; ?>>Pending Editorial Review</option>
                            <option value="published" <?php echo ($status === 'published') ? 'selected' : ''; ?>>Publish to Broadcast Stream</option>
                        </select>
                    </div>
                </div>

                <!-- Manual SEO Configuration Panel -->
                <div class="pt-6 border-t border-dashed mt-6 space-y-4">
                    <div class="flex items-center justify-between border-b pb-1.5 cursor-pointer select-none" onclick="toggleSEOPanel()">
                        <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-emerald-700 flex items-center gap-1.5">
                            🔒 // MANUAL SEO CONFIGURATION PANEL
                        </h3>
                        <span id="seo_toggle_indicator" class="text-xs font-bold font-mono text-emerald-700">[ EXPAND + ]</span>
                    </div>

                    <div id="seo_panel_body" class="hidden space-y-4 bg-slate-50/50 p-4 rounded border border-slate-200">
                        <p class="text-[11px] text-gray-500 italic max-w-2xl leading-relaxed">
                            Define custom search engine parameters below to override automatic models. Leaving fields blank triggers the system fallback compiler.
                        </p>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold">Meta Title Override</label>
                            <input
                                type="text"
                                name="seo_title"
                                value="<?php echo htmlspecialchars($seoTitle); ?>"
                                placeholder="E.g., custom search engine meta title tag..."
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded bg-white focus:outline-none focus:border-emerald-700 focus:ring-1 focus:ring-emerald-700"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold">Meta Description Override</label>
                            <textarea
                                name="seo_description"
                                rows="3"
                                placeholder="E.g., optimized search engine snippet summary..."
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded bg-white focus:outline-none focus:border-emerald-700 focus:ring-1 focus:ring-emerald-700"
                            ><?php echo htmlspecialchars($seoDescription); ?></textarea>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold">Meta Keywords Override</label>
                            <input
                                type="text"
                                name="seo_keywords"
                                value="<?php echo htmlspecialchars($seoKeywords); ?>"
                                placeholder="E.g., world, technology, customized, tag, string"
                                class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded bg-white focus:outline-none focus:border-emerald-700 focus:ring-1 focus:ring-emerald-700"
                            />
                        </div>
                    </div>
                </div>

                <!-- Form Action Controls -->
                <div class="pt-4 border-t flex justify-end gap-3">
                    <a href="/admin/posts" class="px-5 py-2 text-xs border border-slate-300 rounded hover:bg-slate-50 transition text-slate-700">Discard Changes</a>
                    <button
                        type="submit"
                        class="bg-[#bb1919] text-white font-bold text-xs px-6 py-2 rounded hover:bg-[#801111] transition shadow cursor-pointer"
                    >
                        <?php echo $isEdit ? 'Commit Native Overwrite' : 'Save & Publish Draft'; ?>
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Init Summernote lite
        $(document).ready(function() {
            $('#content').summernote({
                placeholder: 'Start typing the factual article body narrative here...',
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

            // Expand the SEO panel automatically if it has values already populated
            <?php if (!empty($seoTitle) || !empty($seoDescription) || !empty($seoKeywords)): ?>
                toggleSEOPanel();
            <?php endif; ?>
        });

        // AJAX Image uploader to route uploaded assets into the /uploads folder natively
        function uploadImage(file) {
            let data = new FormData();
            data.append("image", file);
            $.ajax({
                url: "/api/upload_image",
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

        // Toggle Expandable SEO Panel
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
