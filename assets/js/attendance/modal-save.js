/* ==========================================================================
   attendance/modal-save.js
   The save pipeline: validate, gather, upload, POST.
   Also handles group deletion and open-entry cancellation.

   Depends on: config.js, helpers.js, alerts.js, attachments.js, jQuery
   Calls into (resolved at call time):
     modal-load.js → getLeaveHoursForDay, hasLeaveInHalfDay
     modal-rows.js → getMergedGroups, calculateTotalHours (load.js owns it)
     calendar.js   → refreshCalendarEvents

   Sections
     1. Save entry point
     2. Validation
     3. Data gathering
     4. File upload
     5. Group delete
     6. Open-entry cancel
   ========================================================================== */


/* ==========================================================================
   1. SAVE ENTRY POINT
   ========================================================================== */

/** Read the clicked checkboxes into a flat time-entries array. */
function collectTimeEntries() {
    const entries = [];

    $('#hoursTableBody tr').has('.tchk').each(function () {
        const $chk = $(this).find('.tchk');
        if (!$chk.is(':checked')) return;

        const key    = $chk.attr('id');
        const parsed = parseRowKey(key);

        entries.push({
            hour:      parsed.hour,
            rowKey:    key,
            startTime: $(`#time${key}_1`).val(),
            endTime:   $(`#time${key}_2`).val()
        });
    });

    return entries;
}

/**
 * Main save flow.
 *   1. Collect entries
 *   2. Verify every group is locked
 *   3. Verify total hours (attendance + leave) meets MIN_HOURS
 *   4. Verify AM / PM sessions exist
 *   5. Confirm (if overwriting) → upload files → POST
 */
function saveAttendance() {
    if (!state.selectedDate) {
        swalWarning('No Date Selected', 'Please select a date first.');
        return;
    }

    const timeEntries = collectTimeEntries();

    if (!timeEntries.length) {
        swalWarning('No Time Entries', 'Please select at least one time entry before saving.');
        return;
    }

    // Every group must be locked before save
    const allGroups = getMergedGroups();
    const anyUnlocked = allGroups.some(group =>
        group.some($r => !$r.attr('data-locked-group'))
    );

    if (anyUnlocked) {
        swalWarning(
            'Please Lock All Groups',
            'Every group must be locked before you can save. Click the lock button on each group to finalize it.'
        );
        return;
    }

    // End time must be after start time
    for (const e of timeEntries) {
        const s  = to24h(e.startTime);
        const en = to24h(e.endTime);
        if (en < s) {
            swalError(
                'Invalid Time Range',
                `Hour ${e.hour}:00 has end time (${e.endTime}) before or equal to start time (${e.startTime}).`
            );
            return;
        }
    }

    // Gather group data
    const groupDataArray = collectGroupData();
    if (groupDataArray === null) return;   // validation failed

    let mergedTotal = groupDataArray.reduce((sum, g) => sum + (parseFloat(g.totalHours) || 0), 0);
    mergedTotal = Math.round(mergedTotal * 100) / 100;

    // Add leave hours for the day
    const leaveHoursForDay = getLeaveHoursForDay();
    const effectiveTotal   = Math.round((mergedTotal + leaveHoursForDay) * 100) / 100;

    if (effectiveTotal < MIN_HOURS) {
        const breakdown = leaveHoursForDay > 0
            ? ` (Attendance: ${mergedTotal} hr${mergedTotal !== 1 ? 's' : ''}, Leave: ${leaveHoursForDay} hr${leaveHoursForDay !== 1 ? 's' : ''})`
            : '';
        swalError(
            'Insufficient Hours',
            `You have only ${effectiveTotal} hour${effectiveTotal !== 1 ? 's' : ''}${breakdown}. A minimum of ${MIN_HOURS} hours is required.`
        );
        return;
    }

    // AM / PM session validation
    const amValidation = validateAmPmSessions(timeEntries);
    if (!amValidation.valid) {
        swalError(amValidation.title, amValidation.message);
        return;
    }

    const cappedHours = mergedTotal > MAX_HOURS ? MAX_HOURS : mergedTotal;

    const proceed = () => {
        const payload = {
            date: state.selectedDate,
            dateInfo: {
                in1:       $('#infoIn1').text(),
                out1:      $('#infoOut1').text(),
                in2:       $('#infoIn2').text(),
                out2:      $('#infoOut2').text(),
                hoursWork: cappedHours
            },
            timeEntries: timeEntries,
            groups:      groupDataArray
        };

        swalLoading('Saving attendance...');

        uploadAllFiles(groupDataArray, groupsWithPaths => {
            payload.groups = groupsWithPaths;

            $.ajax({
                url: API.save,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                dataType: 'json'
            }).done(response => {
                if (response.success) {
                    $('#hoursTableBody tr').removeAttr('data-group-dirty');
                    swalSuccess('Saved!', response.message || 'Attendance saved successfully.').then(() => {
                        refreshCalendarEvents();
                        state.allowModalClose = true;
                        $('#timeOffModal').modal('hide');
                    });
                } else {
                    swalError('Save Failed', response.message || 'An error occurred.');
                }
            }).fail(() => swalError('Network Error', 'Could not connect to the server.'));
        });
    };

    if (state.isViewingSavedRecord) {
        swalConfirm(
            'Update Attendance?',
            'This will overwrite the existing attendance record for this date.',
            'Yes, update',
            'Cancel'
        ).then(ok => { if (ok) proceed(); });
    } else {
        proceed();
    }
}


