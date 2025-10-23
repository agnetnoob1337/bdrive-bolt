<?php
    session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: Index.php");
        exit();
    }

    require_once 'db_config.php';

    // function to get all files in the folder and its subfolders
    function subFolderFileScan($dir) {
        $files = [];
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = "$dir/$item";
            if (is_dir($path)) {
                $files = array_merge($files, subFolderFileScan($path));
            } elseif (is_file($path)) {
                $files[] = $path;
            }
        }
        return $files;
    }

    $isRecursive = isset($_GET['recursive']) && $_GET['recursive'] === '1';
    $baseDir = 'files';
    $selectedFolder = isset($_GET['folder']) ? trim($_GET['folder'], '/') : '';
    $scanPath = $selectedFolder ? "$baseDir/$selectedFolder" : $baseDir;

    if (!is_dir($scanPath)) {
        echo "<p style='color:red;'>Folder does not exist: " . htmlspecialchars($scanPath) . "</p>";
        $savedFiles = [];
        $fileDataMap = [];
    } else {
        if ($isRecursive) {
            $sql = "SELECT * FROM files WHERE filepath LIKE ?";
            $stmt = $conn->prepare($sql);
            $likePath = $scanPath . '%';
            $stmt->bind_param("s", $likePath);
        } else {
            $sql = "SELECT * FROM files WHERE filepath LIKE ? AND filepath NOT LIKE ?";
            $stmt = $conn->prepare($sql);
            $likePath = $scanPath . '/%';
            $notLikePath = $scanPath . '/%/%';
            $stmt->bind_param("ss", $likePath, $notLikePath);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $savedFiles = [];
        $fileDataMap = [];
        while ($row = $result->fetch_assoc()) {
            if (file_exists($row['filepath'])) {
                $savedFiles[] = $row['filepath'];
                $fileDataMap[$row['filepath']] = $row;
            }
        }
        $stmt->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDrive</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header>
        <!-- Progress bar for uploads -->
        <div id="progressWrapper">
            <div id="progressContainer" style="display:none;">
                <progress id="uploadProgress" value="0" max="100"></progress>
                <p id="uploadStatus">Uploading...</p>
            </div>
        </div>
        <h1>BDrive</h1>
        <div>
            <!-- File upload form -->
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" name="fileToUpload[]" id="fileToUpload" multiple>
                <input type="submit" value="Upload Files">
                <input type="hidden" name="folder" value="<?php echo htmlspecialchars($scanPath); ?>">
            </form>

            <!-- Search bar, recursive checkbox, and sort dropdown -->
            <div id="searchBarDiv">
                <input type="text" id="searchBar" placeholder="Search files...">
                <p id="resultCount">Files: </p>
            </div>
            <form method="get" id="recursiveForm" style="display: inline;">
                <?php if ($selectedFolder): ?>
                    <input type="hidden" name="folder" value="<?php echo htmlspecialchars($selectedFolder); ?>">
                <?php endif; ?>
                <input type="checkbox" id="recursiveSearch" name="recursive" value="1" <?php if ($isRecursive) echo 'checked'; ?>>
                <label for="recursiveSearch">Include lower folders</label>
            </form>
            <div id="sortBoxDiv">
                <p>Sort by:</p>
                <select name="" id="sortBox">
                    <option value="Newest" selected="selected">Newest</option>
                    <option value="Oldest">Oldest</option>    
                    <option value="A-Z">A-Z</option>
                    <option value="Z-A">Z-A</option>
                    <option value="Type">Type</option>
                </select>
            </div>
        </div>

        <!-- Folder navigation and management -->
        <p id="folderDisplay">
            <?php
                if ($selectedFolder) {
                    echo "Folder:/" . htmlspecialchars($selectedFolder);
                } else {
                    echo "Folder:/";
                }
            ?>
        </p>
        <nav id="folderContainer">
            <?php
                // Back button
                if ($selectedFolder) {
                    $parentFolder = dirname($selectedFolder);
                    $parentFolderValue = $parentFolder !== '.' ? $parentFolder : '';

                    echo "<div class='folderItem'>
                        <form method='get' style='display:inline;'>
                            <input type='hidden' name='folder' value='" . htmlspecialchars($parentFolderValue) . "'>
                            <button class='folderButton' type='submit'>⬅️ Back</button>
                        </form>
                    </div>";
                }

                // folder buttons
                $folders = array_filter(glob($scanPath . '/*'), 'is_dir');
                sort($folders, SORT_NATURAL | SORT_FLAG_CASE);

                foreach ($folders as $folder) {
                    $folderName = basename($folder);
                    $relativePath = ltrim(str_replace($baseDir . '/', '', $folder), '/');
                

                    $fileCount = count(subFolderFileScan($folder));
                    echo "<div class='folderItem'>
                            <form method='get' style='display:inline;'>
                                <input type='hidden' name='folder' value='" . htmlspecialchars($relativePath) . "'>
                                <button class='folderButton' type='submit'>📁 " . htmlspecialchars($folderName) . "</button>
                            </form>
                            
                            <div class='folderControls toggleVisible' style='display:none;'>
                                <form method='post' action='renameFolder.php' style='display:inline;'>
                                    <input type='hidden' name='oldFolder' value='" . htmlspecialchars($relativePath) . "'>
                                    <input type='text' name='newFolder' placeholder='New name' value='" . htmlspecialchars($folderName) . "'>
                                    <button type='submit' class='renameBtn'>✏️ Rename</button>
                                </form>
                                <form method='post' action='deleteFolder.php' style='display:inline;'>
                                    <input type='hidden' name='deleteFolder' value='" . htmlspecialchars($relativePath) . "'>
                                    <button type='submit' class='deleteBtn' 
                                        onclick='return confirm(\"Delete this folder? It contains {$fileCount} file(s).\")'>
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>";
                }
                
                // New folder creation
                echo "<div class='folderItem toggleVisible'>
                    <div class='folderControls'>
                        <form method='post' action='createFolder.php' style='display:inline;'>
                            <input type='hidden' name='parentFolder' value='" . htmlspecialchars($selectedFolder) . "'>
                            <input type='text' name='newFolderName' placeholder='New folder name'>
                            <button type='submit' class='createBtn'>+ Create Folder</button>
                        </form>
                    </div>
                </div>";
        
                // Toggle folder management visibility button
                echo "<button id='toggleFolderControls' class='folderButton' >⚙️ Folders</button>";
            ?>
        </nav>
    </header>
    <div id="container">
        <?php
            foreach ($savedFiles as $filePath) {
                $file = basename($filePath);
                $dbData = $fileDataMap[$filePath] ?? null;
                $tags = $dbData ? $dbData['tags'] : '';

                if (is_file($filePath)){
                    // Get file details
                    $fileName = pathinfo($file, PATHINFO_FILENAME);
                    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $fileModTime = filemtime($filePath);
                    $fileSize = filesize($filePath);
                    
                    // Determine display size prefix
                    if ($fileSize >= 1073741824){
                        $sizeDevision = 1073741824;
                        $fileSizePrefix = 'GB';
                    }elseif ($fileSize >= 1048576){
                        $sizeDevision = 1048576;
                        $fileSizePrefix = 'MB';
                    }elseif ($fileSize >= 1024){
                        $sizeDevision = 1024;
                        $fileSizePrefix = 'KB';
                    }else{
                        $sizeDevision = 1;
                        $fileSizePrefix = 'bytes';
                    }

                    // Categorizing files by type
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

                    // Render files
                    echo "<div class='fileCards'
                            data-filename='" . htmlspecialchars($file) . "'
                            data-date='" . date("c", $fileModTime) . "'
                            data-type='" . htmlspecialchars($typeCategory) . "'
                            data-extension='" . htmlspecialchars($fileExtension) . "'
                            data-tags='" . htmlspecialchars($tags) . "'>";

                    if ($typeCategory === 'image') {
                        echo "<img src='" . htmlspecialchars($filePath) . "' alt='" . htmlspecialchars($file) . "' loading='lazy' class='clickableImage'>";
                    } elseif ($typeCategory === 'video') {
                        echo "<video 
                            class='lazy-video' 
                            controls 
                            preload='none'  
                            playsinline 
                            poster='video-placeholder.gif' 
                            data-src='" . htmlspecialchars($filePath) . "' 
                            data-type='video/" . htmlspecialchars($fileExtension) . "'>
                        </video>";
                
                    } else {
                        echo "<p>" . htmlspecialchars($file) . "</p>";
                    }


                    
                    // Info menu
                    echo "<button class='toggleInfoBtn'>Info</button>";

                    echo "<div class='fileInfo' style='display: none;'>";
                            echo "<div class='fileControls'>";

                            // Editable filename
                            echo "<span class='filenameDisplay'>" . htmlspecialchars($file) . "</span>";
                            echo "<input type='text' class='filenameInput' value='" . htmlspecialchars($fileName) . "' style='display:none;'>";

                            // Edit button
                            echo "<button class='editBtn' title='Edit Name'>✏️</button>";

                            // Save button
                            echo "<form method='post' action='rename.php' class='renameForm' style='display: none; display:inline;'>
                                <input type='hidden' name='oldPath' value='" . htmlspecialchars($filePath) . "'>
                                <input type='hidden' name='folder' value='" . htmlspecialchars($selectedFolder) . "'>
                                <input type='hidden' name='newName' class='newNameInput'>
                                <input type='hidden' class='fileExt' value='" . htmlspecialchars($fileExtension) . "'>
                                <button type='submit' class='saveBtn' >💾</button>
                            </form>";


                            // Delete button
                            echo "<form method='post' action='delete.php' class='deleteForm' style='display:inline;'>
                                    <input type='hidden' name='deletePath' value='" . htmlspecialchars($filePath) . "'>
                                    <input type='hidden' name='folder' value='" . htmlspecialchars($selectedFolder) . "'>
                                    <button type='submit' class='deleteBtn' onclick='return confirm(\"Delete this file?\")'>🗑️</button>
                                </form>";

                            // Download button
                            echo "<a href='" . htmlspecialchars($filePath) . "' download class='downloadBtn'>⬇️</a>";

                            // Close info button and shows file info
                        echo "</div>
                            <p>Type: " . htmlspecialchars($typeCategory) . "</p>
                            <p>Extension: " . htmlspecialchars($fileExtension) . "</p>
                            <p>Modified: " . date("Y-m-d H:i:s", $fileModTime) . "</p>
                            <p>Size: " . number_format($fileSize / $sizeDevision, 2) . " $fileSizePrefix</p>
                            <div class='tagSection'>
                                <span class='tagDisplay'>Tags: " . htmlspecialchars($tags) . "</span>
                                <input type='text' class='tagInput' value='" . htmlspecialchars($tags) . "' style='display:none;' placeholder='Enter tags (comma separated)'>
                                <button class='editTagBtn' title='Edit Tags'>✏️</button>
                                <form method='post' action='updateTags.php' class='tagForm' style='display:none;display:inline;'>
                                    <input type='hidden' name='filepath' value='" . htmlspecialchars($filePath) . "'>
                                    <input type='hidden' name='folder' value='" . htmlspecialchars($selectedFolder) . "'>
                                    <input type='hidden' name='tags' class='tagsHidden'>
                                    <button type='submit' class='saveTagBtn'>💾</button>
                                </form>
                            </div>
                        </div>";

                    echo "</div>";
                }
            }
        ?>
    </div>
</body>
<script src="js.js" defer ></script>
<script src="xhr.js"></script>
</html>