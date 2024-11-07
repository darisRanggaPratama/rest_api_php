$(document).ready(function() {
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
    });

    // Add form submit
    $('#addForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            title: this.title.value,
            image: this.image.value,
            release_at: this.release_at.value,
            summary: this.summary.value
        };

        $.ajax({
            url: 'php/create.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if(response.success) {
                    $('#addModal').modal('hide');
                    loadData();
                }
            }
        });
    });

    // Edit form submit
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            id: this.id.value,
            title: this.title.value,
            image: this.image.value,
            release_at: this.release_at.value,
            summary: this.summary.value
        };

        $.ajax({
            url: 'php/update.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if(response.success) {
                    $('#editModal').modal('hide');
                    loadData();
                }
            }
        });
    });
});

function loadData(search = '') {
    $.get('php/read.php', { search: search }, function(data) {
        $('#tableBody').html(data);
    });
}

function searchData() {
    const search = $('#searchInput').val();
    loadData(search);
}

function editMember(id) {
    $.get('php/read.php', { id: id }, function(data) {
        const member = JSON.parse(data);
        const form = $('#editForm');
        form.find('[name=id]').val(member.id);
        form.find('[name=title]').val(member.title);
        form.find('[name=image]').val(member.image);
        form.find('[name=release_at]').val(member.release_at);
        form.find('[name=summary]').val(member.summary);
        $('#editModal').modal('show');
    });
}

function deleteMember(id) {
    if(confirm('Are you sure you want to delete this member?')) {
        $.post('php/delete.php', { id: id }, function(response) {
            if(response.success) {
                loadData();
            }
        });
    }
}