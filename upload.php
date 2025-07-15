<?php
//the structure of code is  amazing but, the vendor/autoload.php is missing..

require __DIR__ . '/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Drive::DRIVE_FILE);
$client->setAccessType('offline');
$client->setRedirectUri('http://localhost/googledrive/upload.php');

if (!isset($_SESSION['access_token']) && !isset($_GET['code'])) {
    // First time access → ask for Google login
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit();
}

if (isset($_GET['code'])) {
    // Google redirected back with auth code → exchange for token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!is_array($token)) {
        echo "<p>❌ Error: fetchAccessTokenWithAuthCode() returned null or invalid data.</p>";
        var_dump($token);
        exit();
    }

    if (isset($token['error'])) {
        echo "<p>❌ Google returned an error: " . htmlspecialchars($token['error']) . "</p>";
        var_dump($token);
        exit();
    }

    $_SESSION['access_token'] = $token;
    header('Location: upload_form.php');
    exit();
}
