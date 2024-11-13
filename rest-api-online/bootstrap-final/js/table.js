document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('.table tbody tr');

    tableRows.forEach(row => {
        // Mencegah event bubbling untuk tombol
        row.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Menambahkan efek klik pada baris
        row.addEventListener('click', function() {
            // Menghapus kelas selected dari semua baris
            tableRows.forEach(r => r.classList.remove('selected'));
            // Menambahkan kelas selected ke baris yang diklik
            this.classList.add('selected');
        });
    });
});