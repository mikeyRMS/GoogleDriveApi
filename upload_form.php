<?php
//after cloning your git repo and moving the folder to htdocs, I encounter errors on every php files on line 2 when running on localhost
require __DIR__ . '/vendor/autoload.php'; <!-- this part is essential -->
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
    $mime_type = mime_content_type($file_tmp);

    $client = new Google_Client();
    $client->setAuthConfig('credentials.json');
    $client->addScope(Google_Service_Drive::DRIVE_FILE);
    $client->setAccessType('offline');
    $client->setRedirectUri('http://localhost/googledrive/upload.php');
    $client->setAccessToken($_SESSION['access_token']);

    if ($client->isAccessTokenExpired()) {
        echo "<p>⚠️ Access token expired. Please re-authenticate.</p>";
        session_destroy();
        exit();
    }

    $drive = new Google_Service_Drive($client);
    $fileMetadata = new Google_Service_Drive_DriveFile([
        'name' => $file_name
    ]);
    $content = file_get_contents($file_tmp);

    $file = $drive->files->create($fileMetadata, [
        'data' => $content,
        'mimeType' => $mime_type,
        'uploadType' => 'multipart',
        'fields' => 'id'
    ]);

    echo "<div style='padding: 20px; font-family: sans-serif;'>";
    echo "<p>✅ <strong>File uploaded successfully.</strong></p>";
    echo "<p><strong>File ID:</strong> " . $file->id . "</p>";
    echo "<a href='upload_form.php' class='btn btn-primary'>Upload Another</a> ";
    echo "<a href='list.php' class='btn btn-outline-secondary'>Back to List</a>";
    echo "</div>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload File to Google Drive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h4 class="mb-3">Upload File to Google Drive</h4>

    <form action="upload_form.php" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="file" class="form-label">Choose a file</label>
            <input class="form-control" type="file" name="file" id="file" required>
        </div>
        <div class="d-flex justify-content-between">
            <a href="list.php" class="btn btn-outline-secondary">Back to List</a>
            <button type="submit" class="btn btn-primary">Upload</button>
        </div>
    </form>
</div>

</body>
</html>
