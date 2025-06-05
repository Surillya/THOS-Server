<?php
$rootDir = realpath("/home/surillya/");
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['path'])) {
    echo json_encode(['success' => false, 'error' => 'Missing path']);
    exit;
}

// Resolve path properly
$delFullPath = realpath($rootDir . '/' . ltrim($data['path'], '/'));

// Validate path
if (!$delFullPath || strpos($delFullPath, $rootDir) !== 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid path']);
    exit;
}

// Recursive deletion
function deleteRecursively($path) {
    if (is_dir($path)) {
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            deleteRecursively($path . DIRECTORY_SEPARATOR . $item);
        }
        return rmdir($path);
    } elseif (is_file($path)) {
        return unlink($path);
    }
    return false;
}

if (deleteRecursively($delFullPath)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Delete failed']);
}
