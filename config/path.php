<?php
// Auto-detect base path from current script location
$scriptPath = $_SERVER['SCRIPT_NAME'];
$scriptDir = dirname($scriptPath);

// Get the base path (everything before the script name)
// For example: /WebQuanLyChiTieu/index.php -> /WebQuanLyChiTieu
$basePath = rtrim($scriptDir, '/');

// If script is in root, base path is /
if ($basePath === '.' || $basePath === '') {
    $basePath = '/';
}

// If we're in a subdirectory, we need to find the project root
// Check if we're in a subdirectory by comparing with document root
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);

// Get relative path from document root to script directory
$relativePath = str_replace($docRoot, '', dirname($scriptFile));
$relativePath = trim($relativePath, '/\\');

// If relative path is empty, we're at root
if ($relativePath === '') {
    $basePath = '/';
} else {
    // Extract project folder name (first directory in path)
    $pathParts = explode('/', $relativePath);
    $projectFolder = $pathParts[0];
    $basePath = '/' . $projectFolder;
}

// Define constants
define('BASE_PATH', $basePath);
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH);

// Helper function to get full URL
function base_url($path = '') {
    $path = ltrim($path, '/');
    return BASE_PATH . ($path ? '/' . $path : '');
}
?>

