<?php
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/csv_functions.php';

$message = '';
$messageType = '';
$debugInfo = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    // Debug uploaded file
    $debugInfo[] = "File upload details:";
    $debugInfo[] = "Name: " . $file['name'];
    $debugInfo[] = "Type: " . $file['type'];
    $debugInfo[] = "Size: " . $file['size'] . " bytes";
    $debugInfo[] = "Temp path: " . $file['tmp_name'];

    // Validate file
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileExtension !== 'csv') {
        $message = "Please upload a valid CSV file.";
        $messageType = 'danger';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "Upload error occurred. Code: " . $file['error'];
        $messageType = 'danger';
    } else {
        // Process the CSV file
        $result = processCsvImport($conn, $file['tmp_name']);

        // Add debug info
        $debugInfo = array_merge($debugInfo, $result['debug'] ?? []);

        if ($result['errors'] === 0 && $result['success'] > 0) {
            $message = "Successfully imported {$result['success']} records.";
            $messageType = 'success';
        } else {
            $message = "Import issues found: " . implode(" ", $result['messages']);
            $messageType = 'danger';
        }
    }
}

include 'includes/header.php';
?>

    <div class="container-fluid">
        <h2>Import Members Data</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Debug Information (only show in development) -->
        <?php if (!empty($debugInfo) && isset($_GET['debug'])): ?>
            <div class="alert alert-info">
                <h4>Debug Information:</h4>
                <pre><?= htmlspecialchars(print_r($debugInfo, true)) ?></pre>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">CSV File</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>

                    <div class="mb-3">
                        <h5>CSV Format Requirements:</h5>
                        <ul>
                            <li>File must be in CSV format with semicolon (;) as delimiter</li>
                            <li>First row must contain headers: title;image;release_at;summary</li>
                            <li>Supported date formats:
                                <ul>
                                    <li>DD/MM/YYYY (e.g., 31/12/2023)</li>
                                    <li>MM/DD/YYYY (e.g., 12/31/2023)</li>
                                    <li>YYYY-MM-DD (e.g., 2023-12-31)</li>
                                </ul>
                            </li>
                            <li>Required fields: title, release_at, summary</li>
                            <li>Image field is optional</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h5>Sample CSV Content:</h5>
                        <pre>title;image;release_at;summary
Iron Man;http://example.com/ironman.jpg;25/04/2008;Tony Stark becomes Iron Man
Thor;http://example.com/thor.jpg;2011-05-06;Thor is banished to Earth
Black Widow;http://example.com/blackwidow.jpg;07/09/2021;Natasha Romanoff's origin story</pre>
                    </div>

                    <button type="submit" class="btn btn-primary">Import Data</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>