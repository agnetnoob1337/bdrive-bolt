<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Index.php");
    exit();
}

function redirect($parentFolder = ""){
    $redirectUrl = "BDrive.php";
    if ($parentFolder !== "") {
        $redirectUrl .= "?folder=" . urlencode($parentFolder);
    }
    header("Location: $redirectUrl");
    exit();
}

$baseDir = "files"; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['newFolderName'])) {
    $parentFolder = isset($_POST['parentFolder']) ? trim($_POST['parentFolder'], "/") : "";
    $newFolderName = trim($_POST['newFolderName']);

    if ($newFolderName === "") {
        redirect($parentFolder);
    }


    if ($newFolderName === "") {
        redirect($parentFolder);
    }

    $targetPath = $baseDir;
    if ($parentFolder !== "") {
        $targetPath .= "/" . $parentFolder;
    }
    $fullPath = $targetPath . "/" . $newFolderName;

    if (!file_exists($fullPath)) {
        mkdir($fullPath, 0755, true);
    }

    redirect($parentFolder);
} else {
    redirect();
}
