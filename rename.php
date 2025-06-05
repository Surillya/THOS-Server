<?php
$rootDir = realpath("/home/surillya/");
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['oldName']) || empty($data['newName'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Resolve paths properly
$oldFullPath = realpath($rootDir . '/' . ltrim($data['oldName'], '/'));
$newFullPath = $rootDir . '/' . ltrim(dirname($data['oldName']), '/') . '/' . basename($data['newName']);
$newFullPath = realpath(dirname($newFullPath)) . '/' . basename($data['newName']); // Ensure dir exists

// Validate paths
if (!$oldFullPath || strpos($oldFullPath, $rootDir) !== 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid old path']);
    exit;
}

if (file_exists($newFullPath)) {
    echo json_encode(['success' => false, 'error' => 'New name already exists']);
    exit;
}

// Attempt rename
if (rename($oldFullPath, $newFullPath)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Rename failed']);
}
