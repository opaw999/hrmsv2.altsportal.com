/* ==========================================================================
   attendance/day-off.js
   Change-day-off modal: state detection, dropdown, save, cancel.

   Depends on: config.js, helpers.js, alerts.js, jQuery, Bootstrap modal
   Calls into (resolved at call time):
     calendar.js → refreshCalendarEvents

   Sections
     1. Public entry (open modal)
     2. State detection
     3. Dropdown population
     4. Save / cancel
   ========================================================================== */


/* ==========================================================================
   1. PUBLIC ENTRY
   ========================================================================== */

/**
 * Open the day-off modal for a given date.
 * Behavior depends on the current week's request state:
 *   - pending request   → manage mode  (edit or cancel)
 *   - approved request  → rechange mode (change from current off day)
 *   - default           → create mode   (first request for the week)
 */
function openDayoffModal(dateStr) {
    const s = getDayoffState(dateStr);

    let mode = 'create';
    let pendingRequest = null;

    if (s.state === 'pending-new' || s.state === 'pending-old') {
        mode = 'manage';
        pendingRequest = s.request;
    } else if (s.state === 'approved-new') {
        mode = 'rechange';
    } else if (s.state === 'default') {
        mode = 'create';
    }

    $('#offId').val(pendingRequest ? pendingRequest.id : '');
    $('#dayoffRemarks').val(pendingRequest ? (pendingRequest.remarks || '') : '');

    // -------- Determine the current off date --------
    let currentOffDate;

    if (mode === 'manage') {
        currentOffDate = pendingRequest.old_date;
    } else if (mode === 'rechange') {
        currentOffDate = dateStr;
    } else {
        // Create mode: default off is the Sunday of the clicked date's week
        const ts = new Date(dateStr + 'T00:00:00');
        const sunday = new Date(ts);
        sunday.setDate(sunday.getDate() - ts.getDay());
        currentOffDate = toDateStr(sunday);
    }

    $('#offOldDate').val(currentOffDate);
    $('#old_do').val(
        new Date(currentOffDate + 'T00:00:00').toLocaleDateString('en-US', {
            weekday: 'long', month: 'short', day: 'numeric', year: 'numeric'
        })
    );

    // -------- Status banner --------
    if (mode === 'manage') {
        $('#offStatusBanner').removeClass('d-none');
        $('#offStatusText').text('You have a pending day-off request. Change it below or cancel.');
    } else {
        $('#offStatusBanner').addClass('d-none');
    }

    // -------- Buttons & title --------
    $('#cancelDayoff').toggle(mode === 'manage');

    $('#modalDayoffTitle').text(
        mode === 'manage'  ? 'Manage Day-Off Request' :
        mode === 'rechange' ? 'Change Your Day-Off' :
        'Request Day-Off'
    );

    populateDayoffDropdown(currentOffDate, mode === 'manage' ? pendingRequest.new_date : null);

    $('#modalDayoff').modal('show');
}


/* ==========================================================================
   2. STATE DETECTION
   ========================================================================== */

/**
 * Where does `dateStr` sit in the current week's day-off lifecycle?
 * Returns { state, request } where state is one of:
 *   'pending-new'  | 'pending-old'  | 'approved-new' | 'approved-old'
 *   'default'      (Sunday, no request)
 *   'none'         (regular weekday, no request)
 */
function getDayoffState(dateStr) {
    const dow = new Date(dateStr + 'T00:00:00').getDay();

    // Latest request wins — sort by id DESC
    const sorted = state.dayoffRequests.slice().sort((a, b) => (b.id || 0) - (a.id || 0));

    for (let i = 0; i < sorted.length; i++) {
        const req = sorted[i];
        if (req.new_date === dateStr) {
            return { state: req.status === 'pending' ? 'pending-new' : 'approved-new', request: req };
        }
        if (req.old_date === dateStr) {
            return { state: req.status === 'approved' ? 'approved-old' : 'pending-old', request: req };
        }
    }

    if (dow === 0) return { state: 'default', request: null };
    return { state: 'none', request: null };
}

/**
 * Is `dateStr` a day off?
 * Approved request overrides the default Sunday rule.
 */
function isDateOff(dateStr) {
    const approved = state.dayoffRequests.find(r =>
        r.status === 'approved' && (r.new_date === dateStr || r.old_date === dateStr)
    );

    if (approved) return dateStr === approved.new_date;

    return new Date(dateStr + 'T00:00:00').getDay() === 0;
}


/* ==========================================================================
   3. DROPDOWN POPULATION
   ========================================================================== */

