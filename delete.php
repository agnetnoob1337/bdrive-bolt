<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileToDelete = $_POST['deletePath'] ?? '';
    $folder = $_POST['folder'] ?? '';

    if (file_exists($fileToDelete)) {
        unlink($fileToDelete);
    }

    header("Location: BDrive.php?folder=" . urlencode($folder));
    exit();
}
?>
