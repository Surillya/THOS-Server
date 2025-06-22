<?php
require_once('state.php');
$state = thos_load_state();
if (!$state || !isset($state['localData']) || !json_decode($state['localData'], true)['thos_done']) {
  header('Location: oobe.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>THOS</title>
  <style>
    :root {
      --accent: #4CAF50;
      --window-bg: rgba(28, 28, 28, 0.6);
      --window-border: rgba(255, 255, 255, 0.1);
      --window-blur: blur(20px);
      --header-bg: rgba(30, 30, 30, 0.6);
      --header-color: white;
      --header-hover: var(--accent);
    }

    .theme-glassy-dark {
      --window-bg: rgba(28, 28, 28, 0.6);
      --window-border: rgba(255, 255, 255, 0.1);
      --window-blur: blur(20px);
      --header-bg: rgba(30, 30, 30, 0.6);
      --header-color: white;
      --font-color: white;
    }

    .theme-frosted-blue {
      --window-bg: rgba(0, 40, 80, 0.4);
      --window-border: rgba(173, 216, 230, 0.2);
      --window-blur: blur(25px);
      --header-bg: rgba(10, 20, 50, 0.4);
      --header-color: #e3f8ff;
      --font-color: #e3f8ff;
    }

    .theme-dreamy-pink {
      --window-bg: rgba(80, 30, 60, 0.5);
      --window-border: rgba(255, 182, 193, 0.3);
      --window-blur: blur(16px);
      --header-bg: rgba(100, 40, 80, 0.5);
      --header-color: #ffddee;
      --font-color: #ffe9f4;
    }

    .theme-xp {
      --window-bg: #ece9d8;
      --window-border: #3a6ea5;
      --window-blur: none;
      --header-bg: linear-gradient(to bottom, #0a246a, #1c4dbd);
      --header-color: white;
      --font-color: black;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #121212;
      color: white;
      overflow: hidden;
    }

    #app-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 200px;
      height: 100vh;
      background: var(--window-bg);
      backdrop-filter: var(--window-blur);
      -webkit-backdrop-filter: var(--window-blur);
      border: 1px solid var(--window-border);
      padding: 10px;
      box-sizing: border-box;
      z-index: 9999;
    }

    .app-btn {
      display: block;
      margin: 10px 0;
      padding: 10px;
      background: #292929;
      border: none;
      color: white;
      cursor: pointer;
      border-radius: 8px;
      transition: background 0.3s ease;
    }

    .app-btn:hover {
      background: var(--accent);
    }

    .window {
      position: absolute;
      width: 600px;
      height: 400px;
      background: var(--window-bg);
      backdrop-filter: var(--window-blur);
      -webkit-backdrop-filter: var(--window-blur);
      border: 1px solid var(--window-border);
      border-radius: 12px;
      resize: both;
      overflow: hidden;
      z-index: 1;
      transition: box-shadow 0.2s ease, backdrop-filter 0.3s ease;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    }


    .window.active {
      box-shadow: 0 0 20px var(--accent), 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    .window-header {
      background: var(--header-bg);
      color: var(--header-color);
      padding: 6px 12px;
      cursor: grab;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-weight: bold;
      backdrop-filter: var(--window-blur);
      -webkit-backdrop-filter: var(--window-blur);
      border-bottom: 1px solid var(--window-border);
    }

    .window-header:active {
      cursor: grabbing;
    }


    .window-header button {
      background: transparent;
      border: none;
      color: inherit;
      font-weight: bold;
      font-size: 1.1em;
      cursor: pointer;
      user-select: none;
      padding: 0 6px;
      transition: color 0.2s ease;
    }

    .window-header button:hover {
      color: var(--header-hover);
    }


    .window-content {
      background: var(--window-bg);
      backdrop-filter: var(--window-blur);
      -webkit-backdrop-filter: var(--window-blur);
      border: 1px solid var(--window-border);
      width: 100%;
      height: calc(100% - 32px);
      border: none;
    }

    .sparkle {
      position: absolute;
      width: 6px;
      height: 6px;
      background: radial-gradient(circle at center, var(--accent) 0%, transparent 70%);
      border-radius: 50%;
      filter: drop-shadow(0 0 6px var(--accent));
      animation: sparkle 1.2s infinite ease-in-out;
      pointer-events: none;
    }

    @keyframes sparkle {

      0%,
      100% {
        opacity: 0;
        transform: scale(0.5) rotate(0deg);
      }

      50% {
        opacity: 1;
        transform: scale(1) rotate(45deg);
      }
    }

    #screensaver {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      border: none;
      z-index: 9999;
    }

    #drag-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      z-index: 99999;
      display: none;
      cursor: grabbing;
    }

    #fullscreen-toggle {
      border: none;
      background: transparent;
      cursor: pointer;
      backdrop-filter: blur(2px);
      -webkit-backdrop-filter: blur(2px);
      border-radius: 4px;
    }

    #fullscreen-toggle:hover {
      color: var(--accent);
    }

    .grayscale-bg {
      filter: grayscale(100%) brightness(0.9);
    }

    .notification {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
      padding: 1rem 1.25rem;
      border-radius: 1rem;
      border: 2px solid transparent;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      color: white;
      min-width: 260px;
      max-width: 340px;
      pointer-events: auto;
      animation: fadeInUp 0.3s ease-out;
      position: relative;
      transition: transform 0.2s ease, opacity 0.2s ease;
      background: var(--window-bg);
      backdrop-filter: var(--window-blur);
      -webkit-backdrop-filter: var(--window-blur);
      color: var(--accent);
    }

    .notification-dismiss {
      background: transparent;
      border: none;
      cursor: pointer;
    }

    .notification.success {
      border-color: #4caf50;
    }

    .notification.error {
      border-color: #f44336;
    }

    .notification.info {
      border-color: var(--accent, #00bcd4);
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%) scale(0.95);
        opacity: 0;
      }

      to {
        transform: translateX(0) scale(1);
        opacity: 1;
      }
    }

    @keyframes fadeOut {
      from {
        opacity: 1;
        transform: translateX(0) scale(1);
      }

      to {
        opacity: 0;
        transform: translateX(50%) scale(0.95);
      }
    }
  </style>
  <script src="tailwind.es"></script>
