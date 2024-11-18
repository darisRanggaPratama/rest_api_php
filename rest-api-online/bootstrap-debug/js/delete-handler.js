document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Add click event listener to all delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const memberId = this.getAttribute('data-id');

            // Show confirmation dialog
            if (confirm('Are you sure you want to delete this member?')) {
                // Send delete request
                fetch(`api/delete.php`, {  // Removed '../' from path
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken,
                        'Accept': 'application/json'  // Added Accept header
                    },
                    body: JSON.stringify({
                        id: memberId
                    })
                })
                    .then(response => {
                        // Check if response is ok
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Remove the row from the table
                            const row = this.closest('tr');
                            row.remove();

                            // Show success message
                            alert('Member deleted successfully!');

                            // Reload the page if table is empty
                            const remainingRows = document.querySelectorAll('tbody tr').length;
                            if (remainingRows === 0) {
                                window.location.reload();
                            }
                        } else {
                            throw new Error(data.message || 'Failed to delete member');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.message);
                    });
            }
        });
    });
});