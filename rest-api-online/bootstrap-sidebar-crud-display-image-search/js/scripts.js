document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    // Add Member Form Submit
    document.getElementById('addMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('api/create.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error adding member');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding member');
        });
    });

   // Edit Member Button Click
   document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        
        // Log untuk debugging
        console.log('Fetching member data for ID:', id);
        console.log('API URL:', `api/read_one.php?id=${id}`);
        
        fetch(`api/read_one.php?id=${id}`)
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
document.getElementById('editMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const formObject = {};
    formData.forEach((value, key) => {
        formObject[key] = value;
    });
    
    fetch('api/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formObject)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editMemberModal'));
            modal.hide();
            location.reload();
        } else {
            throw new Error(data.error || 'Failed to update member');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating member: ' + error.message);
    });
});


    // Delete Member Button Click
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            if(confirm('Are you sure you want to delete this member?')) {
                const id = this.getAttribute('data-id');
                
                fetch(`api/delete.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting member');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting member');
                });
            }
        });
    });

    // Search functionality
    const searchForm = document.querySelector('form');
    const searchInput = searchForm.querySelector('input[name="search"]');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchForm.submit();
        }, 500);
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
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
        modal.addEventListener('hidden.bs.modal', function() {
            const form = modal.querySelector('form');
            form.reset();
        });
    });

    // Image preview functionality
    const imageInputs = document.querySelectorAll('input[name="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
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
            img.onerror = function() {
                previewDiv.innerHTML = '<p class="text-danger">Invalid image URL</p>';
            };
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Handle network errors
    window.addEventListener('online', function() {
        document.body.classList.remove('offline');
        alert('Connection restored');
    });

    window.addEventListener('offline', function() {
        document.body.classList.add('offline');
        alert('No internet connection');
    });
});

// Helper functions
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
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