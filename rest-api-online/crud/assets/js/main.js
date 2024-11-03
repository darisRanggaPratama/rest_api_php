$(document).ready(function() {
    let table = $('#membersTable').DataTable({
        ajax: {
            url: '../controllers/MemberController.php',
            type: 'POST',
            data: {action: 'getAll'}
        },
        columns: [
            {data: 'id'},
            {data: 'title'},
            {
                data: 'image',
                render: function(data) {
                    return `<img src="${data}" class="img-thumbnail">`;
                }
            },
            {data: 'release_at'},
            {data: 'summary'},
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${data.id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}">Delete</button>
                    `;
                }
            }
        ]
    });

    // Save Member (Create/Update)
    $('#saveMember').click(function() {
        let id = $('#memberId').val();
        let data = {
            title: $('#title').val(),
            image: $('#image').val(),
            release_at: $('#release_at').val(),
            summary: $('#summary').val(),
            action: id ? 'update' : 'create'
        };

        if(id) data.id = id;

        $.ajax({
            url: '../controllers/MemberController.php',
            type: 'POST',
            data: data,
            success: function(response) {
                let result = JSON.parse(response);
                if(result.status === 'success') {
                    $('#memberModal').modal('hide');
                    table.ajax.reload();
                    $('#memberForm')[0].reset();
                } else {
                    alert('Operation failed!');
                }
            }
        });
    });

    // Edit Member
    $('#membersTable').on('click', '.edit-btn', function() {
        let data = table.row($(this).parents('tr')).data();
        $('#memberId').val(data.id);
        $('#title').val(data.title);
        $('#image').val(data.image);
        $('#release_at').val(data.release_at);
        $('#summary').val(data.summary);
        $('#memberModal').modal('show');
    });

    // Delete Member
    $('#membersTable').on('click', '.delete-btn', function() {
        if(confirm('Are you sure you want to delete this member?')) {
            let id = $(this).data('id');
            $.ajax({
                url: '../controllers/MemberController.php',
                type: 'POST',
                data: {
                    action: 'delete',
                    id: id
                },
                success: function(response) {
                    let result = JSON.parse(response);
                    if(result.status === 'success') {
                        table.ajax.reload();
                    } else {
                        alert('Delete failed!');
                    }
                }
            });
        }
    });

    // Reset form when modal is closed
    $('#memberModal').on('hidden.bs.modal', function() {
        $('#memberForm')[0].reset();
        $('#memberId').val('');
    });
});