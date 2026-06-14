<?php
/**
 * NeuralPress - Admin login console page
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\CSRF;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }
    
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (Auth::login($username, $password)) {
        header('Location: /admin/dashboard');
        exit;
    } else {
        $error = 'Invalid credentials. Administrative portal access denied.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NeuralPress Security Login Gate</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
    </style>
</head>
<body class="bg-slate-950 flex items-center justify-center min-h-screen text-slate-200 font-sans">
    <div class="bg-black/40 border border-[#bb1919] p-8 rounded-lg max-w-sm w-full space-y-6 shadow-2xl">
        <div class="text-center">
            <span class="text-2xl font-black tracking-tighter text-[#bb1919] flex items-center justify-center gap-1.5 select-none">
                <span class="bg-white text-[#bb1919] px-1.5 leading-none font-bold">N</span> NeuralPress
            </span>
            <p class="text-[10px] font-mono text-slate-400 mt-2 uppercase tracking-wide">CMS ADMINISTRATIVE ACCESS GATE</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-950/40 border border-red-500/45 p-3 text-xs rounded text-red-200"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <?php echo CSRF::renderField(); ?>
            <div class="space-y-1">
                <label class="text-[10px] font-mono text-slate-400 uppercase">Username / Email</label>
                <input type="text" name="username" required class="bg-slate-900 border border-slate-800 px-3 py-2 w-full text-xs text-white focus:outline-none focus:border-[#bb1919] rounded">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-mono text-slate-400 uppercase">Secure Password</label>
                <input type="password" name="password" required class="bg-slate-900 border border-slate-800 px-3 py-2 w-full text-xs text-white focus:outline-none focus:border-[#bb1919] rounded">
            </div>
            <button type="submit" class="w-full bg-[#bb1919] hover:bg-[#801111] py-2 text-xs font-bold uppercase tracking-wide transition cursor-pointer">Login to CMS Dashboard</button>
        </form>
    </div>
</body>
</html>