function populateDayoffDropdown(currentOffDate, currentNewDate) {
    const $sel = $('#new_do');
    $sel.empty().append('<option value="">-- Select a day --</option>');

    // Build the current week (Sun–Sat)
    const ts = new Date(currentOffDate + 'T00:00:00');
    const sunday = new Date(ts);
    sunday.setDate(sunday.getDate() - ts.getDay());

    const week = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(sunday);
        d.setDate(sunday.getDate() + i);
        week.push(d);
    }

    const from = toDateStr(week[0]);
    const to   = toDateStr(week[6]);

    // Fetch attendance for this week so we can disable days that already have entries
    $.ajax({
        url: API.attendanceDates + '?from=' + from + '&to=' + to,
        type: 'GET',
        dataType: 'json'
    }).done(res => {
        const attendanceDates = (res && res.success && res.data) ? res.data : [];
        buildDayoffOptions($sel, week, currentOffDate, currentNewDate, attendanceDates);
    }).fail(() => {
        buildDayoffOptions($sel, week, currentOffDate, currentNewDate, []);
    });
}

function buildDayoffOptions($sel, week, currentOffDate, currentNewDate, attendanceDates) {
    week.forEach(d => {
        const value = toDateStr(d);
        const label = d.toLocaleDateString('en-US', {
            weekday: 'long', month: 'short', day: 'numeric'
        });

        const isCurrentOff = (value === currentOffDate);
        const isCurrentNew = (value === currentNewDate);
        const isHoliday    = !!state.holidaysCache[value];
        const isLeave = !!state.leavesCache[value];
        const hasAttend    = attendanceDates.indexOf(value) !== -1;

        let disabled = '';
        let suffix   = '';

        if (isHoliday) {
            disabled = 'disabled';
            suffix   = ' (holiday)';
        } else if (hasAttend) {
            disabled = 'disabled';
            suffix   = ' (has attendance)';
        } else if (isLeave) {
            disabled = 'disabled';
            suffix   = ' (has leave)';
        } else if (isCurrentNew) {
            disabled = 'disabled';
            suffix   = ' (current request)';
        } else if (isCurrentOff) {
            suffix   = ' (revert to this)';
        }

        const $opt = $('<option>', { value: value, disabled: !!disabled }).text(label + suffix);
        if (isCurrentNew) $opt.prop('selected', true);
        $sel.append($opt);
    });
}


/* ==========================================================================
   4. SAVE / CANCEL
   ========================================================================== */

$(document).on('click', '#saveDayoff', function () {
    const oldDate = $('#offOldDate').val();
    const newDate = $('#new_do').val();
    const remarks = $('#dayoffRemarks').val() || '';

    if (!newDate) {
        swalWarning('No Date Selected', 'Please pick a new day off.');
        return;
    }
    if (newDate === oldDate) {
        swalWarning('No Change', 'You picked the same day.');
        return;
    }

    submitDayoffChange(oldDate, newDate, remarks);
});

$(document).on('click', '#cancelDayoff', function () {
    const reqId = $('#offId').val();
    if (!reqId) {
        swalWarning('Nothing to Cancel', 'No pending request found.');
        return;
    }

    $('#modalDayoff').modal('hide');
    cancelDayoffRequest(reqId);
});

$(document).on('hidden.bs.modal', '#modalDayoff', function () {
    $('#offId').val('');
    $('#offOldDate').val('');
    $('#new_do').empty();
    $('#dayoffRemarks').val('');
    $('#offStatusBanner').addClass('d-none');
});

function submitDayoffChange(oldDate, newDate, remarks) {
    swalLoading('Saving...');

    $.ajax({
        url: API.saveDayoff,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            old_date: oldDate,
            new_date: newDate,
            remarks: remarks || 'Day-off change'
        }),
        dataType: 'json'
    }).done(response => {
        if (!response.success) {
            swalError('Change Failed', response.message || 'Could not change day off.');
            return;
        }

        $('#modalDayoff').modal('hide');

        swalSuccess('Saved!', 'Your day-off request has been submitted.').then(() => {
            refreshCalendarEvents();
        });
    }).fail(() => {
        swalError('Network Error', 'Could not connect to the server.');
    });
}

function cancelDayoffRequest(requestId) {
    swalConfirm(
        'Cancel day-off request?',
        'This will remove your pending request. You can file attendance on the requested date afterwards.',
        'Yes, cancel it',
        'Keep it',
        true
    ).then(confirmed => {
        if (!confirmed) return;

        swalLoading('Cancelling...');

        $.ajax({
            url: API.cancelDayoff,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: requestId }),
            dataType: 'json'
        }).done(res => {
            if (!res.success) {
                swalError('Cancel Failed', res.message || 'Could not cancel.');
                return;
            }
            swalSuccess('Cancelled!', 'Your day-off request has been removed.').then(() => {
                refreshCalendarEvents();
            });
        }).fail(() => {
            swalError('Network Error', 'Could not connect to the server.');
        });
    });
}