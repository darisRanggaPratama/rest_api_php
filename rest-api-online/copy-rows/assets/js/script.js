// assets/js/scripts.js
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#membersTable').DataTable({
        ajax: {
            url: 'index.php?action=getData',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'title' },
            {
                data: 'image',
                render: function(data) {
                    return '<img src="' + data + '" height="50">';
                }
            },
            {
                data: 'release_at',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            { data: 'summary' }
        ],
        dom: 'Bfrtip',
        buttons: [
            'excel',
            'selectAll',
            'selectNone'
        ],
        select: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10
    });

    // Handle CSV Import
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: 'index.php?action=importCSV',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    alert('Import successful!');
                    table.ajax.reload();
                } else {
                    alert('Import failed: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred during import');
            }
        });
    });
});
