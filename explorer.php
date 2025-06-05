<?php
function getAssociatedApp($extension)
{
    static $associations = null;
    if ($associations === null) {
        $json = file_get_contents('file_associations.json');
        $associations = json_decode($json, true);
    }

    return $associations[strtolower($extension)] ?? null;
}

$root = realpath("/home/surillya");
$cdir = isset($_GET['dir']) ? $_GET['dir'] : '';
$dir = realpath($root . DIRECTORY_SEPARATOR . $cdir);

if (!$dir || strpos($dir, $root) !== 0 || !is_dir($dir)) {
    echo "Invalid directory.";
    exit;
}

$items = scandir($dir);
$relPath = str_replace($root, '', $dir);

function humanFileSize($size)
{
    if ($size < 1024)
        return $size . ' B';
    if ($size < 1048576)
        return round($size / 1024, 2) . ' KB';
    if ($size < 1073741824)
        return round($size / 1048576, 2) . ' MB';
    return round($size / 1073741824, 2) . ' GB';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Surillya Explorer</title>
    <style>
        :root {
            --accent: #61dafb;
        }

        body {
            font-family: sans-serif;
            background: transparent;
            color: #eee;
            padding: 20px;
        }

        a {
            color: var(--accent);
            text-decoration: none;
        }

        h1, p {
            color: var(--accent);
        }

        .explorer {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            padding: 15px;
        }

        .file-item {
            display: flex;
            align-items: center;
            padding: 8px;
            gap: 10px;
            background: #222;
            border-radius: 8px;
            margin-bottom: 6px;
            overflow: hidden;
        }

        .file-item:hover {
            background: #2a2a2a;
        }

        .file-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .context-menu {
            position: absolute;
            background: #222;
            border: 1px solid #555;
            border-radius: 8px;
            display: none;
            z-index: 999;
            color: #fff;
        }

        .context-menu .op {
            padding: 8px 16px;
            cursor: pointer;
        }

        .context-menu .op:hover {
            background: #333;
        }

        .filename {
            display: block;
            max-width: 100%;
            flex-grow: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body>
    <h1>THOS Explorer</h1>
    <p>Current directory: <code><?php echo htmlspecialchars($cdir ?: '/'); ?></code></p>

    <?php
    if ($dir !== $root) {
        $parentDir = dirname($cdir);
        echo '<p><a href="?dir=' . urlencode($parentDir) . '">⬅️ Go Up</a></p>';
    }
    ?>
    <div class="explorer" id="fileGrid">
        <?php
        foreach ($items as $item) {
            if ($item === '.' || $item === '..')
                continue;
            $itemPath = $dir . DIRECTORY_SEPARATOR . $item;
            $relItem = ltrim($relPath . DIRECTORY_SEPARATOR . $item, DIRECTORY_SEPARATOR);
            $isDir = is_dir($itemPath);

            $ext = pathinfo($item, PATHINFO_EXTENSION);
            $app = getAssociatedApp($ext);
            $icon = $isDir ? '📁' : ($app['icon'] ?? '📄');

            if ($isDir)
                echo '<a href="?dir=' . urlencode($relItem) . '">';
            else {
                require_once "vfs.php";
                $vPath = virtualize_path($itemPath);
                echo '<a href="javascript:void(0);" onclick="openFile(\'' . htmlspecialchars($vPath) . '\')">';
            }
            echo '<div class="item" data-name="' . htmlspecialchars($item) . '" data-path="' . htmlspecialchars($relItem) . '" data-isdir="' . ($isDir ? '1' : '0') . '">';
            echo '<div class="file-icon">' . $icon . '</div>';
            echo '<span class="filename">' . htmlspecialchars($item) . '</span>';
            echo '</div></a>';
        }
        ?>
    </div>

    <div class="context-menu" id="contextMenu">
        <div class="op" id="info">Show Info</div>
        <div class="op" id="rename">Rename</div>
        <div class="op" id="delete">Delete</div>
    </div>

    <script>
        function basename(inputPath) {
            if (!inputPath) return '';

            const cleanPath = inputPath.split(/[?#]/)[0];

            const segments = cleanPath.split('/');
            const fileName = segments.pop() || '';

            return fileName.replace(/\.[^.]+$/, '');
        }


        async function openFile(virtualPath) {
            const ext = virtualPath.split('.').pop().toLowerCase();

            try {
                const res = await fetch('file_associations.json');
                const associations = await res.json();

                if (associations[ext]) {
                    const appUrl = associations[ext].app + "?q=" + encodeURIComponent(virtualPath);
                    parent.openApp(appUrl, basename(virtualPath));
                } else {
                    window.parent.notify("Explorer", `No app found for ".${ext}" files`, {
                        type: "error",
                        icon: "⚠️",
                        timeout: 3000
                    });
                }
            } catch (e) {
                console.error('Failed to fetch file associations:', e);
                window.parent.notify("Explorer", "Failed to open file", {
                    type: "error",
                    icon: "⚠️",
                    timeout: 3000
                });
            }
        }

        // function showToast(message) {
        //     const toast = document.createElement('div');
        //     toast.innerText = message;
        //     toast.style.position = 'fixed';
        //     toast.style.bottom = '20px';
        //     toast.style.left = '20px';
        //     toast.style.background = '#333';
        //     toast.style.color = '#fff';
        //     toast.style.padding = '10px 20px';
        //     toast.style.borderRadius = '8px';
        //     toast.style.zIndex = '9999';
        //     document.body.appendChild(toast);
        //     setTimeout(() => toast.remove(), 3000);
        // }

        let contextMenu = document.getElementById("contextMenu");
        let currentTarget = null;
        document.getElementById('fileGrid').addEventListener('contextmenu', e => {
            e.preventDefault();
            const item = e.target.closest('.item');
            if (!item) {
                contextMenu.style.display = 'none';
                return;
            }
            currentTarget = item;

            contextMenu.style.top = `${e.clientY}px`;
            contextMenu.style.left = `${e.clientX}px`;
            contextMenu.style.display = 'block';
        });

        document.addEventListener("click", function () {
            contextMenu.style.display = "none";
        });

        document.getElementById('info').addEventListener('click', () => {
            contextMenu.style.display = 'none';
            if (!currentTarget) return;

            fetch('file_info.php?path=' + encodeURIComponent(currentTarget.dataset.path))
                .then(res => res.json())
                .then(info => {
                    alert(`Name: ${info.name}\nType: ${info.type}\nSize: ${info.size}\nModified: ${info.mtime}`);
                });
        });

        document.getElementById('rename').addEventListener('click', () => {
            contextMenu.style.display = 'none';
            if (!currentTarget) return;

            const oldName = currentTarget.dataset.name;
            const newName = prompt("Rename file/folder", oldName);
            if (!newName || newName === oldName) return;

            fetch('rename.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ oldName: currentTarget.dataset.path, newName })
            }).then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        showToast('Renamed successfully!');
                        location.reload();
                    } else {
                        showToast('Rename failed: ' + resp.error);
                    }
                });
        });

        document.getElementById('delete').addEventListener('click', () => {
            contextMenu.style.display = 'none';
            if (!currentTarget) return;

            if (!confirm(`Are you sure you want to delete "${currentTarget.dataset.name}"?`)) return;

            fetch('delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ path: currentTarget.dataset.path })
            }).then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        showToast('Deleted successfully!');
                        location.reload();
                    } else {
                        showToast('Delete failed: ' + resp.error);
                    }
                });
        });

        document.documentElement.style.setProperty('--accent', window.parent.THOS.getAllSettings().accentColor || '#ff69b4');
    </script>
</body>

</html>