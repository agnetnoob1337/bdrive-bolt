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

echo "Starting file sync to database...\n\n";

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

    $checkStmt = $conn->prepare("SELECT id FROM files WHERE filepath = ?");
    $checkStmt->bind_param("s", $filePath);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo "SKIP: $filePath (already in database)\n";
        $skipped++;
    } else {
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
echo "Synced: $synced files\n";
echo "Skipped: $skipped files (already in DB)\n";
echo "Errors: $errors files\n";
echo "===================================\n";
?>
