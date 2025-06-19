<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../vfs.php';

$dir        = $_GET['dir']      ?? '';
$q          = $_GET['q']        ?? '';
$showHidden = ($_GET['hidden'] ?? '0') === '1';

$base = resolve_path($dir);
if (!is_dir($base)) {
    echo json_encode([]);
    exit;
}

$out = [];
$it  = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
);

foreach ($it as $f) {
    $name = $f->getFilename();
    if (!$showHidden && strpos($name, '.') === 0) continue;
    if (stripos($name, $q) === false) continue;

    $full = $f->getPathname();
    $out[] = [
        'name'    => $name,
        'virtual' => virtualize_path($full),
        'isDir'   => $f->isDir(),
        'ext'     => $f->getExtension(),
        'size'    => $f->getSize(),
        'mtime'   => $f->getMTime(),
    ];
}

echo json_encode($out);
