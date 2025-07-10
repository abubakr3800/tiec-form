<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SVG Modification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .svg-container {
            border: 1px solid #ddd;
            margin: 20px 0;
            text-align: center;
        }
        .svg-container svg {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Test SVG Modification</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Original SVG</h5>
                    </div>
                    <div class="card-body">
                        <div id="originalSvg" class="svg-container">
                            <p>Loading...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Modified SVG</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="testName" class="form-label">Test Name:</label>
                            <input type="text" class="form-control" id="testName" value="John Doe">
                            <button type="button" class="btn btn-primary mt-2" onclick="updateTestSvg()">Update SVG</button>
                        </div>
                        <div id="modifiedSvg" class="svg-container">
                            <p>Click "Update SVG" to see the modified version</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let originalSvgContent = '';
        
        // Load the SVG file
        fetch('cert/Web Development.svg')
            .then(response => response.text())
            .then(svgContent => {
                originalSvgContent = svgContent;
                document.getElementById('originalSvg').innerHTML = svgContent;
                console.log('Original SVG loaded');
                
                // Find and highlight the username element
                const usernameElement = svgContent.match(/<tspan[^>]*id="username"[^>]*>[^<]*<\/tspan>/);
                if (usernameElement) {
                    console.log('Found username element:', usernameElement[0]);
                } else {
                    console.log('Username element not found');
                }
            })
            .catch(error => {
                console.error('Error loading SVG:', error);
                document.getElementById('originalSvg').innerHTML = '<p class="text-danger">Error loading SVG</p>';
            });

        function updateTestSvg() {
            const testName = document.getElementById('testName').value.trim();
            if (!testName || !originalSvgContent) {
                alert('Please enter a name and wait for SVG to load');
                return;
            }

            // Create a copy of the original SVG
            let modifiedSvg = originalSvgContent;
            
            // Replace the username in the SVG
            const usernameRegex = /(<tspan[^>]*id="username"[^>]*>)[^<]*(<\/tspan>)/;
            if (usernameRegex.test(modifiedSvg)) {
                modifiedSvg = modifiedSvg.replace(usernameRegex, `$1${testName}$2`);
                document.getElementById('modifiedSvg').innerHTML = modifiedSvg;
                console.log('SVG updated with name:', testName);
            } else {
                console.log('Username element not found in SVG');
                alert('Could not find username element in SVG');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 