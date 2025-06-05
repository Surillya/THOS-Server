<?php
$rootDir = realpath("/home/surillya/"); // This should be absolute
$path = $_GET['path'] ?? '';
$fullPath = $rootDir . '/' . ltrim($path, '/'); // Fix slash issues
$realFullPath = realpath($fullPath);

// Check if the resolved path is still within rootDir (for security)
if (!$realFullPath || strpos($realFullPath, $rootDir) !== 0) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found or access denied']);
    exit;
}

// Now do the info check
$info = [];
$info['name'] = basename($realFullPath);
$info['type'] = is_dir($realFullPath) ? 'Directory' : mime_content_type($realFullPath);
$info['size'] = is_file($realFullPath) ? filesize($realFullPath) : 0;
$info['mtime'] = date("Y-m-d H:i:s", filemtime($realFullPath));

if (is_file($realFullPath)) {
    if ($info['size'] < 1024) $info['size'] = $info['size'] . ' B';
    else if ($info['size'] < 1048576) $info['size'] = round($info['size'] / 1024, 2) . ' KB';
    else if ($info['size'] < 1073741824) $info['size'] = round($info['size'] / 1048576, 2) . ' MB';
    else $info['size'] = round($info['size'] / 1073741824, 2) . ' GB';
}

header('Content-Type: application/json');
echo json_encode($info);
