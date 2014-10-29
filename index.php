<?php
// ini_set("display_errors",0);error_reporting(0);

    // Include the DirectoryIndex class
    require_once('resources/DirectoryIndex.php');

    // Initialize the DirectoryIndex object
    $index = new DirectoryIndex();

    // Return file hash
    if (isset($_GET['f'])) {

        $filePath = base64_decode($_GET['f']);

        $index->downloadFile($filePath);

    }

    // Initialize the directory array
    if (isset($_GET['dir'])) {
        $dirArray = $index->listDirectory($_GET['dir']);
    } else {
        $dirArray = $index->listDirectory('.');
    }

    // Define theme path
    if (!defined('THEMEPATH')) {
        define('THEMEPATH', $index->getThemePath());
    }

    // Set path to theme index
    $themeIndex = $index->getThemePath(true) . '/index.php';

    // Initialize the theme
    if (file_exists($themeIndex)) {
        include($themeIndex);
    } else {
        die('ERROR: Failed to initialize theme');
    }
