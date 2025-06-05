<?php
$appsDir = "/home/surillya/.apps/";
$symlinkPath = "/usr/thos/apps";
$apps = [];

if (!file_exists($appsDir)) {
    mkdir($appsDir, 0755, true);
}

// Very dirty hack to get the apps exposed to the browser
if (!is_link($symlinkPath) || readlink($symlinkPath) !== $appsDir) {
    @unlink($symlinkPath);
    symlink($appsDir, $symlinkPath);
}

foreach (glob($appsDir . '*', GLOB_ONLYDIR) as $appPath) {
    $id = basename($appPath);
    $manifestFile = $appPath . '/manifest.json';
    if (file_exists($manifestFile)) {
        $data = json_decode(file_get_contents($manifestFile), true);
        if ($data && isset($data['name'], $data['entry'])) {
            $apps[] = [
                'id' => $id,
                'name' => $data['name'],
                'icon' => 'apps/' . $id . '/' . $data['icon'],
                'path' => 'apps/' . $id . '/app/' . $data['entry']
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($apps);
