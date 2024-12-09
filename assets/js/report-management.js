function viewReport(reportId) {
    $.ajax({
        url: '../../admin_operations/get_report_details.php',
        type: 'GET',
        data: { report_id: reportId },
        success: function(response) {
            if (response.success) {
                const report = response.data;
                
                // Create status buttons with cleaner styling
                const statusButtons = `
                    <div class="btn-group" role="group">
                        <button type="button" class="btn ${report.status === 'pending' ? 'btn-warning' : 'btn-outline-warning'} btn-sm" 
                                onclick="updateReportStatus(${report.report_id}, 'pending')">Pending</button>
                        <button type="button" class="btn ${report.status === 'reviewed' ? 'btn-info' : 'btn-outline-info'} btn-sm" 
                                onclick="updateReportStatus(${report.report_id}, 'reviewed')">Reviewed</button>
                        <button type="button" class="btn ${report.status === 'resolved' ? 'btn-success' : 'btn-outline-success'} btn-sm" 
                                onclick="updateReportStatus(${report.report_id}, 'resolved')">Resolved</button>
                    </div>
                `;

                Swal.fire({
                    title: 'Report Details',
                    html: `
                        <div class="report-details text-start">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-muted">Reporter Information</h6>
                                            <p class="mb-1"><strong>${report.reporter_firstname} ${report.reporter_lastname}</strong> (${report.reporter_type})</p>
                                            <small class="text-muted">Reported on: ${new Date(report.created_at).toLocaleString()}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-muted">Report Information</h6>
                                            <p class="mb-1"><strong>Type:</strong> ${report.report_type}</p>
                                            ${report.reason ? `<p class="mb-1"><strong>Reason:</strong> ${report.reason}</p>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            ${report.post_content ? `
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Reported Content</h6>
                                                <p class="mb-2">${report.post_content}</p>
                                                ${report.post_image ? `
                                                    <img src="data:image/jpeg;base64,${report.post_image}" 
                                                         class="img-fluid rounded" 
                                                         alt="Post Image">
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ` : ''}

                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-muted">Status</h6>
                                            ${statusButtons}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `,
                    width: '600px',
                    showConfirmButton: false,
                    showCloseButton: true,
                    customClass: {
                        container: 'report-modal',
                        popup: 'report-modal-popup',
                        closeButton: 'report-modal-close'
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to fetch report details',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

function updateReportStatus(reportId, status) {
    $.ajax({
        url: '../../admin_operations/update_report_status.php',
        type: 'POST',
        data: {
            report_id: reportId,
            status: status
        },
        success: function(response) {
            if (response.success) {
                // Show a simple success toast
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                
                Toast.fire({
                    icon: 'success',
                    title: 'Status updated'
                }).then(() => {
                    // Refresh the page to update the tables
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update report status',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
} 