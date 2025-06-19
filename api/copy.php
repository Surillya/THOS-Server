<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../vfs.php';

$data    = json_decode(file_get_contents('php://input'), true);
$srcV    = $data['src']  ?? '';
$destV   = $data['dest'] ?? '';

$srcReal  = resolve_path($srcV);
$destReal = resolve_path($destV);

if (!file_exists($srcReal) || !is_dir($destReal)) {
    echo json_encode(['success'=>false, 'error'=>'Invalid paths']);
    exit;
}

$basename = basename($srcReal);
$target   = $destReal . '/' . $basename;

if (is_dir($srcReal)) {
    $rc = mkdir($target);
    // simple recursive copy
    $it = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($srcReal, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
      $subPath = $target . substr($item->getPathname(), strlen($srcReal));
      if ($item->isDir()) mkdir($subPath);
      else copy($item->getPathname(), $subPath);
    }
    $ok = $rc;
} else {
    $ok = copy($srcReal, $target);
}

echo json_encode(['success'=>(bool)$ok]);
