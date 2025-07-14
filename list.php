<?php
require __DIR__ . '/vendor/autoload.php';
session_start();

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Drive::DRIVE_FILE);
$client->setAccessType('offline');
$client->setRedirectUri('http://localhost/googledrive/upload.php');

if (!isset($_SESSION['access_token'])) {
    echo "<p>Please <a href='upload.php'>login with Google</a> first.</p>";
    exit();
}

$client->setAccessToken($_SESSION['access_token']);
$drive = new Google_Service_Drive($client);

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchText = htmlspecialchars($searchQuery);

try {
    $params = [
        'fields' => 'files(id, name, mimeType, modifiedTime)',
        'pageSize' => 100,
    ];

    if ($searchQuery !== '') {
        $params['q'] = "name contains '{$searchQuery}'";
    }

    $results = $drive->files->listFiles($params);
    $files = $results->getFiles();
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Google Drive Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h4 class="mb-3">Google Drive Files</h4>

    <form method="GET" class="mb-3 d-flex gap-2">
        <input type="text" name="q" class="form-control" placeholder="Search by name..." value="<?= $searchText ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="upload_form.php" class="btn btn-success">Upload</a>
    </form>

    <?php if (empty($files)): ?>
        <div class="alert alert-light">No files found<?= $searchQuery ? " for <strong>$searchText</strong>" : "" ?>.</div>
    <?php else: ?>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Modified</th>
                    <th width="160">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?= htmlspecialchars($file->getName()) ?></td>
                        <td><?= $file->getMimeType() ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($file->getModifiedTime())) ?></td>
                        <td>
                            <a href="https://drive.google.com/file/d/<?= $file->getId() ?>/view" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="delete.php?id=<?= $file->getId() ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this file?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
