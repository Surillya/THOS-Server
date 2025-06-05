<?php
require_once "vfs.php";
$file = resolve_path($_GET['q'] ?? '');

if (!file_exists($file) || !is_file($file)) {
    die("File not found." . $file);
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime = match ($ext) {
    'jpg', 'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    default => null
};

if (!$mime)
    die("Unsupported format.");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Image Viewer</title>
    <style>
        body {
            background: transparent;
            margin: 0;
            text-align: center;
        }

        img {
            max-width: 100%;
            max-height: 100vh;
        }
    </style>
</head>

<body>
    <h3 style="color:white"><?= htmlspecialchars(basename($file)) ?></h3>
    <img src="<?= "file.php?q=" . urlencode($_GET['q']) ?>" alt="Image">
</body>

</html>