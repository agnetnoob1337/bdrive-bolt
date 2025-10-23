<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

$baseDir = 'files';

if (isset($_POST['deleteFolder'])) {
    $folder = trim($_POST['deleteFolder'], '/');
    $folderPath = realpath("$baseDir/$folder");

    if ($folderPath && str_starts_with($folderPath, realpath($baseDir))) {

        function deleteDir($dir) {
            foreach (scandir($dir) as $item) {
                if ($item === '.' || $item === '..') continue;
                $path = "$dir/$item";
                if (is_dir($path)) deleteDir($path);
                else unlink($path);
            }
            rmdir($dir);
        }

        if (is_dir($folderPath)) {
            deleteDir($folderPath);
        }
    }

    $parentFolder = dirname($folder);
    $redirectFolder = $parentFolder !== '.' ? $parentFolder : '';

    header("Location: BDrive.php?folder=" . urlencode($redirectFolder));
    exit();
}
?>
