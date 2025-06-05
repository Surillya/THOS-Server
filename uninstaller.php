<?php
$id = $_GET['id'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $id)) {
    http_response_code(400);
    exit("Invalid app ID");
}

$dir = __DIR__ . '/apps/' . $id;
if (!is_dir($dir)) {
    http_response_code(404);
    exit("App not found");
}

// Recursively delete
function deleteDir($path) {
    foreach (scandir($path) as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $path . '/' . $item;
        is_dir($full) ? deleteDir($full) : unlink($full);
    }
    return rmdir($path);
}

deleteDir($dir);
echo "App $id uninstalled.";
echo "<script>window.parent.reloadApps();</script>";
