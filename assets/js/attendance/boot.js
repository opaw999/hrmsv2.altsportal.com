/* ==========================================================================
   attendance/boot.js
   Entry point: build the initial DOM, initialize the calendar, bind
   delegated handlers, and handle SPA re-navigation.

   Depends on: every other attendance/*.js file (loaded before this one).

   Sections
     1. Initial boot ($(document).ready)
     2. Event binding (delegated + one-shot)
     3. Interaction tracking (mousedown/keydown guard)
     4. SPA re-initialization (hashchange / popstate / pushState)
     5. Global window exports (functions referenced from inline HTML)
   ========================================================================== */


/* ==========================================================================
   1. INITIAL BOOT
   ========================================================================== */

$(document).ready(function () {
    buildHoursTableBody();
    initializeCalendar();
    rebindEventHandlers();
});

// Prevent mouse-wheel scroll from changing time inputs
$(document).on('wheel', '.input_chk', function (e) {
    if (document.activeElement === this) {
        e.preventDefault();
    }
});


/* ==========================================================================
   2. EVENT BINDING
   All listeners are namespaced or delegated so re-initialization is safe.
   ========================================================================== */

function rebindEventHandlers() {
    // ---- Confirm button ----
    $('#btnConfirmAttendance').off('click.sav').on('click.sav', saveAttendance);

    // ---- Modal open: init flatpickr, apply leave locks, fetch GPS ----
    $('#timeOffModal').off('shown.bs.modal.sav').on('shown.bs.modal.sav', function () {
        initializeTimeTableForTimeOff();

        if (Object.keys(state.currentBlockedHours).length > 0) {
            lockLeaveCoveredHours(state.currentBlockedHours);
        }

        getUserLocation(function (location) {
            if (location && !location.error) {
                window.currentUserLocation = location;
                if (!state.isViewingSavedRecord) {
                    $('.geo-location-text').text(location.address);
                }
            } else {
                window.currentUserLocation = {
                    address:  DEFAULT_LOCATION,
                    coords:   'Unavailable',
                    accuracy: 0,
                    error:    location ? location.error : 'Unknown'
                };
                if (!state.isViewingSavedRecord) {
                    $('.geo-location-text').text(DEFAULT_LOCATION);
                }
            }
        });
    });

    // ---- Select All / Clear All ----
    $('#btnSelectAllHours').off('click').on('click', function () {
        $('#hoursTableBody .tchk:not(:disabled)').each(function () {
            const $row = $(this).closest('tr');
            if ($row.attr('data-locked-group'))              return;
            if ($row.attr('data-leave-covered') === 'true')  return;

            $(this).prop('checked', true);
            activateTimeForTimeOff(this.id);
        });
        $('#checkAllHours').prop('checked', true);
    });

    $('#btnClearAllHours').off('click.sav').on('click.sav', function () {
        $('.tchk').each(function () {
            if (!$(this).closest('tr').attr('data-locked-group')) {
                $(this).prop('checked', false);
                activateTimeForTimeOff(this.id);
            }
        });
        $('#checkAllHours').prop('checked', false);
    });

    $('#checkAllHours').off('change').on('change', function () {
        const isChecked = $(this).is(':checked');

        $('#hoursTableBody .tchk:not(:disabled)').each(function () {
            const $row = $(this).closest('tr');
            if ($row.attr('data-locked-group'))              return;
            if ($row.attr('data-leave-covered') === 'true')  return;

            $(this).prop('checked', isChecked);
            activateTimeForTimeOff(this.id);
        });
    });

    // ---- Dirty tracking (fields) ----
    $(document).off('change.dirty input.dirty').on('change.dirty input.dirty',
        '#hoursTableBody .type-select, #hoursTableBody .category-select, #hoursTableBody .remarks-input, #hoursTableBody input[type="time"]',
        function () {
            if (!state.userInteracting) return;
            markGroupDirty($(this).closest('tr'));
        }
    );

    // ---- Dirty tracking (attachments) ----
    $(document).on('click.dirty', '.attachment-drop-zone, [data-action="remove"]', function () {
        markGroupDirty($(this).closest('tr'));
    });

    // ---- Dirty tracking (checkbox toggle) ----
    $(document).off('click.tchkdirty').on('click.tchkdirty', '#hoursTableBody .tchk', function () {
        const $row = $(this).closest('tr');
        if ($row.attr('data-locked-group')) return;

        const ownMarker = $row.attr('data-saved-group');

        setTimeout(() => {
            const groups   = getMergedGroups();
            const thisRow  = $row[0];

            // Path A: find the group the row belongs to NOW
            let myGroup = null;
            for (const group of groups) {
                if (group.some($r => $r[0] === thisRow)) {
                    myGroup = group;
                    break;
                }
            }

            // Path B: row is gone from all groups (was unchecked) → use captured marker
            if (!myGroup && ownMarker) {
                const $orphans = $('#hoursTableBody tr[data-saved-group="' + ownMarker + '"]');
                if ($orphans.length) $orphans.attr('data-group-dirty', 'true');
                return;
            }

            if (!myGroup || !myGroup.length) return;

            const $first = myGroup[0];
            if (!$first.attr('data-saved-group') && !$first.attr('data-group-id')) return;

            myGroup.forEach($r => $r.attr('data-group-dirty', 'true'));
        }, 0);
    });

    // ---- Modal close: enforce minimum hours ----
    $('#timeOffModal').off('hide.bs.modal.sav').on('hide.bs.modal.sav', function (e) {
        // Programmatic close (after save/delete/cancel) → always allow
        if (state.allowModalClose) {
            state.allowModalClose = false;
            return;
        }

        if (!state.isViewingSavedRecord) return;

        // Open entries are always closable
        const hasOpenEntry = $('#hoursTableBody tr[data-open-entry="true"]').length > 0;
        if (hasOpenEntry) return;

        const hasLeave = state.currentBlockedHours && Object.keys(state.currentBlockedHours).length > 0;
        if (hasLeave) return;

        const groups = getMergedGroups();
        let attendanceHours = 0;
        groups.forEach(group => { attendanceHours += calculateTotalHours(group); });
        attendanceHours = Math.round(attendanceHours * 100) / 100;

        const leaveHours = getLeaveHoursForDay();
        const effective  = Math.round((attendanceHours + leaveHours) * 100) / 100;

        // 0 hours → nothing filed yet, allow
        if (effective <= 0) return;

        // Meets minimum → allow
        if (effective >= MIN_HOURS) return;

        // In between → block + warn
        e.preventDefault();
        e.stopImmediatePropagation();

        const breakdown = leaveHours > 0
            ? ` (Attendance: ${attendanceHours} hr${attendanceHours !== 1 ? 's' : ''}, Leave: ${leaveHours} hr${leaveHours !== 1 ? 's' : ''})`
            : '';

        swalWarning(
            'Insufficient Hours',
            `You have only ${effective} hour${effective !== 1 ? 's' : ''}${breakdown}. ` +
            `A minimum of ${MIN_HOURS} hours is required, or remove all entries to close.`
        );
    });
}


