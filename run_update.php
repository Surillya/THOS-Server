<?php
$mode = $_GET['mode'] ?? 'git';
$repoPath = '/usr/thos';

function getLatestLocalCommit($path) {
    return trim(shell_exec("git -C $path rev-parse HEAD"));
}

function getLatestRemoteCommit($path) {
    return trim(shell_exec("git -C $path rev-parse origin/main"));
}

function getCommitLogs($path, $from, $to) {
    return shell_exec("git -C $path log --pretty=format:'• %C(yellow)%h%Creset %s %Cgreen(%cr)%Creset' $from..$to");
}

function forceGitUpdate($path) {
    $output = [];

    shell_exec("git -C $path fetch origin 2>&1");

    $localBefore = getLatestLocalCommit($path);
    $remote = getLatestRemoteCommit($path);

    if ($localBefore === $remote) {
        return "Already up to date!";
    }

    $changelog = getCommitLogs($path, $localBefore, $remote);

    $output[] = "Soft-resetting local system files...";
    $output[] = shell_exec("git -C $path reset --hard origin/main 2>&1");
    $output[] = shell_exec("git -C $path clean -fd 2>&1");

    $output[] = "\n<strong>Changelog:</strong>";
    $output[] = $changelog ?: "No new commits.";

    return implode("\n", $output);
}

if ($mode === 'git') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<pre style='font-family: monospace; background-color: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 0.5rem'>";
    echo "<strong>Running THOS Git Update...</strong>\n\n";
    echo forceGitUpdate($repoPath);
    echo "\n</pre>";
} elseif ($mode === 'curl') {
    echo "Running installer...\n\n";
    echo shell_exec("sudo bash '$(curl -fsSL https://surillya.com/thos/thos.sh)'");
} else {
    http_response_code(400);
    echo "Unknown update mode";
}
