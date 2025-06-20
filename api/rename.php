<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../vfs.php';

$data    = json_decode(file_get_contents('php://input'), true);
$oldV    = $data['old'] ?? '';
$newName = $data['new'] ?? '';

if (strpos($oldV, '/.apps') === 0 || $oldV === '/.thos_state.json') {
    echo json_encode(['success'=>false, 'error'=>'Cannot rename system files']);
    exit;
}

$oldReal = resolve_path($oldV);
$newReal = dirname($oldReal) . '/' . basename($newName);

if (!file_exists($oldReal)) {
    echo json_encode(['success'=>false,'error'=>'Not found']);
    exit;
}

$ok = rename($oldReal, $newReal);
echo json_encode(['success'=>(bool)$ok]);
