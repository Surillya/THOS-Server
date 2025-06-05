<?php

function validateManifest(array $manifest): array {
    $requiredKeys = ['name', 'version', 'description', 'author', 'entry', 'icon', 'categories'];

    $errors = [];

    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $manifest)) {
            $errors[] = "Missing required manifest field: '$key'";
        }
    }

    // entry file must be inside the app/ folder
    if (isset($manifest['entry']) && strpos($manifest['entry'], '/') === false) {
        // No slashes allowed (it must be inside app/)
    } else {
        $errors[] = "Entry field should be a filename inside the 'app/' folder, e.g. 'main.php'";
    }

    if (!is_array($manifest['categories'] ?? null)) {
        $errors[] = "Categories must be an array";
    }

    return $errors;
}

function installPackage(string $zipPath, string $appsDir): bool {
    if (!file_exists($zipPath)) {
        die("Error: ZIP file does not exist.");
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        die("Error: Unable to open ZIP file.");
        return false;
    }

    // Check for manifest.json at root of ZIP
    $manifestIndex = $zip->locateName('manifest.json', ZipArchive::FL_NODIR);
    if ($manifestIndex === false) {
        die("Error: manifest.json not found in ZIP root.");
        $zip->close();
        return false;
    }

    // Read and decode manifest.json
    $manifestContent = $zip->getFromIndex($manifestIndex);
    $manifest = json_decode($manifestContent, true);

    if (!$manifest) {
        die("Error: manifest.json contains invalid JSON.");
        $zip->close();
        return false;
    }

    // Validate manifest fields
    $errors = validateManifest($manifest);
    if (!empty($errors)) {
        echo "Manifest validation errors:\n";
        foreach ($errors as $error) {
            echo " - $error\n";
        }
        $zip->close();
        return false;
    }

    // App name used for directory (safe slug)
    $appName = preg_replace('/[^a-z0-9_-]/i', '_', $manifest['name']);
    $installPath = $appsDir . $appName;

    // Create app install directory if not exists
    if (!is_dir($installPath)) {
        if (!mkdir($installPath, 0755, true)) {
            echo "Error: Unable to create app directory: $installPath\n";
            $zip->close();
            return false;
        }
    } else {
        echo "<script>console.log('Warning: App directory already exists, overwriting...');</script>";
    }

    // Extract all ZIP files to the install directory
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        $filename = $stat['name'];

        // Security check: prevent path traversal
        if (strpos($filename, '..') !== false) {
            echo "Error: ZIP contains invalid filename (path traversal): $filename\n";
            $zip->close();
            return false;
        }

        // Extract file content
        $content = $zip->getFromIndex($i);

        // Compute target path
        $targetPath = $installPath . '/' . $filename;

        // Create directory if needed
        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        // Write file
        file_put_contents($targetPath, $content);
    }

    $zip->close();

    echo "<script>console.log('App \'{$manifest['name']}\' installed successfully to: $installPath');</script>";
    return true;
}

require_once "vfs.php";
$file = resolve_path($_GET['q'] ?? '');

installPackage($file, __DIR__ . "/apps/");
