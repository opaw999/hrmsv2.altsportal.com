/* ==========================================================================
   attendance/alerts.js
   SweetAlert2 wrappers with the app's brand color. Depends on: Swal (global)
   ========================================================================== */

var BRAND_COLOR  = '#ff6b4a';
var DANGER_COLOR = '#dc3545';
var MUTED_COLOR  = '#6c757d';

/** Swal.fire with the brand confirm-button color as default. */
function swalFire(opts) {
    return Swal.fire(Object.assign({ confirmButtonColor: BRAND_COLOR }, opts));
}

function swalWarning(title, text) {
    return swalFire({ icon: 'warning', title: title, text: text, confirmButtonText: 'Got it' });
}

function swalError(title, text) {
    return swalFire({ icon: 'error', title: title, text: text, confirmButtonText: 'OK' });
}

/** Auto-closing success toast (2s). Returns the Swal promise. */
function swalSuccess(title, text) {
    return swalFire({ icon: 'success', title: title, text: text, timer: 2000, timerProgressBar: true });
}

/** Blocking spinner; close it with Swal.close(). */
function swalLoading(title) {
    return Swal.fire({
        title: title || 'Processing...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: function () { Swal.showLoading(); }
    });
}

/** Yes/Cancel dialog. Resolves to true if confirmed. `danger` switches to red styling. */
function swalConfirm(title, text, confirmText, cancelText, danger) {
    return Swal.fire({
        icon: danger ? 'warning' : 'question',
        title: title,
        text: text,
        showCancelButton: true,
        confirmButtonColor: danger ? DANGER_COLOR : BRAND_COLOR,
        cancelButtonColor: MUTED_COLOR,
        confirmButtonText: confirmText || 'Yes',
        cancelButtonText: cancelText || 'Cancel',
        reverseButtons: true
    }).then(function (r) { return r.isConfirmed; });
}