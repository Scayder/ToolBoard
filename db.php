<?php
// Ajuste estos valores si su XAMPP usa otros datos.
const DB_HOST = 'localhost';
const DB_NAME = 'toolboard';
const DB_USER = 'root';
const DB_PASS = '';

$projectRoot = realpath(__DIR__);
$scriptFile = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$baseUrl = '';

if ($projectRoot !== false && $scriptFile !== false) {
    $projectPath = rtrim(str_replace('\\', '/', $projectRoot), '/');
    $requestedFile = str_replace('\\', '/', $scriptFile);
    $projectPrefix = $projectPath . '/';

    if (stripos($requestedFile, $projectPrefix) === 0) {
        $relativeFile = substr($requestedFile, strlen($projectPath));
        $relativeUrl = '/' . implode('/', array_map('rawurlencode', explode('/', ltrim($relativeFile, '/'))));
        if ($relativeUrl !== '/' && strcasecmp(substr($scriptName, -strlen($relativeUrl)), $relativeUrl) === 0) {
            $baseUrl = rtrim(substr($scriptName, 0, -strlen($relativeUrl)), '/');
        }
    }
}

define('BASE_URL', $baseUrl);
