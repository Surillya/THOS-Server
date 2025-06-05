<?php
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

require_once "vfs.php";
$appPath = resolve_path($_GET['q'] ?? '');
$verified = ($_GET['v'] ?? '0') === '1';

if (strpos($appPath, '..') !== false || !preg_match('/\.thp$/i', $appPath)) {
    http_response_code(400);
    exit("Invalid app package.");
}

if (!file_exists($appPath)) {
    http_response_code(404);
    exit("App package not found.");
}

function render_layout($content) {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Install App</title>
    <script src="tailwind.es"></script>
    <style>
    body {
        @apply bg-gray-950 text-white font-sans;
    }
    .glow {
        text-shadow: 0 0 8px rgba(255,255,255,0.4);
    }
    </style>
    </head>
    <body class="min-h-screen flex items-center justify-center px-4">
    <div class="bg-gray-900 border border-pink-400 rounded-2xl p-6 shadow-2xl max-w-lg w-full space-y-5">
    {$content}
    </div>
    </body>
    </html>
    HTML;
}

if (!$verified) {
    $pathEncoded = urlencode(virtualize_path($appPath));
    $content = <<<HTML
    <div class="flex items-center space-x-3">
    <svg class="w-8 h-8 text-yellow-400 animate-pulse" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 4.5c.88 0 1.74.25 2.5.72.76.47 1.37 1.14 1.8 1.92A5.96 5.96 0 0118 12a5.96 5.96 0 01-1.2 3.86 5.96 5.96 0 01-1.8 1.92A5.96 5.96 0 0112 18a5.96 5.96 0 01-3.86-1.2 5.96 5.96 0 01-1.92-1.8A5.96 5.96 0 016 12c0-.88.25-1.74.72-2.5.47-.76 1.14-1.37 1.92-1.8A5.96 5.96 0 0112 4.5z" />
    </svg>
    <h2 class="text-xl font-bold text-yellow-400 glow">Unverified App Installation</h2>
    </div>
    <p class="text-sm text-gray-300">
    This app hasn’t been verified by THOS. Installing untrusted packages may put your system at risk.
    </p>
    <p class="text-sm text-gray-300">Only continue if you trust this app and its source.</p>
    <a href="?v=1&q={$pathEncoded}" class="inline-block mt-4 px-4 py-2 text-sm font-semibold bg-yellow-500 text-black rounded hover:bg-yellow-400 transition">
    Install Anyway
    </a>
    HTML;
    render_layout($content);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === '1') {
    require_once 'pkg.php';
    $result = installPackage($appPath, __DIR__ . "/apps/");
    $content = $result
    ? <<<HTML
    <div class="flex items-center space-x-3">
    <svg class="w-8 h-8 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    <h2 class="text-xl font-bold text-green-400 glow">Success!</h2>
    </div>
    <p class="text-sm text-gray-300">The app has been installed successfully.</p>
    <script>window.parent.reloadApps?.();</script>
    HTML
    : <<<HTML
    <div class="flex items-center space-x-3">
    <svg class="w-8 h-8 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
    </svg>
    <h2 class="text-xl font-bold text-red-400 glow">Installation Failed</h2>
    </div>
    <p class="text-sm text-gray-300">Something went wrong. Check your server logs for more details.</p>
    HTML;

    render_layout($content);
    exit;
}

// Confirm UI
$appName = basename($appPath);
$content = <<<HTML
<div class="flex items-center space-x-3">
<svg class="w-8 h-8 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h1M4 12H3m15.364-6.364l.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l.707-.707" />
</svg>
<h2 class="text-xl font-bold text-blue-300 glow">Install {$appName}?</h2>
</div>
<p class="text-sm text-gray-300">This app is ready to be installed into your system. Continue?</p>
<form method="POST" class="mt-4 flex justify-end space-x-2">
<input type="hidden" name="confirm" value="1">
<a href="/" class="px-4 py-2 text-sm bg-gray-700 rounded hover:bg-gray-600 transition">Cancel</a>
<button type="submit" class="px-4 py-2 text-sm font-semibold bg-blue-500 text-white rounded hover:bg-blue-400 transition">
Install Now
</button>
</form>
HTML;

render_layout($content);
