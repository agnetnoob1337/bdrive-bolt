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

    // Use relative paths for both filesystem and database
    $oldPathRelative = $oldFolder ? "$baseDir/$oldFolder" : $baseDir;
    $newPathRelative = "$baseDir/" . ($oldFolder ? dirname($oldFolder) . '/' : '') . $newFolderName;

    // Remove duplicate slashes
    $oldPathRelative = preg_replace('#/+#', '/', $oldPathRelative);
    $newPathRelative = preg_replace('#/+#', '/', $newPathRelative);

    // Verify it's a real path for security
    $oldPathReal = realpath($oldPathRelative);

    if ($oldPathReal && str_starts_with($oldPathReal, realpath($baseDir)) && !empty($newFolderName)) {
        if (!file_exists($newPathRelative)) {
            if (rename($oldPathRelative, $newPathRelative)) {
                // Update all file paths in database
                $sql = "SELECT id, filepath FROM files WHERE filepath LIKE ?";
                $stmt = $conn->prepare($sql);
                $likePath = $oldPathRelative . '/%';
                $stmt->bind_param("s", $likePath);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $oldFilePath = $row['filepath'];
                    $newFilePath = str_replace($oldPathRelative, $newPathRelative, $oldFilePath);

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
