<?php
// vfs.php
// Must sit in your project root (or adjust the require path below)
require_once __DIR__ . '/config.php';

/**
 * Converts a virtual path ("/Documents/file.txt")
 * into a real filesystem path under REAL_ROOT.
 */
function resolve_path(string $virtualPath): string {
    // strip any “..” just in case
    $virtualPath = str_replace('..', '', $virtualPath);
    return rtrim(REAL_ROOT, '/') . '/' . ltrim($virtualPath, '/');
}

/**
 * Converts a real filesystem path back into a virtual one
 * (so the front-end never sees your real server layout).
 */
function virtualize_path(string $realPath): string {
    if (str_starts_with($realPath, REAL_ROOT)) {
        return '/' . ltrim(substr($realPath, strlen(REAL_ROOT)), '/');
    }
    // fallback (shouldn’t happen if you always resolve under REAL_ROOT)
    return $realPath;
}