/* ==========================================================================
   2. VALIDATION
   ========================================================================== */

/**
 * AM / PM session presence.
 * - If no AM entries exist, either leave must cover the morning or it's an error.
 * - Same for PM.
 * - If AM entries exist, the last one's end time must be before noon.
 */
function validateAmPmSessions(timeEntries) {
    if (!timeEntries.length) {
        return { valid: false, title: 'No Time Entries', message: 'Please select at least one time entry.' };
    }

    const NOON_24 = 12 * 60;

    const amEntries = timeEntries.filter(e => timeToMinutes(e.startTime) < NOON_24);
    const pmEntries = timeEntries.filter(e => timeToMinutes(e.startTime) >= NOON_24);

    const leaveCoversAM = hasLeaveInHalfDay('AM');
    const leaveCoversPM = hasLeaveInHalfDay('PM');

    if (amEntries.length === 0 && !leaveCoversAM) {
        return {
            valid: false,
            title: 'Missing AM Session',
            message: 'You must file a morning (AM) time entry, or have approved leave covering the morning.'
        };
    }

    if (pmEntries.length === 0 && !leaveCoversPM) {
        return {
            valid: false,
            title: 'Missing PM Session',
            message: 'You must file an afternoon (PM) time entry, or have approved leave covering the afternoon.'
        };
    }

    if (amEntries.length) {
        const lastAm = amEntries.reduce((latest, e) =>
            timeToMinutes(e.startTime) > timeToMinutes(latest.startTime) ? e : latest,
            amEntries[0]
        );

        if (timeToMinutes(lastAm.endTime) >= NOON_24) {
            return {
                valid: false,
                title: 'Invalid Morning End Time',
                message: `Your morning (Out 1) session must end before 12:00 PM. Currently it ends at ${lastAm.endTime}. Please adjust so it's at most 11:59 AM.`
            };
        }
    }

    return { valid: true };
}

/** Per-group validation: type, category, remarks, attachment, time ranges. */
function validateGroup($first, groupRows, idx) {
    const $cells = $first.children('td');

    const typeValue     = $cells.eq(3).find('.type-select').val() || '';
    const categoryValue = $cells.eq(4).find('.category-select').val() || '';
    const remarksValue  = $cells.eq(5).find('.remarks-input').val() || '';
    const uploadedFiles = $first.data('uploadedFiles') || [];

    if (!typeValue) {
        return { valid: false, title: 'Missing Type', text: 'This group has no type selected.' };
    }

    if (typeValue === 'outoffice' && !categoryValue) {
        return { valid: false, title: 'Missing Category', text: 'Out of Office but has no category selected.' };
    }

    if (typeValue === 'outoffice' && !remarksValue.trim()) {
        return { valid: false, title: 'Missing Description', text: 'Out of Office but has no description.' };
    }

    if (typeValue === 'outoffice') {
        const $opt = $cells.eq(4).find('.category-select option:selected');
        if ($opt.data('attachment') === 'required' && uploadedFiles.length === 0) {
            return {
                valid: false,
                title: 'Attachment Required',
                text: `Uses category "${$opt.text()}" which requires an attachment.`
            };
        }
    }

    // End > start on every row of the group
    for (const $r of groupRows) {
        const key = $r.find('.tchk').attr('id');
        const s   = to24h($(`#time${key}_1`).val() || '');
        const e   = to24h($(`#time${key}_2`).val() || '');
        if (!s || !e || e < s) {
            return {
                valid: false,
                title: 'Invalid Time Range',
                text: `Hour ${key} has end time (${$(`#time${key}_2`).val()}) before or equal to start time (${$(`#time${key}_1`).val()}).`
            };
        }
    }

    return { valid: true };
}


/* ==========================================================================
   3. DATA GATHERING
   ========================================================================== */

