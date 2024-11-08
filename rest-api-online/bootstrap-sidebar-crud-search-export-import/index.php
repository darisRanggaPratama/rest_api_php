<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Handle Delete
if(isset($_POST['delete'])) {
    $id = $_POST['id'];
    deleteMember($conn, $id);
    header("Location: index.php");
    exit();
}

// Handle Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$members = getAllMembers($conn, $search);

// Include header
include 'includes/header.php';
?>

    <!-- Main Content -->
    <div class="container-fluid">
        <h2>Members Management</h2>

        <!-- Search Form -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form class="d-flex" method="GET">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <a href="add.php" class="btn btn-success">Add New Member</a>
                <a href="import.php" class="btn btn-info ms-2">Import CSV</a>
                <a href="export.php" class="btn btn-info ms-2">Export CSV</a>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th>Release Date</th>
                    <th>Summary</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($members as $member): ?>
                    <tr>
                        <td><?= htmlspecialchars($member['id']) ?></td>
                        <td><?= htmlspecialchars($member['title']) ?></td>
                        <td>
                            <?php if($member['image']): ?>
                                <img src="<?= htmlspecialchars($member['image']) ?>" alt="Member Image" style="max-width: 100px;">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($member['release_at']) ?></td>
                        <td><?= htmlspecialchars($member['summary']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $member['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>