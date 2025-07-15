<?php
// bale nag fufufnction naman ng maayos yung delete na akapag dinilete ay ayun nadedelete nga anuba.
require __DIR__ . '/vendor/autoload.php';
session_start();

if (!isset($_GET['id'])) {
    exit("❌ Missing file ID.");
}

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Drive::DRIVE_FILE);
$client->setAccessType('offline');
$client->setRedirectUri('http://localhost/googledrive/upload.php');
$client->setAccessToken($_SESSION['access_token']);

$drive = new Google_Service_Drive($client);

try {
    $drive->files->delete($_GET['id']);
    header('Location: list.php?deleted=1');
} catch (Exception $e) {
    echo "❌ Failed to delete file: " . $e->getMessage();
}
