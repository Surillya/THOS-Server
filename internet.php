<?php
$extensionMarker = '/usr/thos/unblocker_installed.txt';
if (!file_exists($extensionMarker)) {
  echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Install Extension</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white flex items-center justify-center h-screen">
  <div class="bg-white/10 p-6 rounded-xl shadow-xl text-center max-w-md w-full">
    <h1 class="text-2xl font-bold mb-4">Unblocker Extension Not Installed</h1>
    <p class="mb-4">THOS needs the browser unblocker extension to function fully. Would you like to install it now and reboot?</p>
    <form method="POST">
      <button name="install" value="yes" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded">Install and Reboot</button>
    </form>
  </div>
</body>
</html>';
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['install'] === 'yes') {
    shell_exec(' sudo bash /usr/thos/install-unblocker.sh');
    file_put_contents($extensionMarker, 'installed');
    echo '<script>alert("Extension installed. System will reboot.");</script>';
    sleep(1);
    shell_exec('reboot');
  }
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>THOS Internet</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html, body {
      background: transparent;
    }
    iframe::-webkit-scrollbar {
      display: none;
    }
  </style>
</head>
<body class="bg-transparent text-white">
  <div class="w-full h-screen flex flex-col overflow-hidden">
    <div class="bg-white/10 backdrop-blur border-b border-white/20 p-2 flex gap-2 items-center">
      <button id="backBtn" class="px-3 py-1 rounded bg-white/20 hover:bg-white/30">◀</button>
      <button id="forwardBtn" class="px-3 py-1 rounded bg-white/20 hover:bg-white/30">▶</button>
      <button id="reloadBtn" class="px-3 py-1 rounded bg-white/20 hover:bg-white/30">⟳</button>
      <input id="urlBar" type="text" placeholder="Search or enter URL" class="flex-1 px-4 py-1 rounded bg-white/10 focus:outline-none focus:ring-2 focus:ring-cyan-400" />
      <button id="goBtn" class="px-4 py-1 rounded bg-cyan-500 hover:bg-cyan-600 text-black font-bold">Go</button>
    </div>

    <div id="bookmarkBar" class="flex gap-2 px-2 py-1 bg-white/5 border-b border-white/10 overflow-x-auto"></div>

    <div id="tabs" class="flex gap-2 px-2 py-1 bg-white/5 border-b border-white/10">
      <button id="newTabBtn" class="px-2 py-1 rounded bg-cyan-600 text-black hover:bg-cyan-700">+ New Tab</button>
    </div>

    <div id="iframeContainer" class="flex-1 relative"></div>
  </div>

  <script>
    const defaultBookmarks = [
      { name: 'YouTube (Yewtu.be)', url: 'https://yewtu.be' },
      { name: 'DuckDuckGo', url: 'https://duckduckgo.com' },
      { name: 'Surillya', url: 'https://surillya.com' }
    ];

    const iframeContainer = document.getElementById('iframeContainer');
    const urlBar = document.getElementById('urlBar');
    const goBtn = document.getElementById('goBtn');
    const backBtn = document.getElementById('backBtn');
    const forwardBtn = document.getElementById('forwardBtn');
    const reloadBtn = document.getElementById('reloadBtn');
    const bookmarkBar = document.getElementById('bookmarkBar');
    const tabs = document.getElementById('tabs');
    const newTabBtn = document.getElementById('newTabBtn');

    let tabId = 0;
    const tabsState = new Map();
    let activeTab = null;

    function createIframe(url, id) {
      const iframe = document.createElement('iframe');
      iframe.className = 'w-full h-full absolute top-0 left-0';
      iframe.sandbox = 'allow-scripts allow-same-origin allow-forms';
      iframe.src = url;
      iframe.loading = 'lazy';
      iframe.dataset.id = id;
      iframe.style.zIndex = 1;
      return iframe;
    }

    function switchTab(id) {
      document.querySelectorAll('iframe').forEach(iframe => {
        iframe.style.zIndex = iframe.dataset.id === String(id) ? 2 : 1;
      });
      activeTab = id;
      const currentTab = tabsState.get(id);
      if (currentTab) {
        urlBar.value = currentTab.history[currentTab.index];
      }
    }

    function addTab(url = 'https://surillya.com') {
      const id = tabId++;
      const tabState = { id, history: [url], index: 0 };
      tabsState.set(id, tabState);
      const iframe = createIframe(url, id);
      iframeContainer.appendChild(iframe);

      const tabBtn = document.createElement('button');
      tabBtn.textContent = `Tab ${id + 1}`;
      tabBtn.className = 'px-2 py-1 rounded bg-white/10 hover:bg-white/20';
      tabBtn.onclick = () => switchTab(id);
      tabs.insertBefore(tabBtn, newTabBtn);

      switchTab(id);
    }

    function loadURL(input) {
      const url = input.includes('.') ? (input.startsWith('http') ? input : 'https://' + input) : `https://duckduckgo.com/?q=${encodeURIComponent(input)}`;
      const tab = tabsState.get(activeTab);
      if (!tab) return;
      tab.index++;
      tab.history = tab.history.slice(0, tab.index);
      tab.history.push(url);
      const iframe = document.querySelector(`iframe[data-id='${tab.id}']`);
      if (iframe) iframe.src = url;
      urlBar.value = url;
    }

    goBtn.onclick = () => loadURL(urlBar.value);
    urlBar.addEventListener('keydown', e => { if (e.key === 'Enter') goBtn.click(); });

    backBtn.onclick = () => {
      const tab = tabsState.get(activeTab);
      if (tab && tab.index > 0) {
        tab.index--;
        const iframe = document.querySelector(`iframe[data-id='${tab.id}']`);
        if (iframe) iframe.src = tab.history[tab.index];
        urlBar.value = tab.history[tab.index];
      }
    };

    forwardBtn.onclick = () => {
      const tab = tabsState.get(activeTab);
      if (tab && tab.index < tab.history.length - 1) {
        tab.index++;
        const iframe = document.querySelector(`iframe[data-id='${tab.id}']`);
        if (iframe) iframe.src = tab.history[tab.index];
        urlBar.value = tab.history[tab.index];
      }
    };

    reloadBtn.onclick = () => {
      const tab = tabsState.get(activeTab);
      if (tab) {
        const iframe = document.querySelector(`iframe[data-id='${tab.id}']`);
        if (iframe) iframe.src = tab.history[tab.index];
      }
    };

    function renderBookmarks() {
      bookmarkBar.innerHTML = '';
      defaultBookmarks.forEach(b => {
        const btn = document.createElement('button');
        btn.textContent = b.name;
        btn.className = 'px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm';
        btn.onclick = () => loadURL(b.url);
        bookmarkBar.appendChild(btn);
      });
    }

    newTabBtn.onclick = () => addTab();

    renderBookmarks();
    addTab();
  </script>
</body>
</html>
