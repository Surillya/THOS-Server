<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../vfs.php';

$data = json_decode(file_get_contents('php://input'), true);

$dirV  = $data['dir'] ?? '';
$name  = $data['name'] ?? '';
$type  = $data['type'] ?? 'file';

if ($type !== 'folder' && $type !== 'file') {
    echo json_encode(['success' => false, 'error' => 'Invalid type: must be "folder" or "file"']);
    exit;
}

if (strpos($dirV, '/.apps') === 0 || $dirV === '/.thos_state.json') {
    echo json_encode(['success' => false, 'error' => 'Cannot create in system directories']);
    exit;
}

$realDir = resolve_path($dirV);
if (!is_dir($realDir)) {
    die(json_encode(['success' => false, 'error' => 'Parent directory not found or invalid']));
}

$newPathReal = $realDir . '/' . $name;

$invalidCharsRegex = '/[<>:"\/\\\\|?*\x00-\x1F]/';
if (preg_match($invalidCharsRegex, $name)) {
    die(json_encode(['success' => false, 'error' => 'Invalid filename: special characters not allowed']));
}

if ($type === 'folder') {
    if (is_dir($newPathReal)) {
        die(json_encode(['success' => false, 'error' => 'Folder with that name already exists']));
    }
} else {
    if (file_exists($newPathReal)) {
        die(json_encode(['success' => false, 'error' => 'File with that name already exists']));
    }
}

if ($type === 'folder') {
    if (!mkdir($newPathReal, 0777, true)) {
        die(json_encode(['success' => false, 'error' => 'Failed to create folder']));
    }
} else {
    if (!touch($newPathReal)) {
        die(json_encode(['success' => false, 'error' => 'Failed to create file']));
    }
}

echo json_encode([
    'success' => true,
    'path'    => virtualize_path($newPathReal),
]);
