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
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if(result.success) {
                        $('#addModal').modal('hide');
                        $('#addForm')[0].reset();
                        loadData();
                    }
                } catch(e) {
                    console.error('Error parsing response:', e);
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
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if(result.success) {
                        $('#editModal').modal('hide');
                        $('#editForm')[0].reset();
                        loadData();
                    } else {
                        alert('Failed to update data: ' + (result.message || 'Unknown error'));
                    }
                } catch(e) {
                    console.error('Error parsing response:', e);
                    alert('Error updating data. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating data:', error);
                alert('Error updating data. Please try again.');
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
    $.ajax({
        url: 'php/read.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(member) {
            if(member) {
                const form = $('#editForm');
                form.find('[name=id]').val(member.id);
                form.find('[name=title]').val(member.title);
                form.find('[name=image]').val(member.image);
                form.find('[name=release_at]').val(member.release_at);
                form.find('[name=summary]').val(member.summary);
                $('#editModal').modal('show');
            } else {
                alert('Member data not found');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching member data:', error);
            alert('Error loading member data. Please try again.');
        }
    });
}

function deleteMember(id) {
    if(confirm('Are you sure you want to delete this member?')) {
        $.ajax({
            url: 'php/delete.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    loadData();
                } else {
                    alert('Failed to delete member: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting member:', error);
                alert('Error deleting member. Please try again.');
            }
        });
    }
}