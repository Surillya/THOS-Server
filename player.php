<?php
require_once 'api/getid3/getid3.php';

require_once "vfs.php";

function extractMusicMetadata($file)
{
  $getID3 = new getID3;
  $fileInfo = $getID3->analyze($file);

  $metadata = [
    'title' => basename($file),
    'artist' => 'Unknown Artist',
    'album' => 'Unknown Album',
    'duration' => 0,
    'albumArt' => null
  ];

  if (isset($fileInfo['tags']['id3v2'])) {
    $metadata['title'] = $fileInfo['tags']['id3v2']['title'][0] ?? $metadata['title'];
    $metadata['artist'] = $fileInfo['tags']['id3v2']['artist'][0] ?? $metadata['artist'];
    $metadata['album'] = $fileInfo['tags']['id3v2']['album'][0] ?? $metadata['album'];
  }

  if (isset($fileInfo['playtime_seconds'])) {
    $metadata['duration'] = $fileInfo['playtime_seconds'];
  }

  if (isset($fileInfo['comments']['picture'])) {
    $picture = $fileInfo['comments']['picture'][0];
    $metadata['albumArt'] = 'data:' . $picture['image_mime'] . ';base64,' .
      base64_encode($picture['data']);
  }

  return $metadata;
}

function scanMusicFiles($directory)
{
  $musicExtensions = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'opus'];
  $musicFiles = [];

  $escapedDir = escapeshellarg($directory);
  $files = shell_exec("find $escapedDir -maxdepth 1 -type f");

  $fileList = explode("\n", trim($files));

  $processedFiles = [];
  foreach ($fileList as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, $musicExtensions)) {
      $processedFiles[] = [
        'path' => $file,
        'filename' => basename($file),
        'metadata' => extractMusicMetadata($file)
      ];
    }
  }

  return $processedFiles;
}

$file = resolve_path($_GET['q'] ?? '');

if (!file_exists($file) || !is_file($file)) {
  die("File not found: " . htmlspecialchars($file));
}

$currentFileInfo = [
  'path' => $file,
  'filename' => basename($file),
  'metadata' => extractMusicMetadata($file)
];

$directory = dirname($file);

$playlist = scanMusicFiles($directory);

$fallbackSvg = 'data:image/svg+xml;utf8,' . urlencode('
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">
    <rect width="100" height="100" fill="#333"/>
    <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="white" font-size="20">
        No Art
    </text>
</svg>
');
?>

<!DOCTYPE html>
<html>

<head>
  <title>Music Player</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --accent: #ff69b4;
    }

    body {
      background: transparent;
      user-select: none;
    }

    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1);
    }

    ::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.3);
      border-radius: 4px;
    }

    #seekBar {
      -webkit-appearance: none;
      appearance: none;
      width: 100%;
      height: 6px;
      background: rgba(255, 255, 255, 0.2);
      outline: none;
      opacity: 0.7;
      transition: opacity 0.2s;
      border-radius: 3px;
    }

    #seekBar:hover {
      opacity: 1;
    }

    #seekBar::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 16px;
      height: 16px;
      background: var(--accent);
      cursor: pointer;
      border-radius: 50%;
    }
  </style>
</head>

