<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="container mt-5">
        <h2 class="mb-4">Data Avengers</h2>

        <div class="alert alert-info mb-3">
            Klik baris untuk memilih data yang akan di-copy. Gunakan Ctrl/Cmd + klik untuk memilih multiple baris.
        </div>
        <table id="tabelData" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th>Release</th>
                    <th>Summary</th>
                </tr>
            </thead>
        </table>

        <!-- Upload Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Upload CSV</h5>
            </div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" id="csvFile" accept=".csv" required>
                        <small class="text-muted">Format: title;image;release_at;summary</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
                <div id="uploadMessage" class="alert mt-3" style="display: none;"></div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>