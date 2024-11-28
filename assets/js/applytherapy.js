function showTherapistApplicationForm() {
    Swal.fire({
        title: 'Therapist Application',
        html: `
            <form id="therapistApplicationForm" class="text-start">
                <div class="row">
                    <!-- Profile Picture Section -->
                    <div class="col-12 mb-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="avatar avatar-xxl position-relative" style="width: 150px; height: 150px;">
                                <img src="../../assets/img/default-avatar.png" id="previewImage" 
                                    class="border-radius-md w-100 h-100" 
                                    style="object-fit: cover;" 
                                    alt="profile-picture">
                                <label for="profilePicture" 
                                    class="btn btn-sm btn-icon-only bg-gradient-dark position-absolute bottom-0 end-0 mb-n2 me-n2"
                                    style="cursor: pointer;">
                                    <span class="material-symbols-rounded text-xs top-0 mt-n2">
                                        edit
                                    </span>
                                </label>
                                <input type="file" 
                                    id="profilePicture" 
                                    name="profilePicture" 
                                    accept="image/*" 
                                    style="display: none;" 
                                    required>
                            </div>
                            <small class="text-muted mt-2">Click the edit icon to upload profile picture (Max: 2MB)</small>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="col-12">
                        <h6 class="text-dark text-gradient mb-4">Personal Information</h6>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-static mb-3">
                            <label>First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-static mb-3">
                            <label>Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-static mb-3">
                            <label>Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-static mb-3">
                            <label>Contact Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>

                    <!-- Professional Information -->
                    <div class="col-12">
                        <h6 class="text-dark text-gradient mb-4 mt-2">Professional Information</h6>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-static mb-3">
                            <label>License Number</label>
                            <input type="text" class="form-control" id="licenseNumber" name="licenseNumber" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-static mb-3">
                            <label>Specialization</label>
                            <select class="form-control" id="specialization" name="specialization" required>
                                <option value="">Select Specialization</option>
                                <option value="clinical">Clinical Psychology</option>
                                <option value="counseling">Counseling Psychology</option>
                                <option value="educational">Educational Psychology</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="input-group input-group-static mb-3">
                            <label>Professional Experience</label>
                            <textarea class="form-control" id="experience" name="experience" rows="4" required
                                placeholder="Describe your relevant experience, including years of practice, areas of expertise, and notable achievements."></textarea>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="col-12">
                        <h6 class="text-dark text-gradient mb-4 mt-2">Required Documents</h6>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-static mb-4">
                            <label class="ms-0">License/Certification</label>
                            <input type="file" 
                                class="form-control border px-3 py-2" 
                                style="border-radius: 0.375rem; cursor: pointer;" 
                                id="licenseFile" 
                                name="licenseFile" 
                                accept=".pdf,.doc,.docx" 
                                required>
                            <small class="text-muted mt-2 d-block">Upload your professional license (PDF/DOC)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-static mb-4">
                            <label class="ms-0">Resume/CV</label>
                            <input type="file" 
                                class="form-control border px-3 py-2" 
                                style="border-radius: 0.375rem; cursor: pointer;" 
                                id="resume" 
                                name="resume" 
                                accept=".pdf,.doc,.docx" 
                                required>
                            <small class="text-muted mt-2 d-block">Upload your detailed CV (PDF/DOC)</small>
                        </div>
                    </div>
                </div>
            </form>`,
        width: '800px',
        showCancelButton: true,
        confirmButtonText: 'Submit Application',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn bg-gradient-primary mx-2',
            cancelButton: 'btn bg-gradient-secondary mx-2',
            actions: 'justify-content-center gap-2 mt-4'
        },
        buttonsStyling: false,
        didOpen: () => {
            // Handle profile picture preview
            const profileInput = document.getElementById('profilePicture');
            const previewImage = document.getElementById('previewImage');

            profileInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        },
        preConfirm: () => {
            // Debug: Log form values before submission
            console.log('Form Values:', {
                firstName: document.querySelector('#firstName').value,
                lastName: document.querySelector('#lastName').value,
                email: document.querySelector('#email').value,
                phone: document.querySelector('#phone').value,
                licenseNumber: document.querySelector('#licenseNumber').value,
                specialization: document.querySelector('#specialization').value,
                experience: document.querySelector('#experience').value,
                profilePicture: document.querySelector('#profilePicture').files[0],
                licenseFile: document.querySelector('#licenseFile').files[0],
                resume: document.querySelector('#resume').files[0]
            });

            // Use FormData with the actual form element
            const form = document.querySelector('#therapistApplicationForm');
            const formData = new FormData(form);

            // Debug: Log FormData entries
            for (let pair of formData.entries()) {
                console.log(pair[0], pair[1]);
            }

            // Submit form
            return fetch('/Space/admin_operations/submit_therapist_application.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                console.log('Raw server response:', text); // Log the raw response

                // Try to parse as JSON, but if it fails, show the raw response
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'error') {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                    return data;
                } catch (e) {
                    console.error('Parse error:', e);
                    throw new Error(`Server response: ${text}`);
                }
            })
            .catch(error => {
                console.error('Submission error:', error);
                let errorMessage = 'An unexpected error occurred';
                if (error.message) {
                    errorMessage = error.message.includes('Server response:') 
                        ? 'Server error: Please try again later' 
                        : error.message;
                }
                Swal.showValidationMessage(`Submission failed: ${errorMessage}`);
                return false;
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value?.status === 'success') {
            Swal.fire({
                title: 'Application Submitted!',
                text: result.value.message || 'Your application has been submitted successfully. We will review and get back to you soon.',
                icon: 'success',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary'
                },
                buttonsStyling: false
            });
        }
    });
}

// Initialize when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    const applyButton = document.querySelector('.btn[onclick*="therapist-application"]');
    if (applyButton) {
        applyButton.onclick = showTherapistApplicationForm;
    }
}); 