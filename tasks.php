<?php
$load = shell_exec("uptime");
preg_match('/load average: ([0-9.]+), ([0-9.]+), ([0-9.]+)/', $load, $matches);
$cpuLoad1 = $matches[1] ?? 'N/A';
$cpuLoad5 = $matches[2] ?? 'N/A';
$cpuLoad15 = $matches[3] ?? 'N/A';

$free = shell_exec("free -m");
preg_match('/Mem:\s+(\d+)\s+(\d+)\s+(\d+)/', $free, $memMatches);
$ramTotal = $memMatches[1] ?? 'N/A';
$ramUsed = $memMatches[2] ?? 'N/A';
$ramFree = $memMatches[3] ?? 'N/A';

$disk = shell_exec("df -h /");
$diskLines = explode("\n", trim($disk));
$diskInfo = isset($diskLines[1]) ? preg_split('/\s+/', $diskLines[1]) : [];
$diskSize = $diskInfo[1] ?? 'N/A';
$diskUsed = $diskInfo[2] ?? 'N/A';
$diskAvailable = $diskInfo[3] ?? 'N/A';
$diskPercent = $diskInfo[4] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>THOS Tasks</title>
<style>
  body {
    color: #eee;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 15px;
  }
  h2 {
    margin-top: 0;
    color: var(--accent);
  }
  .system-info {
    display: flex;
    justify-content: space-around;
    background: #222;
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 0 8px var(--accent);
  }
  .info-box {
    text-align: center;
  }
  .info-box h3 {
    margin-bottom: 6px;
    font-weight: normal;
    color: var(--accent);
  }
  .info-box p {
    font-size: 1.2em;
    margin: 0;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    background: #181818;
    border-radius: 8px;
    overflow: hidden;
  }
  th, td {
    padding: 10px 15px;
    border-bottom: 1px solid #333;
    text-align: left;
  }
  th {
    background: #2e2e2e;
  }
  button {
    background: var(--accent);
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    color: #222;
    font-weight: bold;
    transition: background 0.3s ease;
  }
  button:hover {
    color: white;
  }
</style>
</head>
<body>
  <h2>THOS Tasks</h2>

  <table>
    <thead>
      <tr>
        <th>CPU 1m</th>
        <th>CPU 5m</th>
        <th>CPU 15m</th>
        <th>RAM Usage</th>
        <th>Disk Usage</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td id="cpu1">Loading...</p>
        <td id="cpu5">Loading...</p>
        <td id="cpu15">Loading...</p>
        <td id="ram">Loading...</p>
        <td id="disk">Loading...</p>
      </tr>
    </tbody>
  </table>
  <br><hr><br>

  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>URL</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="task-list">
      <tr><td colspan="3">Loading tasks...</td></tr>
    </tbody>
  </table>

<script>
  async function updateSystemStats() {
    try {
      const res = await fetch('sysstats.php');
      if (!res.ok) throw new Error('Network response not ok');
      const data = await res.json();

      document.getElementById('cpu1').textContent = data.cpuLoad['1min'] ?? 'N/A';
      document.getElementById('cpu5').textContent = data.cpuLoad['5min'] ?? 'N/A';
      document.getElementById('cpu15').textContent = data.cpuLoad['15min'] ?? 'N/A';

      document.getElementById('ram').textContent = `${data.ram.used} MB / ${data.ram.total} MB`;
      document.getElementById('disk').textContent = `${data.disk.used} / ${data.disk.size} (${data.disk.percent})`;
    } catch(e) {
      console.error('Failed to update system stats:', e);
    }
  }

  function basename(inputPath) {
    if (!inputPath) return '';

    // Remove query string and fragment
    const cleanPath = inputPath.split(/[?#]/)[0];

    // Extract just the filename (after the last slash)
    const segments = cleanPath.split('/');
    const fileName = segments.pop() || '';

    // Remove extension
    return fileName.replace(/\.[^.]+$/, '');
  }


  function refreshTasks() {
    const taskList = document.getElementById('task-list');
    const parent = window.parent;
    if (!parent || typeof parent.getTaskList !== 'function') {
      taskList.innerHTML = '<tr><td colspan="3">Unable to fetch task list.</td></tr>';
      return;
    }

    const tasks = parent.getTaskList();
    if (!tasks.length) {
      taskList.innerHTML = '<tr><td colspan="3">No active tasks.</td></tr>';
      return;
    }

    taskList.innerHTML = '';
    tasks.forEach(task => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${task.title}</td>
        <td>${basename(task.url)}</td>
        <td>
          <button onclick="window.parent.focusApp('${task.id}')">Focus</button>
          <button onclick="window.parent.closeApp('${task.id}')">Close</button>
        </td>
      `;
      taskList.appendChild(tr);
    });
  }

  setInterval(() => {
    refreshTasks();
    updateSystemStats();
  }, 1000);

  refreshTasks();
  updateSystemStats();

  document.documentElement.style.setProperty('--accent', window.parent.THOS.getAllSettings().accentColor || '#ff69b4');
</script>
</body>
