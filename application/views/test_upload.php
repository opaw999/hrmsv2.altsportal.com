<!DOCTYPE html>
<html>
<head>
    <title>Upload Test</title>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto; }
        .box { background: #f5f5f5; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        pre { background: #fff; padding: 10px; border: 1px solid #ccc; overflow: auto; max-height: 400px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🧪 Upload Test</h1>
    
    <div class="box">
        <h3>Test Info</h3>
        <p><strong>Upload URL:</strong> <code><?= site_url("api/attendance/upload") ?></code></p>
        <p><strong>Upload Path:</strong> <code><?= FCPATH ?>uploads/attendance/</code></p>
        <p><strong>Session empid:</strong> <code><?= $this->session->userdata('empid') ? $this->session->userdata('empid') : 'NOT LOGGED IN' ?></code></p>
    </div>
    
    <div class="box">
        <h3>Select Files</h3>
        <input type="file" id="testFile" multiple>
        <br><br>
        <button onclick="uploadTest()">🚀 Upload Now</button>
    </div>
    
    <div class="box">
        <h3>Output</h3>
        <pre id="output">Waiting for upload...</pre>
    </div>

    <script>
    function uploadTest() {
        const files = document.getElementById('testFile').files;
        const output = document.getElementById('output');
        
        if (files.length === 0) {
            output.innerHTML = '<span class="error">❌ Please pick a file first!</span>';
            return;
        }
        
        output.textContent = '⏳ Uploading ' + files.length + ' file(s)...';
        
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
            console.log('Added file:', files[i].name, files[i].size, files[i].type);
        }
        
        console.log('POST to:', '<?= site_url("api/attendance/upload") ?>');
        
        $.ajax({
            url: '<?= site_url("api/attendance/upload") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('✅ Success:', response);
                output.innerHTML = '<span class="success">✅ SUCCESS</span>\n\n' + JSON.stringify(response, null, 2);
            },
            error: function(xhr, status, err) {
                console.log('❌ Error:', { status, err, responseText: xhr.responseText });
                output.innerHTML = '<span class="error">❌ ERROR</span>\n\n' +
                    'Status: ' + xhr.status + '\n' +
                    'Error: ' + err + '\n\n' +
                    '=== Response ===\n' + xhr.responseText;
            }
        });
    }
    </script>
</body>
</html>