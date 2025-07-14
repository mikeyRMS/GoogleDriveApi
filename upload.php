<?php
require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
    $mime_type = mime_content_type($file_tmp);

    // Google API Auth
    $client = new Google_Client();
    $client->setAuthConfig('credentials.json');
    $client->addScope(Google_Service_Drive::DRIVE_FILE);
    $client->setRedirectUri('http://localhost/googledrive/upload.php');
    $client->setAccessType('offline');

    session_start();
    if (!isset($_SESSION['access_token']) && !isset($_GET['code'])) {
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit();
    }

    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $token;
        header('Location: upload.php');
        exit();
    }

    if (isset($_SESSION['access_token'])) {
        $client->setAccessToken($_SESSION['access_token']);

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

        echo "<p>✅ File uploaded to Google Drive. File ID: " . $file->id . "</p>";
        echo "<a href='index.html'>Upload Another</a>";
    }
} else {
    echo "<p>No file selected.</p>";
}
