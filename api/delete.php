<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../vfs.php';

$data  = json_decode(file_get_contents('php://input'), true);
$pathV = $data['path'] ?? '';

$real = resolve_path($pathV);
if (!file_exists($real)) {
    echo json_encode(['success'=>false,'error'=>'Not found']);
    exit;
}

function rrmdir($d) {
  foreach (scandir($d) as $f) {
    if (in_array($f, ['.','..'])) continue;
    $p = "$d/$f";
    is_dir($p) ? rrmdir($p) : unlink($p);
  }
  rmdir($d);
}

if (is_dir($real)) rrmdir($real);
else unlink($real);

echo json_encode(['success'=>true]);
