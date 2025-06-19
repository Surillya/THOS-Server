<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../vfs.php';

$dir        = $_GET['dir']      ?? '';
$showHidden = ($_GET['hidden'] ?? '0') === '1';

$realDir = resolve_path($dir);
if (!is_dir($realDir)) {
    echo json_encode([]);
    exit;
}

$out = [];
foreach (scandir($realDir) as $name) {
    if ($name === '.' || $name === '..') continue;
    if (!$showHidden && $name[0] === '.') continue;

    $full = $realDir . '/' . $name;
    $out[] = [
        'name'    => $name,
        'virtual' => virtualize_path($full),
        'isDir'   => is_dir($full),
        'ext'     => pathinfo($name, PATHINFO_EXTENSION),
        'size'    => is_file($full) ? filesize($full) : null,
        'mtime'   => filemtime($full),
    ];
}

echo json_encode($out);
