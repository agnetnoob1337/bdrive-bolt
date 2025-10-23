<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPath = $_POST['oldPath'] ?? '';
    $newName = basename($_POST['newName'] ?? '');
    $folder = $_POST['folder'] ?? '';

    $baseDir = 'files';
    $currentDir = $folder ? "$baseDir/$folder" : $baseDir;

    $newPath = $currentDir . '/' . $newName;

    if ($newName && file_exists($oldPath)) {
        if (rename($oldPath, $newPath)) {
            header("Location: BDrive.php?folder=" . urlencode($folder));
            exit();
        } else {
            echo "Failed to rename file.";
        }
    } else {
        echo "Invalid file name or file doesn't exist.";
    }
}
?>
