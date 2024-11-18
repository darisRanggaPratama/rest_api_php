<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Start session di awal
session_start();
// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SESSION['status'] != "sudah_login") {
    header("location:index.php");
}

require_once 'config/check-db.php';
require_once 'models/Member.php';

global $isAllRows, $limit, $search;

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    $member = Member::getInstance($db);

    // Get Search parameter
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Get and validate limit parameter
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    $isAllRows = ($limit === 0);
    if ($isAllRows) {
        // 0 represents: All
        $limit = PHP_INT_MAX;
    }

// Ambil parameter pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    $members = $member->getAll($search, $page, $limit);
} catch (Exception $e) {
    $error = $e->getMessage();
    echo 'Error: ' . $error;
}


// Debug
//Database::getInstance()->printDie($members);
//Database::getInstance()->dumpDie($members);
//$gotConnect = $database->getConnection();
//$wasConnect = $database->isConnected();
//$gotHost = $database->getHost();
//$gotDatabase = $database->getDatabase();
//$testedConnect = $database->testConnection();

//var_dump($gotConnect);
//var_dump($wasConnect);
//var_dump($gotHost);
//var_dump($gotDatabase);
//var_dump($testedConnect);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Avengers Members</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/table.css" rel="stylesheet">
</head>

<body>
<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button class="btn" id="sidebar-toggle">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>

        <!-- Content -->
        <div class="container-fluid mt-4">
            <h2 class="mb-4">Avengers Members</h2>

            <?php
            if (isset($error)):
                ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Search and Actions Row -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-1">
                    <select class="form-select" id="rowLimit" name="limit">
                        <option value="1" <?php echo $limit == 1 ? 'selected' : ''; ?>>1 row</option>
                        <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5 rows</option>
                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10 rows</option>
                        <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15 rows</option>
                        <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25 rows</option>
                        <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 rows</option>
                        <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 rows</option>
                        <option value="0" <?php echo $isAllRows ? 'selected' : ''; ?>>All rows</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <form method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   placeholder="Search by title, date, or summary"
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   autocapitalize="off"
                                   autocomplete="off">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                        </div>
                        <!-- Add hidden input for current limit -->
                        <input type="hidden" name="limit" value="<?php echo $isAllRows ? '0' : $limit; ?>"
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>
                <div class="col-md-7 text-md-end mt-3 mt-md-0">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle me-2" type="button" id="dropdownMenuButton"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <button class="btn btn-primary me-2" data-bs-toggle="modal"
                                        data-bs-target="#addMemberModal">
                                    Add New Member
                                </button>
                            </li>
                            <li>
                                <button class="btn btn-success me-2" data-bs-toggle="modal"
                                        data-bs-target="#csvUploadModal">
                                    Upload CSV
                                </button>
                            </li>
                            <li>
                                <a href="api/download-csv.php" class="btn btn-info text-white">
                                    Download CSV
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Members Table -->
            <div class="table-responsive">
                <?php if (empty($members['data'])): ?>
                    <div class="alert alert-info">
                        No members found. Please add new member or try different search terms.
                    </div>
                <?php else: ?>
                    <?php $idRow = 1; ?>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Release Date</th>
                            <th>Summary</th>
                            <th class="actions-header">Edit</th>
                            <th class="actions-header">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($members['data'] as $row): ?>
                            <tr data-id="<?php echo htmlspecialchars($row['id'] ?? ''); ?>">
                                <td><?php echo $idRow++ ?></td>
                                <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['title'] ?? ''); ?></td>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['image']); ?>"
                                             class="img-thumbnail"
                                             width="100"
                                             alt="<?php echo htmlspecialchars($row['title'] ?? 'Member image'); ?>">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['release_at'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['summary'] ?? ''); ?></td>
                                <td class="action-column">
                                    <?php if (isset($row['id'])): ?>
                                        <button class="btn btn-sm btn-warning edit-btn"
                                                data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                            <i class="far fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td class="action-column">
                                    <?php if (isset($row['id'])): ?>
                                        <button class="btn btn-sm btn-danger delete-btn"
                                                data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                            <i class="fas fa-backspace"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if (isset($members['total_pages']) && $members['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $members['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link"
                                           href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?><?php echo !empty($search) ? '&search=' . htmlspecialchars($search) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/modals.php'; ?>
<?php include 'includes/csv-modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script src="js/delete-handler.js"></script>
<script src="js/edit-handler.js"></script>
<script src="js/csv-handlers.js"></script>
<!-- New JavaScript for row interaction -->
<script src="js/table.js"></script>
</body>

</html>