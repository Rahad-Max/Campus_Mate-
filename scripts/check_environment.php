<?php
/**
 * PUB CampusMate — Environment Health Check Script
 * Command line runner: php scripts/check_environment.php
 */

define('ROOT_PATH', dirname(__DIR__));

echo "========================================================\n";
echo " PUB CampusMate — System Environment Verification\n";
echo " Pundra University of Science & Technology (PUB)\n";
echo "========================================================\n\n";

$errors = [];
$warnings = [];
$checks = [];

// 1. PHP Version
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '8.2.0', '>=')) {
    $checks[] = "[PASS] PHP Version: {$phpVersion} (>= 8.2 required)";
} else {
    $errors[] = "[FAIL] PHP Version: {$phpVersion} (PHP 8.2+ required)";
}

// 2. Required PHP Extensions
$requiredExtensions = [
    'pdo' => 'PDO Data Objects',
    'pdo_mysql' => 'PDO MySQL Driver',
    'json' => 'JSON Parser',
    'mbstring' => 'Multibyte String',
    'session' => 'Session Support'
];

foreach ($requiredExtensions as $ext => $desc) {
    if (extension_loaded($ext)) {
        $checks[] = "[PASS] PHP Extension: {$ext} ({$desc})";
    } else {
        $errors[] = "[FAIL] Missing PHP Extension: {$ext} ({$desc})";
    }
}

// 3. Recommended Extensions
$recommended = [
    'openssl' => 'OpenSSL Cryptography',
    'curl' => 'cURL HTTP Client'
];
foreach ($recommended as $ext => $desc) {
    if (extension_loaded($ext)) {
        $checks[] = "[PASS] Recommended Extension: {$ext} ({$desc})";
    } else {
        $warnings[] = "[WARN] Optional Extension Missing: {$ext} ({$desc})";
    }
}

// 4. Storage Directories
$directories = [
    ROOT_PATH . '/storage/logs',
    ROOT_PATH . '/storage/cache',
    ROOT_PATH . '/storage/documents'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (is_writable($dir)) {
        $checks[] = "[PASS] Writable Directory: " . str_replace(ROOT_PATH, '', $dir);
    } else {
        $errors[] = "[FAIL] Directory not writable: " . str_replace(ROOT_PATH, '', $dir);
    }
}

// 5. Environment File (.env)
if (file_exists(ROOT_PATH . '/.env')) {
    $checks[] = "[PASS] Configuration File: .env found";
} else {
    $warnings[] = "[WARN] .env not found. Please copy .env.example to .env";
}

// Output Results
echo "CHECK RESULTS:\n";
foreach ($checks as $c) {
    echo "  " . $c . "\n";
}

if (!empty($warnings)) {
    echo "\nWARNINGS:\n";
    foreach ($warnings as $w) {
        echo "  " . $w . "\n";
    }
}

if (!empty($errors)) {
    echo "\nERRORS DETECTED:\n";
    foreach ($errors as $e) {
        echo "  " . $e . "\n";
    }
    echo "\n>>> Environment check FAILED. Please resolve errors before running the application.\n";
    exit(1);
}

echo "\n>>> Environment check PASSED! All prerequisites satisfied.\n";
exit(0);
