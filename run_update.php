<?php
$mode = $_GET['mode'] ?? 'git'; // Future-proof!

if ($mode === 'git') {
    $output = shell_exec("git -C /usr/thos pull 2>&1");
    echo "✅ Git update applied:\n$output";
} elseif ($mode === 'curl') {
    // Placeholder for future upgrade logic
    $output = shell_exec("curl -s https://your.url/thos-update.sh | sudo bash 2>&1");
    echo "🔧 Installer run:\n$output";
} else {
    http_response_code(400);
    echo "❌ Unknown update mode";
}
