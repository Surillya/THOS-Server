<?php
header('Content-Type: application/json');
$level = intval($_GET['level'] ?? 50);
if ($level < 0 || $level > 100) {
  echo json_encode(['success' => false, 'error' => 'Invalid level']);
  exit;
}

$output = shell_exec("pactl set-sink-volume @DEFAULT_SINK@ {$level}%");
echo json_encode(['success' => true]);
?>
