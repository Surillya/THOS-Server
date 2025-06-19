<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../vfs.php';

$pathV = $_GET['path'] ?? '';
$real  = resolve_path($pathV);

if (!file_exists($real)) {
    echo json_encode([]);
    exit;
}

echo json_encode([
  'name'    => basename($real),
  'type'    => is_dir($real) ? 'directory' : 'file',
  'size'    => is_file($real) ? filesize($real) : null,
  'mtime'   => date(DATE_ISO8601, filemtime($real)),
  'path'    => virtualize_path($real),
]);
