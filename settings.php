<!DOCTYPE html>
<html>

<head>
    <title>Settings</title>
    <style>
        body {
            background: transparent;
            min-height: 100vh;
            color: white;
        }
    </style>
    <script src="tailwind.es"></script>
</head>

<body class="text-white text-white p-6 md:p-12">
    <div class="container mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-8">
            <h1
                class="text-4xl font-bold text-gradient bg-gradient-to-r from-indigo-400 to-pink-500 bg-clip-text text-transparent">
                System Settings
            </h1>
            <div class="text-3xl">⚙️</div>
        </div>

        <form id="settingsForm" class="space-y-8">
            <!-- Appearance Section -->
            <div class="settings-section border-b border-gray-700 pb-8">
                <h2 class="text-xl font-semibold text-indigo-300 mb-4 pb-2">
                    Appearance
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- It's so time consuming to add multipme themes - and everyone sane uses dark mode anyway -->
                    <div>
                        <label for="theme" class="block text-sm text-gray-300 mb-2">Theme</label>
                        <select id="theme"
                            class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:ring-2 focus:ring-indigo-500 transition">
                            <option value="theme-glassy-dark" selected>Glassy Dark</option>
                            <option value="theme-frosted-blue">Frosted</option>
                            <option value="theme-dreamy-pink">Dreamy</option>
                            <option value="theme-xp">XP</option>
                        </select>
                    </div>
                    <div>
                        <label for="accent" class="block text-sm text-gray-300 mb-2">Accent Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" id="accent"
                                class="w-16 h-12 rounded-lg border-2 border-gray-600 bg-gray-800">
                            <span class="text-gray-400 text-sm">Custom Accent</span>
                        </div>
                    </div>
                    <div>
                        <label for="wallpaper" class="block text-sm text-gray-300 mb-2">Wallpaper</label>
                        <button type="button" onclick="window.parent.openApp('wallpapers.php', 'Wallpaper Selector')"
                            class="w-full bg-gray-800 text-white border border-gray-700 hover:bg-gray-700 font-medium px-4 py-2 rounded-lg transition flex items-center justify-between">
                            <span>Select Wallpaper</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- System Performance Section -->
            <div class="settings-section border-b border-gray-700 pb-8">
                <h2 class="text-xl font-semibold text-indigo-300 mb-4 pb-2">
                    System Performance
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="screenTimeout" class="block text-sm text-gray-300 mb-2">Screen Timeout</label>
                        <select id="screenTimeout"
                            class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:ring-2 focus:ring-indigo-500 transition">
                            <option value="30">30 seconds</option>
                            <option value="60">1 minute</option>
                            <option value="180">3 minutes</option>
                            <option value="300">5 minutes</option>
                            <option value="600">10 minutes</option>
                            <option value="900">15 minutes</option>
                            <option value="1800">30 minutes</option>
                            <option value="3600">1 hour</option>
                            <option value="never">Never</option>
                        </select>
                    </div>
                    <!-- Will eventually actually add this, but requires stuff in .xinitrc and really don't want to do it rn -->
                    <!-- <div>
                        <label for="powerMode" class="block text-sm text-gray-300 mb-2">Power Mode</label>
                        <select id="powerMode" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:ring-2 focus:ring-indigo-500 transition">
                            <option value="balanced" selected>Balanced</option>
                            <option value="performance">Performance</option>
                            <option value="powersave">Power Saving</option>
                        </select>
                    </div> -->
                </div>
            </div>

            <!-- Media & Sound Section -->
            <div class="settings-section">
                <h2 class="text-xl font-semibold text-indigo-300 mb-4 pb-2">
                    Media & Sound
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="musicEnabled" class="block text-sm text-gray-300 mb-2">Background Music</label>
                        <select id="musicEnabled"
                            class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:ring-2 focus:ring-indigo-500 transition">
                            <option value="on">Enabled</option>
                            <option value="off" selected>Disabled</option>
                        </select>
                    </div>
                    <div>
                        <label for="musicVolume" class="block text-sm text-gray-300 mb-2">Music Volume</label>
                        <input type="range" id="musicVolume" min="0" max="1" step="0.01"
                            class="w-full accent-indigo-500 h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between pt-6 border-t border-gray-700">
                <button type="submit"
                    class="bg-gradient-to-r from-indigo-500 to-pink-500 hover:from-indigo-600 hover:to-pink-600 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transform hover:scale-105 transition">
                    Save Changes
                </button>
                <button type="button" onclick="resetSettings()"
                    class="bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium px-6 py-3 rounded-lg transition">
                    Reset to Defaults
                </button>
            </div>
        </form>
    </div>

    <script>
        const themeInput = document.getElementById('theme');
        const accentInput = document.getElementById('accent');
        const wallpaperInput = document.getElementById('wallpaper');
        // const musicURLInput = document.getElementById('music');
        const musicEnabled = document.getElementById('musicEnabled');
        const musicVolume = document.getElementById('musicVolume');
        const screenTimeout = document.getElementById('screenTimeout');

        const saved = JSON.parse(localStorage.getItem('settings')) || {};
        if (saved.theme) themeInput.value = saved.theme;
        if (saved.accentColor) accentInput.value = saved.accentColor;
        // if (saved.music) musicURLInput.value = saved.music;
        if (saved.musicEnabled) musicEnabled.value = saved.musicEnabled;
        if (saved.musicVolume) musicVolume.value = saved.musicVolume;
        screenTimeout.value = saved.screenTimeout || 180;

        document.getElementById('settingsForm').addEventListener('submit', function (e) {
            e.preventDefault();
            let settings = {
                theme: themeInput.value,
                accentColor: accentInput.value,
                wallpaper: saved.wallpaper,
                // music: musicURLInput.value,
                musicEnabled: musicEnabled.value,
                musicVolume: musicVolume.value,
                screenTimeout: parseInt(screenTimeout.value)
            };
            localStorage.setItem('settings', JSON.stringify(settings));
            parent.postMessage({ type: 'applySettings', settings }, '*');
            window.parent.notify("Settings", `Settings applied successfully!`, {
                type: "success",
                icon: "✅"
            });
        });

        async function resetSettings() {
            if (confirm("Reset settings to default?")) {
                localStorage.removeItem('settings');
                await window.parent.saveTHOSState();
                location.reload();
            }
        }
    </script>
</body>

</html>