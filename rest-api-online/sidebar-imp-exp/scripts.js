$(document).ready(function() {
    // Inisialisasi DataTable
    let membersTable = $('#membersTable').DataTable({
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
                    return data ? `<img src="${data}" alt="Member Image" style="height:50px;">` : 'No Image';
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
                orderable: false,
                render: function(data) {
                    return `
                        <button onclick="editMember(${data.id})" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="deleteMember(${data.id})" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-plus"></i> Add New',
                className: 'btn btn-success',
                action: function() {
                    $('#addModal').modal('show');
                }
            },
            {
                text: '<i class="fas fa-file-import"></i> Import CSV',
                className: 'btn btn-info',
                action: function() {
                    $('#importModal').modal('show');
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export Excel',
                className: 'btn btn-primary',
                title: 'Members Data'
            }
        ],
        responsive: true,
        order: [[0, 'desc']]
    });

    // Handle Add Form Submit
    $('#addForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: 'ajax_handler.php?action=create',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#addModal').modal('hide');
                $('#addForm')[0].reset();
                membersTable.ajax.reload();
                showAlert('Success', 'Member added successfully!', 'success');
            },
            error: function(xhr) {
                showAlert('Error', xhr.responseText, 'error');
            }
        });
    });

    // Handle Edit Form Submit
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: 'ajax_handler.php?action=update',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#editModal').modal('hide');
                membersTable.ajax.reload();
                showAlert('Success', 'Member updated successfully!', 'success');
            },
            error: function(xhr) {
                showAlert('Error', xhr.responseText, 'error');
            }
        });
    });

    // Handle Import Form Submit
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: 'ajax_handler.php?action=import',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#importModal').modal('hide');
                $('#importForm')[0].reset();
                membersTable.ajax.reload();
                showAlert('Success', 'Data imported successfully!', 'success');
            },
            error: function(xhr) {
                showAlert('Error', xhr.responseText, 'error');
            }
        });
    });
});

// Function to edit member
function editMember(id) {
    $.ajax({
        url: 'ajax_handler.php?action=get&id=' + id,
        type: 'GET',
        success: function(response) {
            let data = JSON.parse(response);
            $('#editId').val(data.id);
            $('#editTitle').val(data.title);
            $('#editReleaseAt').val(data.release_at);
            $('#editSummary').val(data.summary);
            $('#editModal').modal('show');
        },
        error: function(xhr) {
            showAlert('Error', xhr.responseText, 'error');
        }
    });
}

// Function to delete member
function deleteMember(id) {
    if(confirm('Are you sure you want to delete this member?')) {
        $.ajax({
            url: 'ajax_handler.php?action=delete',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                $('#membersTable').DataTable().ajax.reload();
                showAlert('Success', 'Member deleted successfully!', 'success');
            },
            error: function(xhr) {
                showAlert('Error', xhr.responseText, 'error');
            }
        });
    }
}

// Function to show alerts
function showAlert(title, message, type) {
    let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    let alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <strong>${title}!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Remove existing alerts
    $('.alert').remove();

    // Add new alert
    $('.main-content').prepend(alertHtml);

    // Auto hide after 3 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}