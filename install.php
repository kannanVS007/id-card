<?php
/**
 * Little Krish ID Card System - Auto Installer
 * Run this file once to set up the folder structure
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>ID Card System - Installation</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 p-8'>
<div class='max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8'>";

echo "<h1 class='text-3xl font-bold text-teal-600 mb-6'>🚀 Little Krish ID Card System</h1>";
echo "<h2 class='text-xl font-semibold text-gray-700 mb-4'>Installation Progress</h2>";

$errors = [];
$success = [];

// Check if running on localhost
$isLocalhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);
echo "<div class='mb-4 p-4 bg-blue-50 border-l-4 border-blue-500'>";
echo "<p class='font-semibold'>Environment: " . ($isLocalhost ? "✅ Localhost" : "🌐 Live Server") . "</p>";
echo "<p class='text-sm text-gray-600'>Server: {$_SERVER['SERVER_NAME']}</p>";
echo "</div>";

// Create directories
$directories = [
    'uploads',
    'uploads/photos',
    'uploads/processed'
];

echo "<div class='mb-6'>";
echo "<h3 class='font-bold text-gray-800 mb-2'>Creating Directories:</h3>";

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            $success[] = "✅ Created: $dir";
            echo "<p class='text-green-600'>✅ Created: <code class='bg-green-100 px-2 py-1 rounded'>$dir</code></p>";
        } else {
            $errors[] = "❌ Failed to create: $dir";
            echo "<p class='text-red-600'>❌ Failed to create: <code class='bg-red-100 px-2 py-1 rounded'>$dir</code></p>";
        }
    } else {
        echo "<p class='text-blue-600'>ℹ️ Already exists: <code class='bg-blue-100 px-2 py-1 rounded'>$dir</code></p>";
    }
}
echo "</div>";

// Check PHP extensions
echo "<div class='mb-6'>";
echo "<h3 class='font-bold text-gray-800 mb-2'>Checking PHP Extensions:</h3>";

$extensions = ['gd', 'zip', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='text-green-600'>✅ {$ext} - Installed</p>";
    } else {
        echo "<p class='text-red-600'>❌ {$ext} - Not Installed</p>";
        $errors[] = "Missing extension: $ext";
    }
}
echo "</div>";

// Check file permissions
echo "<div class='mb-6'>";
echo "<h3 class='font-bold text-gray-800 mb-2'>Checking Permissions:</h3>";

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p class='text-green-600'>✅ $dir - Writable</p>";
        } else {
            echo "<p class='text-red-600'>❌ $dir - Not Writable</p>";
            $errors[] = "Directory not writable: $dir";
        }
    }
}
echo "</div>";

// Check required files
echo "<div class='mb-6'>";
echo "<h3 class='font-bold text-gray-800 mb-2'>Checking Required Files:</h3>";

$requiredFiles = [
    'index.php',
    'id_card_template.php',
    'process_single.php',
    'process_bulk.php',
    'view_card.php',
    'view_bulk.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='text-green-600'>✅ $file</p>";
    } else {
        echo "<p class='text-orange-600'>⚠️ $file - Missing</p>";
    }
}
echo "</div>";

// Final status
echo "<div class='mt-8 p-6 rounded-lg " . (empty($errors) ? "bg-green-50 border-2 border-green-500" : "bg-orange-50 border-2 border-orange-500") . "'>";

if (empty($errors)) {
    echo "<h3 class='text-2xl font-bold text-green-700 mb-4'>🎉 Installation Successful!</h3>";
    echo "<p class='text-green-800 mb-4'>Your ID Card system is ready to use.</p>";
    echo "<div class='flex gap-4'>";
    echo "<a href='demo.php' class='bg-teal-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-teal-700 inline-block'>View Demo</a>";
    echo "<a href='index.php' class='bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 inline-block'>Start Creating Cards</a>";
    echo "</div>";
    
    echo "<div class='mt-6 p-4 bg-blue-100 rounded'>";
    echo "<h4 class='font-bold text-blue-900 mb-2'>Quick Access URLs:</h4>";
    echo "<ul class='text-sm text-blue-800 space-y-1'>";
    echo "<li>📍 Demo Page: <code class='bg-blue-200 px-2 py-1 rounded'>http://localhost/id-card/demo.php</code></li>";
    echo "<li>📍 Main System: <code class='bg-blue-200 px-2 py-1 rounded'>http://localhost/id-card/index.php</code></li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<h3 class='text-2xl font-bold text-orange-700 mb-4'>⚠️ Installation Issues Found</h3>";
    echo "<p class='text-orange-800 mb-4'>Please fix the following issues:</p>";
    echo "<ul class='list-disc list-inside space-y-2'>";
    foreach ($errors as $error) {
        echo "<li class='text-red-600'>$error</li>";
    }
    echo "</ul>";
    
    echo "<div class='mt-6 p-4 bg-yellow-100 rounded'>";
    echo "<h4 class='font-bold text-yellow-900 mb-2'>💡 Solutions:</h4>";
    echo "<ul class='text-sm text-yellow-800 space-y-1 list-disc list-inside'>";
    echo "<li>For permission issues: Right-click folders → Properties → Security → Allow full control</li>";
    echo "<li>For missing extensions: Edit php.ini and uncomment extension lines</li>";
    echo "<li>After fixing, refresh this page</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>";

// System information
echo "<div class='mt-6 p-4 bg-gray-100 rounded'>";
echo "<h4 class='font-bold text-gray-700 mb-2'>System Information:</h4>";
echo "<div class='text-sm text-gray-600 space-y-1'>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "</div>";
echo "</div>";

echo "</div></body></html>";
?>