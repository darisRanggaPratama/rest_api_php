<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avengers Database</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header p-3">
            <h3>Avengers DB</h3>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#">
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    Members
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    Settings
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div class="content">
        <button id="sidebar-toggle" class="btn btn-dark">
            Toggle Sidebar
        </button>

        <div class="container mt-4">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col">
                            <h4>Members List</h4>
                        </div>
                        <div class="col text-end">
                            <button type="button" class="btn btn-success" id="create_record">
                                Add New Member
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="memberTable" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Release Date</th>
                            <th>Summary</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal" id="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="member_form" enctype="multipart/form-data">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" id="image" class="form-control">
                        <input type="hidden" name="hidden_image" id="hidden_image">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Release Date</label>
                        <input type="date" name="release_at" id="release_at" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Summary</label>
                        <textarea name="summary" id="summary" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" id="action" value="create">
                    <input type="hidden" name="hidden_id" id="hidden_id">
                    <input type="submit" name="form_action" id="form_action" class="btn btn-primary" value="Create">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="scripts.js"></script>

</body>
</html>