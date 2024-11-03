<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members CRUD</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Members Management</h2>

    <!-- Add Member Button -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#memberModal">
        Add Member
    </button>

    <!-- DataTable -->
    <table id="membersTable" class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Image</th>
            <th>Release Date</th>
            <th>Summary</th>
            <th>Actions</th>
        </tr>
        </thead>
    </table>
</div>

<!-- Modal for Add/Edit Member -->
<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Member Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="memberForm">
                    <input type="hidden" id="memberId">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image URL</label>
                        <input type="text" class="form-control" id="image" required>
                    </div>
                    <div class="mb-3">
                        <label for="release_at" class="form-label">Release Date</label>
                        <input type="date" class="form-control" id="release_at" required>
                    </div>
                    <div class="mb-3">
                        <label for="summary" class="form-label">Summary</label>
                        <textarea class="form-control" id="summary" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveMember">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>