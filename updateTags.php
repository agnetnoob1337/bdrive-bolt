<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filepath = $_POST['filepath'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $folder = $_POST['folder'] ?? '';

    if ($filepath) {
        $stmt = $conn->prepare("UPDATE files SET tags = ? WHERE filepath = ?");
        $stmt->bind_param("ss", $tags, $filepath);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: BDrive.php?folder=" . urlencode($folder));
            exit();
        } else {
            echo "Failed to update tags.";
        }
        $stmt->close();
    }
}
?>
