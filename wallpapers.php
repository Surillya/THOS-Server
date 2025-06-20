<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Wallpaper Chooser</title>
  <script src="tailwind.es"></script>
  <style>
    body {
      background: transparent;
    }
  </style>
</head>

<body class="text-white p-6 font-sans">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold text-pink-400 mb-6">Wallpaper Picker</h1>
    <div id="wallpapers" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php
      $wallpaperDir = "/root/Pictures/";
      $images = glob($wallpaperDir . "*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
      foreach ($images as $img) {
        require_once "vfs.php";
        $path = virtualize_path(htmlspecialchars($img));
        $url = "file.php?q=" . urlencode(virtualize_path($img));
        echo <<<HTML
        <div onclick="selectWallpaper('$path')" class="cursor-pointer hover:scale-105 transition transform duration-300 rounded-xl overflow-hidden shadow-lg border-2 border-transparent hover:border-pink-400">
          <img src="$url" alt="Wallpaper" class="w-full h-40 object-cover">
        </div>
        HTML;
      }
      ?>
    </div>
  </div>

  <script>
    let selectedWallpaper = '';

    function selectWallpaper(path) {
      selectedWallpaper = path;
      let settings = JSON.parse(localStorage.getItem('settings')) || {};
      settings.wallpaper = selectedWallpaper;
      localStorage.setItem('settings', JSON.stringify(settings));
      parent.postMessage({ type: 'applySettings', settings }, '*');
    }
  </script>
</body>

</html>