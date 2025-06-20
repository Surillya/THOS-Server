<?php
$repoPath = '/usr/thos';
$updateCount = trim(shell_exec("git -C $repoPath fetch origin && git -C $repoPath rev-list HEAD...origin/main --count"));

echo json_encode([
  'updates' => (int)$updateCount,
  'upToDate' => $updateCount === '0',
]);
