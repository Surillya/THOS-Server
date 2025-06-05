<?php
if (!isset($_GET['action'])) {
    http_response_code(400);
    echo "No action given";
    exit;
}

$action = $_GET['action'];
switch ($action) {
    case 'shutdown':
        shell_exec('shutdown now');
        echo "Shutting down...";
        break;
    case 'reboot':
        shell_exec('reboot');
        echo "Rebooting...";
        break;
    default:
        http_response_code(400);
        echo "Unknown action";
}
?>
