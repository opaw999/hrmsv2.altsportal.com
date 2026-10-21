/* ==========================================================================
   attendance/modal-load.js
   Opening the attendance modal: date click → leaf/leave checks → load record
   → build DOM groups → render each group.

   Depends on: config.js, helpers.js, alerts.js, jQuery, flatpickr
   Depends on DOM: #timeOffModal, #hoursTableBody, #dateInfoTable
   Calls into (resolved at call time):
     calendar.js   → refreshCalendarEvents, isDateOff
     day-off.js    → getDayoffState, openDayoffModal
     modal-rows.js → insertSubRow, buildHoursTableBody, updateDynamicInputsForTimeOff
     modal-save.js → (none — save flows run the other direction)

   Sections
     1. Date click router
     2. Modal reset & setup
     3. Load API record
     4. transformApiRecord (raw API → JS)
     5. processLoadedRecord (JS → DOM) + step helpers
     6. Group display (regular / open / spanning)
     7. Time-range summary & date-info table
     8. Leave blocks (lockLeaveCoveredHours)
     9. Leave-hours helpers (getLeaveHoursForDay, hasLeaveInHalfDay)
   ========================================================================== */


/* ==========================================================================
   1. DATE CLICK ROUTER
   ========================================================================== */

/**
 * Handle a click on a day cell.
 * Order of precedence:
 *   holiday → day-off request/state → whole-day leave → hourly leave flow
 */
function handleDateClick(date) {
    // 1. Holiday
    if (state.holidaysCache[date]) {
        showHolidayInfo(state.holidaysCache[date]);
        return;
    }

    // 2. Day-off logic
    const s = getDayoffState(date);
    if (s.state === 'pending-new' || s.state === 'pending-old' ||
        s.state === 'default'     || s.state === 'approved-new') {
        openDayoffModal(date);
        return;
    }

    // 3. Whole-day leave — no API call needed
    const leaveInfo = state.leavesCache[date];
    if (leaveInfo && leaveInfo.isWholeDay) {
        showLeaveInfo(leaveInfo);
        return;
    }

    // 4. Fetch blocked leave hours, then open the modal
    state.selectedDate = date;
    state.isViewingSavedRecord = false;

    swalLoading('Checking leaves...');

    $.ajax({
        url: API.leaveBlockedHours + '?date=' + date,
        type: 'GET',
        dataType: 'json'
    }).done(res => {
        Swal.close();

        const blocked = (res && res.success && res.data) ? res.data : {};
        const blockedHourCount = Object.keys(blocked).length;

        // Safety net: if the API says every hour is blocked, treat as whole-day
        if (blockedHourCount >= 11) {
            if (leaveInfo) showLeaveInfo(leaveInfo);
            else swalWarning('On Leave', 'You have a full-day leave on this date.');
            return;
        }

        state.currentBlockedHours = blocked;

        openAttendanceModal(date);
    }).fail(() => {
        Swal.close();
        state.currentBlockedHours = {};

        if (leaveInfo) {
            showLeaveInfo(leaveInfo);
            return;
        }

        openAttendanceModal(date);
    });
}

