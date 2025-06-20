<?php
$rootDir = realpath("/root");

$query = $_GET['q'] ?? '';

$cleanQuery = preg_replace('#/+#', '/', ltrim($query, '/'));

$targetPath = $rootDir . DIRECTORY_SEPARATOR . $cleanQuery;

$requestedPath = realpath($targetPath);

if (
    !$requestedPath ||
    strpos($requestedPath, $rootDir) !== 0 ||
    !is_file($requestedPath)
) {
    http_response_code(404);
    echo "File not found or access denied.";
    exit;
}

$escapedPath = escapeshellarg($requestedPath);
$mime = trim(shell_exec("file -b --mime-type $escapedPath"));

header("Content-Type: $mime");
header("Content-Length: " . filesize($requestedPath));
header("Content-Disposition: inline; filename=\"" . basename($requestedPath) . "\"");

readfile($requestedPath);
exit;
?>
