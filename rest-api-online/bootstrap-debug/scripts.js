document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Toggle
    document.getElementById('sidebar-toggle').addEventListener('click', function () {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    // Add Member Form Submit
    document.getElementById('addMemberForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('create.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Error adding member');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Gunakan variable newMemberId dari respons create.php
                    const newMemberId = data.info.id;
                    console.log('New Member created with ID: ', newMemberId);
                    // Lakukan reload halaman
                    location.reload();
                } else {
                    alert(data.info);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message);
            });
    });

    // Edit Member Button Click
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');

            // Log untuk debugging
            console.log('Fetching member data for ID:', id);
            console.log('API URL:', `read_one.php?id=${id}`);

            fetch(`read_one.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(response => {
                    console.log('Response received:', response); // Log untuk debugging

                    if (response.status === 'success' && response.data) {
                        // Populate form
                        const form = document.getElementById('editMemberForm');
                        form.querySelector('[name="id"]').value = response.data.id;
                        form.querySelector('[name="title"]').value = response.data.title;
                        form.querySelector('[name="image"]').value = response.data.image;
                        form.querySelector('[name="release_at"]').value = response.data.release_at;
                        form.querySelector('[name="summary"]').value = response.data.summary;

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
                        modal.show();
                    } else {
                        throw new Error(response.message || 'Failed to load member data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading member data: ' + error.message);
                });
        });

    });

// Edit Member Form Submit
    document.getElementById('editMemberForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const formObject = {};
        formData.forEach((value, key) => {
            formObject[key] = value;
        });

        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: formObject.id,
                title: formObject.title,
                image: formObject.image,
                release_at: formObject.release_at,
                summary: formObject.summary
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('success', data.message || 'Member updated successfully');

                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editMemberModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Reload after short delay to show success message
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to update member');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', error.message || 'An error occurred while updating the member');
            });
    });

// Add notification function if not already present
    function showNotification(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

        // Insert at the top of the container
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alertDiv, container.firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Delete Member Button Click
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            if (confirm('Are you sure you want to delete this member?')) {
                const id = this.getAttribute('data-id');

                fetch(`delete.php?id=${id}`, {
                    method: 'DELETE',
                    headers: {
                        // Implement this function
                        'X-CSRF-Token': getCsrfToken(),
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove row from table
                            const row = document.querySelector(`tr[data-id="${id}"]`);
                            if (row) row.remove();

                            // Show success Message
                            showNotification('success', 'Member deleted successfully');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            alert(data.error || 'Error deleting member');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', error.message || 'An error occurred while deleting number');
                    });
            }
        });
    });

    // Helper function untuk notification
    function showNotification(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.querySelector('.container-fluid').insertAdjacentElement('afterbegin', alertDiv);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Search functionality
    const searchForm = document.querySelector('form');
    const searchInput = searchForm.querySelector('input[name="search"]');
    let searchTimeout;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchForm.submit();
        }, 500);
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (event) {
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');

        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) &&
                !sidebarToggle.contains(event.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Reset forms when modals are closed
    ['addMemberModal', 'editMemberModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        modal.addEventListener('hidden.bs.modal', function () {
            const form = modal.querySelector('form');
            form.reset();
        });
    });

    // Image preview functionality
    const imageInputs = document.querySelectorAll('input[name="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function () {
            const img = document.createElement('img');
            img.src = this.value;
            img.style.maxWidth = '200px';
            img.style.marginTop = '10px';

            const previewDiv = this.nextElementSibling || document.createElement('div');
            if (!this.nextElementSibling) {
                previewDiv.className = 'image-preview';
                this.parentNode.appendChild(previewDiv);
            }

            previewDiv.innerHTML = '';
            previewDiv.appendChild(img);

            // Handle image load error
            img.onerror = function () {
                previewDiv.innerHTML = '<p class="text-danger">Invalid image URL</p>';
            };
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Handle network errors
    window.addEventListener('online', function () {
        document.body.classList.remove('offline');
        alert('Connection restored');
    });

    window.addEventListener('offline', function () {
        document.body.classList.add('offline');
        alert('No internet connection');
    });
});

// Helper functions
function formatDate(dateString) {
    const options = {year: 'numeric', month: 'long', day: 'numeric'};
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function sanitizeInput(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}