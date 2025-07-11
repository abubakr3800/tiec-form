<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مولد الشهادات - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .certificate-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .certificate-preview {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            background: white;
            padding: 20px;
        }
        .certificate-preview svg {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .loading {
            display: none;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container certificate-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="dashboard.php" class="btn btn-primary me-2">
                    <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                </a>
                <a href="index.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-right"></i> العودة للصفحة الرئيسية
                </a>
            </div>
            <h1 class="text-center mb-0">مولد الشهادات - TIEC</h1>
            <div style="width: 150px;"></div> <!-- Spacer for centering -->
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>إدخال بيانات الشهادة</h5>
                    </div>
                    <div class="card-body">
                        <form id="certificateForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">اسم المشارك:</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <button type="submit" class="btn btn-primary">عرض الشهادة</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>معاينة الشهادة</h5>
                    </div>
                    <div class="card-body">
                        <div id="certificatePreview" class="certificate-preview">
                            <p class="text-muted">أدخل اسم المشارك لمعاينة الشهادة</p>
                        </div>
                        <div class="loading mt-3">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <p class="text-center mt-2">جاري إنشاء PDF...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- html2canvas for better PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // Load the SVG certificate
        let originalSvg = '';
        
        // Load the SVG file
        fetch('cert/Web Development.svg')
            .then(response => {
                if (!response.ok) {
                    throw new Error('فشل في تحميل ملف الشهادة');
                }
                return response.text();
            })
            .then(svgContent => {
                originalSvg = svgContent;
                console.log('تم تحميل ملف SVG بنجاح');
            })
            .catch(error => {
                console.error('خطأ في تحميل SVG:', error);
                document.getElementById('certificatePreview').innerHTML = 
                    '<div class="alert alert-danger">خطأ في تحميل ملف الشهادة. يرجى التحقق من وجود الملف.</div>';
            });

        // Function to update the certificate preview
        function updateCertificatePreview(username) {
            if (!originalSvg) {
                console.error('لم يتم تحميل ملف SVG بعد');
                document.getElementById('certificatePreview').innerHTML = 
                    '<div class="alert alert-warning">جاري تحميل ملف الشهادة...</div>';
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
                // Update the preview first
                updateCertificatePreview(username);
                
                // Wait a bit for the SVG to render
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Get the certificate preview element
                const certificateElement = document.getElementById('certificatePreview');
                
                // Use html2canvas to capture the certificate
                const canvas = await html2canvas(certificateElement, {
                    scale: 2, // Higher quality
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff'
                });

                // Create PDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('landscape', 'mm', 'a4');

                // Calculate dimensions
                const imgWidth = 297; // A4 width in mm
                const pageHeight = 210; // A4 height in mm
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;
                
                // Add image to PDF
                pdf.addImage(canvas, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                
                // Add new page if needed
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(canvas, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                // Save the PDF
                const fileName = `certificate_${username.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`;
                pdf.save(fileName);
                
                // Show success message
                alert('تم تحميل الشهادة بنجاح!');

            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('خطأ في إنشاء PDF. يرجى المحاولة مرة أخرى.\nError: ' + error.message);
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
            if (e.target.classList.contains('btn-download') || e.target.closest('.btn-download')) {
                const username = document.getElementById('username').value.trim();
                if (username) {
                    generatePDF(username);
                } else {
                    alert('يرجى إدخال اسم المشارك أولاً.');
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
            downloadBtn.className = 'btn btn-success me-2 btn-download';
            downloadBtn.innerHTML = '<i class="fas fa-download"></i> تحميل PDF';
            submitBtn.parentNode.appendChild(downloadBtn);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 