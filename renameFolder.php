<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

require_once 'db_config.php';

$baseDir = 'files';

if (isset($_POST['oldFolder'], $_POST['newFolder'])) {
    $oldFolder = trim($_POST['oldFolder'], '/');
    $newFolderName = trim($_POST['newFolder']);

    $oldPath = realpath("$baseDir/$oldFolder");
    $newPath = "$baseDir/" . ($oldFolder ? dirname($oldFolder) . '/' : '') . $newFolderName;

    if ($oldPath && str_starts_with($oldPath, realpath($baseDir)) && !empty($newFolderName)) {
        if (!file_exists($newPath)) {
            if (rename($oldPath, $newPath)) {
                $sql = "SELECT id, filepath FROM files WHERE filepath LIKE ?";
                $stmt = $conn->prepare($sql);
                $likePath = $oldPath . '%';
                $stmt->bind_param("s", $likePath);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $oldFilePath = $row['filepath'];
                    $newFilePath = str_replace($oldPath, $newPath, $oldFilePath);

                    $updateStmt = $conn->prepare("UPDATE files SET filepath = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $newFilePath, $row['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                $stmt->close();
            }
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
