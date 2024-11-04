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

    // Function untuk edit member
    window.editMember = function(id) {
        if (!id) {
            console.error('Error: Member ID is required');
            return;
        }

        // Fetch member data
        $.ajax({
            url: 'ajax_handler.php',
            method: 'GET',
            data: {
                action: 'get',
                id: id
            },
            dataType: 'json',
            success: function(data) {
                if (data) {
                    // Populate form fields
                    $('#edit-id').val(data.id);
                    $('#edit-title').val(data.title);
                    $('#edit-release-date').val(data.release_at);
                    $('#edit-summary').val(data.summary);

                    // Show current image if exists
                    if (data.image) {
                        $('#current-image').html(`
                        <div class="mb-2">
                            <label class="form-label">Current Image:</label>
                            <img src="${data.image}" 
                                 class="d-block img-thumbnail" 
                                 style="max-height: 150px;"
                                 alt="Current image">
                        </div>
                    `);
                    } else {
                        $('#current-image').html('<p class="text-muted">No current image</p>');
                    }

                    // Show modal
                    $('#editModal').modal('show');
                } else {
                    alert('Error: Member data not found');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Error loading member data. Please try again.');
            }
        });
    };

// Handle edit form submission
    $('#editForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('action', 'update');

        $.ajax({
            url: 'ajax_handler.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    $('#membersTable').DataTable().ajax.reload();
                    alert('Member updated successfully!');
                } else {
                    alert('Error updating member: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Error updating member. Please try again.');
            }
        });
    });

// Reset form when modal is closed
    $('#editModal').on('hidden.bs.modal', function() {
        $('#editForm')[0].reset();
        $('#current-image').html('');
    });

    // Delete member function
    window.deleteMember = function(id) {
        if (!id) {
            alert('Error: Invalid member ID');
            return;
        }

        if (confirm('Are you sure you want to delete this member?')) {
            $.ajax({
                url: 'ajax_handler.php',
                method: 'POST',
                data: {
                    action: 'delete',
                    id: id
                },
                success: function(response) {
                    table.ajax.reload();
                    alert('Member deleted successfully!');
                },
                error: function(xhr) {
                    alert('Error deleting member: ' + (xhr.responseText || 'Unknown error'));
                }
            });
        }
    };

    // Handle add form submission
    $('#addForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('action', 'create');

        $.ajax({
            url: 'ajax_handler.php',
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
                alert('Error adding member: ' + (xhr.responseText || 'Unknown error'));
            }
        });
    });

    // Handle CSV import
    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('action', 'import');

        $.ajax({
            url: 'ajax_handler.php',
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
                alert('Error importing data: ' + (xhr.responseText || 'Unknown error'));
            }
        });
    });
});