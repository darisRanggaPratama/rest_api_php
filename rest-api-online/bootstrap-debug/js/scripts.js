document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Toggle
    document.getElementById('sidebar-toggle').addEventListener('click', function () {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    // Row limit change handler
    document.getElementById('rowLimit').addEventListener('change', function () {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('limit', this.value);
        currentUrl.searchParams.set('page', '1'); // Reset to first page when changing limit
        window.location.href = currentUrl.toString();
    });

    // Add Member Form Submit
    document.getElementById('addMemberForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../api/create.php', {
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

    // Search functionality
    const searchForm = document.querySelector('form');
    const searchInput = searchForm.querySelector('input[name="search"]');
    let searchTimeout;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchForm.submit();
        }, 5000);
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