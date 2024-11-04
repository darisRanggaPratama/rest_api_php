$(document).ready(function() {
    let table = $('#tabelData').DataTable({
        ajax: {
            url: 'data.php',
            type: 'GET',
            dataSrc: function(json) {
                // Log response untuk debugging
                console.log('DataTables Response:', json);

                if (json.error) {
                    // Tampilkan error dalam alert
                    alert('Error: ' + json.error + '\n\nDetail debug: ' + JSON.stringify(json.debug));
                    return [];
                }

                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('Ajax Error:', {
                    xhr: xhr,
                    error: error,
                    thrown: thrown
                });

                // Tampilkan detail error
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
            { data: 'summary' }
        ],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
        },
        processing: true,
        pageLength: 10,
        dom: 'Bfrtip',
        select: true, // Mengaktifkan fitur select
        buttons: [
            {
                extend: 'copy',
                text: 'Copy',
                className: 'btn btn-primary btn-sm',
                exportOptions: {
                    modifier: {
                        selected: true // Hanya mengambil baris yang dipilih
                    }
                },
                init: function(api, node, config) {
                    // Disable tombol copy saat tidak ada baris yang dipilih
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

    // Menangani status tombol copy berdasarkan seleksi
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