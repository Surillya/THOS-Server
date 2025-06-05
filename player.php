<?php
require_once "vfs.php";
$file = resolve_path($_GET['q'] ?? '');

if (!file_exists($file) || !is_file($file)) {
  die("File not found." . $file);
}

$filename = htmlspecialchars(basename($file));
?>

<!DOCTYPE html>
<html>

<head>
  <title>Now Playing: <?= $filename ?></title>
  <style>
    body {
      background: transparent;
      color: white;
      font-family: sans-serif;
      text-align: center;
    }

    audio {
      width: 90%;
      margin-top: 1em;
    }

    .controls {
      margin-top: 1em;
    }
  </style>
</head>

<body>
  <h2>🎵 <?= $filename ?></h2>
  <audio controls autoplay>
    <source src="<?= "file.php?q=" . urlencode(virtualize_path($file)) ?>" type="audio/mpeg">
    Audio cannot be played on your system.
  </audio>
</body>

</html>