<body class="text-white bg-transparent font-sans p-4">
  <div class="max-w-md mx-auto bg-black/50 rounded-lg overflow-hidden shadow-xl">
    <div id="albumArtContainer" class="relative">
      <div id="albumArtWrapper" class="relative">
        <img id="albumArt" src="<?= $currentFileInfo['metadata']['albumArt'] ?? $fallbackSvg ?>"
          class="w-full h-64 object-cover blur-sm opacity-50">
        <div id="albumArtFallback" class="absolute inset-0 bg-gradient-to-br from-purple-600 to-blue-500 opacity-50"
          style="display: <?= $currentFileInfo['metadata']['albumArt'] ? 'none' : 'block' ?>">
        </div>
      </div>
      <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
        <h2 id="nowPlayingTitle" class="text-2xl font-bold truncate">
          <?= htmlspecialchars($currentFileInfo['metadata']['title']) ?>
        </h2>
        <p id="nowPlayingArtist" class="text-gray-300 truncate">
          <?= htmlspecialchars($currentFileInfo['metadata']['artist']) ?>
        </p>
      </div>
    </div>

    <div class="p-4">
      <input type="range" id="seekBar" min="0" max="100" value="0" class="w-full mb-2">

      <div class="flex items-center justify-between">
        <button id="shuffleBtn" class="text-gray-300 hover:text-[var(--accent)]">
          <i class="fas fa-random"></i>
        </button>

        <button id="prevBtn" class="text-gray-300 hover:text-[var(--accent)]">
          <i class="fas fa-step-backward"></i>
        </button>

        <button id="playPauseBtn"
          class="text-white bg-[var(--accent)] rounded-full w-12 h-12 flex items-center justify-center">
          <i id="playPauseIcon" class="fas fa-pause"></i>
        </button>

        <button id="nextBtn" class="text-gray-300 hover:text-[var(--accent)]">
          <i class="fas fa-step-forward"></i>
        </button>

        <button id="loopBtn" class="text-gray-300 hover:text-[var(--accent)]">
          <i class="fas fa-repeat"></i>
        </button>
      </div>

      <div class="flex justify-between text-xs text-gray-400 mt-2">
        <span id="currentTime">0:00</span>
        <span id="duration">
          <?= sprintf(
            '%d:%02d',
            floor($currentFileInfo['metadata']['duration'] / 60),
            $currentFileInfo['metadata']['duration'] % 60
          ) ?>
        </span>
      </div>
    </div>

    <div class="max-h-64 overflow-y-auto bg-black/30">
      <ul id="playlist" class="divide-y divide-white/10">
        <?php foreach ($playlist as $index => $playlistItem):
          $encodedFile = urlencode(virtualize_path($playlistItem['path']));
          ?>
          <li class="playlist-item px-4 py-2 cursor-pointer hover:bg-white/10 
                        <?= $playlistItem['path'] === $file ? 'bg-blue-500/50' : '' ?>" data-index="<?= $index ?>"
            data-src="<?= "file.php?q=" . $encodedFile ?>"
            data-title="<?= htmlspecialchars($playlistItem['metadata']['title']) ?>"
            data-artist="<?= htmlspecialchars($playlistItem['metadata']['artist']) ?>"
            data-album-art="<?= $playlistItem['metadata']['albumArt'] ?? '' ?>">
            <div class="flex items-center">
              <div class="flex-grow">
                <p class="font-semibold truncate"><?= htmlspecialchars($playlistItem['filename']) ?></p>
                <p class="text-xs text-gray-400 truncate">
                  <?= htmlspecialchars($playlistItem['metadata']['artist']) ?>
                </p>
              </div>
              <span class="text-xs text-gray-500">
                <?= sprintf(
                  '%d:%02d',
                  floor($playlistItem['metadata']['duration'] / 60),
                  $playlistItem['metadata']['duration'] % 60
                ) ?>
              </span>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <audio id="musicPlayer" style="display:none;" autoplay>
      <source src="<?= "file.php?q=" . urlencode(virtualize_path($file)) ?>" type="audio/mpeg">
    </audio>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      try {
        document.documentElement.style.setProperty('--accent', window.parent.THOS.getAllSettings().accentColor || '#ff69b4');
      } catch (error) {
        console.warn('Could not set accent color from parent window');
      }

      const player = document.getElementById('musicPlayer');
      const playlistItems = document.querySelectorAll('.playlist-item');
      const seekBar = document.getElementById('seekBar');
      const currentTimeEl = document.getElementById('currentTime');
      const durationEl = document.getElementById('duration');
      const playPauseBtn = document.getElementById('playPauseBtn');
      const playPauseIcon = document.getElementById('playPauseIcon');
      const nextBtn = document.getElementById('nextBtn');
      const prevBtn = document.getElementById('prevBtn');
      const shuffleBtn = document.getElementById('shuffleBtn');
      const loopBtn = document.getElementById('loopBtn');
      const albumArt = document.getElementById('albumArt');
      const albumArtFallback = document.getElementById('albumArtFallback');
      const nowPlayingTitle = document.getElementById('nowPlayingTitle');
      const nowPlayingArtist = document.getElementById('nowPlayingArtist');

      let currentIndex = Array.from(playlistItems).findIndex(item =>
        item.classList.contains('bg-blue-500/50')
      );
      let isShuffleMode = false;
      let isLoopMode = false;

      function updateTrackUI(item) {
        playlistItems.forEach(el => el.classList.remove('bg-blue-500/50'));

        item.classList.add('bg-blue-500/50');

        const albumArtSrc = item.getAttribute('data-album-art');

        if (albumArtSrc) {
          albumArt.src = albumArtSrc;
          albumArt.style.display = 'block';
          albumArtFallback.style.display = 'none';
        } else {
          albumArt.style.display = 'none';
          albumArtFallback.style.display = 'block';
        }

        nowPlayingTitle.textContent = item.getAttribute('data-title');
        nowPlayingArtist.textContent = item.getAttribute('data-artist');
      }

      function playTrack(item) {
        const src = item.getAttribute('data-src');
        player.src = src;
        player.play();
        updateTrackUI(item);
        playPauseIcon.classList.replace('fa-play', 'fa-pause');
      }

      document.getElementById('playlist').addEventListener('click', (e) => {
        const item = e.target.closest('.playlist-item');
        if (item) {
          currentIndex = parseInt(item.getAttribute('data-index'));
          playTrack(item);
        }
      });

      seekBar.addEventListener('input', () => {
        const time = (seekBar.value / 100) * player.duration;
        player.currentTime = time;
      });

      playPauseBtn.addEventListener('click', () => {
        if (player.paused) {
          player.play();
          playPauseIcon.classList.replace('fa-play', 'fa-pause');
        } else {
          player.pause();
          playPauseIcon.classList.replace('fa-pause', 'fa-play');
        }
      });

      nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % playlistItems.length;
        playTrack(playlistItems[currentIndex]);
      });

      prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + playlistItems.length) % playlistItems.length;
        playTrack(playlistItems[currentIndex]);
      });

      shuffleBtn.addEventListener('click', () => {
        isShuffleMode = !isShuffleMode;
        shuffleBtn.classList.toggle('text-blue-500', isShuffleMode);
      });

      loopBtn.addEventListener('click', () => {
        isLoopMode = !isLoopMode;
        player.loop = isLoopMode;
        loopBtn.classList.toggle('text-blue-500', isLoopMode);
      });

      player.addEventListener('timeupdate', () => {
        const progress = (player.currentTime / player.duration) * 100;
        seekBar.value = progress;

        const minutes = Math.floor(player.currentTime / 60);
        const seconds = Math.floor(player.currentTime % 60);
        currentTimeEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
      });

      player.addEventListener('ended', () => {
        if (isLoopMode) {
          player.play();
          return;
        }

        if (isShuffleMode) {
          currentIndex = Math.floor(Math.random() * playlistItems.length);
        } else {
          currentIndex = (currentIndex + 1) % playlistItems.length;
        }

        playTrack(playlistItems[currentIndex]);
      });

      player.addEventListener('loadedmetadata', () => {
        const minutes = Math.floor(player.duration / 60);
        const seconds = Math.floor(player.duration % 60);
        durationEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
      });
    });
  </script>
</body>

</html>