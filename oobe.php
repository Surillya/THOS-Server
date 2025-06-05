<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welcome to THOS</title>
<script src="tailwind.es"></script>
<style>
        @keyframes fadeIn {
          from { opacity: 0; transform: translateY(20px); }
          to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
          animation: fadeIn 0.8s ease-out;
        }
        .wifi-network:hover {
            background-color: rgba(255,255,255,0.1);
            transition: background-color 0.3s ease;
        }
        .wifi-signal {
            display: inline-block;
            width: 20px;
            height: 12px;
            background: linear-gradient(
                to top,
                rgba(255,255,255,0.2) 25%,
                rgba(255,255,255,0.6) 25%,
                rgba(255,255,255,0.6) 50%,
                rgba(255,255,255,0.8) 50%,
                rgba(255,255,255,0.8) 75%,
                rgba(255,255,255,1) 75%
            );
        }
</style>
</head>
<body class="bg-gray-900 text-white font-sans">
<div id="oobe" class="min-h-screen flex flex-col items-center justify-center p-4">
<!-- Welcome Screen -->
<div id="step-welcome" class="animate-fade-in text-center space-y-6">
<h1 class="text-4xl font-bold text-blue-300">Welcome to THOS</h1>
<p class="text-lg">Let's get things set up for the best experience.</p>
<button onclick="nextStep('step-name')" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600 transition">Get Started</button>
</div>

<!-- Name Screen -->
<div id="step-name" class="hidden animate-fade-in text-center space-y-4">
<h2 class="text-2xl">Who's using THOS?</h2>
<input id="user-name" type="text" placeholder="Enter your name" class="px-3 py-2 text-black rounded">
<button onclick="saveName()" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600 transition">Next</button>
</div>

<!-- Wi-Fi Setup -->
<div id="step-wifi" class="hidden w-full max-w-md p-8 bg-gray-800 rounded-xl shadow-2xl">
    <div class="text-center mb-6">
    <h2 class="text-2xl font-bold text-blue-400">Connect to Wi-Fi</h2>
    <p class="text-gray-400 mt-2">Select a network to continue setup</p>
    </div>

    <div class="mb-4 flex justify-between items-center">
    <button id="reload-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
    Refresh Networks
    </button>
    <button id="skip-btn" class="text-gray-400 hover:text-white underline">
    Skip for now
    </button>
    </div>

    <div id="wifi-list" class="space-y-3 max-h-64 overflow-y-auto">
    </div>

    <div id="wifi-password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-gray-800 p-6 rounded-xl w-96 shadow-2xl">
    <h3 class="text-xl font-semibold mb-4 text-blue-400" id="selected-network-name">Network Name</h3>
    <input
    type="password"
    id="wifi-pass"
    placeholder="Enter network password"
    class="w-full p-3 bg-gray-700 rounded-lg border border-gray-600 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
    <div class="flex space-x-3">
    <button
    id="connect-btn"
    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg transition"
    >
    Connect
    </button>
    <button
    id="cancel-btn"
    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg transition"
    >
    Cancel
    </button>
    </div>
    <div id="wifi-status" class="mt-3 text-center text-sm text-gray-400"></div>
    </div>
    </div>
</div>

<!-- Final Step -->
<div id="step-final" class="hidden animate-fade-in text-center space-y-4">
<h2 class="text-3xl font-bold text-green-400">You're all set!</h2>
<p>Welcome to THOS, <span id="final-name"></span>!</p>
<div id="saving-msg" class="hidden text-sm text-gray-400 mt-2">Saving setup...</div>
<button onclick="finishSetup()" class="px-4 py-2 bg-green-500 rounded hover:bg-green-600 transition">Launch THOS</button>
</div>
</div>

<script>
const steps = ['step-welcome', 'step-name', 'step-wifi', 'step-final'];

function nextStep(stepId) {
  steps.forEach(id => document.getElementById(id)?.classList.add('hidden'));
  document.getElementById(stepId)?.classList.remove('hidden');
}

function saveName() {
  const name = document.getElementById('user-name').value;
  if (!name.trim()) return alert("Please enter your name!");
  if (!isValidName(name)) {
    alert("Please use only letters, numbers, spaces, dashes and underscores (max 32 characters).");
    return;
  }
  localStorage.setItem('thos_name', name);
  nextStep('step-wifi');
  loadWiFiList();
}