/** Reset + open the attendance modal for the given date. */
function openAttendanceModal(date) {
    resetModalState();
    updateDateInfoTable(date);
    updateGeoLocationDates(date);

    $('#hoursTableBody tr').removeAttr('data-leave-covered data-leave-code data-leave-range');
    $('#hoursTableBody .leave-covered-badge').remove();

    if (Object.keys(state.currentBlockedHours).length > 0) {
        lockLeaveCoveredHours(state.currentBlockedHours);
    }

    // ⭐ Show loading before fetching
    showModalLoading('Loading attendance...');

    loadAttendanceForDate(date);

    $('#timeOffModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    $('#timeOffModal').modal('show');
}


/* ==========================================================================
   2. MODAL RESET & SETUP
   ========================================================================== */

/**
 * Wipe the modal back to a fresh state.
 * `skipLeaveCleanup` is true while loading a saved record (leave markers are
 * reapplied afterwards).
 */
function resetModalState(skipLeaveCleanup) {

    //hideModalLoading();
    const missingBaseRows = HOURS.some(h => $('#' + h).length === 0);
    if (missingBaseRows) {
        buildHoursTableBody();
    }

    state.rangeStartKey = null;

    $('.tchk').prop('checked', false);
    $('#checkAllHours').prop('checked', false);

    // Remove every sub-row
    $('#hoursTableBody tr[data-sub="true"]').each(function () {
        const key = $(this).find('.tchk').attr('id');
        destroyFlatpickrFor(key);
        $(this).remove();
    });

    // Reset main rows to their default times (12h display)
    HOURS.forEach(h => {
        const start12 = formatTo12h(hourStart(h));
        const end12   = formatTo12h(hourEnd(h));

        $(`#time${h}_1`).prop('disabled', true).val(start12);
        $(`#time${h}_2`).prop('disabled', true).val(end12);
    });

    if (!skipLeaveCleanup) {
        $('#hoursTableBody tr').removeAttr('data-leave-covered data-leave-code data-leave-range');
        $('#hoursTableBody .leave-covered-badge').remove();
    }

    // Wipe markers & content on every non-leave-covered row
    $('#hoursTableBody tr').each(function () {
        const $row = $(this);

        if ($row.attr('data-leave-covered') === 'true') return;

        $row.removeAttr(
            'data-locked-group data-saved-group data-group-id ' +
            'data-open-entry data-status data-spanning-entry data-gap data-gap-role ' +
            'data-type data-sort-start ' +
            'data-leave-covered data-leave-code data-leave-range data-leave-gap ' +
            'data-biometric-range'
        );
        $row.removeData(
            'originalLocation originalLat originalLng originalAccuracy ' +
            'updatedLocation status uploadedFiles saved-start saved-end'
        );
        $row.find('input[type="time"], .input_chk').removeAttr('data-real-start');

        $row.find('.tchk').prop('disabled', false);
        $row.children('td').slice(3, 9).show().removeAttr('rowspan').empty();
        $row.data('uploadedFiles', []);
    });

    // Any orphan sub-rows (letter-suffixed ids)
    $('#hoursTableBody tr').each(function () {
        const $chk = $(this).find('.tchk');
        if (!$chk.length) return;

        const id = $chk.attr('id') || '';
        if (/^[0-9]+[a-z]$/.test(id)) {
            destroyFlatpickrFor(id);
            $(this).remove();
        }
    });

    state.groupCounter = 1;

    // Restore the lunch-break row
    $('#hoursTableBody tr').removeClass('lunch-break-row');
    const $h12 = $('#hoursTableBody tr').has('#' + LUNCH_HOUR);
    $h12.addClass('lunch-break-row');
    $h12.children('td').eq(3).html('<span class="lunch-break-badge" id="lunchBreakBadge">🍽️ Lunch Break</span>');

    // Clear the summary line
    $('#infoIn1, #infoOut1, #infoIn2, #infoOut2, #infoHoursWork').text('-');
}

/** Destroy a row's two flatpickr instances (safe if not initialized). */
function destroyFlatpickrFor(rowKey) {
    const s = document.getElementById(`time${rowKey}_1`);
    const e = document.getElementById(`time${rowKey}_2`);
    if (s && s._flatpickr) try { s._flatpickr.destroy(); } catch (err) {}
    if (e && e._flatpickr) try { e._flatpickr.destroy(); } catch (err) {}
}

function updateDateInfoTable(date) {
    const formatted = formatDisplayDate(date);
    const d = new Date(date + 'T00:00:00');

    $('#selectedDateDisplay').text(formatted);
    $('#infoDate').text(formatted);
    $('#infoDay').text(d.toLocaleDateString('en-US', { weekday: 'long' }));
}

function updateGeoLocationDates(date) {
    $('.geo-date-text').text(formatDisplayDate(date));
}


/* ==========================================================================
   3. LOAD API RECORD
   ========================================================================== */

function loadAttendanceForDate(date) {
    $.ajax({
        url: API.load + '/' + date,
        type: 'GET',
        dataType: 'json'
    }).done(response => {
        if (!response.success || !response.data) {
            hideModalLoading();
            $('#btnConfirmAttendance').html('<i class="bi bi-check-circle me-1"></i> Confirm');
            return;
        }

        const run = () => {
            state.isViewingSavedRecord = true;
            processLoadedRecord(transformApiRecord(response.data));
        };

        //$('#registered-location').text(response.data.location?.[0]?.location ? response.data.location?.[0]?.location : '');
        // ⭐ If the modal is fully shown, run now.
        //    Otherwise, wait for `shown.bs.modal`.
        if ($('#timeOffModal').hasClass('show')) {
            run();
        } else {
            $('#timeOffModal').one('shown.bs.modal.load', run);
        }
    }).fail(() => {
        hideModalLoading();
        $('#btnConfirmAttendance').html('<i class="bi bi-check-circle me-1"></i> Confirm');
    });
}


/* ==========================================================================
   4. transformApiRecord — raw API → JS
   ========================================================================== */

/**
 * Convert the API response into the shape the display pipeline expects:
 *   { date, submittedAt, submittedAtDisplay, dateInfo,
 *     timeEntries: [...], groups: [...] }
 *
 * Side effects:
 *   - Hour collisions between different groups get relocated to sub-rows
 *     (see resolveEntryCollisions).
 */
function transformApiRecord(rec) {
    const timeEntries = (rec.time_entries || []).map(e => {
        const rawEnd = e.end_time || '';
        const isOpen = (!rawEnd || rawEnd === '00:00:00' || rawEnd === '0');

        const hour = Math.max(FIRST_HOUR, Math.min(LAST_HOUR, parseInt(e.hour, 10)));
        const actualStartH = parseInt((e.start_time || '00:00:00').split(':')[0], 10);

        let startTime12 = to12h(e.start_time);
        let endTime12;

        // ⭐ Preserve the original times for display purposes
        const realStartTime = to12h(e.start_time);
        const realEndTime   = isOpen ? null : to12h(rawEnd);

        if (isOpen) {
            // Open entry: show real start if it's within the displayed hour,
            // otherwise snap to the hour's :00 boundary
            const displayH = Math.max(FIRST_HOUR, actualStartH);
            const hourStartStr = String(displayH).padStart(2, '0') + ':00';

            if (actualStartH === displayH && e.start_time) {
                // Real start is inside the display hour → use it
                startTime12 = to12h(e.start_time);      // e.g. "1:27 PM"
            } else {
                // Start is below FIRST_HOUR or otherwise outside → snap to :00
                startTime12 = formatTo12h(hourStartStr);
            }

            endTime12 = formatTo12h(String(displayH).padStart(2, '0') + ':59');
        } else {
            endTime12 = to12h(rawEnd);
            if (actualStartH < FIRST_HOUR) {
                startTime12 = formatTo12h(hourStart(FIRST_HOUR));
            }
        }

        const rawRowKey = e.row_key || String(hour);
        const parsedKey = parseRowKey(rawRowKey);
        const rowKey = (parsedKey && parsedKey.hour < FIRST_HOUR)
            ? String(FIRST_HOUR)
            : rawRowKey;

        return {
            hour,
            rowKey,
            startTime:     startTime12,      // clamped — for inputs
            endTime:       endTime12,        // clamped — for inputs
            realStartTime: realStartTime,    // ⭐ original — for display
            realEndTime:   realEndTime,      // ⭐ original (null if open)
            groupId:       e.group_id,
            isOpen
        };
    });

    // ---- Resolve hour collisions ----
    resolveEntryCollisions(timeEntries, rec);

    // ---- Build groups ----
    const groups = (rec.groups || []).map(g => {
        const entries = timeEntries.filter(e => e.groupId == g.id);
        const isSpanning = (g.type === 'regular');

        return {
            id:               g.id,
            type:             g.type,
            status:           g.status,
            category:         g.category,
            remarks:          g.remarks,
            timeRange:        g.time_range,
            totalHours:       g.total_hours,
            location:         g.location,
            latitude:         g.latitude,
            longitude:        g.longitude,
            accuracy:         g.accuracy,
            updatedLocation:  g.updated_location,
            updatedLatitude:  g.updated_latitude,
            updatedLongitude: g.updated_longitude,
            submittedAt:      g.created_at ? formatDateTimeDisplay(g.created_at) : '',
            updatedAt:        g.updated_at ? formatDateTimeDisplay(g.updated_at) : '',
            hours:            entries.map(e => e.hour),
            rowKeys:          entries.map(e => e.rowKey),
            isOpen:           entries.some(e => e.isOpen),
            isSpanning:       isSpanning,
            rawEntries:       entries,
            attachments:      (g.attachments || []).map(a => ({
                name:    a.file_name,
                path:    a.file_path,
                dataUrl: null,
                size:    parseInt(a.file_size, 10),
                type:    a.file_type
            }))
        };
    });

    return {
        date:               rec.attendance_date,
        submittedAt:        rec.submitted_at,
        submittedAtDisplay: formatDateTimeDisplay(rec.submitted_at),
        dateInfo: {
            in1:       to12h(rec.in1), out1: to12h(rec.out1),
            in2:       to12h(rec.in2), out2: to12h(rec.out2),
            hoursWork: rec.hours_worked
        },
        timeEntries: timeEntries,
        groups:      groups
    };
}

/**
 * When two entries from DIFFERENT groups share the same hour, the earliest
 * keeps the main row; later ones get relocated to sub-rows (10a, 10b, ...).
 *
 * Tiebreak: an in-office / out-of-office group always wins the main row over
 * a biometric (regular) group. The biometric merges into the same row later
 * as a "Has Entry" chip (see annotateOverlappingBiometrics).
 */
function resolveEntryCollisions(timeEntries, record) {
    const groupById = {};
    (record.groups || []).forEach(g => { groupById[String(g.id)] = g; });

    // Build hour → occupants list
    const byHour = {};

    timeEntries.forEach(entry => {
        const group = groupById[String(entry.groupId)];
        const isSpanning = group && group.type === 'regular';

        if (!isSpanning) {
            const h = entry.hour;
            if (!byHour[h]) byHour[h] = [];
            byHour[h].push({
                entry: entry,
                groupId: String(entry.groupId),
                sliceStart: timeToMinutes(entry.startTime)
            });
            return;
        }

        const startH = hourFrom12h(entry.startTime);
        const endH   = hourFrom12h(entry.endTime);

        for (let h = startH; h <= endH; h++) {
            if (!byHour[h]) byHour[h] = [];

            const hourStr = String(h).padStart(2, '0');
            const sliceStart = (h === startH)
                ? timeToMinutes(entry.startTime)
                : timeToMinutes(formatTo12h(hourStr + ':00'));

            if (!byHour[h].some(x => x.entry === entry)) {
                byHour[h].push({
                    entry: entry,
                    groupId: String(entry.groupId),
                    sliceStart: sliceStart
                });
            }
        }
    });

    // For each hour with multiple occupants, relocate later ones
    Object.keys(byHour).forEach(hourStr => {
        const hour = parseInt(hourStr, 10);
        const occupants = byHour[hour];

        const groupIds = new Set(occupants.map(o => o.groupId));
        if (groupIds.size <= 1) return;

        // ⭐ Sort: by slice start; ties broken in favor of in-office / out-of-office.
        //    Biometric (regular) groups lose the tie so they merge into the
        //    in-office row instead of stealing the main slot.
        occupants.sort((a, b) => {
            if (a.sliceStart !== b.sliceStart) return a.sliceStart - b.sliceStart;

            const aGroup = groupById[a.groupId];
            const bGroup = groupById[b.groupId];
            const aIsBiometric = aGroup && aGroup.type === 'regular';
            const bIsBiometric = bGroup && bGroup.type === 'regular';

            if (aIsBiometric !== bIsBiometric) return aIsBiometric ? 1 : -1;
            return 0;
        });

        for (let i = 1; i < occupants.length; i++) {
            const occ = occupants[i];

            // Only relocate if we can find a matching DB entry for this group
            // at this hour. Spanning groups have no DB entry for middle hours
            // — nothing to relocate, which is exactly what we want.
            const target = timeEntries.find(e =>
                e.groupId == occ.groupId &&
                e.hour === hour &&
                !e.relocatedToSubRow
            );

            if (target) {
                target.rowKey = getRowKey(hour, i);
                target.relocatedToSubRow = true;
            }
        }
    });
}

function sortAllRowsByStartTime() {
    const sortKey = ($row) => {
        const stored = parseFloat($row.attr('data-sort-start'));
        if (!isNaN(stored)) return stored;
        const key = $row.find('.tchk').attr('id');
        return timeToMinutes($('#time' + key + '_1').val());
    };

    HOURS.forEach(h => {
        let $mainRow = $();
        $('#hoursTableBody tr').has('.tchk').each(function () {
            const key = $(this).find('.tchk').attr('id');
            if (key === String(h)) { $mainRow = $(this); return false; }
        });
        if (!$mainRow.length) return;

        const rows = [{ $row: $mainRow, startMin: sortKey($mainRow) }];
        let $next = $mainRow.next('tr');
        while ($next.length && $next.attr('data-sub') === 'true') {
            const subKey = $next.find('.tchk').attr('id');
            const subParsed = parseRowKey(subKey);
            if (!subParsed || subParsed.hour !== h) break;

            rows.push({ $row: $next, startMin: sortKey($next) });
            $next = $next.next('tr');
        }
        if (rows.length <= 1) return;

        const $before = $mainRow.prev('tr');
        rows.forEach(item => item.$row.detach());
        rows.sort((a, b) => a.startMin - b.startMin);

        let $anchor = $before;
        rows.forEach(item => {
            if ($anchor.length) $anchor.after(item.$row);
            else $('#hoursTableBody').prepend(item.$row);
            $anchor = item.$row;
        });
    });
}


/* ==========================================================================
   5. processLoadedRecord — JS → DOM
   Runs in 3 timed phases so each has a settled DOM to work against.
   ========================================================================== */

function processLoadedRecord(record) {
    // Phase 1 (immediate): reset and mark up rows
    resetModalState(true);
    $('#hoursTableBody tr').removeAttr('data-saved-group');

    createSubRowsFromEntries(record);
    applySavedGroupMarkers(record);
    checkBoxesForRecord(record);
    handleLunchBreakRow(record);

    // Phase 2: values + group data
    setTimeout(() => {
        try {
            applyTimeEntryValues(record);

            record.timeEntries.forEach(e => {
                const parsed = parseRowKey(e.rowKey);
                if (!parsed || parsed.subIndex !== 0) return;
                syncHeadGap(e.rowKey);
            });
            applyGroupDataForRecord(record);
            rebuildMissingHourGaps();
            updateDateInfoFromRecord(record);

            $('#btnConfirmAttendance').html('<i class="bi bi-check-circle me-1"></i> Update');
            updateTimeRangeSummary();

            // Phase 3: re-sync flatpickr + cascade
            setTimeout(() => {
                reapplyFlatpickrTimes(record);
                reapplySavedGroupMarkers(record);
                refreshEndMaxForAllRows();
                updateTimeRangeSummary();

                setTimeout(() => {
                    cascadeFromMainRows(record);
                    updateTimeRangeSummary();

                    setTimeout(() => {
                        deriveUncheckedMainRowsFromSubRows();
                        updateTimeRangeSummary();

                        // ⭐ Hide loading — data is fully rendered
                        hideModalLoading();
                    }, 150);
                }, 100);
            }, 200);
        } catch (err) {
            // ⭐ Safety net — always hide loading on error
            console.error('[processLoadedRecord] failed:', err);
            hideModalLoading();
        }
    }, 300);

    // Leave-covered lock always runs last
    setTimeout(() => {
        if (Object.keys(state.currentBlockedHours).length > 0) {
            lockLeaveCoveredHours(state.currentBlockedHours);
        }
    }, 500);
}

/* ---- Phase 1 steps ---- */

/**
 * Ensure the DOM has a row (main or sub) for every entry's rowKey.
 *
 * Note: this function does NOT create any "gap" rows. Gaps are a live-editing
 * affordance and are only created when the user edits a row's start or end.
 * On load, the record's saved entries already represent the real rows; any
 * uncovered minutes are simply empty space the user can fill by editing.
 */
function createSubRowsFromEntries(record) {
    if (!record.timeEntries || !record.timeEntries.length) return;

    record.timeEntries.forEach(entry => {
        const parsed = parseRowKey(entry.rowKey);
        if (!parsed) return;

        const hour = parsed.hour;

        // Main row already exists in the base table — nothing to do.
        if (parsed.subIndex === 0) return;

        // Only create the exact sub-row this entry references — do NOT build
        // a filler parent chain for intermediate indices.
        const key = getRowKey(hour, parsed.subIndex);
        if ($('#' + key).length > 0) return;   // already exists

        // Find the best anchor: closest preceding sub-row, or the main row.
        let $anchor = $();
        for (let i = parsed.subIndex - 1; i >= 1; i--) {
            const prevKey = getRowKey(hour, i);
            if ($('#' + prevKey).length) {
                $anchor = $('#' + prevKey).closest('tr');
                break;
            }
        }
        if (!$anchor.length) {
            $anchor = getMainRowForHour(hour);
        }
        if (!$anchor.length) return;

        insertSubRow($anchor, key, hour, hourStart(hour), hourEnd(hour));
    });
}

/** Mark rows with `data-group-id`, `data-saved-group`, and `data-type`. */
function applySavedGroupMarkers(record) {
    if (!record.groups) return;

    record.groups.forEach((g, i) => {
        (g.rowKeys || (g.hours || []).map(String)).forEach(key => {
            const $tr = $('#' + key).closest('tr');
            if (!$tr.length) return;

            $tr.attr('data-saved-group', 'saved_group_' + i);
            if (g.id) $tr.attr('data-group-id', g.id);
            $tr.attr('data-type', g.type);   // ⭐ NEW
        });
    });
}

function checkBoxesForRecord(record) {
    if (!record.timeEntries) return;

    record.timeEntries.forEach(e => {
        const $chk = $('#' + e.rowKey);
        if ($chk.closest('tr').attr('data-leave-covered') === 'true') return;

        $chk.prop('checked', true);
        $(`#time${e.rowKey}_1, #time${e.rowKey}_2`).prop('disabled', false);
    });

    record.timeEntries.forEach(e => {
        const $chk = $('#' + e.rowKey);
        if ($chk.closest('tr').attr('data-leave-covered') === 'true') return;
        activateTimeForTimeOff(e.rowKey);
    });
}

function handleLunchBreakRow(record) {
    const hour12Checked = record.timeEntries.some(e => e.hour === LUNCH_HOUR);
    const $h12 = $('#hoursTableBody tr').has('#' + LUNCH_HOUR);

    if (hour12Checked) {
        $h12.removeClass('lunch-break-row');
    } else {
        $h12.addClass('lunch-break-row');
        $h12.children('td').eq(3).html('<span class="lunch-break-badge" id="lunchBreakBadge">🍽️ Lunch Break</span>');
    }
}

/* ---- Phase 2 steps ---- */

function applyTimeEntryValues(record) {
    record.timeEntries.forEach(e => {
        const group = (record.groups || []).find(g => g.id == e.groupId);
        if (group && group.isSpanning && !group.isOpen) return;

        const s  = document.getElementById(`time${e.rowKey}_1`);
        const en = document.getElementById(`time${e.rowKey}_2`);

        if (s)  { s.value = e.startTime;  if (s._flatpickr)  s._flatpickr.setDate(e.startTime, false); }
        if (en) { en.value = e.endTime;   if (en._flatpickr) en._flatpickr.setDate(e.endTime, false); }

        // ⭐ ADD THIS BLOCK
        const $row = $('#' + e.rowKey).closest('tr');
        if ($row.length && e.startTime) {
            $row.attr('data-sort-start', timeToMinutes(e.startTime));
        }
    });
}

function applyGroupDataForRecord(record) {
    const dbGroupsById = {};
    (record.groups || []).forEach(g => {
        if (g.id) dbGroupsById[String(g.id)] = g;
    });

    const dbGroupsBySignature = {};
    (record.groups || []).forEach(g => {
        const sig = (g.rowKeys || g.hours || []).map(String).sort().join('|');
        if (sig) dbGroupsBySignature[sig] = g;
    });

    const otherRangesByHour = buildOtherRangesByHour(record.groups);

    const groups = getMergedGroups();

    // Chronological: earlier-starting groups claim their main rows first
    groups.sort((a, b) => {
        const ap = parseRowKey(a[0].find('.tchk').attr('id'));
        const bp = parseRowKey(b[0].find('.tchk').attr('id'));
        if (!ap || !bp) return 0;
        if (ap.hour !== bp.hour) return ap.hour - bp.hour;
        return ap.subIndex - bp.subIndex;
    });

    groups.forEach((group, idx) => {
        if (!group.length) return;

        const $first = group[0];
        const domKeys = group.map($r => $r.find('.tchk').attr('id')).sort().join('|');

        // Prefer signature lookup — data-group-id might be stale after another
        // spanning group has stamped these rows.
        let groupData = dbGroupsBySignature[domKeys];
        if (!groupData) {
            const dbId = $first.attr('data-group-id');
            groupData = dbId ? dbGroupsById[String(dbId)] : null;
        }
        if (!groupData) return;

        applyGroupData($first, groupData, idx, record.submittedAtDisplay, otherRangesByHour);

        if (groupData.isOpen)     return;
        if (groupData.isSpanning) return;   // applySpanningEntryDisplay already locked

        lockGroupAfterLoad(group);
    });
    sortAllRowsByStartTime();
    annotateOverlappingBiometrics(record);   // ⭐ NEW
}

/** { hour: [{ groupId, start, end }] } for every hour any group's entry touches. */
function buildOtherRangesByHour(groups) {
    const map = {};

    (groups || []).forEach(g => {
        (g.rawEntries || []).forEach(e => {
            const startH = hourFrom12h(e.startTime);
            const endH   = e.endTime ? hourFrom12h(e.endTime) : startH;

            for (let hh = startH; hh <= endH; hh++) {
                if (!map[hh]) map[hh] = [];
                map[hh].push({
                    groupId: String(g.id || ''),
                    start:   to24h(e.startTime),
                    end:     e.endTime ? to24h(e.endTime) : null
                });
            }
        });
    });

    return map;
}

function updateDateInfoFromRecord(record) {
    if (!record.dateInfo) return;

    $('#infoIn1').text(record.dateInfo.in1 || '-');
    $('#infoOut1').text(record.dateInfo.out1 || '-');
    $('#infoIn2').text(record.dateInfo.in2 || '-');
    $('#infoOut2').text(record.dateInfo.out2 || '-');

    // Approved-only total
    let approvedHours = 0;
    (record.groups || []).forEach(g => {
        const s = String(g.status || '').toLowerCase();
        if (g.type === 'inoffice' || s === 'approved') {
            approvedHours += parseFloat(g.totalHours) || 0;
        }
    });
    approvedHours = Math.round(approvedHours * 100) / 100;
    if (approvedHours > MAX_HOURS) approvedHours = MAX_HOURS;

    $('#infoHoursWork').text(approvedHours > 0 ? approvedHours : '-');
}

/**
 * After entry values have been applied, clip each gap row's end time
 * so it doesn't overlap the earliest non-gap row that starts inside
 * the same hour.
 */
// function clipGapRows() {
//     HOURS.forEach(h => {
//         const $rowsInHour = $('#hoursTableBody tr').has('.tchk').filter(function () {
//             const p = parseRowKey($(this).find('.tchk').attr('id'));
//             return p && p.hour === h;
//         });

//         // Collect ranges from non-gap rows that have both values set.
//         // (Gap rows — head or tail — are not real coverage.)
//         const nonGapRanges = [];
//         $rowsInHour.each(function () {
//             if ($(this).attr('data-gap') === 'true') return;
//             const key = $(this).find('.tchk').attr('id');
//             const s = document.getElementById(`time${key}_1`);
//             const e = document.getElementById(`time${key}_2`);
//             if (!s || !e || !s.value || !e.value) return;
//             nonGapRanges.push({ start: to24h(s.value), end: to24h(e.value) });
//         });

//         if (!nonGapRanges.length) return;

//         // Clip each TAIL gap against the earliest non-gap start inside this hour.
//         // (Head gaps are already correct by construction — syncHeadGap owns them.)
//         $rowsInHour.each(function () {
//             if ($(this).attr('data-gap') !== 'true') return;
//             if ($(this).attr('data-gap-role') === 'head') return;
//             const key = $(this).find('.tchk').attr('id');
//             const s = document.getElementById(`time${key}_1`);
//             const e = document.getElementById(`time${key}_2`);
//             if (!s || !e) return;

//             const gapStart24 = to24h(s.value);
//             const hourEndStr = hourEnd(h);

//             const candidates = nonGapRanges
//                 .filter(r => r.start > gapStart24 && r.start <= hourEndStr)
//                 .sort((a, b) => a.start.localeCompare(b.start));

//             if (candidates.length) {
//                 const clipped = subtractOneMinute(candidates[0].start);
//                 e.value = formatTo12h(clipped);
//                 if (e._flatpickr) e._flatpickr.setDate(formatTo12h(clipped), false);
//             }
//         });
//     });
// }
/* ---- Phase 3 steps ---- */

function reapplyFlatpickrTimes(record) {
    record.timeEntries.forEach(e => {
        const group = (record.groups || []).find(g => g.id == e.groupId);
        if (group && group.isSpanning && !group.isOpen) return;

        const parsed   = parseRowKey(e.rowKey);
        const hourStr  = String(parsed.hour).padStart(2, '0');
        const maxTime  = hourEnd(parsed.hour);
        const start24  = to24h(e.startTime);

        const s  = document.getElementById(`time${e.rowKey}_1`);
        const en = document.getElementById(`time${e.rowKey}_2`);

        if (s && s._flatpickr) {
            s._flatpickr.set('minTime', hourStart(parsed.hour));
            s._flatpickr.set('maxTime', maxTime);
            s.value = e.startTime;
            s._flatpickr.setDate(e.startTime, false);
        }
        if (en && en._flatpickr) {
            en._flatpickr.set('minTime', start24);
            en._flatpickr.set('maxTime', maxTime);
            en.value = e.endTime;
            en._flatpickr.setDate(e.endTime, false);
        }
    });
}

function reapplySavedGroupMarkers(record) {
    if (!record.groups) return;

    record.groups.forEach((g, i) => {
        (g.rowKeys || (g.hours || []).map(String)).forEach(key => {
            $('#' + key).closest('tr').attr('data-saved-group', 'saved_group_' + i);
        });
    });
}

function cascadeFromMainRows(record) {
    record.timeEntries
        .filter(e => parseRowKey(e.rowKey).subIndex === 0)
        .filter(e => {
            const group = (record.groups || []).find(g => g.id == e.groupId);
            return !(group && group.isSpanning);
        })
        .forEach(entry => {
            const nextHour = entry.hour + 1;
            if (nextHour > LAST_HOUR || !entry.endTime) return;

            const nextStart = document.getElementById(`time${nextHour}_1`);
            if (!nextStart) return;

            const currentSaved = $('#' + entry.rowKey).closest('tr').attr('data-saved-group');
            const nextSaved    = $('#' + nextHour).closest('tr').attr('data-saved-group');
            const differentGroup = currentSaved && nextSaved && currentSaved !== nextSaved;

            const nextHourSelected = record.timeEntries.some(e =>
                e.hour === nextHour && parseRowKey(e.rowKey).subIndex === 0
            );
            if (nextHourSelected && differentGroup) return;

            if (getSubRowAfter(entry.rowKey).length > 0) return;

            const nextMaxTime  = hourEnd(nextHour);
            const nextMinTime  = addOneMinute(to24h(entry.endTime));
            const nextHourStart = hourStart(nextHour);

            // ⭐ Don't cascade if the next hour's start would be below its
            //    own hour boundary. E.g. an entry ending at 2:30 PM should
            //    NOT push 3:00 PM row's start to 2:31 PM.
            if (nextMinTime < nextHourStart) return;

            if (nextHourSelected) {
                if (nextStart._flatpickr) {
                    nextStart._flatpickr.set('minTime', nextMinTime);
                    nextStart._flatpickr.set('maxTime', nextMaxTime);
                }
            } else {
                nextStart.value = nextMinTime;
                if (nextStart._flatpickr) {
                    nextStart._flatpickr.set('minTime', nextMinTime);
                    nextStart._flatpickr.set('maxTime', nextMaxTime);
                    nextStart._flatpickr.setDate(nextMinTime, false);
                }
                const nextEnd = document.getElementById(`time${nextHour}_2`);
                if (nextEnd && nextEnd._flatpickr) nextEnd._flatpickr.set('minTime', nextMinTime);
            }
        });
}

/** If a main row is unchecked but has a checked sub-row, back-fill its times. */
function deriveUncheckedMainRowsFromSubRows() {
    $('#hoursTableBody tr').has('.tchk').each(function () {
        const $row    = $(this);
        const $chk    = $row.find('.tchk');
        const rowKey  = $chk.attr('id');
        const parsed  = parseRowKey(rowKey);

        if (!parsed || parsed.subIndex !== 0) return;
        if ($chk.is(':checked') || $row.attr('data-locked-group')) return;

        const $next = $row.next('tr');
        if (!$next.length || $next.attr('data-sub') !== 'true') return;

        const $nextChk = $next.find('.tchk');
        if (!($nextChk.is(':checked') || $next.attr('data-locked-group'))) return;

        const nextKey    = $nextChk.attr('id');
        const nextParsed = parseRowKey(nextKey);
        if (!nextParsed || nextParsed.hour !== parsed.hour) return;

        const subStart = document.getElementById(`time${nextKey}_1`);
        if (!subStart || !subStart.value) return;

        const mainEndDisplay = formatTo12h(subtractOneMinute(to24h(subStart.value)));

        const mainStart = document.getElementById(`time${rowKey}_1`);
        const mainEnd   = document.getElementById(`time${rowKey}_2`);

        if (mainStart) {
            mainStart.value = hourStart(parsed.hour);
            if (mainStart._flatpickr) mainStart._flatpickr.setDate(hourStart(parsed.hour), false);
        }
        if (mainEnd) {
            mainEnd.value = mainEndDisplay;
            if (mainEnd._flatpickr) mainEnd._flatpickr.setDate(mainEndDisplay, false);
        }
    });
}

/** Apply `lockGroupAfterLoad` behavior to a group of rows. */
function lockGroupAfterLoad(group) {
    if (!group.length) return;

    const groupId = 'locked_' + Date.now() + '_' + state.groupCounter++;

    group.forEach($row => {
        $row.attr('data-locked-group', groupId);
        $row.find('.tchk').prop('disabled', true);
        $row.find('input[type="time"]').prop('disabled', true);
        lockFlatpickrOnRow($row);
    });

    const $first = group[0];
    const $cells = $first.children('td');

    $cells.eq(3).find('.type-select').prop('disabled', true);
    $cells.eq(4).find('.category-select').prop('disabled', true);
    $cells.eq(5).find('.remarks-input').prop('disabled', true);
    $cells.eq(6).find('.attachment-drop-zone').css({ pointerEvents: 'none', opacity: '0.6', cursor: 'not-allowed' });
    $cells.eq(6).find('.file-actions .btn-outline-danger').prop('disabled', true);
    $cells.eq(6).find('.file-actions .btn-outline-primary').prop('disabled', false);

    if (isLockedApprovedGroup($first)) {
        $cells.eq(8).empty();
    } else {
        $cells.eq(8).html('<button type="button" class="btn btn-sm btn-secondary action-btn" onclick="unlockGroupForTimeOff(this)" title="Unlock to edit">Unlock</button>');
    }
}


/* ==========================================================================
   6. GROUP DISPLAY
   One dispatcher + three specialized renderers.
   ========================================================================== */

/** Dispatch to the right renderer for a group's first row. */
function applyGroupData($firstRow, groupData, groupIndex, submittedFallback, otherRangesByHour) {
    // ⭐ Stash first
    if (groupData.id) $firstRow.attr('data-group-id', groupData.id);
    $firstRow.attr('data-total-hours', groupData.totalHours || 0);
    $firstRow.attr('data-is-spanning', groupData.isSpanning ? 'true' : 'false');

    if (groupData.isOpen) {
        applyOpenEntryDisplay($firstRow, groupData, groupIndex, submittedFallback);
        return;
    }
    if (groupData.isSpanning) {
        applySpanningEntryDisplay($firstRow, groupData, groupIndex, submittedFallback, otherRangesByHour);
        return;
    }

    applyRegularGroupDisplay($firstRow, groupData, groupIndex, submittedFallback);
}

/**
 * Normal in-office / out-of-office group.
 * Stashes location so saveAttendance can read it back.
 */
function applyRegularGroupDisplay($firstRow, groupData, groupIndex, submittedFallback) {
    const $cells = $firstRow.children('td');

    if (groupData.id) $firstRow.attr('data-group-id', groupData.id);

    $firstRow.data('originalLocation', groupData.location || '');
    $firstRow.data('originalLat',      groupData.latitude  || null);
    $firstRow.data('originalLng',      groupData.longitude || null);
    $firstRow.data('originalAccuracy', groupData.accuracy  || null);

    $firstRow.attr('data-status', groupData.status || '');
    $firstRow.attr('data-type',   groupData.type   || '');
    $firstRow.data('status', groupData.status || '');
    $firstRow.data('updatedLocation', groupData.updatedLocation || null);

    [3, 4, 5, 6, 7, 8].forEach(i => $cells.eq(i).show());
    updateDynamicInputsForTimeOff();

    fillGroupGeoBlock($firstRow, groupData, submittedFallback);
    fillGroupTypeAndCategory($firstRow, groupData);
    fillGroupRemarks($firstRow, groupData);
    fillGroupAttachments($firstRow, groupData, groupIndex);
}

/** Out-of-office or in-office: fill the geo/time/hours/status block. */
function fillGroupGeoBlock($firstRow, groupData, submittedFallback) {
    const $c = $firstRow.children('td');

    // Selected date
    const $dateText = $c.eq(7).find('.geo-selected-date-text');
    if ($dateText.length && state.selectedDate) {
        $dateText.text(formatDisplayDate(state.selectedDate));
    }

    // Submitted location + timestamp
    const $loc = $c.eq(7).find('.geo-location-text');
    if ($loc.length) {
        const locText = shortenLocationForDisplay(groupData.location || '');
        const subAt   = groupData.submittedAt || submittedFallback || '';
        $loc.text(locText + (subAt ? ` (${subAt})` : ''));
        $loc.attr('title', groupData.location || '');
    }

    // Edited location + timestamp
    const $updLoc     = $c.eq(7).find('.updated-location-display');
    const $updLocText = $c.eq(7).find('.updated-location-text');

    if (groupData.updatedLocation) {
        $updLoc.removeClass('d-none');
        const updText = shortenLocationForDisplay(groupData.updatedLocation);
        const updAt   = groupData.updatedAt || '';
        $updLocText.text(updText + (updAt ? ` (${updAt})` : ''));
        $updLoc.attr('title', groupData.updatedLocation);
    } else {
        $updLoc.addClass('d-none');
    }

    // Status badge (out-of-office only)
    const $statusDisplay = $c.eq(7).find('.status-display');
    if (groupData.type === 'outoffice') {
        $statusDisplay.show();
        $c.eq(7).find('.status-badge-slot').html(renderStatusBadge(groupData.status));
    } else {
        $statusDisplay.hide();
    }
}

function fillGroupTypeAndCategory($firstRow, groupData) {
    const $c = $firstRow.children('td');

    const $type = $c.eq(3).find('.type-select');
    if ($type.length && groupData.type) {
        const hasOpt = $type.find(`option[value="${groupData.type}"]`).length > 0;
        $type.val(hasOpt ? groupData.type : '');
    }

    if (groupData.type === 'outoffice') {
        const $cat = $c.eq(4).find('.category-select');
        if ($cat.length) {
            $cat.show();
            if (groupData.category) {
                $cat.val(groupData.category);
                const $opt = $cat.find('option:selected');
                if ($opt.data('attachment') === 'required') {
                    $c.eq(6).find('.attachment-upload-area').show();
                }
            }
        }
    }
}

function fillGroupRemarks($firstRow, groupData) {
    const $c    = $firstRow.children('td');
    const $rem  = $c.eq(5).find('.remarks-input');
    const $badge = $c.eq(5).find('.remarks-required-badge');

    if ($rem.length && groupData.remarks) $rem.val(groupData.remarks);

    if (groupData.type === 'outoffice') {
        if ($c.eq(5).find('.remarks-required-badge').length === 0) {
            $c.eq(5).prepend('<span class="badge bg-danger remarks-required-badge" style="font-size: 0.55rem; margin-bottom: 3px; display: inline-block;">Required</span>');
        }
        $rem.attr('data-required', 'true');
    } else {
        $badge.remove();
        $rem.attr('data-required', 'false');
    }
}

function fillGroupAttachments($firstRow, groupData, groupIndex) {
    if (!groupData.attachments || !groupData.attachments.length) return;

    const files = groupData.attachments.map((att, i) => ({
        id:      'saved_' + Date.now() + '_' + groupIndex + '_' + i,
        file:    null,
        name:    att.name,
        size:    att.size,
        type:    att.type,
        path:    att.path    || null,
        dataUrl: att.dataUrl || null
    }));

    $firstRow.data('uploadedFiles', files);
    displayFilePreviews($firstRow);
}

/* ---- Open entry ---- */

/**
 * A biometric punch that never ended.
 * Shows a "Has Entry" badge and a Cancel button instead of a normal group.
 */
function applyOpenEntryDisplay($firstRow, groupData, groupIndex, submittedFallback) {
    const hour = parseInt($firstRow.find('.tchk').attr('id'), 10);
    const claimedLock  = $firstRow.attr('data-locked-group');
    const claimedSaved = $firstRow.attr('data-saved-group');

    const myGroupId  = String(groupData.id || '');
    const rowGroupId = String($firstRow.attr('data-group-id') || '');
    const rowIsOurs  = myGroupId && rowGroupId === myGroupId;

    // Relocate to a sub-row if another group owns this row
    if (!rowIsOurs && (claimedLock || claimedSaved)) {
        let $anchor = $firstRow;
        let lastSubIndex = 0;

        $firstRow.nextAll('tr[data-sub="true"]').each(function () {
            const subKey    = $(this).find('.tchk').attr('id');
            const subParsed = parseRowKey(subKey);
            if (subParsed && subParsed.hour === hour) {
                $anchor = $(this);
                lastSubIndex = subParsed.subIndex;
            } else {
                return false;
            }
        });

        const newKey     = getRowKey(hour, lastSubIndex + 1);
        const entryStart = (groupData.rawEntries && groupData.rawEntries[0])
            ? groupData.rawEntries[0].startTime
            : hourStart(hour);

        if ($('#' + newKey).length === 0) {
            insertSubRow($anchor, newKey, hour, entryStart, formatTo12h(hourEnd(hour)));
        }

        $firstRow = $('#' + newKey).closest('tr');
        groupData.rowKeys = [newKey];
        groupData.hours   = [hour];
    }

    const $cells  = $firstRow.children('td');
    const lockId  = 'locked_open_' + Date.now() + '_' + state.groupCounter++;

    $firstRow.attr('data-locked-group', lockId);
    $firstRow.find('.tchk').prop('disabled', true);
    $firstRow.find('input[type="time"]').prop('disabled', true);
    lockFlatpickrOnRow($firstRow);

    if (groupData.id) $firstRow.attr('data-group-id', groupData.id);
    $firstRow.attr('data-open-entry', 'true');
    $firstRow.attr('data-status', groupData.status || '');
    $firstRow.attr('data-type',   groupData.type   || '');
    $firstRow.data('status', groupData.status || '');

    // ⭐ ADD THIS BLOCK
    const entryStart = (groupData.rawEntries && groupData.rawEntries[0])
        ? groupData.rawEntries[0].startTime
        : null;
    if (entryStart) {
        $firstRow.attr('data-sort-start', timeToMinutes(entryStart));
    }

    $firstRow.data('originalLocation', groupData.location || '');
    $firstRow.data('originalLat',      groupData.latitude  || null);
    $firstRow.data('originalLng',      groupData.longitude || null);
    $firstRow.data('originalAccuracy', groupData.accuracy  || null);
    $firstRow.data('updatedLocation',  groupData.updatedLocation || null);

    [3, 4, 5, 6, 7, 8].forEach(i => $cells.eq(i).show());

    const $c = $firstRow.children('td');

    console.log('[applyOpenEntryDisplay] groupData:', groupData);
    console.log('[applyOpenEntryDisplay] rawEntries:', groupData.rawEntries);
    console.log('[applyOpenEntryDisplay] rawEntries[0]:', groupData.rawEntries && groupData.rawEntries[0]);

    // Type column → Has Entry badge
    $c.eq(3).html(`
        <span class="badge bg-warning text-dark has-entry-badge" style="font-size:0.65rem;"
              title="Punched in but never punched out">
            <i class="bi bi-hourglass-split me-1"></i>Has Entry
        </span>
    `);
    $c.eq(4).empty();
    $c.eq(5).empty();
    $c.eq(6).empty();

    // Geo block
    const dateFormatted = state.selectedDate ? formatDisplayDate(state.selectedDate) : '—';
    const rowKey = $firstRow.find('.tchk').attr('id');

    // ⭐ Store the real start so the summary table can read it
    const realEntry = groupData.rawEntries && groupData.rawEntries[0];
    const realStart = (realEntry && realEntry.realStartTime) ? realEntry.realStartTime : null;
    if (realStart) {
        $(`#time${rowKey}_1`).attr('data-real-start', realStart);   // ⭐ NEW
    }

    const realEnd = (realEntry && realEntry.realEndTime) ? realEntry.realEndTime : null;
    const rangeText = realEnd ? `${realStart} to ${realEnd}` : `${realStart} (open)`;

    $c.eq(7).html(`
        <div class="geo-time-summary">
            <div class="geo-selected-date-display" style="font-size:0.7rem;color:#666;margin-bottom:3px;">
                <i class="bi bi-calendar-check me-1"></i><span class="geo-selected-date-text">${dateFormatted}</span>
            </div>
            <div class="time-range-display" style="font-size:0.75rem;color:#333;font-weight:600;margin-bottom:2px;">
                <i class="bi bi-clock me-1"></i><span class="time-range-text">${rangeText}</span>
            </div>
            <div class="location-display" style="font-size:0.65rem;color:#6c757d;margin-bottom:3px;">
                <i class="bi bi-send me-1"></i>Submitted: <span class="geo-location-text">${shortenLocationForDisplay(groupData.location || '')}${groupData.submittedAt ? ' (' + groupData.submittedAt + ')' : ''}</span>
            </div>
        </div>
    `);

    // Action
    $c.eq(8).html(`
        <button type="button" class="btn btn-sm btn-danger action-btn"
                onclick="cancelOpenEntry(this)" title="Cancel this entry">
            <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
    `);

    $firstRow.find('.tchk').attr('title', 'Punched in but never punched out — click Cancel to remove');
}

/* ---- Spanning entry (biometric punch with a start AND end) ---- */

/**
 * A biometric punch that spans one or more hours.
 * Renders as "Has Entry" + geo, and locks every hour in the range.
 *
 * If the LAST hour is already claimed by another group, its slice is
 * relocated to a pre-assigned or freshly created sub-row.
 */
function applySpanningEntryDisplay($firstRow, groupData, groupIndex, submittedFallback, otherRangesByHour) {
    otherRangesByHour = otherRangesByHour || {};

    const entry = groupData.rawEntries && groupData.rawEntries[0];
    if (!entry || !entry.startTime || !entry.endTime) return;

    const startH = hourFrom12h(entry.startTime);
    const endH   = hourFrom12h(entry.endTime);

    // Defensive cleanup — remove stray sub-rows for this span (but keep gaps
    // and other groups' sub-rows)
    for (let h = startH; h <= endH; h++) {
        for (let i = 1; i <= 5; i++) {
            const subKey = getRowKey(h, i);
            const $sub   = $('#' + subKey).closest('tr');
            if (!$sub.length || $sub.attr('data-sub') !== 'true') continue;
            if ($sub.attr('data-gap') === 'true') continue;

            const subGroupId = $sub.attr('data-group-id');
            if (subGroupId && groupData.id && String(subGroupId) !== String(groupData.id)) continue;

            destroyFlatpickrFor(subKey);
            $sub.remove();
        }
    }

    const lockId   = 'locked_span_' + Date.now() + '_' + state.groupCounter++;
    const savedTag = 'saved_group_' + groupIndex;

    let spanLastHourRow = null;

    // ---- 1) Lock every hour in the span ----
    for (let h = startH; h <= endH; h++) {
        const hourStr = String(h).padStart(2, '0');
        const isFirst = (h === startH);
        const isLast  = (h === endH);

        let sVal, eVal;
        if (isFirst && isLast) { sVal = entry.startTime; eVal = entry.endTime; }
        else if (isFirst)      { sVal = entry.startTime; eVal = formatTo12h(hourStr + ':59'); }
        else if (isLast)       { sVal = formatTo12h(hourStr + ':00'); eVal = entry.endTime; }
        else                   { sVal = formatTo12h(hourStr + ':00'); eVal = formatTo12h(hourStr + ':59'); }

        // Locate the main row for this hour
        let $row = $();
        $('#hoursTableBody tr').has('.tchk').each(function () {
            const key = $(this).find('.tchk').attr('id');
            if (key === String(h)) { $row = $(this); return false; }
        });
        if (!$row.length) continue;

        const existingGroupId = $row.attr('data-group-id');
        const claimedByOther  = existingGroupId && String(existingGroupId) !== String(groupData.id);

        if (claimedByOther) {
            const rowType = String($row.attr('data-type') || '').toLowerCase();
            const isInOfficeRow = (rowType === 'inoffice' || rowType === 'outoffice');

            // ⭐ If the existing row is an in-office / out-of-office group, merge
            //    the biometric into that row (via a Has Entry chip) instead of
            //    creating a duplicate sub-row.
            if (isInOfficeRow) {
                const others = (otherRangesByHour[h] || []).filter(o =>
                    String(o.groupId) === String(existingGroupId)
                );

                const sMin = timeToMinutes(sVal);
                const eMin = timeToMinutes(eVal);

                let overlaps = false;
                others.forEach(o => {
                    if (!o.start || !o.end) return;
                    const oStart = timeToMinutes(formatTo12h(o.start));
                    const oEnd   = timeToMinutes(formatTo12h(o.end));
                    if (sMin < oEnd && eMin > oStart) overlaps = true;
                });

                if (overlaps) {
                    if (isLast) spanLastHourRow = $row;
                    continue;
                }
            }

            // Otherwise relocate the slice to a sub-row (existing behavior)
            const result = relocateSliceToSubRow(
                $row, h, sVal, eVal, groupData, lockId, savedTag, isFirst, isLast, spanLastHourRow
            );

            if (result.isFirst) $firstRow = result.relocatedRow;
            if (result.isLast)  spanLastHourRow = result.relocatedRow;

            continue;
        }

        // Normal case: main row is free
        $row.removeClass('lunch-break-row');
        $row.find('.tchk').prop('checked', true).prop('disabled', true);
        $row.find('input[type="time"]').prop('disabled', true);

        $row.attr('data-locked-group', lockId);
        $row.attr('data-saved-group', savedTag);
        $row.attr('data-spanning-entry', 'true');
        if (groupData.id) $row.attr('data-group-id', groupData.id);

        $row.attr('data-sort-start', timeToMinutes(sVal));

        $row.data('saved-start', sVal);
        $row.data('saved-end', eVal);

        const s = document.getElementById(`time${h}_1`);
        const e = document.getElementById(`time${h}_2`);
        if (s) { s.value = sVal; if (s._flatpickr) { s._flatpickr.set('clickOpens', false); s._flatpickr.setDate(sVal, false); } }
        if (e) { e.value = eVal; if (e._flatpickr) { e._flatpickr.set('clickOpens', false); e._flatpickr.setDate(eVal, false); } }

        if (isLast) spanLastHourRow = $row;
    }

    // ---- 2) Gap sub-row for the tail of the last hour ----
    createSpanGapSubRow(spanLastHourRow, endH, entry.endTime, groupData, otherRangesByHour);

    // ---- 3) Visual: badge on every segment, geo only on the first ----
    const $spanRows = $('#hoursTableBody tr[data-locked-group="' + lockId + '"]');

    const mergedSegments = [];
    let currentSegment = [];

    $spanRows.each(function () {
        const $r = $(this);

        if (currentSegment.length && $r.prev('tr')[0] === currentSegment[currentSegment.length - 1]) {
            currentSegment.push($r[0]);
        } else {
            if (currentSegment.length) mergedSegments.push(currentSegment);
            currentSegment = [$r[0]];
        }
    });
    if (currentSegment.length) mergedSegments.push(currentSegment);

    // Badge on every segment
    mergedSegments.forEach(segment => {
        renderSpanningBadge($(segment[0]), entry);
    });

    // Geo block only on the first segment
    if (mergedSegments.length) {
        renderSpanningGeo($(mergedSegments[0][0]), entry, groupData, submittedFallback);
    }

    mergeLeaveColumnsForTimeOff();
    updateDynamicInputsForTimeOff();
    refreshEndMaxForAllRows(); 
    updateTimeRangeSummary();
}

/**
 * Move this span's slice for hour `h` into a sub-row.
 * Prefers a pre-assigned sub-row (from collision resolution) if one exists.
 *
 * Returns { relocatedRow, isFirst, isLast } so the caller can reassign
 * $firstRow (for the visual block) and spanLastHourRow (for the gap).
 */
function relocateSliceToSubRow($row, h, sVal, eVal, groupData, lockId, savedTag, isFirst, isLast, spanLastHourRow) {
    const preAssignedKey = (groupData.rowKeys || []).find(k => {
        const p = parseRowKey(k);
        return p && p.hour === h && p.subIndex > 0;
    });

    let $sub, subKey;

    if (preAssignedKey && $('#' + preAssignedKey).length) {
        $sub   = $('#' + preAssignedKey).closest('tr');
        subKey = preAssignedKey;
        $sub.removeAttr('data-gap');
    } else {
        let $anchor = $row;
        $row.nextAll('tr[data-sub="true"]').each(function () {
            const k = $(this).find('.tchk').attr('id');
            const p = parseRowKey(k);
            if (p && p.hour === h) $anchor = $(this);
            else return false;
        });

        const lastKey    = $anchor.find('.tchk').attr('id');
        const lastParsed = parseRowKey(lastKey);
        const nextIndex  = (lastParsed && lastParsed.hour === h) ? lastParsed.subIndex + 1 : 1;
        subKey = getRowKey(h, nextIndex);

        if ($('#' + subKey).length === 0) insertSubRow($anchor, subKey, h, sVal, eVal);
        $sub = $('#' + subKey).closest('tr');
    }

    $sub.find('.tchk').prop('checked', true).prop('disabled', true);
    $sub.find('input[type="time"]').prop('disabled', true);
    $sub.attr('data-locked-group', lockId);
    $sub.attr('data-saved-group', savedTag);
    $sub.attr('data-spanning-entry', 'true');
    if (groupData.id) $sub.attr('data-group-id', groupData.id);

    $sub.attr('data-sort-start', timeToMinutes(sVal));

    const s = document.getElementById(`time${subKey}_1`);
    const e = document.getElementById(`time${subKey}_2`);
    if (s) { s.value = sVal; if (s._flatpickr) { s._flatpickr.set('clickOpens', false); s._flatpickr.setDate(sVal, false); } }
    if (e) { e.value = eVal; if (e._flatpickr) { e._flatpickr.set('clickOpens', false); e._flatpickr.setDate(eVal, false); } }

    $sub.data('saved-start', sVal);
    $sub.data('saved-end', eVal);

    return {
        relocatedRow: $sub,
        isFirst:      isFirst,
        isLast:       isLast
    };
}

/**
 * Create a gap sub-row after the span's last hour, if the span ends before
 * the natural end of that hour. The gap is clipped by any other group's
 * entry that starts within the same hour.
 */
function createSpanGapSubRow($spanLastHourRow, endH, endTime12, groupData, otherRangesByHour) {
    if (!$spanLastHourRow) return;

    const lastHourEnd = hourEnd(endH);
    const endTime24   = to24h(endTime12);

    if (endTime24 === lastHourEnd) return;

    let gapStart24 = addOneMinute(endTime24);
    let gapEnd24   = lastHourEnd;

    const others = (otherRangesByHour[endH] || []).filter(o =>
        String(o.groupId) !== String(groupData.id) &&
        o.start > endTime24 &&
        o.start <= lastHourEnd
    );

    if (others.length) {
        const earliestStart = others.reduce((min, o) => o.start < min ? o.start : min, others[0].start);
        gapEnd24 = subtractOneMinute(earliestStart);
    }

    if (gapStart24 > gapEnd24) return;

    let $anchor = $spanLastHourRow;
    let lastSubIndex = 0;

    $spanLastHourRow.nextAll('tr[data-sub="true"]').each(function () {
        const subKey    = $(this).find('.tchk').attr('id');
        const subParsed = parseRowKey(subKey);
        if (subParsed && subParsed.hour === endH) {
            $anchor = $(this);
            lastSubIndex = subParsed.subIndex;
        } else {
            return false;
        }
    });

    const subRowKey = getRowKey(endH, lastSubIndex + 1);
    if ($('#' + subRowKey).length) return;

    insertSubRow($anchor, subRowKey, endH, formatTo12h(gapStart24), formatTo12h(gapEnd24));
    $('#' + subRowKey).closest('tr').attr('data-gap', 'true');

    $('#' + subRowKey).closest('tr').attr('data-sort-start', timeToMinutes(formatTo12h(gapStart24)));
}

/** Badge + geo block on the spanning entry's primary row. */
function renderSpanningVisual($row, entry, groupData, submittedFallback) {
    renderSpanningBadge($row, entry);
    renderSpanningGeo($row, entry, groupData, submittedFallback);
}

/**
 * Just the "Has Entry" badge in the Type column, plus empty Category /
 * Remarks / Attachments / Action cells. Does NOT touch the geo column.
 */
function renderSpanningBadge($row, entry) {
    const $c = $row.children('td');

    [3, 4, 5, 6, 8].forEach(i => $c.eq(i).show());

    $c.eq(3).html(`
        <span class="badge bg-warning text-dark has-entry-badge" style="font-size:0.65rem;"
              title="Biometric entry: ${entry.startTime} to ${entry.endTime}">
            <i class="bi bi-fingerprint me-1"></i>Has Entry
        </span>
    `);
    $c.eq(4).empty();
    $c.eq(5).empty();
    $c.eq(6).empty();
    $c.eq(8).empty();

    $row.find('.tchk').attr('title', 'Biometric entry — ' + entry.startTime + ' to ' + entry.endTime + ' (locked)');
}

/**
 * The geo block (date, time range, hours, submitted/edited location).
 * Rendered once on the span's primary segment.
 */
function renderSpanningGeo($row, entry, groupData, submittedFallback) {
    const $c = $row.children('td');
    $c.eq(7).show();

    const dateFormatted = state.selectedDate ? formatDisplayDate(state.selectedDate) : '—';

    const subText = shortenLocationForDisplay(groupData.location || '');
    const subAt   = groupData.submittedAt || submittedFallback || '';
    const subLine = subText + (subAt ? ' (' + subAt + ')' : '');

    const updText = groupData.updatedLocation ? shortenLocationForDisplay(groupData.updatedLocation) : '';
    const updAt   = groupData.updatedAt || '';
    const updLine = updText + (updAt ? ' (' + updAt + ')' : '');
    const hasUpd  = !!groupData.updatedLocation;

    const hoursLabel = parseFloat(groupData.totalHours) || 0;

    $c.eq(7).html(`
        <div class="geo-time-summary">
            <div class="geo-selected-date-display" style="font-size:0.7rem;color:#666;margin-bottom:3px;">
                <i class="bi bi-calendar-check me-1"></i><span class="geo-selected-date-text">${dateFormatted}</span>
            </div>
            <div class="time-range-display" style="font-size:0.75rem;color:#333;font-weight:600;margin-bottom:2px;">
                <i class="bi bi-clock me-1"></i><span class="time-range-text">${entry.startTime} to ${entry.endTime}</span>
            </div>
            <div class="hours-total-display" style="font-size:0.75rem;color:var(--brand);font-weight:700;margin-bottom:3px;">
                <i class="bi bi-hourglass-split me-1"></i><span class="hours-total-text">${hoursLabel} hour${hoursLabel !== 1 ? 's' : ''}</span>
            </div>

            <div class="location-display" style="font-size:0.65rem;color:#6c757d;margin-bottom:3px;">
                <i class="bi bi-send me-1"></i>Submitted:
                <span class="geo-location-text" title="${groupData.location || ''}">${subLine}</span>
            </div>

            <div class="updated-location-display ${hasUpd ? '' : 'd-none'}" style="font-size:0.65rem;color:#0d6efd;margin-bottom:3px;">
                <i class="bi bi-pencil-square me-1"></i>Edited:
                <span class="updated-location-text" title="${groupData.updatedLocation || ''}">${updLine}</span>
            </div>

            <div class="status-display" style="font-size:0.65rem;display:none;">
                <i class="bi bi-shield-check me-1"></i>Status: <span class="status-badge-slot">—</span>
            </div>
        </div>
    `);
}


/* ==========================================================================
   7. TIME-RANGE SUMMARY & DATE-INFO TABLE
   ========================================================================== */

/** Fill the geo block's time-range and hours for each merged group. */
function updateTimeRangeSummary() {
    const groups = getMergedGroups();
    const dateFormatted = state.selectedDate ? formatDisplayDate(state.selectedDate) : '—';

    groups.forEach(group => {
        if (!group.length) return;

        const $first    = group[0];

        // ⭐ Skip rows whose geo block is owned by another renderer
        if ($first.attr('data-spanning-entry') === 'true') return;
        if ($first.attr('data-open-entry')     === 'true') return;   // ← NEW

        const firstHour = $first.find('.tchk').attr('id');
        const lastHour  = group[group.length - 1].find('.tchk').attr('id');
        const startTime = $(`#time${firstHour}_1`).val();
        const endTime   = $(`#time${lastHour}_2`).val();
        const totalHours = group.length > 1
            ? calculateTotalHours(group)
            : calculateHoursBetween(startTime, endTime);

        if ($first.attr('data-spanning-entry') === 'true') return;

        const $locTd = $first.find('td').eq(7);
        const $date  = $locTd.find('.geo-selected-date-text');
        const $range = $locTd.find('.time-range-text');
        const $hours = $locTd.find('.hours-total-text');

        if ($date.length)  $date.text(dateFormatted);
        if ($range.length) $range.text(`${startTime} to ${endTime}`);
        if ($hours.length) $hours.text(`${totalHours} hour${totalHours !== 1 ? 's' : ''}`);
    });

    updateDateInfoTableWithTimeEntries();
}

function calculateTotalHours(group) {
    if (!group.length) return 0;
    const firstKey = group[0].find('.tchk').attr('id');
    const lastKey  = group[group.length - 1].find('.tchk').attr('id');
    return calculateHoursBetween($(`#time${firstKey}_1`).val(), $(`#time${lastKey}_2`).val());
}

/** Rebuild the summary line above the hours table (In 1 / Out 1 / In 2 / Out 2 / Hours Work). */
function updateDateInfoTableWithTimeEntries() {
    const checked = [];
    $('#hoursTableBody tr').has('.tchk').each(function () {
        if ($(this).find('.tchk').is(':checked') || $(this).attr('data-locked-group')) {
            checked.push($(this));
        }
    });

    if (!checked.length) {
        $('#infoIn1, #infoOut1, #infoIn2, #infoOut2, #infoHoursWork').text('-');
        return;
    }

    // ⭐ Build coverage from active non-gap rows, EXCLUDING open entries
    const activeRanges = [];
    let openEntryRealStart24 = null;

    $('#hoursTableBody tr').has('.tchk').each(function () {
        const $r = $(this);
        if ($r.attr('data-gap') === 'true') return;
        const isActive = $r.find('.tchk').is(':checked') || $r.attr('data-locked-group');
        if (!isActive) return;

        const k = $r.find('.tchk').attr('id');
        const s = document.getElementById(`time${k}_1`);
        const e = document.getElementById(`time${k}_2`);
        if (!s || !e || !s.value || !e.value) return;

        // ⭐ Open entries: contribute only their real start, not coverage
        if ($r.attr('data-open-entry') === 'true') {
            const realStartAttr = $(`#time${k}_1`).attr('data-real-start');
            const realStart = realStartAttr || s.value;
            if (realStart) {
                const open24 = to24h(realStart);
                if (openEntryRealStart24 === null || open24 < openEntryRealStart24) {
                    openEntryRealStart24 = open24;
                }
            }
            return;
        }

        activeRanges.push({ start: to24h(s.value), end: to24h(e.value) });
    });

    // If nothing meaningful to summarize, blank everything
    if (!activeRanges.length && openEntryRealStart24 === null) {
        $('#infoIn1, #infoOut1, #infoIn2, #infoOut2, #infoHoursWork').text('-');
        return;
    }

    const merged = activeRanges.length ? mergeIntervals(activeRanges) : [];

    // ⭐ If the open entry starts earlier than the first range, extend the
    //    range's start to the open entry's real start (for In 1)
    if (openEntryRealStart24 !== null) {
        if (!merged.length) {
            // Only an open entry — show In 1 = real start, blank the rest
            $('#infoIn1').text(formatTo12h(openEntryRealStart24));
            $('#infoOut1').text('-');
            $('#infoIn2').text('-');
            $('#infoOut2').text('-');

            // Hours work: 0 (open entry excluded)
            computeAndRenderHoursWork();
            return;
        }

        // Merge open entry into the earliest range
        if (openEntryRealStart24 < merged[0].start) {
            merged[0].start = openEntryRealStart24;
        }
    }

    const PM_ONLY_BOUNDARY = '12:00';

    const earliest = merged[0].start;
    const latest   = merged[merged.length - 1].end;

    let amEnd24 = null;
    for (let i = merged.length - 1; i >= 0; i--) {
        if (merged[i].start < PM_ONLY_BOUNDARY) {
            amEnd24 = merged[i].end;
            break;
        }
    }

    let pmStart24 = null;
    for (let i = 0; i < merged.length; i++) {
        if (merged[i].start >= PM_ONLY_BOUNDARY) {
            pmStart24 = merged[i].start;
            break;
        }
    }

    let in1  = null;
    let out1 = null;
    let in2  = null;
    let out2 = null;

    if (earliest >= PM_ONLY_BOUNDARY) {
        in2  = formatTo12h(earliest);
        out2 = formatTo12h(latest);
    } else {
        in1 = formatTo12h(earliest);

        if (amEnd24 === '11:59') {
            out1 = '12:00 PM';
        } else {
            out1 = formatTo12h(amEnd24);
        }

        if (pmStart24 !== null) {
            in2  = formatTo12h(pmStart24);
            out2 = formatTo12h(latest);
        }
    }

    $('#infoIn1').text(in1 || '-');
    $('#infoOut1').text(formatSummaryTime(out1) || '-');
    $('#infoIn2').text(in2 || '-');
    $('#infoOut2').text(formatSummaryTime(out2) || '-');

    computeAndRenderHoursWork();
}

/** Compute and render hours worked, excluding open entries. */
function computeAndRenderHoursWork() {
    const groups = getMergedGroups();
    let totalHoursWork = 0;

    groups.forEach(group => {
        if (!group.length) return;

        const $first = group[0];

        // Skip open entries
        if ($first.attr('data-open-entry') === 'true') return;

        const isSpan  = $first.attr('data-is-spanning') === 'true';
        const dbHours = parseFloat($first.attr('data-total-hours'));

        let hours;
        if (isSpan && !isNaN(dbHours) && dbHours > 0) {
            hours = dbHours;
        } else {
            hours = calculateTotalHours(group);
        }

        totalHoursWork += hours;
    });

    totalHoursWork = Math.round(totalHoursWork * 100) / 100;
    if (totalHoursWork > MAX_HOURS) totalHoursWork = MAX_HOURS;

    $('#infoHoursWork').text(totalHoursWork > 0 ? totalHoursWork : '-');
}

/** Colored chip for an approval status (used in the geo block). */
function renderStatusBadge(status) {
    if (!status) return '<span class="text-muted" style="font-size:0.7rem;">—</span>';

    const s = String(status).toLowerCase();
    if (s === 'approved') return '<span class="badge bg-success" style="font-size:0.6rem;">Approved</span>';
    if (s === 'pending')  return '<span class="badge bg-warning text-dark" style="font-size:0.6rem;">Pending</span>';
    if (s === 'rejected') return '<span class="badge bg-danger" style="font-size:0.6rem;">Rejected</span>';

    return `<span class="badge bg-secondary" style="font-size:0.6rem;">${s}</span>`;
}


/* ==========================================================================
   8. LEAVE BLOCKS
   Lock rows that are fully covered by a leave, keeping the uncovered gaps
   in fresh sub-rows.
   ========================================================================== */

function lockLeaveCoveredHours(blockedHours) {
    const hours = Object.keys(blockedHours).map(Number).sort((a, b) => b - a);

    hours.forEach(hour => {
        const ranges = blockedHours[hour];
        const $chk   = $('#' + hour);
        const $row   = $chk.closest('tr');
        if (!$row.length) return;

        $('#hoursTableBody tr[data-leave-gap="true"]').each(function () {
            const p = parseRowKey($(this).find('.tchk').attr('id'));
            if (p && p.hour === hour) $(this).removeAttr('data-leave-gap');
        });

        const badges = ranges.map(r => buildLeaveBadge(r));

        const rangeText = ranges.map(r => `${r.start.substr(0,5)}–${r.end.substr(0,5)}`).join(', ');
        const codeText  = ranges[0].code ? ranges[0].code.toUpperCase() : 'Leave';
        const tooltip   = `On ${codeText} leave — ${rangeText}`;

        const intervals = ranges.map(r => ({
            start: to24hFromDB(r.start),
            end:   to24hFromDB(r.end)
        }));
        const merged = mergeIntervals(intervals);

        const sMain = document.getElementById(`time${hour}_1`);
        const eMain = document.getElementById(`time${hour}_2`);

        // Main row shows the first covered interval
        if (merged[0]) {
            const sDisp = formatTo12h(merged[0].start);
            const eDisp = formatTo12h(merged[0].end);

            if (sMain) { sMain.value = sDisp; if (sMain._flatpickr) sMain._flatpickr.setDate(sDisp, false); }
            if (eMain) { eMain.value = eDisp; if (eMain._flatpickr) eMain._flatpickr.setDate(eDisp, false); }
        }

        // Lock the main row
        $chk.prop('disabled', true).prop('checked', false).attr('title', tooltip);
        $row.attr('data-leave-covered', 'true');
        $row.attr('data-leave-code', codeText);
        $row.attr('data-leave-range', rangeText);

        if (sMain && sMain._flatpickr) sMain._flatpickr.set('clickOpens', false);
        if (eMain && eMain._flatpickr) eMain._flatpickr.set('clickOpens', false);

        $row.children('td').eq(3).html(badges.join(''));

        // One sub-row per uncovered gap
        const gaps = findGaps(merged, hourStart(hour), hourEnd(hour));

        gaps.reverse().forEach((gap, revIdx) => {
            const subIndex  = gaps.length - revIdx;
            const subRowKey = getRowKey(hour, subIndex);
            const startDisp = formatTo12h(gap.start);
            const endDisp   = formatTo12h(gap.end);

            if ($('#' + subRowKey).length === 0) {
                insertSubRow($row, subRowKey, hour, startDisp, endDisp);
            } else {
                const sEl = document.getElementById(`time${subRowKey}_1`);
                const eEl = document.getElementById(`time${subRowKey}_2`);
                if (sEl) { sEl.value = startDisp; if (sEl._flatpickr) sEl._flatpickr.setDate(startDisp, false); }
                if (eEl) { eEl.value = endDisp;   if (eEl._flatpickr) eEl._flatpickr.setDate(endDisp, false); }
            }

            // ⭐ Tag this sub-row as a leave gap — so unchecking it won't remove it
            $('#' + subRowKey).closest('tr').attr('data-leave-gap', 'true');
        });
    });
}

/** HTML for a single leave badge in the Type column. */
function buildLeaveBadge(r) {
    const code = r.code || 'leave';
    const label = leaveShortCode(code);
    const st = String(r.status || '').toLowerCase();

    let colorClass, statusIcon;

    if (st === 'pending') {
        colorClass = 'type-leave-pending';
        statusIcon = '⏳';
    } else if (st === 'disapproved' || st === 'rejected') {
        colorClass = 'type-leave-rejected';
        statusIcon = '<i class="bi bi-x-circle-fill"></i>';
    } else if (st === 'cancelled') {
        colorClass = 'type-leave-cancelled';
        statusIcon = '<i class="bi bi-slash-circle-fill"></i>';
    } else {
        colorClass = 'type-leave-approved';
        statusIcon = '<i class="bi bi-check-circle-fill"></i>';
    }

    const rangeTxt = `${r.start.substr(0,5)}–${r.end.substr(0,5)}`;

    return `<span class="leave-covered-badge chip ${colorClass}" title="${label} (${st || 'leave'}) — ${rangeTxt}">
                ${statusIcon} ${label}
            </span>`;
}


/* ==========================================================================
   9. LEAVE-HOURS HELPERS
   ========================================================================== */

/** Total leave hours for the currently selected date. */
function getLeaveHoursForDay() {
    if (!state.currentBlockedHours || !Object.keys(state.currentBlockedHours).length) return 0;

    const all = [];

    Object.values(state.currentBlockedHours).forEach(ranges => {
        (ranges || []).forEach(r => {
            if (!r.start || !r.end) return;
            all.push({ start: to24hFromDB(r.start), end: to24hFromDB(r.end) });
        });
    });

    if (!all.length) return 0;

    const merged = mergeIntervals(all);

    let total = 0;
    merged.forEach(iv => {
        total += calculateHoursBetween(formatTo12h(iv.start), formatTo12h(iv.end));
    });

    return Math.round(total * 100) / 100;
}

/** Does any leave interval overlap the given half-day? ('AM' or 'PM') */
function hasLeaveInHalfDay(halfDay) {
    if (!state.currentBlockedHours || !Object.keys(state.currentBlockedHours).length) return false;

    const NOON_24 = 12 * 60;
    let found = false;

    Object.values(state.currentBlockedHours).forEach(ranges => {
        (ranges || []).forEach(r => {
            if (found || !r.start || !r.end) return;

            const startMin = timeToMinutes(formatTo12h(to24hFromDB(r.start)));
            const endMin   = timeToMinutes(formatTo12h(to24hFromDB(r.end)));

            if (halfDay === 'AM') {
                if (startMin < NOON_24 && endMin > 0) found = true;
            } else {
                if (endMin > NOON_24) found = true;
            }
        });
    });

    return found;
}

/**
 * Reset a row's two time inputs to the given 24h defaults.
 * Writes 12h display values so the input (which flatpickr converts to
 * type="text") shows "8:00 AM" instead of "08:00".
 */
function setRowTimes(rowKey, start24, end24, disabled) {
    const s = document.getElementById(`time${rowKey}_1`);
    const e = document.getElementById(`time${rowKey}_2`);

    const start12 = formatTo12h(start24);
    const end12   = formatTo12h(end24);

    if (s) {
        if (s._flatpickr && s._flatpickr.config) {
            try { s._flatpickr.setDate(start12, false); }
            catch (err) { s.value = start12; }
        } else {
            s.value = start12;
        }
        s.disabled = !!disabled;
    }
    if (e) {
        if (e._flatpickr && e._flatpickr.config) {
            try { e._flatpickr.setDate(end12, false); }
            catch (err) { e.value = end12; }
        } else {
            e.value = end12;
        }
        e.disabled = !!disabled;
    }
}

function detectOpenEntry() {
    const $openRow = $('#hoursTableBody tr[data-open-entry="true"]').first();
    if (!$openRow.length) {
        // Clear banner
        $('#openEntryBanner').remove();
        return null;
    }

    // Get the real start time from the geo block or data attribute
    const realStart = $openRow.find('.time-range-text').text().split(' (')[0] || 'an earlier time';

    // Show banner at the top of the modal
    if (!$('#openEntryBanner').length) {
        const bannerHtml = `
            <div id="openEntryBanner" class="alert alert-warning py-2 px-3 mb-3 d-flex align-items-center gap-2" style="font-size:0.8rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    You have an <strong>open entry at ${realStart}</strong> on this date.
                    Please <strong>Time Out</strong> or <strong>Cancel</strong> it before filing attendance.
                </div>
            </div>
        `;
        // Insert at the top of the modal body
        $('#timeOffModal .modal-body').prepend(bannerHtml);
    }

    return {
        rowKey: $openRow.find('.tchk').attr('id'),
        startTime: realStart
    };
}

function showModalLoading(text) {
    const $overlay = $('#modalLoadingOverlay');
    if (!$overlay.length) return;
    if (text) $overlay.find('p').text(text);
    $overlay.addClass('is-visible');
}

function hideModalLoading() {
    $('#modalLoadingOverlay').removeClass('is-visible');
}