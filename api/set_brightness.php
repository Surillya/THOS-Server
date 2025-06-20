<?php
header('Content-Type: application/json');
$level = intval($_GET['level'] ?? 50);
if ($level < 10 || $level > 100) {
  echo json_encode(['success' => false, 'error' => 'Invalid level']);
  exit;
}

$output = shell_exec("sudo brightnessctl set {$level}%");
echo json_encode(['success' => true]);
?>
