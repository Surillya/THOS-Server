<?php
header('Content-Type: application/json');

$output = shell_exec("nmcli -t -f SSID device wifi list");
$lines = explode("\n", trim($output));

$ssids = array_unique(array_filter($lines));

echo json_encode(array_values($ssids));
?>