<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'check-db.php';
require_once 'Member.php';

$database = Database::getInstance();
$db = $database->getConnection();
$member = Member::getInstance($db);

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Ambil parameter pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
// pastikan $page minimal 1
$page = max(1, $page);

$members = $member->getAll($search, $page, $limit);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avengers Members</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="table.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

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

                <!-- Search and Actions Row -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6">
                        <form>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by title, date, or summary" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            Add New Member
                        </button>
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#csvUploadModal">
                            Upload CSV
                        </button>
                        <a href="api/download-csv.php" class="btn btn-info text-white">
                            Download CSV
                        </a>
                    </div>
                </div>

                <!-- Members Table -->
                <div class="table-responsive">
                    <?php if (empty($members['data'])): ?>
                        <div class="alert alert-info">
                            No members found. Please add new member or try different search terms.
                        </div>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
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
                                                    Edit
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-column">
                                            <?php if (isset($row['id'])): ?>
                                                <button class="btn btn-sm btn-danger delete-btn"
                                                    data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                    Delete
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
                                        <li class="page-item <?php echo ($i == ($members['page'] ?? 1)) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . htmlspecialchars($search) : ''; ?>">
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

    <?php include 'modals.php'; ?>
    <?php include 'csv-modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>
    <script src="csv-handlers.js"></script>
    <!-- New JavaScript for row interaction -->
    <script src="table.js"></script>
</body>

</html>