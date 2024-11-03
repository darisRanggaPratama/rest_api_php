$(document).ready(function() {
    // Initialize DataTable
    var dataTable = $('#memberTable').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            url: "operations.php",
            type: "POST",
            data: { action: 'fetch' }
        },
        "columns": [
            { "data": 0 }, // id
            { "data": 1 }, // title
            { "data": 2 }, // image
            { "data": 3 }, // release_at
            { "data": 4 }, // summary
            { "data": 5 }, // edit button
            { "data": 6 }  // delete button
        ]
    });

    // Sidebar Toggle
    $('#sidebar-toggle').click(function() {
        $('.sidebar').toggleClass('collapsed');
    });

    // Create Record
    $('#create_record').click(function() {
        $('#modal_title').text('Add New Member');
        $('#action').val('create');
        $('#form_action').val('Create');
        $('#member_form')[0].reset();
        $('#modal').modal('show');
    });

    // Form Submit
    $('#member_form').on('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        formData.append('action', $('#action').val());

        $.ajax({
            url: "operations.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                $('#modal').modal('hide');
                dataTable.ajax.reload();
                alert(data);
            }
        });
    });

    // Edit Record
    $(document).on('click', '.edit', function() {
        var id = $(this).attr('id');
        $.ajax({
            url: "operations.php",
            method: "POST",
            data: { action: 'get_single', id: id },
            dataType: "json",
            success: function(data) {
                $('#title').val(data.title);
                $('#hidden_image').val(data.image);
                $('#release_at').val(data.release_at);
                $('#summary').val(data.summary);
                $('#hidden_id').val(id);
                $('#modal_title').text('Edit Member');
                $('#action').val('update');
                $('#form_action').val('Update');
                $('#modal').modal('show');
            }
        });
    });

    // Delete Record
    $(document).on('click', '.delete', function() {
        var id = $(this).attr('id');
        if(confirm("Are you sure you want to delete this?")) {
            $.ajax({
                url: "operations.php",
                method: "POST",
                data: { action: 'delete', id: id },
                success: function(data) {
                    dataTable.ajax.reload();
                    alert(data);
                }
            });
        }
    });
});