</head>

<body class="bg-black">
  <div id="appWrapper" class="transition-all duration-300">
    <div id="bg-image" class="fixed inset-0 z-[-10] bg-cover bg-center transition-all duration-300"></div>
    <div id="app-bar" class="bg-[#1e1e1e] fixed top-0 left-0 w-52 h-screen p-2.5 z-50">
      <div class="flex flex-col h-full">
        <div class="flex items-start mb-4 gap-2">
          <button id="powerButton" class="group transition duration-300">
            <img src="THOS.png" alt="Power" class="w-12 h-12 transition-all duration-300 ease-in-out
     group-hover:brightness-125
     group-hover:scale-110
     group-hover:drop-shadow-[0_0_10px_rgba(255,200,255,0.6)]
     group-hover:saturate-150
     cursor-pointer select-none">
          </button>

          <div id="icon-tray" class="grid grid-cols-4 grid-rows-2 gap-1 pt-1">
            <button id="wifi-toggle" class="group p-1 hover:bg-[#2e2e2e] rounded-md" title="Wi-Fi">
              <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-400 transition-all" viewBox="0 0 24 24"
                fill="currentColor">
                <path
                  d="M12 18c.552 0 1 .448 1 1s-.448 1-1 1-1-.448-1-1 .448-1 1-1zm0-2c1.104 0 2 .896 2 2s-.896 2-2 2-2-.896-2-2 .896-2 2-2zm0-3.75a7.25 7.25 0 0 1 5.12 2.13.75.75 0 1 0 1.06-1.06 8.75 8.75 0 0 0-12.36 0 .75.75 0 1 0 1.06 1.06A7.25 7.25 0 0 1 12 12.25zm0-3.5a10.75 10.75 0 0 1 7.6 3.15.75.75 0 1 0 1.06-1.06 12.25 12.25 0 0 0-17.32 0 .75.75 0 0 0 1.06 1.06A10.75 10.75 0 0 1 12 8.75z" />
              </svg>
            </button>
            <!-- Don't work for no reason -->
            <!-- <button id="volume-toggle" class="group p-1 hover:bg-[#2e2e2e] rounded-md" title="Volume">
              <svg class="w-6 h-6 text-gray-300 group-hover:text-green-400 transition-all" viewBox="0 0 24 24"
                fill="currentColor">
                <path d="M5 9v6h4l5 5V4l-5 5H5z" />
                <path d="M16.5 12a4.5 4.5 0 0 0-1.5-3.36v6.72A4.5 4.5 0 0 0 16.5 12z" />
              </svg>
            </button>

            <button id="brightness-toggle" class="group p-1 hover:bg-[#2e2e2e] rounded-md" title="Brightness">
              <svg class="w-6 h-6 text-gray-300 group-hover:text-yellow-400 transition-all" viewBox="0 0 24 24"
                fill="currentColor">
                <path
                  d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12zm0-16v2m0 16v2m8-10h2M2 12H0m17.66 6.34l1.41 1.41M4.93 4.93l1.41 1.41m0 12.73l-1.41 1.41M19.07 4.93l-1.41 1.41" />
              </svg>
            </button> -->
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3 p-2 flex flex-col justify-between">
          <button onclick="openApp('explorer.html', 'Explorer')" title="File Explorer"
            class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition-all">
            <svg class="w-6 h-6 text-blue-300 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path d="M3 7l6-3 6 3 6-3v12l-6 3-6-3-6 3V7z" />
              <path d="M9 4v12" />
              <path d="M15 7v12" />
            </svg>
          </button>

          <button onclick="openApp('tasks.php', 'Task Manager')" title="Task Manager"
            class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition-all">
            <svg class="w-6 h-6 text-red-300 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <rect x="3" y="3" width="7" height="7" rx="1" />
              <rect x="14" y="3" width="7" height="7" rx="1" />
              <rect x="3" y="14" width="7" height="7" rx="1" />
              <rect x="14" y="14" width="7" height="7" rx="1" />
            </svg>
          </button>

          <button onclick="openApp('https://surillya.com/thos/store', 'THOS Store')" title="THOS Store"
            class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition-all">
            <svg class="w-6 h-6 text-pink-300 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path d="M4 9h16l-1.68 10.08a2 2 0 0 1-2 1.92H7.68a2 2 0 0 1-2-1.92L4 9Z" />
              <path d="M8 11V8a4 4 0 0 1 8 0v3" />
            </svg>
          </button>

          <button onclick="openApp('internet.php', 'Internet', true)"
            title="Internet" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition-all">
            <svg class="w-6 h-6 text-green-300 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" />
              <path d="M2 12h20" />
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
            </svg>
          </button>

          <button onclick="openApp('settings.php', 'Settings')" title="Settings"
            class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition-all">
            <svg class="w-6 h-6 text-yellow-300 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
              <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1
                  1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0
                  010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65
                  1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0
                  012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65
                  0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" />
            </svg>
          </button>
        </div>

        <div class="flex-grow"></div>

        <div id="trdp"
          class="grid grid-cols-2 gap-2 mt-4 p-2 bg-[#252525] rounded-lg overflow-y-auto p-2 justify-center items-center">
        </div>

        <div id="version-display-container" class="w-full flex justify-center items-center mt-4">
          <div id="version-display" class="flex items-center gap-2 text-sm text-gray-400 select-none">
            <button id="fullscreen-toggle" title="Toggle fullscreen"
              class="text-white hover:text-[var(--accent)] transition-colors duration-200 text-xs p-1 rounded bg-transparent hover:bg-[var(--glass-bg-light)]">
              ⛶
            </button>
            <span class="text-[var(--accent)]">THOS</span><span id="version-number"></span>
          </div>
        </div>
      </div>
    </div>

    <div id="desktop" class="ml-52 h-screen relative">
      <div id="notification-container" class="fixed top-4 right-4 z-50 flex flex-col gap-3 pointer-events-none"></div>
    </div>

    <iframe id="screensaver" src="screensaver.html"></iframe>

    <div id="drag-overlay"></div>

    <div id="wifi-dropdown"
      class="hidden absolute left-[220px] top-[60px] w-64 bg-[#1f1f1f] text-white rounded shadow-xl z-[999] p-3 border border-gray-700">
      <div id="wifi-list" class="max-h-60 overflow-y-auto text-sm">Loading networks...</div>
      <div id="wifi-connect" class="hidden mt-3">
        <input type="password" id="wifi-pass" placeholder="Wi-Fi Password"
          class="w-full p-1 mb-2 bg-[#2a2a2a] border border-gray-600 rounded text-white placeholder-gray-400" />
        <button id="connect-btn"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm p-1 rounded">Connect</button>
      </div>
    </div>

    <div id="volume-control"
      class="hidden absolute left-[220px] top-[100px] w-48 bg-[#1f1f1f] text-white rounded p-3 shadow-lg z-[999] border border-gray-700">
      <label class="text-sm block mb-1">Volume</label>
      <input type="range" min="0" max="100" id="volume-slider" class="w-full">
    </div>

    <div id="brightness-control"
      class="hidden absolute left-[220px] top-[160px] w-48 bg-[#1f1f1f] text-white rounded p-3 shadow-lg z-[999] border border-gray-700">
      <label class="text-sm block mb-1">Brightness</label>
      <input type="range" min="10" max="100" id="brightness-slider" class="w-full">
    </div>
  </div>

  <div id="powerDialog" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="bg-black/30 rounded-2xl shadow-2xl p-6 w-80 backdrop-blur-sm text-center text-white">
      <h2 class="text-xl font-semibold mb-6 tracking-wide">Power Options</h2>
      <div class="space-y-3">
        <button id="shutdownBtn"
          class="w-full py-2 px-4 border border-dotted rounded-xl transition-all duration-200 text-sm bg-black/40 hover:bg-black/50 backdrop-blur-sm border-red-400 text-red-300 hover:text-red-100 hover:border-red-300">Shutdown</button>
        <button id="restartBtn"
          class="w-full py-2 px-4 border border-dotted rounded-xl transition-all duration-200 text-sm bg-black/40 hover:bg-black/50 backdrop-blur-sm border-yellow-400 text-yellow-300 hover:text-yellow-100 hover:border-yellow-300">Restart</button>
        <button id="reloadBtn"
          class="w-full py-2 px-4 border border-dotted rounded-xl transition-all duration-200 text-sm bg-black/40 hover:bg-black/50 backdrop-blur-sm border-green-400 text-green-300 hover:text-green-100 hover:border-green-300">Reload</button>
        <button id="cancelBtn"
          class="w-full py-2 px-4 border border-dotted rounded-xl transition-all duration-200 text-sm bg-black/40 hover:bg-black/50 backdrop-blur-sm border-white/40 text-white hover:text-white hover:border-white/80">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    window.THOS = {
      version: '6 Build-35'
    };

    let timeoutSeconds = 180;

    const thosState = <?= json_encode($state ?? ['cookies' => '', 'localData' => '{}']) ?>;

    thosState.cookies?.split('; ').forEach(cookie => document.cookie = cookie);

    const localItems = JSON.parse(thosState.localData || '{}');
    for (let key in localItems) {
      localStorage.setItem(key, localItems[key]);
    }

    if (!localStorage.getItem('thos_done')) {
      window.location.href = 'oobe.php';
    }

    class WindowManager {
      constructor(container) {
        this.container = container;
        this.zIndexCounter = 1;
        this.draggingWindow = null;
        this.resizingWindow = null;
        this.resizeDir = null;
        this.offsetX = 0;
        this.offsetY = 0;
        this.startWidth = 0;
        this.startHeight = 0;
        this.startLeft = 0;
        this.startTop = 0;
        this.isMaximized = new WeakMap();
        this.activeWindow = null;

        this.dragOverlay = document.getElementById('drag-overlay');

        document.addEventListener('mousemove', this.onMouseMove.bind(this));
        document.addEventListener('mouseup', this.onMouseUp.bind(this));

        this.makeWindowsDraggable();
      }

      makeWindowsDraggable() {
        const windows = this.container.querySelectorAll('.window');
        windows.forEach(win => {
          const header = win.querySelector('.window-header');
          this.makeDraggable(win, header);
          this.makeResizable(win);
          this.setupButtons(win);
          this.setupFocus(win);
        });
      }

      setupFocus(win) {
        win.addEventListener('mousedown', () => {
          this.bringToFront(win);
          this.setActiveWindow(win);
        });

        win.addEventListener('focus', () => {
          this.bringToFront(win);
          this.setActiveWindow(win);
        });
      }

      setActiveWindow(win) {
        if (this.activeWindow && this.activeWindow !== win) {
          this.activeWindow.classList.remove('active');
        }
        this.activeWindow = win;
        win.classList.add('active');
      }

      makeDraggable(win, header) {
        header.style.cursor = 'grab';

        header.addEventListener('mousedown', (e) => {
          if (e.target.closest('button')) return;

          e.preventDefault();
          this.bringToFront(win);
          this.setActiveWindow(win);

          if (this.isMaximized.get(win)) {
            win.style.transition = "all 0.2s ease";
            win.style.left = win.dataset.prevLeft;
            win.style.top = win.dataset.prevTop;
            win.style.width = win.dataset.prevWidth;
            win.style.height = win.dataset.prevHeight;
            this.isMaximized.set(win, false);
          }

          this.draggingWindow = win;
          this.offsetX = e.clientX - win.offsetLeft;
          this.offsetY = e.clientY - win.offsetTop;

          header.style.cursor = 'grabbing';

          if (this.dragOverlay) {
            this.dragOverlay.style.display = 'block';
            this.dragOverlay.style.cursor = 'grabbing';
          }

          document.body.style.userSelect = 'none';
        });
      }

      onMouseMove(e) {
        if (this.draggingWindow) {
          let newLeft = e.clientX - this.offsetX;
          let newTop = e.clientY - this.offsetY;

          const containerRect = this.container.getBoundingClientRect();
          const winRect = this.draggingWindow.getBoundingClientRect();

          newLeft = Math.max(containerRect.left, Math.min(newLeft, containerRect.right - winRect.width));
          newTop = Math.max(containerRect.top, Math.min(newTop, containerRect.bottom - winRect.height));

          this.draggingWindow.style.left = newLeft + 'px';
          this.draggingWindow.style.top = newTop + 'px';
        }
      }

      onMouseUp() {
        if (this.draggingWindow) {
          const header = this.draggingWindow.querySelector('.window-header');
          if (header) header.style.cursor = 'grab';
          this.draggingWindow = null;

          if (this.dragOverlay) {
            this.dragOverlay.style.display = 'none';
            this.dragOverlay.style.cursor = '';
          }

          document.body.style.userSelect = '';
        }

        if (this.resizingWindow) {
          this.resizingWindow = null;
          this.resizeDir = null;
        }
      }

      bringToFront(win) {
        win.style.zIndex = ++this.zIndexCounter;
      }

      toggleMaximize(win) {
        if (!this.isMaximized.get(win)) {
          win.dataset.prevLeft = win.style.left;
          win.dataset.prevTop = win.style.top;
          win.dataset.prevWidth = win.style.width;
          win.dataset.prevHeight = win.style.height;

          win.style.left = "0px";
          win.style.top = "0px";
          win.style.width = "100%";
          win.style.height = "100%";
          win.style.transition = "all 0.2s ease";

          this.isMaximized.set(win, true);
        } else {
          win.style.transition = "all 0.2s ease";
          win.style.left = win.dataset.prevLeft;
          win.style.top = win.dataset.prevTop;
          win.style.width = win.dataset.prevWidth;
          win.style.height = win.dataset.prevHeight;
          this.isMaximized.set(win, false);
        }
      }
    }

    const tasks = [];
    let z = 2;
    const wm = new WindowManager(document.body);
    const appBar = document.getElementById('trdp');
    const contextMenu = document.createElement('div');
    contextMenu.id = 'context-menu';
    contextMenu.style.position = 'absolute';
    contextMenu.style.display = 'none';
    contextMenu.style.background = '#222';
    contextMenu.style.border = '1px solid #444';
    contextMenu.style.borderRadius = '6px';
    contextMenu.style.padding = '6px';
    contextMenu.style.zIndex = '9999';
    document.body.appendChild(contextMenu);

    document.addEventListener('click', (e) => {
      contextMenu.style.display = 'none';
      if (!wifiDropdown.contains(e.target) && !wifiToggle.contains(e.target)) {
        wifiDropdown.classList.add('hidden');
      }
    });

    async function loadApps() {
      const res = await fetch('apps.php');
      const apps = await res.json();
      console.log(apps);

      appBar.innerHTML = '';
      apps.forEach(app => {
        const wrapper = document.createElement('div');
        wrapper.classList.add('wrapper');
        wrapper.style.alignItems = 'center';
        wrapper.style.height = '50px';
        wrapper.style.width = '50px';
        wrapper.style.margin = '8px';
        wrapper.style.transition = 'transform 0.3s ease';

        const btn = document.createElement('img');
        btn.src = app.icon;
        btn.alt = app.name;
        btn.title = app.name;
        btn.style.height = '50px';
        btn.style.width = '50px';
        btn.style.borderRadius = '16px';
        btn.style.border = '2px solid #ffffff22';
        btn.style.boxShadow = '0 2px 10px rgba(255, 192, 203, 0.25)';
        btn.style.background = 'linear-gradient(135deg, #222, #1a1a1a)';
        btn.style.padding = '6px';
        btn.style.transition = 'all 0.3s ease';
        btn.style.cursor = 'pointer';

        btn.onmouseenter = () => {
          wrapper.style.transform = 'scale(1.08)';
          btn.style.borderColor = 'var(--accent)';
          btn.style.boxShadow = '0 4px 16px var(--accent), 0 0 8px var(--accent)';

          for (let i = 0; i < 6; i++) {
            const sparkle = document.createElement('div');
            sparkle.classList.add('sparkle');

            sparkle.style.top = `${Math.random() * 100}%`;
            sparkle.style.left = `${Math.random() * 100}%`;

            sparkle.style.animationDelay = `${Math.random() * 1.5}s`;

            wrapper.appendChild(sparkle);

            sparkle.addEventListener('animationiteration', () => sparkle.remove());
          }
        };

        btn.onmouseleave = () => {
          wrapper.style.transform = 'scale(1)';
          btn.style.borderColor = '#ffffff22';
          btn.style.boxShadow = '0 2px 10px rgba(255, 192, 203, 0.25)';

          const sparkles = wrapper.querySelectorAll('.sparkle');
          sparkles.forEach(s => s.remove());
        };

        btn.onclick = () => openApp(app.path, app.name);

        wrapper.appendChild(btn);

        wrapper.oncontextmenu = e => {
          e.preventDefault();
          showContextMenu(e.pageX, e.pageY, app);
        };

        appBar.appendChild(wrapper);
      });
    }

    function showContextMenu(x, y, app) {
      contextMenu.innerHTML = `
      <div onclick="uninstallApp('${app.id}')">🗑️ Uninstall</div>
      `;
      contextMenu.style.left = `${x}px`;
      contextMenu.style.top = `${y}px`;
      contextMenu.style.display = 'block';
    }

    function openApp(url, title, sandbox = false) {
      const id = 'win_' + Date.now();
      const win = document.createElement('div');
      win.className = 'window';
      win.style.top = Math.random() * 300 + 'px';
      win.style.left = Math.random() * 400 + 'px';
      win.style.zIndex = z++;
      win.setAttribute('data-id', id);

      win.innerHTML = `
          <div class="window-header">
            <span class="window-title">${title}</span>
            <div class="window-controls">
              <button class="max-btn" title="Maximize/Restore">🗖</button>
              <button class="close-btn" title="Close">×</button>
            </div>
          </div>`
        ;

      if (!sandbox) {
        win.innerHTML += `<iframe src="${url}" class="window-content" allowtransparency="true"></iframe>`;
      } else {
        win.innerHTML += `<iframe src="${url}" sandbox="allow-scripts allow-forms allow-same-origin" class="window-content" allowtransparency="true"></iframe>`;
      }

      document.getElementById('desktop').appendChild(win);
      tasks.push({ id, title, url });
      let header = win.querySelector(".window-header");
      wm.makeDraggable(win, header);
      header.querySelector('.max-btn').addEventListener('click', (e) => {
        e.stopPropagation();
        wm.toggleMaximize(win);
      });
      header.querySelector('.close-btn').addEventListener('click', (e) => {
        e.stopPropagation();
        closeApp(id);
      });
    }

    function closeApp(id) {
      const win = document.querySelector(`.window[data-id='${id}']`);
      if (win) win.remove();
      const idx = tasks.findIndex(t => t.id === id);
      if (idx !== -1) tasks.splice(idx, 1);
    }

    function focusApp(id) {
      const win = document.querySelector(`.window[data-id='${id}']`);
      if (win) win.style.zIndex = z++;
    }

    function uninstallApp(appId) {
      if (!confirm("Are you sure you want to uninstall this app?")) return;
      fetch(`uninstaller.php?id=${encodeURIComponent(appId)}`)
        .then(res => res.text())
        .then(() => loadApps());
    }

    loadApps();

    window.addEventListener('message', async (event) => {
      const { type, appId, packageFileUrl } = event.data || {};

      if (type === 'THOS_INSTALL_APP') {
        try {
          const isValid = /^https:\/\/surillya\.com\//.test(packageFileUrl);
          const fileName = packageFileUrl.split('/').pop().split('?')[0];
          const outputPath = `/root/.temp/${fileName}`;

          // I HATE UNDERSCORES!!!!
          // underscore =/= dash
          const url = `/install_app.php?url=${encodeURIComponent(packageFileUrl)}&output=${encodeURIComponent(outputPath)}&verified=${isValid ? 1 : 0}`;
          const response = await fetch(url);
          const result = await response.json();

          if (!result.success) throw new Error(result.message);

          const appUrl = `thp.php?q=${encodeURIComponent(result.filename)}&v=${result.verified}`;
          window.openApp(appUrl, 'THOS Package Installer');
        } catch (err) {
          console.error('[THOS] App install failed:', err);
          alert('Failed to install app.');
        }
      }
    });

    window.getTaskList = () => JSON.parse(JSON.stringify(tasks));
    window.focusApp = focusApp;
    window.closeApp = closeApp;
    window.openApp = openApp;
    window.reloadApps = loadApps;
    window.saveTHOSState = saveTHOSState;

    function applySettings(settings) {
      console.log(settings);

      function applyTheme(theme) {
        document.body.className = theme;
      }

      const savedTheme = settings.theme;
      if (savedTheme) applyTheme(savedTheme);

      document.documentElement.style.setProperty('--accent', settings.accentColor || '#ff69b4');

      if (settings.wallpaper) {
        document.getElementById('bg-image').style.background = `url('file.php?q=${settings.wallpaper}') center/cover`;
      } else {
        document.getElementById('bg-image').style.background = '';
      }

      const audio = document.getElementById('bgMusic');
      if (audio) {
        if (settings.musicEnabled === 'on') {
          document.addEventListener("click", function () {
            audio.src = `file.php?q=${settings.music}`;
            audio.volume = parseFloat(settings.musicVolume || 0.3);
            audio.play().catch(() => {
              alert('An error occured while starting background music. Please turn background music off, or enable autoplay.');
            });
          }, { once: true });
        } else {
          audio.pause;
        }
      }

      timeoutSeconds = settings.screenTimeout ?? timeoutSeconds;

      window.THOS = Object.assign(window.THOS || {}, {
        getAllSettings() {
          return settings;
        }
      });

      saveTHOSState();
    }

    const savedSettings = JSON.parse(localStorage.getItem('settings')) || {};
    applySettings(savedSettings);

    window.addEventListener('message', (event) => {
      if (event.data?.type === 'applySettings') {
        applySettings(event.data.settings);
      }
    });

    function saveTHOSState() {
      const cookies = document.cookie;
      const localData = JSON.stringify(localStorage);

      fetch('save_state.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cookies, localData })
      });
    }

    setInterval(saveTHOSState, 60000);
    window.addEventListener('beforeunload', saveTHOSState);

    const powerButton = document.getElementById('powerButton');
    const powerDialog = document.getElementById('powerDialog');
    const cancelBtn = document.getElementById('cancelBtn');
    const shutdownBtn = document.getElementById('shutdownBtn');
    const restartBtn = document.getElementById('restartBtn');
    const reloadBtn = document.getElementById('reloadBtn');

    powerButton.addEventListener('click', () => {
      document.getElementById("appWrapper").classList.add("grayscale-bg");
      powerDialog.classList.remove('hidden');
    });

    cancelBtn.addEventListener('click', () => {
      document.getElementById("appWrapper").classList.remove("grayscale-bg");
      powerDialog.classList.add('hidden');
    });

    shutdownBtn.addEventListener('click', () => {
      sendCommand('shutdown');
      document.getElementById("appWrapper").classList.remove("grayscale-bg");
      powerDialog.classList.add('hidden');
    });

    restartBtn.addEventListener('click', () => {
      sendCommand('reboot');
      document.getElementById("appWrapper").classList.remove("grayscale-bg");
      powerDialog.classList.add('hidden');
    });

    reloadBtn.addEventListener('click', () => {
      location.reload();
    });

    powerDialog.addEventListener('click', (e) => {
      if (e.target === powerDialog) {
        document.getElementById("appWrapper").classList.remove("grayscale-bg");
        powerDialog.classList.add('hidden');
      }
    });

    function sendCommand(action) {
      fetch(`server_command.php?action=${encodeURIComponent(action)}`)
        .then(res => res.text())
        .then(result => {
          alert("Server response: " + result);
        })
        .catch(err => {
          alert("Failed to send command: " + err);
        });
    }

    document.getElementById('version-number').textContent = window.THOS.version;

    document.getElementById("fullscreen-toggle").addEventListener("click", () => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
          console.error(`Failed to enter fullscreen: ${err.message}`);
        });
      } else {
        document.exitFullscreen();
      }
    });

    const screensaver = document.getElementById("screensaver");
    let screensaverTimeout;

    function showScreensaver() {
      screensaver.style.display = "block";
    }

    function hideScreensaver() {
      if (screensaver.style.display === "block") {
        screensaver.contentWindow.postMessage({ type: 'screensaver_hidden' }, '*'); // optional
      }
      screensaver.style.display = "none";
    }

    function resetInactivityTimer() {
      hideScreensaver();
      clearTimeout(screensaverTimeout);
      screensaverTimeout = setTimeout(showScreensaver, timeoutSeconds * 1000);
    }

    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'wheel'].forEach(event => {
      document.addEventListener(event, resetInactivityTimer, { passive: true });
    });

    window.addEventListener("message", event => {
      if (event.data?.type === "user_active") {
        resetInactivityTimer();
      }
    });

    resetInactivityTimer();

    function notify(title, description = "", options = {}) {
      const {
        type = "info",
        timeout = 5000,
        icon = null,
        id = `notif-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`
      } = options;

      const container = document.getElementById("notification-container");

      const notification = document.createElement("div");
      notification.className = `notification ${type}`;
      notification.id = id;

      notification.innerHTML = `
    ${icon ? `<div class="text-xl">${icon}</div>` : ""}
    <div class="flex-1">
      <div class="notification-title font-semibold">${title}</div>
      ${description ? `<div class="notification-desc text-sm text-gray-400">${description}</div>` : ""}
    </div>
    <button class="notification-dismiss absolute top-2 right-2 text-white/50 hover:text-white transition-colors text-sm" aria-label="Dismiss">&times;</button>
  `;

      const dismiss = () => {
        notification.style.animation = "fadeOut 0.25s ease-out forwards";
        setTimeout(() => {
          if (container.contains(notification)) container.removeChild(notification);
        }, 250);
      };

      notification.querySelector(".notification-dismiss").onclick = dismiss;

      container.appendChild(notification);

      if (timeout > 0) {
        setTimeout(() => {
          if (container.contains(notification)) dismiss();
        }, timeout);
      }
    }

    window.notify = notify;

    const wifiToggle = document.getElementById('wifi-toggle');
    const wifiDropdown = document.getElementById('wifi-dropdown');
    const wifiList = document.getElementById('wifi-list');
    const wifiConnect = document.getElementById('wifi-connect');
    const wifiPass = document.getElementById('wifi-pass');
    const connectBtn = document.getElementById('connect-btn');

    let selectedSSID = null;

    wifiToggle.addEventListener('click', () => {
      wifiDropdown.classList.toggle('hidden');
      wifiPass.value = '';
      wifiConnect.classList.add('hidden');
      selectedSSID = null;

      fetch('wifi_list.php')
        .then(res => res.json())
        .then(ssids => {
          wifiList.innerHTML = '';
          if (!ssids.length) {
            wifiList.innerHTML = '<div class="text-gray-400 italic">No networks found</div>';
            return;
          }

          ssids.forEach(ssid => {
            const div = document.createElement('div');
            div.textContent = ssid || '(Hidden)';
            div.className = 'cursor-pointer px-2 py-1 hover:bg-[#333] rounded';
            div.onclick = () => {
              selectedSSID = ssid;
              wifiConnect.classList.remove('hidden');
            };
            wifiList.appendChild(div);
          });
        })
        .catch(err => {
          wifiList.innerHTML = '<div class="text-red-400">Failed to load networks</div>';
          notify("Wi-Fi", "Could not fetch network list.", {
            type: "error",
            icon: "📡",
          });
        });
    });

    connectBtn.addEventListener('click', () => {
      const pass = wifiPass.value;
      if (!selectedSSID || !pass) {
        notify("MWi-Fi", "Please select a network and enter a password.", {
          type: "warning",
          icon: "⚠️"
        });
        return;
      }

      fetch(`connect_wifi.php?ssid=${encodeURIComponent(selectedSSID)}&pass=${encodeURIComponent(pass)}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            notify("Wi-Fi", `You are now connected to "${selectedSSID}"`, {
              type: "success",
              icon: "✅"
            });
            wifiDropdown.classList.add('hidden');
          } else {
            notify("Wi-Fi", data.error || "Could not connect to the network.", {
              type: "error",
              icon: "❌"
            });
          }
        })
        .catch(() => {
          notify("Wi-Fi", "Could not communicate with the server.", {
            type: "error",
            icon: "💥"
          });
        });
    });

    const volumeToggle = document.getElementById("volume-toggle");
    const volumeControl = document.getElementById("volume-control");
    const volumeSlider = document.getElementById("volume-slider");

    const brightnessToggle = document.getElementById("brightness-toggle");
    const brightnessControl = document.getElementById("brightness-control");
    const brightnessSlider = document.getElementById("brightness-slider");

    // Toggle panels
    volumeToggle.onclick = () => {
      volumeControl.classList.toggle("hidden");
      brightnessControl.classList.add("hidden");
      wifiDropdown.classList.add("hidden");
    };

    brightnessToggle.onclick = () => {
      brightnessControl.classList.toggle("hidden");
      volumeControl.classList.add("hidden");
      wifiDropdown.classList.add("hidden");
    };

    // Volume logic
    volumeSlider.oninput = () => {
      fetch(`api/set_volume.php?level=${volumeSlider.value}`)
        .then(r => r.json())
        .then(res => {
          if (!res.success) {
            notify("Volume", res.error || "Your system may not be set up correctly", {
              type: "error",
              icon: "❌"
            });
          }
        });
    };

    // Brightness logic
    brightnessSlider.oninput = () => {
      fetch(`api/set_brightness.php?level=${brightnessSlider.value}`)
        .then(r => r.json())
        .then(res => {
          if (!res.success) {
            notify("Brightness", res.error || "Your system may not be set up correctly", {
              type: "error",
              icon: "❌"
            });
          }
        });
    };
  </script>
</body>

</html>