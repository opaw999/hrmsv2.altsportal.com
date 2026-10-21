/* ==========================================================================
   attendance/attachments.js
   File upload / preview / view / remove for the time-off modal.
   Depends on: helpers.js, jQuery, Bootstrap modal
   ========================================================================== */

function triggerFileUpload(zone) {
    $(zone).siblings('.attachment-input').click();
}

function handleFileUpload(input) {
    const files = input.files;
    const $row  = $(input).closest('tr');
    if (!$row.data('uploadedFiles')) $row.data('uploadedFiles', []);
    const list = $row.data('uploadedFiles');

    Array.from(files).forEach((file, i) => {
        const id = Date.now() + '_' + i;
        const obj = { id: id, file: file, name: file.name, size: file.size, type: file.type, dataUrl: null };
        list.push(obj);

        const reader = new FileReader();
        reader.onload = function (e) {
            const idx = list.findIndex(f => f.id === id);
            if (idx !== -1) list[idx].dataUrl = e.target.result;
            $row.data('uploadedFiles', list);
            displayFilePreviews($row);
        };
        reader.readAsDataURL(file);
    });

    input.value = '';
}

function displayFilePreviews($row) {
    const list     = $row.data('uploadedFiles') || [];
    const $preview = $row.find('.attachment-preview-list');
    $preview.empty();

    list.forEach(fileObj => {
        const size    = formatFileSize(fileObj.size);
        const icon    = getFileIcon(fileObj.type, fileObj.name);
        const isSaved = !fileObj.file && fileObj.dataUrl;

        $preview.append(`
            <div class="attachment-file-item" data-file-id="${fileObj.id}">
                <div style="display:flex;align-items:center;min-width:0;flex:1;">
                    <i class="bi ${icon}" style="font-size:0.7rem;margin-right:3px;flex-shrink:0;"></i>
                    <div class="file-info">
                        <span class="file-name" title="${fileObj.name}">${fileObj.name}</span>
                        <span class="file-size">${size} ${isSaved ? '• Saved' : ''}</span>
                    </div>
                </div>
                <div class="file-actions">
                    <button type="button" class="btn btn-xs btn-outline-primary" data-action="view"   data-file-id="${fileObj.id}" title="View"><i class="bi bi-eye"></i></button>
                    <button type="button" class="btn btn-xs btn-outline-danger"  data-action="remove" data-file-id="${fileObj.id}" title="Remove"><i class="bi bi-x"></i></button>
                </div>
            </div>
        `);
    });
}

function removeAttachment(btn, fileId) {
    const $row = $(btn).closest('tr');
    const list = ($row.data('uploadedFiles') || []).filter(f => f.id !== fileId);
    $row.data('uploadedFiles', list);
    displayFilePreviews($row);
}

function viewAttachment(btn, fileId) {
    const $row = $(btn).closest('tr');
    const list = $row.data('uploadedFiles') || [];
    const fileObj = list.find(f => f.id === fileId);
    if (!fileObj) { alert('File not found'); return; }

    let url;
    if (fileObj.file instanceof File)                          url = URL.createObjectURL(fileObj.file);
    else if (fileObj.path)                                     url = BASE_URL + fileObj.path;
    else if (fileObj.dataUrl && fileObj.dataUrl.startsWith('data:')) url = fileObj.dataUrl;
    else { alert('File content not available.'); return; }

    $('#viewerFileName').text(fileObj.name);
    $('#btnDownloadFile').attr({ href: url, download: fileObj.name });

    const $content = $('#documentViewerContent').empty();

    if (fileObj.type && fileObj.type.startsWith('image/')) {
        $content.html(`<img src="${url}" style="max-width:100%;max-height:100%;object-fit:contain;">`);
    } else if (fileObj.type === 'application/pdf') {
        $content.html(`<iframe src="${url}" style="width:100%;height:100%;border:none;"></iframe>`);
    } else {
        $content.html(`
            <div style="text-align:center;">
                <i class="bi bi-file-earmark" style="font-size:4rem;color:#999;"></i>
                <p style="margin-top:10px;color:#666;">Preview not available</p>
                <a href="${url}" download="${fileObj.name}" class="btn btn-primary mt-3">
                    <i class="bi bi-download me-1"></i>Download File
                </a>
            </div>
        `);
    }

    $('#documentViewerModal').modal('show');
}

function clearAttachments($row) {
    $row.data('uploadedFiles', []);
    $row.find('.attachment-preview-list').empty();
    $row.find('.attachment-input').val('');
}

/* ---- Delegated view/remove handlers (survive re-renders) ---- */
$(document).on('click', '[data-action="view"]',   function () { viewAttachment(this, $(this).data('file-id')); });
$(document).on('click', '[data-action="remove"]', function () { removeAttachment(this, $(this).data('file-id')); });