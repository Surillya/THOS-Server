<?php
header('Content-Type: application/json');

$url = $_GET['url'] ?? '';
$output = $_GET['output'] ?? '';
$verified = $_GET['verified'] ?? 0;

function fail($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Force verify off if not surillya.com
if (!preg_match('/^https:\/\/surillya\.com\//', $url)) {
    $verified = 0;
}

$targetPath = '/home/surillya/.temp/' . basename($output);

if (!is_dir("/home/surillya/.temp/")){
    if (!mkdir("/home/surillya/.temp/", 0755, true)){
        fail("Failed to create temporary download directory.");
    }
}

try {
    $data = file_get_contents($url);
    if ($data === false) fail("Failed to download file.");

    file_put_contents($targetPath, $data);
    echo json_encode([
        'success' => true,
        'filename' => ".temp/" . basename($targetPath),
                     'verified' => $verified
    ]);
} catch (Exception $e) {
    fail($e->getMessage());
}