/**
 * Walk the merged groups and build the payload array.
 * Returns null on validation failure (an alert is shown).
 */
function collectGroupData() {
    const groups = getMergedGroups();
    const groupDataArray = [];

    for (let idx = 0; idx < groups.length; idx++) {
        const group = groups[idx];
        if (!group.length) continue;

        const $first = group[0];
        const $cells = $first.children('td');

        const typeValue     = $cells.eq(3).find('.type-select').val() || '';
        const categoryValue = $cells.eq(4).find('.category-select').val() || '';
        const remarksValue  = $cells.eq(5).find('.remarks-input').val() || '';
        const uploadedFiles = $first.data('uploadedFiles') || [];

        const groupRowKeys = group.map($r => $r.find('.tchk').attr('id'));

        // Preserve original location for existing groups; use live GPS for new ones
        const stashedLocation = $first.data('originalLocation');
        const isNewGroup      = !stashedLocation;

        let originalLocation, originalLat, originalLng, originalAccuracy;

        if (isNewGroup) {
            originalLocation = (window.currentUserLocation && window.currentUserLocation.address) || DEFAULT_LOCATION;
            originalLat      = (window.currentUserLocation && window.currentUserLocation.lat)      || null;
            originalLng      = (window.currentUserLocation && window.currentUserLocation.lng)      || null;
            originalAccuracy = (window.currentUserLocation && window.currentUserLocation.accuracy) || null;
        } else {
            originalLocation = stashedLocation;
            originalLat      = $first.data('originalLat');
            originalLng      = $first.data('originalLng');
            originalAccuracy = $first.data('originalAccuracy');
        }

        const isExistingGroup = !isNewGroup;
        const groupDbId       = $first.attr('data-group-id');
        const isDirty         = $first.attr('data-group-dirty') === 'true';

        // Current location, if we have it, becomes the "updated location"
        const liveLoc = window.currentUserLocation || {};

        groupDataArray.push({
            id:         groupDbId || null,
            isDirty:    isDirty,
            groupIndex: idx,
            type:       typeValue,
            category:   categoryValue || null,
            remarks:    remarksValue,
            timeRange: roundTimeRangeForSave($cells.eq(7).find('.time-range-text').text() || ''),
            totalHours: parseFloat($cells.eq(7).find('.hours-total-text').text()) || 0,
            location:   originalLocation,
            latitude:   originalLat,
            longitude:  originalLng,
            accuracy:   originalAccuracy,
            updatedLocation:  isExistingGroup ? liveLoc.address  : null,
            updatedLatitude:  isExistingGroup ? liveLoc.lat      : null,
            updatedLongitude: isExistingGroup ? liveLoc.lng      : null,
            hours:      groupRowKeys.map(k => parseRowKey(k).hour),
            rowKeys:    groupRowKeys,
            attachments: uploadedFiles.map(f => ({
                file: f.file || null,
                name: f.name,
                path: f.path || null,
                size: f.size,
                type: f.type
            }))
        });
    }

    return groupDataArray;
}


/* ==========================================================================
   4. FILE UPLOAD
   ========================================================================== */

/**
 * Upload any File objects in `groups`, then call `callback` with the same
 * array but where each attachment has a server `path` instead of a `file`.
 */