/* ==========================================================================
   3. INTERACTION TRACKING
   Mark `state.userInteracting` for a brief window after real user input
   inside the modal. Prevents programmatic changes (e.g. from
   processLoadedRecord) from marking groups dirty.
   ========================================================================== */

['mousedown', 'keydown', 'touchstart'].forEach(function (evt) {
    document.addEventListener(evt, function (e) {
        if ($(e.target).closest('#timeOffModal').length) {
            state.userInteracting = true;
            setTimeout(() => { state.userInteracting = false; }, 500);
        }
    }, true);
});


/* ==========================================================================
   4. SPA RE-INITIALIZATION
   The dashboard can be re-entered via hash links, back/forward, or
   pushState. On each, tear down the calendar and rebuild.
   ========================================================================== */

/**
 * Destroy + rebuild the attendance UI. Retries a few times if #calendar
 * hasn't been rendered into the DOM yet (SPA transition timing).
 */
window.reinitializeAttendance = function (attempt) {
    attempt = attempt || 1;
    const MAX_ATTEMPTS = 20;
    const RETRY_DELAY  = 300;

    const el = document.getElementById('calendar');

    if (!el) {
        if (attempt >= MAX_ATTEMPTS) {
            console.warn('❌ #calendar never appeared after ' + MAX_ATTEMPTS + ' attempts');
            return;
        }
        setTimeout(() => window.reinitializeAttendance(attempt + 1), RETRY_DELAY);
        return;
    }

    // Already rendered into this element → nothing to do
    if (state.calendar && state.calendar.el === el && el.querySelector('.fc-view-harness')) {
        return;
    }

    // Destroy old instance
    if (state.calendar) {
        try { state.calendar.destroy(); } catch (e) { console.warn(e); }
        state.calendar = null;
    }

    // Clear leftover DOM
    el.innerHTML = '';

    // Reset state
    state.selectedDate    = null;
    state.groupCounter    = 1;
    state.isViewingSavedRecord = false;

    // Rebuild
    if ($('#hoursTableBody').length) {
        buildHoursTableBody();
    }

    rebindEventHandlers();
    initializeCalendar();
    console.log('✅ Attendance calendar re-initialized');
};

// ---- Triggers ----

window.addEventListener('hashchange', function () {
    if (location.hash === '#dashboard' || location.hash === '') {
        setTimeout(() => window.reinitializeAttendance(), 50);
    }
});

window.addEventListener('popstate', function () {
    setTimeout(() => window.reinitializeAttendance(), 50);
});

// Intercept pushState so SPA route changes trigger the same rebuild
(function () {
    const originalPush = history.pushState;

    history.pushState = function () {
        const result = originalPush.apply(this, arguments);

        setTimeout(() => {
            const dash = document.getElementById('page-dashboard');
            if (dash && dash.offsetParent !== null) {
                window.reinitializeAttendance();
            }
        }, 50);

        return result;
    };
})();


/* ==========================================================================
   5. GLOBAL WINDOW EXPORTS
   Functions referenced from inline `onclick` / `onchange` attributes in
   the HTML (rendered by modal-rows.js / modal-load.js).
   ========================================================================== */

window.activateTimeForTimeOff   = activateTimeForTimeOff;
window.unlockGroupForTimeOff    = unlockGroupForTimeOff;
window.lockGroupForTimeOff      = lockGroupForTimeOff;
window.deleteGroupForTimeOff    = deleteGroupForTimeOff;
window.cancelOpenEntry          = cancelOpenEntry;
window.handleTypeChange         = handleTypeChange;
window.handleCategoryChange     = handleCategoryChange;
window.triggerFileUpload        = triggerFileUpload;
window.handleFileUpload         = handleFileUpload;