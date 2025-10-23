<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileToDelete = $_POST['deletePath'] ?? '';
    $folder = $_POST['folder'] ?? '';

    if (file_exists($fileToDelete)) {
        unlink($fileToDelete);

        $stmt = $conn->prepare("DELETE FROM files WHERE filepath = ?");
        $stmt->bind_param("s", $fileToDelete);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: BDrive.php?folder=" . urlencode($folder));
    exit();
}
?>
