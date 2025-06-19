<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../vfs.php';

$data    = json_decode(file_get_contents('php://input'), true);
$srcV    = $data['src']  ?? '';
$destV   = $data['dest'] ?? '';

$srcReal  = resolve_path($srcV);
$destReal = resolve_path($destV);

if (!file_exists($srcReal) || !is_dir($destReal)) {
    echo json_encode(['success'=>false,'error'=>'Invalid paths']);
    exit;
}

$target = $destReal . '/' . basename($srcReal);
$ok     = rename($srcReal, $target);

echo json_encode(['success'=>(bool)$ok]);
