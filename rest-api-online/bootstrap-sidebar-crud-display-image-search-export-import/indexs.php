<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
require_once 'models/Member.php';

$database = new Database();
$db = $database->getConnection();
$member = new Member($db);

$search = isset($_GET['search']) ? $_GET['search'] : '';
$members = $member->getAll($search);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avengers Members</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <style>
        /* Custom styles for table layout */
        .table th.actions-header {
            min-width: 100px;
        }
        .edit-btn {
            min-width: 60px;
        }
        .delete-btn {
            min-width: 70px;
        }
        .table td {
            vertical-align: middle;
        }
        .action-column {
            text-align: center;
            white-space: nowrap;
        }
        
        /* New styles for row highlighting */
        .table tbody tr {
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1) !important;
            transform: scale(1.002);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Style untuk efek klik */
        .table tbody tr.selected {
            background-color: rgba(0, 123, 255, 0.2) !important;
        }
        
        /* Memastikan striped masih terlihat bagus dengan hover */
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.02);
        }
        
        .table-striped > tbody > tr:nth-of-type(odd):hover {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }
    </style>
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">Avengers Members</h2>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            Add New Member
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#csvUploadModal">
                            Upload CSV
                        </button>
                        <a href="api/download-csv.php" class="btn btn-info text-white">
                            Download CSV
                        </a>
                    </div>
                </div>

                <!-- Search Form -->
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by title, date, or summary" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </form>

                <!-- Members Table -->
                <div class="table-responsive">
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
                            <?php foreach ($members as $row): ?>
                                <tr data-id="<?php echo $row['id']; ?>">
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><img src="<?php echo htmlspecialchars($row['image']); ?>" class="img-thumbnail" width="100"></td>
                                    <td><?php echo htmlspecialchars($row['release_at']); ?></td>
                                    <td><?php echo htmlspecialchars($row['summary']); ?></td>
                                    <td class="action-column">
                                        <button class="btn btn-sm btn-warning edit-btn" data-id="<?php echo $row['id']; ?>">Edit</button>
                                    </td>
                                    <td class="action-column">
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/modals.php'; ?>
    <?php include 'includes/csv-modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/csv-handlers.js"></script>
    
    <!-- New JavaScript for row interaction -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableRows = document.querySelectorAll('.table tbody tr');
        
        tableRows.forEach(row => {
            // Mencegah event bubbling untuk tombol
            row.querySelectorAll('button').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            });
            
            // Menambahkan efek klik pada baris
            row.addEventListener('click', function() {
                // Menghapus kelas selected dari semua baris
                tableRows.forEach(r => r.classList.remove('selected'));
                // Menambahkan kelas selected ke baris yang diklik
                this.classList.add('selected');
            });
        });
    });
    </script>
</body>

</html>