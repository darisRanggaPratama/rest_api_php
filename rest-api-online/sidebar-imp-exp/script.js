$(document).ready(function() {
    // Initialize DataTable
    var table = $('#membersTable').DataTable({
        ajax: {
            url: 'ajax_handler.php?action=list',
            dataSrc: ''
        },
        columns: [
            { data: 'id' },
            { data: 'title' },
            {
                data: 'image',
                render: function(data) {
                    return `<img src="${data}" height="50">`;
                }
            },
            {
                data: 'release_at',
                render: function(data) {
                    return moment(data).format('DD/MM/YYYY');
                }
            },
            { data: 'summary' },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-primary btn-action" onclick="editMember(${data.id})">Edit</button>
                        <button class="btn btn-sm btn-danger btn-action" onclick="deleteMember(${data.id})">Delete</button>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            'excel',
            {
                text: 'Import CSV',
                action: function() {
                    $('#importModal').modal('show');
                }
            },
            {
                text: 'Add New',
                action: function() {
                    $('#addModal').modal('show');
                }
            }
        ]
    });

    // Handle form submission for adding new member
    $('#addForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax_handler.php?action=create',
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                $('#addModal').modal('hide');
                table.ajax.reload();
                alert('Member added successfully!');
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });

    // Handle CSV import
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax_handler.php?action=import',
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                $('#importModal').modal('hide');
                table.ajax.reload();
                alert('Data imported successfully!');
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });
});

// Function to edit member
function editMember(id) {
    $.get('ajax_handler.php?action=get&id=' + id, function(data) {
        $('#editId').val(data.id);
        $('#editTitle').val(data.title);
        $('#editReleaseAt').val(data.release_at);
        $('#editSummary').val(data.summary);
        $('#editModal').modal('show');
    });
}

// Function to delete member
function deleteMember(id) {
    if(confirm('Are you sure you want to delete this member?')) {
        $.post('ajax_handler.php?action=delete', {id: id}, function() {
            $('#membersTable').DataTable().ajax.reload();
        });
    }
}

// Handle edit form submission
$('#editForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'ajax_handler.php?action=update',
        method: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        success: function(response) {
            $('#editModal').modal('hide');
            $('#membersTable').DataTable().ajax.reload();
            alert('Member updated successfully!');
        },
        error: function(xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
});