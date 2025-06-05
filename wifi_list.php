<?php
header('Content-Type: application/json');

// Run nmcli to list Wi-Fi networks
$output = shell_exec("nmcli -t -f SSID device wifi list");
$lines = explode("\n", trim($output));

// Remove empty or duplicate SSIDs
$ssids = array_unique(array_filter($lines));

// Return as JSON
echo json_encode(array_values($ssids));
?>