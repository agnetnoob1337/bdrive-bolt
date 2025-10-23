<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

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
    }
}

echo json_encode($response);
?>
