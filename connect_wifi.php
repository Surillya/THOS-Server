<?php
header('Content-Type: application/json');

$ssid = $_GET['ssid'] ?? '';
$password = $_GET['pass'] ?? '';

// Basic input validation
if (!$ssid || !$password) {
  echo json_encode(['success' => false, 'error' => 'Missing SSID or password']);
  exit;
}

// Sanitize inputs (very basic — assume safe env or sandbox it!)
$ssidSafe = escapeshellarg($ssid);
$passwordSafe = escapeshellarg($password);

// Attempt to connect using nmcli
$command = "nmcli dev wifi connect $ssidSafe password $passwordSafe";
$output = shell_exec($command);

// Check result
if (strpos($output, 'successfully activated') !== false) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => trim($output)]);
}
?>
