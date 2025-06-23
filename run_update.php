<?php
$mode = $_GET['mode'] ?? 'git';
$repoPath = '/usr/thos';
shell_exec("git config --system --add safe.directory /usr/thos");

function getLatestLocalCommit($path) {
    return trim(shell_exec("git -C $path rev-parse HEAD"));
}

function getLatestRemoteCommit($path) {
    return trim(shell_exec("git -C $path rev-parse origin/main"));
}

function getCommitLogs($path, $from, $to) {
    return shell_exec("git -C $path log --pretty=format:'- %h %s (%cr)' $from..$to");
}

function forceGitUpdate($path) {
    shell_exec("git -C $path fetch origin 2>&1");

    $localBefore = getLatestLocalCommit($path);
    $remote = getLatestRemoteCommit($path);

    if ($localBefore === $remote) {
        return "Already up to date.";
    }

    $changelog = getCommitLogs($path, $localBefore, $remote);

    shell_exec("git -C $path reset --hard origin/main 2>&1");
    shell_exec("git -C $path clean -fd 2>&1");

    $output = "Update installed successfully, reload system to apply.\n\n";
    $output .= "Changelog:\n";
    $output .= $changelog ?: "- No new commits found.";

    return $output;
}

if ($mode === 'git') {
    header('Content-Type: text/plain; charset=utf-8');
    echo forceGitUpdate($repoPath);
} elseif ($mode === 'curl') {
    echo shell_exec("sudo bash '$(curl -fsSL https://surillya.com/thos/thos.sh)'");
} else {
    http_response_code(400);
    echo "Unknown update mode";
}
