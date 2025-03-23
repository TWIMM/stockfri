<!-- Modal de Visualisation PDF -->
<div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-labelledby="pdfViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <input type="hidden" id='invoices_url' value='YOUR_PDF_URL_HERE'> <!-- Set the PDF URL here -->
            <div class="modal-header bg-primary text-white">
                <h5 style="color: white" class="modal-title" id="pdfViewerModalLabel">
                    <i class="ti ti-file-text me-2"></i>Visualisation du Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- PDF Controls -->
                <div class="bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold" id="documentName">Document</span>
                        <span class="text-muted small ms-2" id="documentInfo">(Version 1.0)</span>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="zoomIn">
                            <i class="ti ti-zoom-in"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="zoomOut">
                            <i class="ti ti-zoom-out"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="download">
                            <i class="ti ti-download"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="print">
                            <i class="ti ti-printer"></i>
                        </button>
                    </div>
                </div>

                <!-- PDF Loading State -->
                <div id="pdfLoading" class="d-flex justify-content-center align-items-center" style="height: 70vh;">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="text-muted">Chargement du document...</p>
                    </div>
                </div>

                <!-- PDF Error State -->
                <div id="pdfError" class="d-none d-flex justify-content-center align-items-center"
                    style="height: 70vh;">
                    <div class="text-center">
                        <i class="ti ti-file-x text-danger" style="font-size: 3rem;"></i>
                        <p class="text-danger mt-3">Impossible de charger le document. Veuillez réessayer.</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" id="retryButton">
                            <i class="ti ti-refresh me-1"></i>Réessayer
                        </button>
                    </div>
                </div>

                <!-- PDF Viewer -->
                <div id="pdfContainer" class="d-none" style="height: 70vh; overflow: auto;">
                    <iframe id="pdfViewerFrame" src="about:blank" style="width: 100%; height: 100%; border: none;"
                        title="PDF Viewer"></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row w-100">
                    <div class="col-md-6 text-start">
                        <select class="form-select form-select-sm" id="pdfPageSelect" disabled>
                            <option value="">Chargement des pages...</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" id="approveDocument">
                            <i class="ti ti-check me-1"></i>Approuver
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables
    let currentZoom = 100;
    let currentPdfUrl = '';
    let totalPages = 0;

    // Function to initialize the PDF viewer modal
    function initPdfViewer() {
        // Zoom in button
        document.getElementById('zoomIn').addEventListener('click', function() {
            currentZoom += 25;
            updateZoom();
        });

        // Zoom out button
        document.getElementById('zoomOut').addEventListener('click', function() {
            if (currentZoom > 50) {
                currentZoom -= 25;
                updateZoom();
            }
        });

        // Download button
        document.getElementById('download').addEventListener('click', function() {
            if (currentPdfUrl) {
                const a = document.createElement('a');
                a.href = currentPdfUrl;
                a.download = document.getElementById('documentName').textContent + '.pdf';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        });

        // Print button
        document.getElementById('print').addEventListener('click', function() {
            const iframe = document.getElementById('pdfViewerFrame');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.print();
            }
        });

        // Retry button
        document.getElementById('retryButton').addEventListener('click', function() {
            loadPdfFromUrl();
        });

        // Approve button
        document.getElementById('approveDocument').addEventListener('click', function() {
            alert('Document approuvé avec succès!');
            $('#pdfViewerModal').modal('hide');
        });
    }

    // Function to update zoom level
    function updateZoom() {
        const iframe = document.getElementById('pdfViewerFrame');
        if (iframe && iframe.contentDocument && iframe.contentDocument.body) {
            iframe.contentDocument.body.style.zoom = currentZoom + '%';
        }
    }

    

    // Function to show error state
    function showError(message) {
        document.getElementById('pdfLoading').classList.add('d-none');
        document.getElementById('pdfContainer').classList.add('d-none');
        document.getElementById('pdfError').classList.remove('d-none');
        console.error(message);
    }

    // Function to simulate page count update
    function simulatePageCount() {
        setTimeout(function() {
            totalPages = Math.floor(Math.random() * 20) + 1; // Random page count between 1-20
            const pageSelect = document.getElementById('pdfPageSelect');
            pageSelect.innerHTML = '';
            pageSelect.disabled = false;

            for (let i = 1; i <= totalPages; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = 'Page ' + i + ' / ' + totalPages;
                pageSelect.appendChild(option);
            }
        }, 1000);
    }

    

    //loadPdfFromUrl();

    // Initialize the PDF viewer when the document is ready
    document.addEventListener('DOMContentLoaded', initPdfViewer);

    // Open the PDF viewer with the URL in the hidden input field
    //openPdfViewer();
</script>
