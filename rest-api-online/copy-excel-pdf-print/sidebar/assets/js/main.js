$(document).ready(function() {
    let table = $('#tabelData').DataTable({
        ajax: {
            url: 'data.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'title' },
            {
                data: 'image',
                render: function(data, type, row) {
                    return data ? `<img src="${data}" alt="poster" style="max-height: 50px;">` : 'No Image';
                }
            },
            { data: 'release_at' },
            { data: 'summary' }
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
            },
            {
                text: 'Download CSV',
                className: 'btn btn-secondary btn-sm',
                action: function () {
                    window.location.href = 'includes/download.php';
                }
            }
        ]
    });

    // Handle file upload
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData();
        formData.append('csvFile', $('#csvFile')[0].files[0]);
        
        $.ajax({
            url: 'includes/upload.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#uploadMessage')
                        .removeClass('alert-danger')
                        .addClass('alert-success')
                        .text(response.message)
                        .show();
                    
                    // Reload DataTable
                    table.ajax.reload();
                } else {
                    $('#uploadMessage')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .text(response.message)
                        .show();
                }
            },
            error: function() {
                $('#uploadMessage')
                    .removeClass('alert-success')
                    .addClass('alert-danger')
                    .text('Error occurred during upload')
                    .show();
            }
        });
    });

    table.on('select deselect', function() {
        let selectedRows = table.rows({ selected: true }).count();
        let copyButton = $('.buttons-copy');

        if (selectedRows > 0) {
            copyButton.removeClass('disabled');
        } else {
            copyButton.addClass('disabled');
        }
    });
});