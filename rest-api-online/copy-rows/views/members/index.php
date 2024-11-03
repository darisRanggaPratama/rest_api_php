<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Management</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/select/1.3.4/css/select.bootstrap5.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Members Management</h2>

    <!-- Import Form -->
    <div class="mb-4">
        <form id="importForm" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4">
                    <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                </div>
                <div class="col-md-3">
                    <select name="delimiter" class="form-control">
                        <option value=",">Comma (,)</option>
                        <option value=";">Semicolon (;)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Import CSV</button>
                </div>
            </div>
        </form>
    </div>

    <!-- DataTable -->
    <table id="membersTable" class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Image</th>
            <th>Release Date</th>
            <th>Summary</th>
        </tr>
        </thead>
    </table>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/select/1.3.4/js/dataTables.select.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>