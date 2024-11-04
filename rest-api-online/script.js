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