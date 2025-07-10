<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Generator - Alternative</title>
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
                            <button type="button" class="btn btn-success ms-2" id="downloadBtn">Download PDF</button>
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
    
    <script>
        // jsPDF SVG plugin (inline version)
        (function(global) {
            'use strict';
            
            // SVG to PDF plugin implementation
            function svgToPdf(svgElement, pdf, options) {
                const svgString = new XMLSerializer().serializeToString(svgElement);
                const svgBlob = new Blob([svgString], {type: 'image/svg+xml'});
                const url = URL.createObjectURL(svgBlob);
                
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        
                        // Set canvas size
                        canvas.width = options.width || 800;
                        canvas.height = options.height || 600;
                        
                        // Draw image on canvas
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                        
                        // Convert canvas to PDF
                        const imgData = canvas.toDataURL('image/png');
                        pdf.addImage(imgData, 'PNG', 0, 0, pdf.internal.pageSize.getWidth(), pdf.internal.pageSize.getHeight());
                        
                        URL.revokeObjectURL(url);
                        resolve();
                    };
                    img.onerror = reject;
                    img.src = url;
                });
            }
            
            // Add to jsPDF
            if (global.jspdf) {
                global.jspdf.svgToPdf = svgToPdf;
            }
        })(window);

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

            // Create a copy of the original SVG
            let modifiedSvg = originalSvg;
            
            // Replace the username in the SVG
            // Find the tspan element with id="username" and update its content
            const usernameRegex = /(<tspan[^>]*id="username"[^>]*>)[^<]*(<\/tspan>)/;
            if (usernameRegex.test(modifiedSvg)) {
                modifiedSvg = modifiedSvg.replace(usernameRegex, `$1${username}$2`);
            }

            // Display the modified SVG
            const previewContainer = document.getElementById('certificatePreview');
            previewContainer.innerHTML = modifiedSvg;
        }

        // Function to generate PDF using the SVG plugin
        async function generatePDF(username) {
            const loading = document.querySelector('.loading');
            loading.style.display = 'block';

            try {
                // Create a copy of the original SVG
                let modifiedSvg = originalSvg;
                
                // Replace the username in the SVG
                const usernameRegex = /(<tspan[^>]*id="username"[^>]*>)[^<]*(<\/tspan>)/;
                if (usernameRegex.test(modifiedSvg)) {
                    modifiedSvg = modifiedSvg.replace(usernameRegex, `$1${username}$2`);
                }

                // Create a temporary container for the SVG
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = modifiedSvg;
                const svgElement = tempContainer.querySelector('svg');

                // Create PDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('landscape', 'mm', 'a4');

                // Use the SVG to PDF plugin
                await window.jspdf.svgToPdf(svgElement, pdf, {
                    width: 800,
                    height: 600
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

        // Download button functionality
        document.getElementById('downloadBtn').addEventListener('click', function() {
            const username = document.getElementById('username').value.trim();
            if (username) {
                generatePDF(username);
            } else {
                alert('Please enter a name first.');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 