let selectedSSID = "";

function loadWiFiList() {
  const list = document.getElementById("wifi-list");
  list.innerHTML = `
  <div class="text-center text-gray-500 py-4">
  <svg class="animate-spin h-5 w-5 mx-auto text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
  </svg>
  <p class="mt-2">Scanning for networks...</p>
  </div>
  `;

  fetch("wifi_list.php")
  .then(res => res.json())
  .then(data => {
    list.innerHTML = data.map(wifi => `
    <div class="wifi-network bg-gray-700 rounded-lg p-3 flex justify-between items-center hover:bg-gray-600 transition">
    <div class="flex items-center space-x-3">
    <div class="wifi-signal"></div>
    <span>${wifi || "<i>Hidden SSID</i>"}</span>
    </div>
    <button onclick="showPasswordModal('${wifi.replace(/'/g, "\\'")}')" class="text-blue-400 hover:underline">
    Connect
    </button>
    </div>
    `).join('');
  })
  .catch(err => {
    list.innerHTML = `
    <div class="text-center text-red-500 py-4">
    Failed to load networks. Please try again.
    </div>
    `;
    console.error("Network fetch error:", err);
  });
}

function showPasswordModal(ssid) {
  selectedSSID = ssid;
  document.getElementById('selected-network-name').textContent = ssid;
  document.getElementById('wifi-password-modal').classList.remove('hidden');
  document.getElementById('wifi-password-modal').classList.add('flex');
}

function connectWiFi() {
  const pass = document.getElementById('wifi-pass').value;
  const status = document.getElementById('wifi-status');
  status.textContent = "Connecting...";
  status.className = "mt-3 text-center text-sm text-gray-400";

  fetch(`connect_wifi.php?ssid=${encodeURIComponent(selectedSSID)}&pass=${encodeURIComponent(pass)}`)
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      status.textContent = "✅ Connected successfully!";
      status.classList.add('text-green-500');

      // Hide modal after successful connection
      setTimeout(() => {
        document.getElementById('wifi-password-modal').classList.add('hidden');
        document.getElementById('wifi-password-modal').classList.remove('flex');
        nextStep('step-final');
        document.getElementById('final-name').innerHTML = localStorage.getItem('thos_name');
      }, 1500);
    } else {
      status.textContent = "❌ Failed to connect. Check your password.";
      status.classList.add('text-red-500');
    }
  })
  .catch(err => {
    status.textContent = "❌ Connection error. Please try again.";
    status.classList.add('text-red-500');
    console.error("Connection error:", err);
  });
}

// Event Listeners
document.getElementById('reload-btn').addEventListener('click', loadWiFiList);
document.getElementById('connect-btn').addEventListener('click', connectWiFi);
document.getElementById('cancel-btn').addEventListener('click', () => {
  document.getElementById('wifi-password-modal').classList.add('hidden');
  document.getElementById('wifi-password-modal').classList.remove('flex');
});
document.getElementById('skip-btn').addEventListener('click', () => {
  if(confirm("Wi-Fi setup is recommended. Skip anyway?")) {
    document.getElementById("wifi-status").textContent = "⚠️ Some features will be limited.";
    nextStep('step-final');
    document.getElementById('final-name').innerHTML = localStorage.getItem('thos_name');
  }
});

function isValidName(name) {
  return /^[A-Za-z]+( [A-Za-z]+)?$/.test(name) && name.length <= 32;
}

async function saveTHOSState() {
  try {
    const cookies = document.cookie;
    const localData = JSON.stringify(localStorage);

    const response = await fetch('save_state.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cookies, localData })
    });

    if (!response.ok) {
      throw new Error("Failed to save state to the server.");
    }

    const result = await response.text();
    console.log("State saved:", result);
    return true;
  } catch (error) {
    console.error("Error saving THOS state:", error);
    return false;
  }
}

async function finishSetup() {
  document.getElementById("saving-msg").classList.remove("hidden");
  localStorage.setItem('thos_done', 'true');
  const saved = await saveTHOSState();

  if (saved) {
    window.location.href = 'index.php';
  } else {
    alert("Something went wrong saving your setup. Please try again.");
    document.getElementById("saving-msg").classList.add("hidden");
  }
}
</script>
</body>
</html>
