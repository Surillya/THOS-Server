<?php
// sysstats.php — returns JSON with current system stats

header('Content-Type: application/json');

// CPU load
$load = shell_exec("uptime");
preg_match('/load average: ([0-9.]+), ([0-9.]+), ([0-9.]+)/', $load, $matches);
$cpuLoad1 = $matches[1] ?? null;
$cpuLoad5 = $matches[2] ?? null;
$cpuLoad15 = $matches[3] ?? null;

// RAM
$free = shell_exec("free -m");
preg_match('/Mem:\s+(\d+)\s+(\d+)\s+(\d+)/', $free, $memMatches);
$ramTotal = $memMatches[1] ?? null;
$ramUsed = $memMatches[2] ?? null;
$ramFree = $memMatches[3] ?? null;

// Disk (root)
$disk = shell_exec("df -h /");
$diskLines = explode("\n", trim($disk));
$diskInfo = isset($diskLines[1]) ? preg_split('/\s+/', $diskLines[1]) : [];
$diskSize = $diskInfo[1] ?? null;
$diskUsed = $diskInfo[2] ?? null;
$diskAvailable = $diskInfo[3] ?? null;
$diskPercent = $diskInfo[4] ?? null;

echo json_encode([
    'cpuLoad' => [
        '1min' => $cpuLoad1,
        '5min' => $cpuLoad5,
        '15min' => $cpuLoad15,
    ],
    'ram' => [
        'total' => $ramTotal,
        'used' => $ramUsed,
        'free' => $ramFree,
    ],
    'disk' => [
        'size' => $diskSize,
        'used' => $diskUsed,
        'available' => $diskAvailable,
        'percent' => $diskPercent,
    ],
]);