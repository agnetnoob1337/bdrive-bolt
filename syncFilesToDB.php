<?php
// Sync existing files in the 'files' directory to the database
// Run this script once to populate the database with existing files

require_once 'db_config.php';

function scanAllFiles($dir) {
    $files = [];
    if (!is_dir($dir)) return $files;

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = "$dir/$item";
        if (is_dir($path)) {
            $files = array_merge($files, scanAllFiles($path));
        } elseif (is_file($path)) {
            $files[] = $path;
        }
    }
    return $files;
}

$baseDir = 'files';
$allFiles = scanAllFiles($baseDir);

$synced = 0;
$skipped = 0;
$errors = 0;
$updated = 0;
$deleted = 0;

echo "Starting file sync to database...\n\n";

// Step 1: Clean up orphaned database records (files that no longer exist on filesystem)
echo "Step 1: Cleaning up orphaned database records...\n";
$allDbFiles = $conn->query("SELECT id, filepath FROM files");
while ($row = $allDbFiles->fetch_assoc()) {
    if (!file_exists($row['filepath'])) {
        $deleteStmt = $conn->prepare("DELETE FROM files WHERE id = ?");
        $deleteStmt->bind_param("i", $row['id']);
        $deleteStmt->execute();
        $deleteStmt->close();
        echo "DELETE: {$row['filepath']} (file no longer exists)\n";
        $deleted++;
    }
}
echo "\n";

// Step 2: Sync filesystem files to database
echo "Step 2: Syncing filesystem files to database...\n";
foreach ($allFiles as $filePath) {
    $filename = basename($filePath);
    $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $typeCategory = 'other';
    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
        $typeCategory = 'image';
    } elseif (in_array($fileExtension, ['mp4', 'webm', 'avi', 'mov'])) {
        $typeCategory = 'video';
    } elseif (in_array($fileExtension, ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx', 'xls', 'xlsx'])) {
        $typeCategory = 'document';
    } elseif (in_array($fileExtension, ['mp3', 'wav', 'ogg'])) {
        $typeCategory = 'audio';
    }

    // Check if file exists in database
    $checkStmt = $conn->prepare("SELECT id, filename, filetype FROM files WHERE filepath = ?");
    $checkStmt->bind_param("s", $filePath);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // File already exists, check if we need to update it
        $row = $result->fetch_assoc();
        if ($row['filename'] !== $filename || $row['filetype'] !== $typeCategory) {
            $updateStmt = $conn->prepare("UPDATE files SET filename = ?, filetype = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $filename, $typeCategory, $row['id']);
            $updateStmt->execute();
            $updateStmt->close();
            echo "UPDATE: $filePath (metadata changed)\n";
            $updated++;
        } else {
            echo "SKIP: $filePath (already in database)\n";
            $skipped++;
        }
    } else {
        // Insert new file
        $stmt = $conn->prepare("INSERT INTO files (filename, filepath, filetype, tags) VALUES (?, ?, ?, '')");
        $stmt->bind_param("sss", $filename, $filePath, $typeCategory);

        if ($stmt->execute()) {
            echo "SYNC: $filePath\n";
            $synced++;
        } else {
            echo "ERROR: $filePath - " . $stmt->error . "\n";
            $errors++;
        }
        $stmt->close();
    }
    $checkStmt->close();
}

echo "\n========== Sync Complete ==========\n";
echo "Deleted: $deleted orphaned records\n";
echo "Synced: $synced new files\n";
echo "Updated: $updated files\n";
echo "Skipped: $skipped files (no changes)\n";
echo "Errors: $errors files\n";
echo "===================================\n";
?>
