<?php
$mode = $_GET['mode'] ?? 'git';
$repoPath = '/usr/thos';

function forceGitUpdate($path) {
    $output = [];

    $output[] = shell_exec("git -C $path fetch origin 2>&1");
    $output[] = shell_exec("git -C $path reset --hard origin/main 2>&1");

    $output[] = shell_exec("git -C $path clean -fd 2>&1");

    return implode("\n", $output);
}

if ($mode === 'git') {
    echo "🔄 Forcing full git reset and update...\n\n";
    echo forceGitUpdate($repoPath);
} elseif ($mode === 'curl') {
    echo "🔧 Running installer...\n\n";
    echo shell_exec("curl -s https://surillya.com/thos/thos.sh | sudo bash 2>&1");
} else {
    http_response_code(400);
    echo "❌ Unknown update mode";
}
