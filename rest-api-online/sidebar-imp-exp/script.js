$(document).ready(function () {
    // Initialize DataTable with error handling
    let table = $('#membersTable').DataTable({
        ajax: {
            url: 'ajax_handler.php?action=list',
            dataSrc: '',
            error: function (xhr, error, thrown) {
                console.error('DataTable error:', error);
                alert('Error loading data. Please try again later.');
            }
        },
        columns: [
            {data: 'id'},
            {data: 'title'},
            {
                data: 'image',
                render: function (data) {
                    if (!data) return 'No image';
                    return `<img src="${data}" height="50" onerror="this.onerror=null;this.src='placeholder.jpg';">`;
                }
            },
            {
                data: 'release_at',
                render: function (data) {
                    return data ? moment(data).format('DD/MM/YYYY') : 'No date';
                }
            },
            {data: 'summary'},
            {
                data: null,
                render: function (data) {
                    return `
                        <button onclick="editMember(${data.id})" class="btn btn-sm btn-primary">Edit</button>
                        <button onclick="deleteMember(${data.id})" class="btn btn-sm btn-danger">Delete</button>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            'excel',
            {
                text: 'Import CSV',
                action: function () {
                    $('#importModal').modal('show');
                }
            },
            {
                text: 'Add New',
                action: function () {
                    $('#addModal').modal('show');
                }
            }
        ]
    });

    // Function to edit member
    window.editMember = function(id) {
        if (!id) {
            alert('Error: Invalid member ID');
            return;
        }

        // Fetch member data
        $.ajax({
            url: 'ajax_handler.php?action=get&id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                // Populate form fields
                $('#edit-title').val(data.title);
                $('#edit-release-date').val(data.release_at);
                $('#edit-summary').val(data.summary);
                $('#edit-id').val(data.id);

                // Show current image if exists
                if (data.image) {
                    $('#current-image').html(`
                        <div class="mb-2">
                            <label>Current Image:</label>
                            <img src="${data.image}" height="50" class="d-block">
                        </div>
                    `);
                } else {
                    $('#current-image').html('No current image');
                }

                // Show modal
                $('#editModal').modal('show');
            },
            error: function(xhr) {
                console.error('Get member error:', xhr);
                alert('Error loading member data');
            }
        });
    };

    // Handle edit form submission
    $('#editForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: 'ajax_handler.php?action=update',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#editModal').modal('hide');
                table.ajax.reload();
                alert('Member updated successfully!');
            },
            error: function(xhr) {
                alert('Error updating member: ' + xhr.responseText);
            }
        });
    });

    // Delete member function
    window.deleteMember = function(id) {
        if (confirm('Are you sure you want to delete this member?')) {
            $.ajax({
                url: 'ajax_handler.php?action=delete',
                method: 'POST',
                data: {id: id},
                success: function(response) {
                    table.ajax.reload();
                    alert('Member deleted successfully!');
                },
                error: function(xhr) {
                    alert('Error deleting member');
                }
            });
        }
    };

    // Handle add form submission
    $('#addForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: 'ajax_handler.php?action=create',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#addModal').modal('hide');
                table.ajax.reload();
                alert('Member added successfully!');
                $('#addForm')[0].reset();
            },
            error: function(xhr) {
                alert('Error adding member: ' + xhr.responseText);
            }
        });
    });

    // Handle CSV import
    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: 'ajax_handler.php?action=import',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#importModal').modal('hide');
                table.ajax.reload();
                alert('Data imported successfully!');
                $('#importForm')[0].reset();
            },
            error: function(xhr) {
                alert('Error importing data: ' + xhr.responseText);
            }
        });
    });
});