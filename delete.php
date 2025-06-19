<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$path = $_SERVER['DOCUMENT_ROOT'] . '/' . $data['path'];

if (!file_exists($path)) {
    echo json_encode(['success' => false, 'error' => 'File/folder does not exist']);
    exit;
}

// Check if it's a directory
if (is_dir($path)) {
    // Delete recursively
    deleteDirectory($path);
} else {
    unlink($path);
}

echo json_encode(['success' => true]);
?>

<?php
function deleteDirectory($dir) {
    if (!is_dir($dir)) return;

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}
?>
