<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

require_once 'db_config.php';

header('Content-Type: application/json');
$response = ['success' => true, 'errors' => []];
$fileDir = $_POST['folder'] ?? '';


if (empty($_FILES["fileToUpload"]["name"][0])) {
    $response['success'] = false;
    $response['errors'][] = 'No files selected.';
    echo json_encode($response);
    exit;
}


foreach ($_FILES["fileToUpload"]["name"] as $key => $name) {
    $target_file = $fileDir . "/" . basename($name);

    if (file_exists($target_file)) {
        $response['success'] = false;
        $response['errors'][] = "File already exists: $name ";
        continue;
    }

    if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$key], $target_file)) {
        $response['success'] = false;
        $response['errors'][] = "Failed to upload: $name";
    } else {
        $filename = basename($name);
        $filepath = $target_file;
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

        $stmt = $conn->prepare("INSERT INTO files (filename, filepath, filetype, tags) VALUES (?, ?, ?, '')");
        $stmt->bind_param("sss", $filename, $filepath, $typeCategory);

        if (!$stmt->execute()) {
            $response['errors'][] = "File uploaded but failed to add to database: $name";
        }

        $stmt->close();
    }
}

echo json_encode($response);
?>
