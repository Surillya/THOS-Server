<?php
require_once "config.php";

// Converts virtual path like "/Documents/file.txt" → real path like "/home/surillya/Documents/file.txt"
function resolve_path(string $virtualPath): string {
    $virtualPath = str_replace('..', '', $virtualPath); // prevent traversal
    return rtrim(REAL_ROOT, '/') . '/' . ltrim($virtualPath, '/');
}

// Converts real path like "/home/surillya/Documents/file.txt" → virtual path like "/Documents/file.txt"
function virtualize_path(string $realPath): string {
    if (str_starts_with($realPath, REAL_ROOT)) {
        return '/' . ltrim(substr($realPath, strlen(REAL_ROOT)), '/');
    }
    return $realPath; // fallback, possibly invalid or external
}