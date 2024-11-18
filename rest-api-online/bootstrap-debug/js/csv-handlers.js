document.addEventListener('DOMContentLoaded', function() {
    const csvUploadForm = document.getElementById('csvUploadForm');
    const csvFileInput = document.getElementById('csvFile');

    if (csvUploadForm) {
        csvUploadForm.addEventListener('submit', handleCsvUpload);
    }

    function handleCsvUpload(e) {
        e.preventDefault();

        // Validasi file
        const file = csvFileInput.files[0];
        if (!file) {
            showError('Please select a file');
            return;
        }

        // Validasi ekstensi file
        if (!file.name.endsWith('.csv')) {
            showError('Please upload a CSV file');
            return;
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

        // Disable form selama upload
        const submitButton = csvUploadForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';

        // Send request
        fetch('../api/upload-csv.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    if (data.details && data.details.errors && data.details.errors.length > 0) {
                        showErrorDetails(data.details.errors);
                    }
                    // Reload page after successful upload
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showError(data.error || 'Upload failed');
                }
            })
            .catch(error => {
                showError('Upload failed: ' + error.message);
            })
            .finally(() => {
                // Re-enable form
                submitButton.disabled = false;
                submitButton.innerHTML = 'Upload';
                csvFileInput.value = '';
            });
    }

    function showSuccess(message) {
        const alert = createAlert('success', message);
        insertAlert(alert);
    }

    function showError(message) {
        const alert = createAlert('danger', message);
        insertAlert(alert);
    }

    function showErrorDetails(errors) {
        if (errors.length > 0) {
            const detailsList = document.createElement('ul');
            detailsList.className = 'mt-2 mb-0';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                detailsList.appendChild(li);
            });

            const alert = createAlert('warning', 'Import Errors:', detailsList);
            insertAlert(alert);
        }
    }

    function createAlert(type, message, additionalContent = null) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';

        const messageText = document.createElement('div');
        messageText.textContent = message;
        alertDiv.appendChild(messageText);

        if (additionalContent) {
            alertDiv.appendChild(additionalContent);
        }

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('data-bs-dismiss', 'alert');
        closeButton.setAttribute('aria-label', 'Close');
        alertDiv.appendChild(closeButton);

        return alertDiv;
    }

    function insertAlert(alert) {
        const modalBody = csvUploadForm.closest('.modal-body');
        const existingAlert = modalBody.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        modalBody.insertBefore(alert, csvUploadForm);
    }
});