<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

$baseDir = 'files';

if (isset($_POST['oldFolder'], $_POST['newFolder'])) {
    $oldFolder = trim($_POST['oldFolder'], '/');
    $newFolderName = trim($_POST['newFolder']);

    $oldPath = realpath("$baseDir/$oldFolder");
    $newPath = "$baseDir/" . ($oldFolder ? dirname($oldFolder) . '/' : '') . $newFolderName;

    if ($oldPath && str_starts_with($oldPath, realpath($baseDir)) && !empty($newFolderName)) {
        if (!file_exists($newPath)) {
            rename($oldPath, $newPath);
        }
    }


    $redirectFolder = dirname($oldFolder);
    if ($redirectFolder === '.' || $redirectFolder === $baseDir) {
        $redirectFolder = ''; 
    }

    header("Location: BDrive.php?folder=" . urlencode($redirectFolder));
    exit();
}
?>
