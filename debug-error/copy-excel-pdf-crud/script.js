$(document).ready(function() {
    // Initialize DataTable
    let table = $('#tabelData').DataTable({
        ajax: {
            url: 'data.php',
            type: 'GET',
            dataSrc: function(json) {
                console.log('DataTables Response:', json);
                if (json.error) {
                    alert('Error: ' + json.error + '\n\nDetail debug: ' + JSON.stringify(json.debug));
                    return [];
                }
                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('Ajax Error:', { xhr, error, thrown });
                alert('Error Detail:\n' +
                    'Status: ' + xhr.status + '\n' +
                    'Error: ' + error + '\n' +
                    'Message: ' + thrown);
            }
        },
        columns: [
            { data: 'id' },
            { data: 'title' },
            {
                data: 'image',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return data ? `<img src="${data}" alt="poster" style="max-height: 50px;">` : 'No Image';
                    }
                    return data;
                }
            },
            {
                data: 'release_at',
                render: function(data, type, row) {
                    if (type === 'display' && data) {
                        return new Date(data).toLocaleDateString('id-ID');
                    }
                    return data;
                }
            },
            { data: 'summary' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}">Hapus</button>
                    `;
                }
            }
        ],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
        },
        processing: true,
        pageLength: 10,
        dom: 'Bfrtip',
        select: true,
        buttons: [
            {
                extend: 'copy',
                text: 'Copy',
                className: 'btn btn-primary btn-sm',
                exportOptions: {
                    modifier: {
                        selected: true
                    }
                },
                init: function(api, node, config) {
                    $(node).addClass('disabled');
                }
            },
            {
                extend: 'excel',
                text: 'Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: 'PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: 'Print',
                className: 'btn btn-info btn-sm'
            }
        ]
    });

    // Handle copy button state
    table.on('select deselect', function() {
        let selectedRows = table.rows({ selected: true }).count();
        let copyButton = $('.buttons-copy');
        copyButton.toggleClass('disabled', selectedRows === 0);
    });

    // Reset form when modal is closed
    $('#formModal').on('hidden.bs.modal', function() {
        $('#dataForm')[0].reset();
        $('#id').val('');
        $('#action').val('create');
        $('.modal-title').text('Form Data');
    });

    // Handle edit button click
    $('#tabelData').on('click', '.edit-btn', function() {
        let id = $(this).data('id');

        // Fetch data for editing
        $.ajax({
            url: 'operations.php',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                if (response.status === 'success') {
                    let data = response.data;
                    $('#id').val(data.id);
                    $('#title').val(data.title);
                    $('#image').val(data.image);
                    $('#release_at').val(data.release_at);
                    $('#summary').val(data.summary);
                    $('#action').val('update');
                    $('.modal-title').text('Edit Data');
                    $('#formModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });

    // Handle delete button click
    $('#tabelData').on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        $('#deleteModal').data('id', id).modal('show');
    });

    // Handle delete confirmation
    $('#confirmDelete').click(function() {
        let id = $('#deleteModal').data('id');

        $.ajax({
            url: 'operations.php?id=' + id,
            type: 'DELETE',
            success: function(response) {
                if (response.status === 'success') {
                    $('#deleteModal').modal('hide');
                    table.ajax.reload();
                    alert(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });

    // Handle save button click
    $('#saveButton').click(function() {
        // Validate form
        if (!$('#dataForm')[0].checkValidity()) {
            $('#dataForm')[0].reportValidity();
            return;
        }

        let formData = new FormData($('#dataForm')[0]);

        $.ajax({
            url: 'operations.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#formModal').modal('hide');
                    table.ajax.reload();
                    alert(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });

    // Handle image preview
    $('#image').on('change', function() {
        let url = $(this).val();
        if (url) {
            $('<img>', {
                src: url,
                error: function() {
                    alert('URL gambar tidak valid atau tidak dapat diakses');
                    $('#image').val('');
                },
                load: function() {
                    console.log('Image loaded successfully');
                }
            });
        }
    });
});