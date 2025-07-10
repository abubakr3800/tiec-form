<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .certificate-preview {
            border: 1px solid #ddd;
            margin: 20px 0;
            text-align: center;
        }
        .certificate-preview svg {
            max-width: 100%;
            height: auto;
        }
        .loading {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container certificate-container">
        <h1 class="text-center mb-4">Certificate Generator</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Enter Certificate Details</h5>
                    </div>
                    <div class="card-body">
                        <form id="certificateForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Name:</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Generate Certificate</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Certificate Preview</h5>
                    </div>
                    <div class="card-body">
                        <div id="certificatePreview" class="certificate-preview">
                            <p class="text-muted">Enter a name to see the certificate preview</p>
                        </div>
                        <div class="loading mt-3">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <p class="text-center mt-2">Generating PDF...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- SVG to PDF plugin (local copy for reliability) -->
    <script src="assets/libs/svg2pdf.umd.js"></script>

    <script>
        // Load the SVG certificate
        let originalSvg = '';
        
        // Load the SVG file
        fetch('cert/Web Development.svg')
            .then(response => response.text())
            .then(svgContent => {
                originalSvg = svgContent;
                console.log('SVG loaded successfully');
            })
            .catch(error => {
                console.error('Error loading SVG:', error);
            });

        // Function to update the certificate preview
        function updateCertificatePreview(username) {
            if (!originalSvg) {
                console.error('SVG not loaded yet');
                return;
            }

            // Parse SVG as DOM
            const parser = new DOMParser();
            const svgDoc = parser.parseFromString(originalSvg, 'image/svg+xml');
            const usernameTspan = svgDoc.getElementById('username');
            if (usernameTspan) {
                // Remove all attributes except id
                [...usernameTspan.attributes].forEach(attr => {
                    if (attr.name !== 'id') usernameTspan.removeAttribute(attr.name);
                });
                // Set text content
                usernameTspan.textContent = username;
                // After clearing attributes
                usernameTspan.setAttribute('x', '360'); // or your SVG's center x
                usernameTspan.setAttribute('y', '200'); // or your desired y
                usernameTspan.setAttribute('text-anchor', 'middle');
                // Remove all siblings (other tspans) inside the parent <text>
                const parentText = usernameTspan.parentNode;
                if (parentText) {
                    // Remove all child nodes except the usernameTspan
                    [...parentText.childNodes].forEach(child => {
                        if (child !== usernameTspan) parentText.removeChild(child);
                    });
                }
            }
            // Serialize back to string
            const serializer = new XMLSerializer();
            const modifiedSvg = serializer.serializeToString(svgDoc.documentElement);

            // Display the modified SVG
            const previewContainer = document.getElementById('certificatePreview');
            previewContainer.innerHTML = modifiedSvg;
        }

        // Function to generate PDF
        async function generatePDF(username) {
            const loading = document.querySelector('.loading');
            loading.style.display = 'block';

            try {
                // Parse SVG as DOM
                const parser = new DOMParser();
                const svgDoc = parser.parseFromString(originalSvg, 'image/svg+xml');
                const usernameTspan = svgDoc.getElementById('username');
                if (usernameTspan) {
                    // Remove all attributes except id
                    [...usernameTspan.attributes].forEach(attr => {
                        if (attr.name !== 'id') usernameTspan.removeAttribute(attr.name);
                    });
                    // Set text content
                    usernameTspan.textContent = username;
                    // After clearing attributes
                    usernameTspan.setAttribute('x', '360'); // or your SVG's center x
                    usernameTspan.setAttribute('y', '200'); // or your desired y
                    usernameTspan.setAttribute('text-anchor', 'middle');
                    // Remove all siblings (other tspans) inside the parent <text>
                    const parentText = usernameTspan.parentNode;
                    if (parentText) {
                        // Remove all child nodes except the usernameTspan
                        [...parentText.childNodes].forEach(child => {
                            if (child !== usernameTspan) parentText.removeChild(child);
                        });
                    }
                }
                // Serialize back to string
                const serializer = new XMLSerializer();
                const modifiedSvg = serializer.serializeToString(svgDoc.documentElement);

                // Create a temporary container for the SVG
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = modifiedSvg;
                const svgElement = tempContainer.querySelector('svg');

                // Create PDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('landscape', 'mm', 'a4');

                // Convert SVG to PDF
                if (typeof window.svg2pdf !== 'function') {
                    alert('svg2pdf.js library not loaded correctly!\nجرب تحديث الصفحة أو تحقق من اتصال الإنترنت أو جرب سكريبت آخر.');
                    loading.style.display = 'none';
                    return;
                }
                window.svg2pdf(svgElement, pdf, {
                    width: 297, // A4 width in mm
                    height: 210, // A4 height in mm
                    preserveAspectRatio: true
                });

                // Save the PDF
                pdf.save(`certificate_${username.replace(/\s+/g, '_')}.pdf`);

            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Error generating PDF. Please try again.');
            } finally {
                loading.style.display = 'none';
            }
        }

        // Event listeners
        document.getElementById('certificateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            
            if (username) {
                updateCertificatePreview(username);
            }
        });

        // Add real-time preview
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value.trim();
            if (username && originalSvg) {
                updateCertificatePreview(username);
            }
        });

        // Add download button functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-download')) {
                const username = document.getElementById('username').value.trim();
                if (username) {
                    generatePDF(username);
                } else {
                    alert('Please enter a name first.');
                }
            }
        });

        // Add download button to the form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('certificateForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Add download button
            const downloadBtn = document.createElement('button');
            downloadBtn.type = 'button';
            downloadBtn.className = 'btn btn-success ms-2 btn-download';
            downloadBtn.textContent = 'Download PDF';
            submitBtn.parentNode.appendChild(downloadBtn);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 