function uploadAllFiles(groups, callback) {
    if (!groups.length) return callback(groups);

    const result    = JSON.parse(JSON.stringify(groups));
    let processed   = 0;

    groups.forEach((group, idx) => {
        const newFiles   = (group.attachments || []).filter(a => a.file instanceof File);
        const savedFiles = (group.attachments || []).filter(a => !(a.file instanceof File) && a.path);

        const finalize = (extra = []) => {
            result[idx].attachments = [
                ...savedFiles.map(a => ({ name: a.name, path: a.path, size: a.size, type: a.type })),
                ...extra
            ];
            processed++;
            if (processed === groups.length) callback(result);
        };

        if (!newFiles.length) return finalize();

        const fd = new FormData();
        newFiles.forEach(a => fd.append('files[]', a.file));

        $.ajax({
            url: API.upload,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(res => {
            finalize(res.success && res.files ? res.files : []);
        }).fail(() => finalize());
    });
}


/* ==========================================================================
   5. GROUP DELETE
   ========================================================================== */

function deleteGroupForTimeOff(btn) {
    const $firstRow = $(btn).closest('tr');

    if ($firstRow.attr('data-locked-group')) {
        swalWarning('Group Locked', 'Please unlock the group before deleting.');
        return;
    }

    const groupDbId = $firstRow.attr('data-group-id');
    if (!groupDbId) {
        swalWarning('Not Saved', 'This group has not been saved yet. Save it first.');
        return;
    }

    const marker = String(groupDbId);

    // Find all contiguous rows with the same group id
    const $allRows  = $('#hoursTableBody tr').has('.tchk');
    const startIdx  = $allRows.index($firstRow);
    if (startIdx === -1) return;

    const groupIdx = [];
    let i = startIdx;

    while (i >= 0) {
        const $r  = $allRows.eq(i);
        const rm  = $r.attr('data-group-id') || null;
        const ck  = $r.find('.tchk').is(':checked');
        if (!ck) break;
        if (i < startIdx && rm !== marker) break;
        groupIdx.unshift(i);
        i--;
    }

    i = startIdx + 1;
    while (i < $allRows.length) {
        const $r  = $allRows.eq(i);
        const rm  = $r.attr('data-group-id') || null;
        const ck  = $r.find('.tchk').is(':checked');
        if (!ck) break;
        if (rm !== marker) break;
        groupIdx.push(i);
        i++;
    }

    const firstHour = parseInt($allRows.eq(groupIdx[0]).find('.tchk').attr('id'), 10);
    const lastHour  = parseInt($allRows.eq(groupIdx[groupIdx.length - 1]).find('.tchk').attr('id'), 10);
    const startTime = $(`#time${firstHour}_1`).val() || '';
    const endTime   = $(`#time${lastHour}_2`).val() || '';

    Swal.fire({
        icon: 'warning',
        title: 'Delete this group?',
        html: `<div class="text-start">
                   <p class="mb-2"><strong>Time:</strong> ${startTime} to ${endTime}</p>
                   <p class="mb-0 text-danger">
                       <i class="bi bi-exclamation-triangle me-1"></i>This cannot be undone.
                   </p>
               </div>`,
        showCancelButton: true,
        confirmButtonColor: DANGER_COLOR,
        cancelButtonColor: MUTED_COLOR,
        confirmButtonText: '<i class="bi bi-trash me-1"></i>Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then(result => {
        if (!result.isConfirmed) return;
        swalLoading('Deleting group...');

        $.ajax({
            url: API.deleteGroup + '/' + groupDbId,
            type: 'POST',
            dataType: 'json'
        }).done(response => {
            if (!response.success) {
                swalError('Delete Failed', response.message || 'Could not delete group.');
                return;
            }

            // ⭐ Full re-render from the API — no in-place cleanup
            const wasLastGroup = response.record_deleted || response.remaining_groups === 0;

            if (!wasLastGroup) {
                // Re-load the record to reflect the surviving groups
                loadAttendanceForDate(state.selectedDate);
            }

            refreshCalendarEvents();
            swalSuccess('Deleted!', 'The group has been deleted.');

            if (wasLastGroup) {
                state.allowModalClose = true;
                setTimeout(() => $('#timeOffModal').modal('hide'), 1500);
            }
        }).fail(() => swalError('Network Error', 'Could not connect to the server.'));
    });
}


/* ==========================================================================
   6. OPEN-ENTRY CANCEL
   ========================================================================== */

function cancelOpenEntry(btn) {
    const $firstRow = $(btn).closest('tr');
    const groupDbId = $firstRow.attr('data-group-id');

    if (!groupDbId) {
        swalWarning('Cannot Cancel', 'This entry has no database id.');
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: 'Cancel this entry?',
        html: `<div class="text-start">
                   <p class="mb-2">This will permanently remove the attendance entry that was never punched out.</p>
                   <p class="mb-0 text-danger">
                       <i class="bi bi-exclamation-triangle me-1"></i>This cannot be undone.
                   </p>
               </div>`,
        showCancelButton: true,
        confirmButtonColor: DANGER_COLOR,
        cancelButtonColor: MUTED_COLOR,
        confirmButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel Entry',
        cancelButtonText: 'Keep it',
        reverseButtons: true
    }).then(result => {
        if (!result.isConfirmed) return;
        swalLoading('Cancelling entry...');

        $.ajax({
            url: API.cancelOpenEntry + '/' + groupDbId,
            type: 'POST',
            dataType: 'json'
        }).done(response => {
            if (!response.success) {
                swalError('Cancel Failed', response.message || 'Could not cancel the entry.');
                return;
            }

            const wasLastGroup = response.record_deleted || response.remaining_groups === 0;

            if (!wasLastGroup) {
                loadAttendanceForDate(state.selectedDate);
            }

            refreshCalendarEvents();

            swalSuccess('Cancelled!', 'The entry has been removed.').then(() => {
                if (wasLastGroup) {
                    state.allowModalClose = true;
                    $('#timeOffModal').modal('hide');
                }
            });
        }).fail(() => swalError('Network Error', 'Could not connect to the server.'));
    });
}