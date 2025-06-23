<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>THOS Browser</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    :root {
      --accent: #ff69b4;
    }
    body {
      background-color: #121212;
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: rgba(255,255,255,0.1);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: var(--accent);
      border-radius: 4px;
    }
    iframe {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
      pointer-events: none;
    }
    iframe.active {
      opacity: 1;
      pointer-events: auto;
    }
  </style>
</head>
<body class="text-white antialiased">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div class="w-16 bg-[#1E1E1E] flex flex-col items-center py-4 space-y-4 border-r border-[#2C2C2C]">
      <button id="newTabBtn" class="hover:bg-[#2C2C2C] p-2 rounded-md transition-colors">
        <i class="fas fa-plus text-lg"></i>
      </button>
      <div id="tabList" class="flex-1 overflow-y-auto space-y-2">
        <!-- Tabs will be dynamically added here -->
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col">
      <!-- URL Bar -->
      <div class="bg-[#1E1E1E] border-b border-[#2C2C2C] px-4 py-2 flex items-center space-x-2">
        <button id="backBtn" class="text-gray-400 hover:text-white transition-colors">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button id="forwardBtn" class="text-gray-400 hover:text-white transition-colors">
          <i class="fas fa-chevron-right"></i>
        </button>
        <button id="reloadBtn" class="text-gray-400 hover:text-white transition-colors">
          <i class="fas fa-redo"></i>
        </button>
        
        <div class="flex-1 relative">
          <input 
            id="urlBar" 
            type="text" 
            placeholder="Search or enter URL" 
            class="w-full pl-8 pr-4 py-2 bg-[#2C2C2C] rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--accent)] text-white"
          />
          <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
        
        <button 
          id="goBtn" 
          class="bg-[var(--accent)] hover:opacity-80 text-white font-bold py-2 px-4 rounded-md transition-opacity"
        >
          Go
        </button>
      </div>

      <!-- Bookmarks -->
      <div 
        id="bookmarkBar" 
        class="bg-[#1E1E1E] px-4 py-2 flex space-x-2 overflow-x-auto custom-scrollbar"
      ></div>

      <!-- Iframe Container -->
      <div id="iframeContainer" class="flex-1 relative bg-[#121212]"></div>
    </div>
  </div>

  <script>
    // Set accent color
    document.documentElement.style.setProperty('--accent', window.parent.THOS.getAllSettings().accentColor || '#ff69b4');

    const defaultBookmarks = [
      { name: 'YouTube (Yewtu.be)', url: 'https://yewtu.be', icon: 'fab fa-youtube' },
      { name: 'DuckDuckGo', url: 'https://duckduckgo.com', icon: 'fas fa-search' },
      { name: 'Surillya', url: 'https://surillya.com', icon: 'fas fa-globe' }
    ];

    const iframeContainer = document.getElementById('iframeContainer');
    const urlBar = document.getElementById('urlBar');
    const goBtn = document.getElementById('goBtn');
    const backBtn = document.getElementById('backBtn');
    const forwardBtn = document.getElementById('forwardBtn');
    const reloadBtn = document.getElementById('reloadBtn');
    const bookmarkBar = document.getElementById('bookmarkBar');
    const tabList = document.getElementById('tabList');
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
      return iframe;
    }

    function createTabButton(id) {
      const tabBtn = document.createElement('button');
      tabBtn.dataset.id = id;
      tabBtn.className = `w-10 h-10 rounded-md flex items-center justify-center hover:bg-[#2C2C2C] transition-colors ${id === activeTab ? 'bg-[var(--accent)] bg-opacity-20' : ''}`;
      tabBtn.innerHTML = `<i class="fas fa-globe"></i>`;
      tabBtn.onclick = () => switchTab(id);
      
      const closeBtn = document.createElement('button');
      closeBtn.innerHTML = `<i class="fas fa-times text-xs"></i>`;
      closeBtn.className = 'absolute top-0 right-0 p-1 hover:text-red-500';
      closeBtn.onclick = (e) => {
        e.stopPropagation();
        closeTab(id);
      };

      tabBtn.appendChild(closeBtn);
      return tabBtn;
    }

    function switchTab(id) {
      document.querySelectorAll('iframe').forEach(iframe => {
        iframe.classList.remove('active');
      });
      
      // Update tab styling
      document.querySelectorAll('[data-id]').forEach(btn => {
        btn.classList.remove('bg-[var(--accent)]', 'bg-opacity-20');
      });
      const tabBtn = document.querySelector(`[data-id='${id}']`);
      if (tabBtn) tabBtn.classList.add('bg-[var(--accent)]', 'bg-opacity-20');

      const iframe = document.querySelector(`iframe[data-id='${id}']`);
      if (iframe) iframe.classList.add('active');
      activeTab = id;
      const currentTab = tabsState.get(id);
      if (currentTab) {
        urlBar.value = currentTab.history[currentTab.index];
      }
    }

    function closeTab(id) {
      // Remove iframe
      const iframe = document.querySelector(`iframe[data-id='${id}']`);
      if (iframe) iframe.remove();

      // Remove tab button
      const tabBtn = document.querySelector(`[data-id='${id}']`);
      if (tabBtn) tabBtn.remove();

      // Remove from tabs state
      tabsState.delete(id);

      // Switch to last tab or create new tab
      const remainingTabs = Array.from(tabsState.keys());
      if (remainingTabs.length > 0) {
        switchTab(remainingTabs[remainingTabs.length - 1]);
      } else {
        addTab();
      }
    }

    function addTab(url = 'https://surillya.com') {
      const id = tabId++;
      const tabState = { id, history: [url], index: 0 };
      tabsState.set(id, tabState);
      const iframe = createIframe(url, id);
      iframeContainer.appendChild(iframe);

      const tabBtn = createTabButton(id);
      tabList.appendChild(tabBtn);

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

    // Event Listeners
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
        btn.innerHTML = `<i class="${b.icon} mr-2"></i>${b.name}`;
        btn.className = 'px-3 py-1 rounded bg-[#2C2C2C] hover:bg-[var(--accent)] hover:bg-opacity-20 text-sm flex items-center space-x-2 transition-colors';
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