document.addEventListener('DOMContentLoaded', function () {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle edit button click
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const memberId = this.getAttribute('data-id');
            fetchMemberData(memberId);
        });
    });

    // Fetch member data
    function fetchMemberData(id) {
        fetch(`api/getID.php?id=${id}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateEditForm(data.data);
                    const editModal = new bootstrap.Modal(document.getElementById('editMemberModal'));
                    editModal.show();
                } else {
                    alert('Error fetching member data: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching member data');
            });
    }

    // Populate edit form
    function populateEditForm(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_title').value = data.title;
        document.getElementById('edit_release_at').value = formatDate(data.release_at);
        document.getElementById('edit_summary').value = data.summary;
        document.getElementById('edit_image').value = data.image;

        // Show current image preview
        const previewDiv = document.getElementById('current_image_preview');
        if (data.image) {
            previewDiv.innerHTML = `<img src="${data.image}" class="img-thumbnail" width="100" alt="Current image">`;
        } else {
            previewDiv.innerHTML = '<span class="badge bg-secondary">No image</span>';
        }
    }

    // Handle form submission
    document.getElementById('editMemberForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('api/update.php', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Member updated successfully');
                    window.location.reload();
                } else {
                    alert('Error updating member: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating member');
            });
    });

    // Helper function to format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }
});