/* ==========================================================================
   attendance/modal-rows.js
   The hours-table row system: build, check/uncheck, sub-rows, cascade,
   lock/unlock groups, group merging, dynamic column inputs.

   Depends on: config.js, helpers.js, alerts.js, jQuery, flatpickr
   Depends on DOM: #hoursTableBody, #checkAllHours
   Calls into (resolved at call time):
     modal-load.js → updateTimeRangeSummary, updateDynamicInputsForTimeOff,
                     calculateTotalHours

   Sections
     1. Build initial table
     2. Sub-row management (insert / remove / find)
     3. Row activation & cascade (check / uncheck / flatpickr sync)
     4. Group merging (getMergedGroups, column merge)
     5. Group locking (lock / unlock)
     6. Dynamic inputs (Type / Category / Remarks / Attachments column)
   ========================================================================== */


/* ==========================================================================
   1. BUILD INITIAL TABLE
   ========================================================================== */

/** Build the empty 8:00–18:00 rows in #hoursTableBody. Called once at boot. */
function buildHoursTableBody() {
    const $tbody = $('#hoursTableBody');
    $tbody.empty();

    HOURS.forEach(h => {
        const start12 = formatTo12h(hourStart(h));   // "8:00 AM"
        const end12   = formatTo12h(hourEnd(h));     // "8:59 AM"
        const isLunch = (h === LUNCH_HOUR);

        const typeHtml = isLunch
            ? '<td class="text-center"><span class="lunch-break-badge" id="lunchBreakBadge">🍽️ Lunch Break</span></td>'
            : '<td></td>';

        $tbody.append(`
            <tr class="${isLunch ? 'lunch-break-row' : ''}">
                <td class="text-center">
                    <input type="checkbox" class="form-check-input tchk" id="${h}"
                           onclick="activateTimeForTimeOff('${h}')">
                </td>
                <td class="text-center">
                    <input type="text" class="form-control form-control-sm input_chk"
                           id="time${h}_1" value="${start12}" disabled>
                </td>
                <td class="text-center">
                    <input type="text" class="form-control form-control-sm input_chk"
                           id="time${h}_2" value="${end12}" disabled>
                </td>
                ${typeHtml}
                <td></td><td></td><td></td><td></td><td></td>
            </tr>
        `);
    });
}

/* ==========================================================================
   1b. MODAL OPEN — INITIALIZE FLATPICKR ON ALL MAIN ROWS
   Called every time the time-off modal is shown. Destroys any previous
   flatpickr instances first, then re-creates them with the modal-open
   defaults.
   ========================================================================== */

function initializeTimeTableForTimeOff() {
    // ---- 1) Destroy any existing flatpickr on main rows ----
    HOURS.forEach(h => {
        ['_1', '_2'].forEach(suffix => {
            const el = document.getElementById(`time${h}${suffix}`);
            if (el && el._flatpickr) {
                try { el._flatpickr.destroy(); } catch (e) {}
            }
        });
    });

    // ---- 2) Create fresh pickers (12h format) ----
    const makeCfg = (min, max, def12) => ({
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: min, maxTime: max, defaultDate: def12,
        minuteIncrement: 1, static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal')
    });

    // initializeTimeTableForTimeOff — main rows
    HOURS.forEach(h => {
        const start24 = hourStart(h);
        const end24   = hourEnd(h);
        const start12 = formatTo12h(start24);
        const end12   = formatTo12h(end24);

        const s_el = document.getElementById(`time${h}_1`);
        const e_el = document.getElementById(`time${h}_2`);

        const s_val = (s_el && s_el.value && /[AP]M$/i.test(s_el.value)) ? s_el.value : start12;
        const e_val = (e_el && e_el.value && /[AP]M$/i.test(e_el.value)) ? e_el.value : end12;

        // ⭐ Start input: min = previous row's end + 1 (if any active prev row)
        const minStart24 = getMinStartTimeForRow(String(h));
        flatpickr('#time' + h + '_1', makeCfg(minStart24, end24, s_val));

        const maxEnd24 = getMaxEndTimeForRow(String(h));
        flatpickr('#time' + h + '_2', makeCfg(start24, maxEnd24, e_val));
    });

    // ---- 3) Wire the onChange handlers ----
    HOURS.forEach(h => {
        const s = document.getElementById('time' + h + '_1');
        const e = document.getElementById('time' + h + '_2');

        if (s && s._flatpickr) {
            s._flatpickr.set('onChange', (_, dateStr) => {
                if (e && e._flatpickr) {
                    e._flatpickr.set('minTime', dateStr);
                    // Recompute maxEnd — the start time may have changed the cap
                    const newMax24 = getMaxEndTimeForRow(String(h));
                    e._flatpickr.set('maxTime', newMax24);
                }
                syncHeadGap(String(h));
                updateTimeRangeSummary();
            });
        }

        if (e && e._flatpickr) {
            e._flatpickr.set('onChange', (_, dateStr) => {
                // ⭐ Clamp in case the value exceeds the new max
                const clamped = clampEndTime(String(h), dateStr);
                if (clamped !== dateStr) {
                    e.value = clamped;
                    e._flatpickr.setDate(clamped, false);
                }

                cascadeNextRowStart(String(h));
                autoCreateSubRow(String(h), clamped);

                if (!getSubRowAfter(String(h)).length) {
                    const nextHour = h + 1;
                    if (nextHour <= LAST_HOUR) {
                        const ns = document.getElementById('time' + nextHour + '_1');
                        if (ns && ns._flatpickr) {
                            const nextMin = addOneMinute(to24h(clamped));
                            const nMax    = hourEnd(nextHour);

                            ns._flatpickr.set('minTime', nextMin);
                            ns._flatpickr.set('maxTime', nMax);
                            ns._flatpickr.setDate(nextMin, false);
                            ns.value = formatTo12h(nextMin);
                        }
                    }
                }

                // ⭐ Refresh the next row's max too (in case it's constrained by
                //    this row's end)
                refreshEndMaxForAllRows();
                mergeLeaveColumnsForTimeOff();
                updateDynamicInputsForTimeOff();
            });
        }
    });

    // ---- 4) Re-apply saved-start / saved-end if present ----
    $('#hoursTableBody tr').each(function () {
        const $row = $(this);
        const savedStart = $row.data('saved-start');
        const savedEnd   = $row.data('saved-end');
        if (!savedStart && !savedEnd) return;

        const rowKey = $row.find('.tchk').attr('id');
        if (!rowKey) return;

        const s = document.getElementById('time' + rowKey + '_1');
        const e = document.getElementById('time' + rowKey + '_2');

        if (s && s._flatpickr && savedStart) s._flatpickr.setDate(savedStart, false);
        else if (s && savedStart) s.value = savedStart;

        if (e && e._flatpickr && savedEnd) {
            const clamped = clampEndTime(rowKey, savedEnd);
            e._flatpickr.setDate(clamped, false);
        } else if (e && savedEnd) {
            e.value = clampEndTime(rowKey, savedEnd);
        }
    });
}

/**
 * Refresh the max-time cap on every end input.
 * Call after any structural change that could add/move the "next active row".
 */
function refreshEndMaxForAllRows() {
    $('#hoursTableBody tr').has('.tchk').each(function () {
        const $r = $(this);
        const key = $r.find('.tchk').attr('id');
        if (!key) return;

        const e = document.getElementById('time' + key + '_2');
        if (!e || !e._flatpickr) return;

        const max24 = getMaxEndTimeForRow(key);
        const max12 = formatTo12h(max24);
        e._flatpickr.set('maxTime', max12);
    });
}

/* ==========================================================================
   2. SUB-ROW MANAGEMENT
   Sub-rows are letter-suffixed rows (8a, 8b) used when a single hour needs
   to hold more than one segment — e.g. biometric punch ends at :30, gap
   starts at :31, or two groups share the same hour.
   ========================================================================== */

function insertSubRow($afterRow, newRowKey, hour, startTime, endTime) {
    const $new = $(`
        <tr data-hour="${hour}" data-sub="true">
            <td class="text-center">
                <input type="checkbox" class="form-check-input tchk" id="${newRowKey}"
                       onclick="activateTimeForTimeOff('${newRowKey}')">
            </td>
            <td class="text-center">
                <input type="text" class="form-control form-control-sm input_chk"
                       id="time${newRowKey}_1" value="${startTime}" disabled>
            </td>
            <td class="text-center">
                <input type="text" class="form-control form-control-sm input_chk"
                       id="time${newRowKey}_2" value="${endTime}" disabled>
            </td>
            <td></td><td></td><td></td><td></td><td></td><td></td>
        </tr>
    `);

    $afterRow.after($new);
    initializeSubRowTimePickers(newRowKey, hour, startTime, endTime);
    return $new;
}

// initializeSubRowTimePickers — sub-rows
function initializeSubRowTimePickers(rowKey, hour, startTime, endTime) {
    const s = document.getElementById(`time${rowKey}_1`);
    const e = document.getElementById(`time${rowKey}_2`);
    if (!s || !e) return;

    if (s._flatpickr) try { s._flatpickr.destroy(); } catch (err) {}
    if (e._flatpickr) try { e._flatpickr.destroy(); } catch (err) {}

    const start24 = to24h(startTime);
    const maxStart24 = hourEnd(hour);
    const maxEnd24   = getMaxEndTimeForRow(rowKey);
    const minStart24 = getMinStartTimeForRow(rowKey);      // ⭐ NEW

    s._flatpickr = flatpickr(s, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: minStart24, maxTime: maxStart24,          // ⭐ use minStart24
        defaultDate: startTime, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            if (e._flatpickr) {
                e._flatpickr.set('minTime', dateStr);
                const newMax24 = getMaxEndTimeForRow(rowKey);
                e._flatpickr.set('maxTime', newMax24);
            }
            syncHeadGap(String(hour));
            updateTimeRangeSummary();
        }
    });

    e._flatpickr = flatpickr(e, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: start24, maxTime: maxEnd24,
        defaultDate: endTime, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            const clamped = clampEndTime(rowKey, dateStr);
            if (clamped !== dateStr) {
                e.value = clamped;
                e._flatpickr.setDate(clamped, false);
                return;
            }

            cascadeNextRowStart(rowKey);
            autoCreateSubRow(rowKey, clamped);
            refreshEndMaxForAllRows();
            mergeLeaveColumnsForTimeOff();
            updateDynamicInputsForTimeOff();
        }
    });
}

/** Remove a sub-row by key (destroys flatpickr first). */
function removeSubRowByKey(subRowKey) {
    const $sub = $('#' + subRowKey).closest('tr');
    if (!$sub.length) return;

    const s = document.getElementById(`time${subRowKey}_1`);
    const e = document.getElementById(`time${subRowKey}_2`);
    if (s && s._flatpickr) try { s._flatpickr.destroy(); } catch (err) {}
    if (e && e._flatpickr) try { e._flatpickr.destroy(); } catch (err) {}

    $sub.remove();
}

/** The immediate sub-row after `rowKey`, if it belongs to the same hour. */
function getSubRowAfter(rowKey) {
    const $row = $('#' + rowKey).closest('tr');
    if (!$row.length) return $();

    const $next = $row.next('tr');
    if (!$next.length || $next.attr('data-sub') !== 'true') return $();

    const cp = parseRowKey(rowKey);
    const np = parseRowKey($next.find('.tchk').attr('id'));
    if (!cp || !np || cp.hour !== np.hour) return $();

    return $next;
}

/**
 * Auto-manage sub-rows after the end-time of `rowKey` changes.
 * - If the new end time is the natural end of the hour, remove the sub-row.
 * - If it's earlier, ensure a sub-row exists for the remaining minutes.
 */
function autoCreateSubRow(rowKey, newEndTime) {
    const parsed = parseRowKey(rowKey);
    if (!parsed) return;

    const { hour } = parsed;
    const maxEnd24 = hourEnd(hour);
    const newEnd24 = to24h(newEndTime);

    // ---- Case 1: end covers the whole hour → drop any trailing sub-row ----
    if (newEnd24 >= maxEnd24) {
        const $existing = getSubRowAfter(rowKey);
        if ($existing.length) {
            const ek = $existing.find('.tchk').attr('id');
            if (!$('#' + ek).is(':checked') && !$existing.attr('data-locked-group')) {
                removeSubRowByKey(ek);
            }
        }
        return;
    }

    // ---- Case 2: end leaves uncovered minutes ----
    const nextStart24      = addOneMinute(newEnd24);
    const nextStartDisplay = formatTo12h(nextStart24);

    // ⭐ Don't create a tail gap if ANY subsequent row in the same hour
    //    already occupies the time range we'd fill.
    let $blocked = $();

    $('#' + rowKey).closest('tr').nextAll().each(function () {
        const $r = $(this);
        const k = $r.find('.tchk').attr('id');
        const p = k ? parseRowKey(k) : null;
        if (!p || p.hour !== hour) return false;   // stop at next hour

        // Skip gaps — they don't block
        if ($r.attr('data-gap') === 'true') return;

        const ns = document.getElementById(`time${k}_1`);
        if (!ns || !ns.value) return;

        const rowStart24 = to24h(ns.value);
        if (rowStart24 >= nextStart24) {
            $blocked = $r;
            return false;
        }

        const ne = document.getElementById(`time${k}_2`);
        if (!ne || !ne.value) return;
        const rowEnd24 = to24h(ne.value);
        if (rowEnd24 >= nextStart24) {
            $blocked = $r;
            return false;
        }
    });

    if ($blocked.length) {
        // Remove any stale unchecked tail gap we created earlier
        const $existing = getSubRowAfter(rowKey);
        if ($existing.length) {
            const ek = $existing.find('.tchk').attr('id');
            if (!$('#' + ek).is(':checked') && !$existing.attr('data-locked-group')) {
                removeSubRowByKey(ek);
            }
        }
        return;
    }

    // ---- Case 3: look for an existing tail sub-row to update ----
    let $existingTail = $();
    $('#' + rowKey).closest('tr').nextAll('tr[data-sub="true"]').each(function () {
        const $r = $(this);
        if ($r.attr('data-gap-role') === 'head') return;   // skip head gaps
        const k = $r.find('.tchk').attr('id');
        const p = parseRowKey(k);
        if (!p || p.hour !== hour) return false;
        $existingTail = $r;
        return false;
    });

    if ($existingTail.length) {
        const ek = $existingTail.find('.tchk').attr('id');
        const ep = parseRowKey(ek);
        const eMax = hourEnd(ep.hour);

        const es = document.getElementById(`time${ek}_1`);
        const ee = document.getElementById(`time${ek}_2`);
        if (!es || !ee) return;

        es.value = nextStartDisplay;
        if (es._flatpickr) {
            es._flatpickr.set('minTime', nextStartDisplay);
            es._flatpickr.setDate(nextStartDisplay, false);
        }

        if (to24h(ee.value) <= nextStart24) {
            ee.value = formatTo12h(eMax);
            if (ee._flatpickr) ee._flatpickr.setDate(formatTo12h(eMax), false);
        }
        if (ee._flatpickr) ee._flatpickr.set('minTime', nextStartDisplay);
        return;
    }

    // ---- Case 4: create a new tail gap at the next free sub-index ----
    let nextSubIndex = 1;
    while ($('#' + getRowKey(hour, nextSubIndex)).length > 0) {
        nextSubIndex++;
        if (nextSubIndex > 10) return;
    }

    const newKey = getRowKey(hour, nextSubIndex);
    insertSubRow(
        $('#' + rowKey).closest('tr'),
        newKey,
        hour,
        nextStartDisplay,
        formatTo12h(maxEnd24)
    );

    const $new = $('#' + newKey).closest('tr');
    $new.attr('data-gap', 'true');
    $new.attr('data-gap-role', 'tail');
    $new.attr('data-sort-start', timeToMinutes(nextStartDisplay));
}


/* ==========================================================================
   3. ROW ACTIVATION & CASCADE
   ========================================================================== */

/**
 * Check or uncheck a single row and re-align every time input around it.
 * Called from the row checkbox's onclick attribute.
 */
function activateTimeForTimeOff(rowKey) {
    const $chk       = $('#' + rowKey);
    const isChecked  = $chk.is(':checked');
    const $row       = $chk.closest('tr');
    const hour       = parseInt($row.attr('data-hour') || rowKey, 10);
    const isSubRow   = $row.attr('data-sub') === 'true';

    if (isChecked && !$row.attr('data-open-entry')) {
        const $openRow = $('#hoursTableBody tr[data-open-entry="true"]').first();
        if ($openRow.length) {
            const openRowKey = $openRow.find('.tchk').attr('id');
            const openStartInput = document.getElementById(`time${openRowKey}_1`);
            const openRealStart = $openRow.find('input').first().attr('data-real-start')
                || (openStartInput ? openStartInput.value : null);

            // Get this row's end time
            const thisStart = $row.find('input[type="text"]').first().val();
            const thisEnd = $row.find('input[type="text"]').last().val();

            if (openRealStart && thisStart && thisEnd) {
                const openStart24 = to24h(openRealStart);
                const thisEnd24 = to24h(thisEnd);

                // Block if this row ends AFTER the open entry started
                if (thisEnd24 > openStart24) {
                    // Revert
                    $chk.prop('checked', false);

                    swalWarning(
                        'Open Entry Exists',
                        'You have an open entry at ' + openRealStart + ' on this date. ' +
                        'Please Time Out or Cancel it before filing attendance past that time.'
                    );
                    return;
                }
            }
        }
    }

    $(`#time${rowKey}_1, #time${rowKey}_2`).prop('disabled', !isChecked);

    if (!isChecked) {
        handleUncheck(rowKey, $row, hour, isSubRow);
    } else {
        handleCheck(rowKey, $row, hour, isSubRow);
    }

    if (hour === LUNCH_HOUR && !isSubRow) {
        if (isChecked) $row.removeClass('lunch-break-row');
        else           $row.addClass('lunch-break-row');
    }

    // ⭐ Always recompute the head gap from the MAIN row's state.
    syncHeadGap(String(hour));

    mergeLeaveColumnsForTimeOff();
    updateDynamicInputsForTimeOff();
    refreshEndMaxForAllRows();
    updateTimeRangeSummary();
}

function handleUncheck(rowKey, $row, hour, isSubRow) {
    const $next = getSubRowAfter(rowKey);
    let nextIsChecked = false;
    if ($next.length) {
        const nextKey = $next.find('.tchk').attr('id');
        nextIsChecked = $('#' + nextKey).is(':checked');
        if (!nextIsChecked) removeSubRowByKey(nextKey);
    }

    if (!isSubRow) {
        if (!nextIsChecked) {
            resetMainRowToFullHour(hour);
            resetUncheckedRowsBelow(hour);
        }
        return;
    }

    // Sub-row unchecked
    const $prev = $row.prev('tr');
    let prevKey = null, prevIsChecked = false;
    if ($prev.length) {
        prevKey = $prev.find('.tchk').attr('id');
        if (prevKey) prevIsChecked = $('#' + prevKey).is(':checked');
    }

    if (!prevIsChecked) {
        // ⭐ Leave-gap sub-rows: uncheck but DON'T remove them.
        //    They're derived from the leave and must stay visible.
        if ($row.attr('data-leave-gap') === 'true') {
            $row.find('.tchk').prop('checked', false);
            $row.find('input[type="text"]').prop('disabled', true);
            mergeLeaveColumnsForTimeOff();
            updateDynamicInputsForTimeOff();
            updateTimeRangeSummary();
            return;
        }

        resetPreviousRowIfFullyUnchecked(rowKey);
        removeSubRowByKey(rowKey);
        mergeLeaveColumnsForTimeOff();
        updateDynamicInputsForTimeOff();
        updateTimeRangeSummary();
        return;
    }

    // Previous is checked → align this end time with the next row (or full hour)
    const $next2 = getSubRowAfter(rowKey);
    let nextKey2 = null, nextIsChecked2 = false;
    if ($next2.length) {
        nextKey2 = $next2.find('.tchk').attr('id');
        nextIsChecked2 = $('#' + nextKey2).is(':checked');
    }

    const defEnd = hourEnd(hour);

    if (nextIsChecked2) {
        const nextStart = document.getElementById(`time${nextKey2}_1`);
        if (nextStart && nextStart.value) {
            const aligned = formatTo12h(subtractOneMinute(to24h(nextStart.value)));
            const thisEnd = document.getElementById(`time${rowKey}_2`);
            if (thisEnd) {
                thisEnd.value = aligned;
                if (thisEnd._flatpickr) {
                    thisEnd._flatpickr.set('maxTime', defEnd);
                    thisEnd._flatpickr.setDate(aligned, false);
                }
            }
        }
    } else {
        const thisEnd = document.getElementById(`time${rowKey}_2`);
        if (thisEnd) {
            thisEnd.value = defEnd;
            if (thisEnd._flatpickr) {
                thisEnd._flatpickr.set('maxTime', defEnd);
                thisEnd._flatpickr.setDate(defEnd, false);
            }
        }
    }
}

function handleCheck(rowKey, $row, hour, isSubRow) {
    if (isSubRow) return;   // sub-rows don't cascade down

    const $next = getSubRowAfter(rowKey);
    let nextIsChecked = false;
    if ($next.length) {
        nextIsChecked = $('#' + $next.find('.tchk').attr('id')).is(':checked');
    }

    if (!nextIsChecked) {
        const defEnd = hourEnd(hour);
        const endInput = document.getElementById(`time${rowKey}_2`);
        if (endInput) {
            endInput.value = defEnd;
            if (endInput._flatpickr) {
                endInput._flatpickr.set('maxTime', defEnd);
                endInput._flatpickr.setDate(defEnd, false);
            }
        }
    }

    adjustStartTimeBasedOnPrevRow(hour);
    adjustEndTimeBasedOnNextRow(hour);
    cascadeDownFromCheckedRow(hour);
}

/** Reset a main row's times to [hour:00, hour:59] (respecting the previous row). */
function resetMainRowToFullHour(hour) {
    const defEnd   = hourEnd(hour);
    const defStart = hourStart(hour);

    const s = document.getElementById(`time${hour}_1`);
    const e = document.getElementById(`time${hour}_2`);
    if (!s || !e) return;

    const $row = $(s).closest('tr');
    if ($row.attr('data-leave-covered') === 'true') return;

    let newStart = defStart;
    const prevHour = hour - 1;
    if (prevHour >= FIRST_HOUR && prevHour <= LAST_HOUR) {
        const prevChecked = $('#' + prevHour).is(':checked');
        const prevLocked  = $('#' + prevHour).closest('tr').attr('data-locked-group') !== undefined;
        if (prevChecked || prevLocked) {
            const pe = document.getElementById(`time${prevHour}_2`);
            if (pe && pe.value) newStart = formatTo12h(addOneMinute(to24h(pe.value)));
        }
    }

    s.value = newStart;
    if (s._flatpickr) {
        s._flatpickr.set('minTime', hourStart(hour));
        s._flatpickr.set('maxTime', defEnd);
        s._flatpickr.setDate(newStart, false);
    }

    e.value = defEnd;
    if (e._flatpickr) {
        e._flatpickr.set('minTime', to24h(newStart));
        e._flatpickr.set('maxTime', defEnd);
        e._flatpickr.setDate(defEnd, false);
    }
}

/** Align this row's start time to follow the previous row's end + 1 minute. */
function adjustStartTimeBasedOnPrevRow(hour) {
    const prevHour = hour - 1;
    if (prevHour < FIRST_HOUR) return;

    const $prev       = $('#' + prevHour).closest('tr');
    const prevChecked = $('#' + prevHour).is(':checked');
    const prevLocked  = $prev.attr('data-locked-group') !== undefined;
    if (!prevChecked || prevLocked) return;

    const pe = document.getElementById(`time${prevHour}_2`);
    if (!pe || !pe.value) return;

    const expectedStart = addOneMinute(to24h(pe.value));
    const s = document.getElementById(`time${hour}_1`);
    if (!s) return;

    if (to24h(s.value) >= expectedStart) return;

    const maxTime = hourEnd(hour);

    if (s._flatpickr) s._flatpickr.destroy();
    s.value = expectedStart;
    s._flatpickr = flatpickr(s, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: expectedStart, maxTime: maxTime,
        defaultDate: expectedStart, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            const en = document.getElementById(`time${hour}_2`);
            if (en && en._flatpickr) en._flatpickr.set('minTime', dateStr);
            updateTimeRangeSummary();
        }
    });

    const en = document.getElementById(`time${hour}_2`);
    if (en && en._flatpickr) en._flatpickr.set('minTime', expectedStart);
}

/** Align this row's end time to precede the next row's start (subtract 1 minute). */
function adjustEndTimeBasedOnNextRow(hour) {
    const nextHour = hour + 1;
    if (nextHour > LAST_HOUR) return;

    const $next       = $('#' + nextHour).closest('tr');
    const nextChecked = $('#' + nextHour).is(':checked');
    const nextLocked  = $next.attr('data-locked-group') !== undefined;
    if (!nextChecked || nextLocked) return;

    const ns = document.getElementById(`time${nextHour}_1`);
    if (!ns || !ns.value) return;

    const expectedEnd = subtractOneMinute(to24h(ns.value));
    const e = document.getElementById(`time${hour}_2`);
    if (!e) return;
    if (to24h(e.value) <= expectedEnd) return;

    const s = document.getElementById(`time${hour}_1`);
    const maxTime = getMaxEndTimeForRow(String(hour));   // ⭐ dynamic cap
    const currentStart24 = (s && s.value) ? to24h(s.value) : hourStart(hour);

    if (e._flatpickr) e._flatpickr.destroy();
    e.value = formatTo12h(expectedEnd);
    e._flatpickr = flatpickr(e, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: currentStart24, maxTime: maxTime,
        defaultDate: formatTo12h(expectedEnd), minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            const clamped = clampEndTime(String(hour), dateStr);
            if (clamped !== dateStr) {
                e.value = clamped;
                e._flatpickr.setDate(clamped, false);
            }
            cascadeToNextRow(hour, clamped);
            refreshEndMaxForAllRows();
            mergeLeaveColumnsForTimeOff();
            updateDynamicInputsForTimeOff();
        }
    });
}

/** Push this row's end time into the next row's start time. */
function cascadeToNextRow(hour, dateStr) {
    const nextHour = hour + 1;
    if (nextHour > LAST_HOUR) return;

    const $next = $('#' + nextHour).closest('tr');
    if ($next.attr('data-locked-group') !== undefined) return;

    const ns = document.getElementById(`time${nextHour}_1`);
    if (!ns || !ns._flatpickr) return;

    const nextMinTime = addOneMinute(to24h(dateStr));
    const nextMaxTime = hourEnd(nextHour);

    ns._flatpickr.set('minTime', nextMinTime);
    ns._flatpickr.set('maxTime', nextMaxTime);
    ns._flatpickr.setDate(nextMinTime, false);
    ns.value = nextMinTime;
}

/** Cascade start times downward from a just-checked row (until we hit another checked row). */
function cascadeDownFromCheckedRow(hour) {
    for (let h = hour + 1; h <= LAST_HOUR; h++) {
        const $row = $('#' + h).closest('tr');
        if ($('#' + h).is(':checked') || $row.attr('data-locked-group') !== undefined) break;

        const pe = document.getElementById(`time${h - 1}_2`);
        if (!pe || !pe.value) break;

        const cascadedStart = addOneMinute(to24h(pe.value));
        const maxTime = hourEnd(h);

        const s = document.getElementById(`time${h}_1`);
        const e = document.getElementById(`time${h}_2`);
        if (s && s._flatpickr) {
            s.value = cascadedStart;
            s._flatpickr.set('minTime', cascadedStart);
            s._flatpickr.setDate(cascadedStart, false);
            if (e && e._flatpickr) e._flatpickr.set('minTime', cascadedStart);
        }
    }
}

/** Reset every unchecked row below `fromHour` to a cascaded or default time. */
function resetUncheckedRowsBelow(fromHour) {
    for (let h = fromHour + 1; h <= LAST_HOUR; h++) {
        const $row = $('#' + h).closest('tr');

        // ⭐ Skip leave-covered rows
        if ($row.attr('data-leave-covered') === 'true') continue;

        if ($('#' + h).is(':checked') || $row.attr('data-locked-group') !== undefined) continue;

        const prevHour = h - 1;
        let prevValid = false, prevEnd = null;
        if (prevHour >= FIRST_HOUR) {
            const pc = $('#' + prevHour).is(':checked');
            const pl = $('#' + prevHour).closest('tr').attr('data-locked-group') !== undefined;
            if (pc || pl) {
                const pe = document.getElementById(`time${prevHour}_2`);
                if (pe && pe.value) { prevValid = true; prevEnd = pe.value; }
            }
        }

        if (prevValid && prevEnd) cascadeUncheckedRowFromPrevious(h, prevEnd);
        else                      resetUncheckedRow(h);
    }
}

function cascadeUncheckedRowFromPrevious(hour, prevEndTime) {
    const maxTime       = hourEnd(hour);
    const cascadedStart = addOneMinute(to24h(prevEndTime));

    const s = document.getElementById(`time${hour}_1`);
    const e = document.getElementById(`time${hour}_2`);
    if (!s || !e) return;

    if (s._flatpickr) s._flatpickr.destroy();
    if (e._flatpickr) e._flatpickr.destroy();

    s.value = formatTo12h(cascadedStart);
    e.value = formatTo12h(maxTime);

    const maxEnd24 = getMaxEndTimeForRow(String(hour));   // ⭐

    s._flatpickr = flatpickr(s, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: cascadedStart, maxTime: maxTime,
        defaultDate: cascadedStart, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            if (e._flatpickr) {
                e._flatpickr.set('minTime', dateStr);
                const newMax24 = getMaxEndTimeForRow(String(hour));
                e._flatpickr.set('maxTime', newMax24);
            }
            syncHeadGap(String(hour));
            updateTimeRangeSummary();
        }
    });

    e._flatpickr = flatpickr(e, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: cascadedStart, maxTime: maxEnd24,   // ⭐
        defaultDate: maxTime, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            const clamped = clampEndTime(String(hour), dateStr);
            if (clamped !== dateStr) {
                e.value = clamped;
                e._flatpickr.setDate(clamped, false);
            }

            cascadeNextRowStart(String(hour));
            autoCreateSubRow(String(hour), clamped);
            if (!getSubRowAfter(String(hour)).length) {
                cascadeToNextRow(hour, clamped);
            }

            refreshEndMaxForAllRows();
            mergeLeaveColumnsForTimeOff();
            updateDynamicInputsForTimeOff();
        }
    });
}

function resetUncheckedRow(hour) {
    const defStart = hourStart(hour);
    const defEnd   = hourEnd(hour);

    const s = document.getElementById(`time${hour}_1`);
    const e = document.getElementById(`time${hour}_2`);
    if (!s || !e) return;

    if (s._flatpickr) s._flatpickr.destroy();
    if (e._flatpickr) e._flatpickr.destroy();

    s.value = formatTo12h(defStart);
    e.value = formatTo12h(defEnd);

    const maxEnd24 = getMaxEndTimeForRow(String(hour));   // ⭐

    s._flatpickr = flatpickr(s, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: defStart, maxTime: defEnd,
        defaultDate: defStart, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            if (e._flatpickr) {
                e._flatpickr.set('minTime', dateStr);
                const newMax24 = getMaxEndTimeForRow(String(hour));
                e._flatpickr.set('maxTime', newMax24);
            }
            syncHeadGap(String(hour));
            updateTimeRangeSummary();
        }
    });

    e._flatpickr = flatpickr(e, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: defStart, maxTime: maxEnd24,   // ⭐
        defaultDate: defEnd, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            // ⭐ Clamp
            const clamped = clampEndTime(String(hour), dateStr);
            if (clamped !== dateStr) {
                e.value = clamped;
                e._flatpickr.setDate(clamped, false);
            }

            cascadeNextRowStart(String(hour));
            autoCreateSubRow(String(hour), clamped);

            if (!getSubRowAfter(String(hour)).length) {
                const nextHour = hour + 1;
                if (nextHour <= LAST_HOUR) {
                    const ns = document.getElementById(`time${nextHour}_1`);
                    if (ns && ns._flatpickr) {
                        const nextMin = addOneMinute(to24h(clamped));
                        ns._flatpickr.set('minTime', nextMin);
                        ns._flatpickr.set('maxTime', hourEnd(nextHour));
                        ns._flatpickr.setDate(nextMin, false);
                        ns.value = formatTo12h(nextMin);
                    }
                }
            }

            refreshEndMaxForAllRows();
            mergeLeaveColumnsForTimeOff();
            updateDynamicInputsForTimeOff();
        }
    });
}

function resetPreviousRowIfFullyUnchecked(subRowKey) {
    const $subRow = $('#' + subRowKey).closest('tr');
    const $prev   = $subRow.prev('tr');
    if (!$prev.length) return;

    const prevKey = $prev.find('.tchk').attr('id');
    if (!prevKey) return;

    const prevParsed = parseRowKey(prevKey);
    if (!prevParsed) return;

    const prevChecked = $('#' + prevKey).is(':checked');
    const prevLocked  = $prev.attr('data-locked-group') !== undefined;
    if (prevChecked || prevLocked) return;

    const isMain = !$prev.attr('data-sub');
    if (isMain) {
        // Scan consecutive sub-rows for any checked
        let anyChecked = false;
        let $scan = $prev.next('tr');
        while ($scan.length && $scan.attr('data-sub') === 'true') {
            const sk = $scan.find('.tchk').attr('id');
            if ($('#' + sk).is(':checked')) { anyChecked = true; break; }
            $scan = $scan.next('tr');
        }
        if (!anyChecked) {
            const $mainRow = $('#' + prevParsed.hour).closest('tr');
            // ⭐ Skip leave-covered main rows
            if ($mainRow.attr('data-leave-covered') !== 'true') {
                resetMainRowToFullHour(prevParsed.hour);
            }
            resetUncheckedRowsBelow(prevParsed.hour);
        }
    } else {
        resetPreviousRowIfFullyUnchecked(prevKey);
    }
}


/* ==========================================================================
   4. GROUP MERGING
   ========================================================================== */

/**
 * Merge contiguous checked / locked rows into groups.
 * Two adjacent rows stay in the same group only when they share:
 *   - the same data-locked-group (or both unlocked)
 *   - the same data-saved-group (or one is missing)
 *   - the same data-group-id (or one is missing)
 */
function getMergedGroups() {
    const $rows   = $('#hoursTableBody tr').has('.tchk');
    const checked = [];

    $rows.each(function () {
        if ($(this).find('.tchk').is(':checked') || $(this).attr('data-locked-group')) {
            checked.push($(this));
        }
    });

    if (!checked.length) return [];

    const groups = [];
    let cur = [checked[0]];

    for (let i = 1; i < checked.length; i++) {
        const prev = checked[i - 1];
        const curr = checked[i];

        const pi = $rows.index(prev);
        const ci = $rows.index(curr);

        const pg = prev.attr('data-locked-group') || 'unlocked';
        const cg = curr.attr('data-locked-group') || 'unlocked';

        const ps = prev.attr('data-saved-group');
        const cs = curr.attr('data-saved-group');
        const differentSaved = ps && cs && ps !== cs;

        const pgid = prev.attr('data-group-id');
        const cgid = curr.attr('data-group-id');
        const differentGroupId = pgid && cgid && pgid !== cgid;

        if (ci === pi + 1 && pg === cg && !differentSaved && !differentGroupId) {
            cur.push(curr);
        } else {
            groups.push(cur);
            cur = [curr];
        }
    }
    groups.push(cur);
    return groups;
}

/**
 * Merge columns 3–8 (Type → Action) across each multi-row group using rowspan.
 * Single-row groups are left as-is.
 */
function mergeLeaveColumnsForTimeOff() {
    const $rows = $('#hoursTableBody tr').has('.tchk');

    // Reset
    $rows.each(function () {
        $(this).children('td').slice(3, 9).show().removeAttr('rowspan');
    });

    const groups = getMergedGroups();
    groups.forEach(group => {
        if (group.length <= 1) return;

        const firstCells = group[0].children('td');
        const span = group.length;

        for (let col = 3; col <= 8; col++) {
            firstCells.eq(col).attr('rowspan', span);
            for (let i = 1; i < group.length; i++) {
                group[i].children('td').eq(col).hide();
            }
        }
    });
}


/* ==========================================================================
   5. GROUP LOCKING
   ========================================================================== */

/** Freeze a group's rows (usually after the user finalizes it). */
function lockGroupForTimeOff(btn) {
    const $parentTd = $(btn).closest('td');
    const $firstRow = $(btn).closest('tr');

    if ($firstRow.attr('data-open-entry') === 'true')     return;
    if ($firstRow.attr('data-spanning-entry') === 'true') return;

    const rowSpan   = parseInt($parentTd.attr('rowspan')) || 1;
    const $rows     = $('#hoursTableBody tr').has('.tchk');
    const startIdx  = $rows.index($firstRow);

    const groupRows = [];
    for (let i = 0; i < rowSpan; i++) groupRows.push($rows.eq(startIdx + i));

    const check = validateGroup($firstRow, groupRows, 0);
    if (!check.valid) {
        swalError(check.title, check.text);
        return;
    }

    const groupId = `group_${state.groupCounter++}`;

    for (let i = 0; i < rowSpan; i++) {
        const $r = $rows.eq(startIdx + i);
        $r.attr('data-locked-group', groupId);
        $r.find('.tchk').prop('disabled', true);
        $r.find('input[type="time"]').prop('disabled', true);
        lockFlatpickrOnRow($r);
    }

    const $cells = $firstRow.children('td');
    $cells.eq(3).find('.type-select').prop('disabled', true);
    $cells.eq(4).find('.category-select').prop('disabled', true);
    $cells.eq(5).find('.remarks-input').prop('disabled', true);
    $cells.eq(6).find('.attachment-drop-zone').css({ pointerEvents: 'none', opacity: '0.6', cursor: 'not-allowed' });
    $cells.eq(6).find('.file-actions .btn-outline-danger').prop('disabled', true);
    $cells.eq(6).find('.file-actions .btn-outline-primary').prop('disabled', false);
    $cells.eq(8).html(`<button type="button" class="btn btn-sm btn-secondary action-btn"
                              onclick="unlockGroupForTimeOff(this)" title="Unlock to edit">
                          <i class="bi bi-unlock me-1"></i>Unlock
                      </button>`);

    updateDynamicInputsForTimeOff();
}

/** Thaw a group so the user can edit it again. */
function unlockGroupForTimeOff(btn) {
    const $firstRow = $(btn).closest('tr');
    const groupId   = $firstRow.attr('data-locked-group');
    if (!groupId) return;

    const $grouped = $(`tr[data-locked-group="${groupId}"]`);
    $grouped.each(function () {
        $(this).removeAttr('data-locked-group');
        $(this).find('.tchk').prop('disabled', false);
        $(this).find('input[type="time"]').prop('disabled', false);
        unlockFlatpickrOnRow($(this));
    });

    const $cells = $firstRow.children('td');
    $cells.eq(3).find('.type-select').prop('disabled', false);
    $cells.eq(4).find('.category-select').prop('disabled', false);
    $cells.eq(5).find('.remarks-input').prop('disabled', false);
    $cells.eq(6).find('.attachment-drop-zone').css({ pointerEvents: 'auto', opacity: '1', cursor: 'pointer' });
    $cells.eq(6).find('.file-actions .btn-outline-danger').prop('disabled', false);
    $cells.eq(6).find('.file-actions .btn-outline-primary').prop('disabled', false);

    const hasId = $firstRow.attr('data-group-id') !== undefined;
    $cells.eq(8).html(
        hasId
            ? `<div class="d-flex gap-1">
                   <button type="button" class="btn btn-sm btn-primary action-btn"
                           onclick="lockGroupForTimeOff(this)"><i class="bi bi-lock"></i></button>
                   <button type="button" class="btn btn-sm btn-danger action-btn"
                           onclick="deleteGroupForTimeOff(this)"><i class="bi bi-trash"></i></button>
               </div>`
            : `<button type="button" class="btn btn-sm btn-primary action-btn"
                       onclick="lockGroupForTimeOff(this)"><i class="bi bi-lock"></i></button>`
    );

    // Rebuild time pickers on the (now editable) rows
    $grouped.each(function () {
        rebuildRowTimePickers($(this));
    });

    mergeLeaveColumnsForTimeOff();
    updateDynamicInputsForTimeOff();
}

/**
 * Reattach flatpickr to a row's two time inputs after they were re-enabled.
 * Preserves the current values.
 */
function rebuildRowTimePickers($row) {
    const rowKey    = $row.find('.tchk').attr('id');
    const hour      = parseInt($row.attr('data-hour') || rowKey, 10);
    const isSub     = $row.attr('data-sub') === 'true';
    const maxStart  = hourEnd(hour);

    const oldS = document.getElementById(`time${rowKey}_1`);
    const oldE = document.getElementById(`time${rowKey}_2`);
    if (!oldS || !oldE) return;

    const curStart = oldS.value || hourStart(hour);
    const curEnd   = oldE.value || hourEnd(hour);

    const s = refreshInputElement(oldS);
    const e = refreshInputElement(oldE);
    s.value = curStart;
    e.value = curEnd;

    const start24 = to24h(curStart);
    const maxEnd24 = getMaxEndTimeForRow(rowKey);   // ⭐

    const minStart24 = getMinStartTimeForRow(rowKey);
    s._flatpickr = flatpickr(s, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: minStart24, maxTime: maxStart,
        defaultDate: curStart, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            if (e._flatpickr) {
                e._flatpickr.set('minTime', dateStr);
                const newMax24 = getMaxEndTimeForRow(rowKey);
                e._flatpickr.set('maxTime', newMax24);
            }
            syncHeadGap(String(hour));
            updateTimeRangeSummary();
        }
    });

    e._flatpickr = flatpickr(e, {
        enableTime: true, noCalendar: true, dateFormat: 'G:i K',
        minTime: start24, maxTime: maxEnd24,   // ⭐
        defaultDate: curEnd, minuteIncrement: 1,
        static: true, disableMobile: true,
        appendTo: document.getElementById('timeOffModal'),
        onChange: (_, dateStr) => {
            // ⭐ Clamp
            const clamped = clampEndTime(rowKey, dateStr);
            if (clamped !== dateStr) {
                e.value = clamped;
                e._flatpickr.setDate(clamped, false);
            }

            cascadeNextRowStart(rowKey);
            autoCreateSubRow(rowKey, clamped);

            if (!getSubRowAfter(rowKey).length && !isSub) {
                const nextHour = hour + 1;
                if (nextHour <= LAST_HOUR) {
                    const ns = document.getElementById(`time${nextHour}_1`);
                    if (ns && ns._flatpickr) {
                        const nextMin = addOneMinute(to24h(clamped));
                        ns._flatpickr.set('minTime', nextMin);
                        ns._flatpickr.set('maxTime', hourEnd(nextHour));
                        ns._flatpickr.setDate(nextMin, false);
                        ns.value = formatTo12h(nextMin);
                    }
                }
            }

            refreshEndMaxForAllRows();
            mergeLeaveColumnsForTimeOff();
            updateDynamicInputsForTimeOff();
        }
    });
}
/** Detach flatpickr clicks on a row (used when locking). */
function lockFlatpickrOnRow($row) {
    const key = $row.find('.tchk').attr('id');
    const s = document.getElementById(`time${key}_1`);
    const e = document.getElementById(`time${key}_2`);
    if (s && s._flatpickr) s._flatpickr.set('clickOpens', false);
    if (e && e._flatpickr) e._flatpickr.set('clickOpens', false);
}

/** Re-enable flatpickr clicks on a row (used when unlocking). */
function unlockFlatpickrOnRow($row) {
    const key = $row.find('.tchk').attr('id');
    const s = document.getElementById(`time${key}_1`);
    const e = document.getElementById(`time${key}_2`);
    if (s && s._flatpickr) s._flatpickr.set('clickOpens', true);
    if (e && e._flatpickr) e._flatpickr.set('clickOpens', true);
}

/**
 * Replace an <input> with a fresh clone (no attached event listeners).
 * Used to strip stale flatpickr bindings before re-initializing.
 */
function refreshInputElement(el) {
    if (!el) return null;
    const newEl = document.createElement('input');
    newEl.type      = el.type || 'time';
    newEl.id        = el.id;
    newEl.className = el.className;
    newEl.value     = el.value;
    el.parentNode.replaceChild(newEl, el);
    return newEl;
}


/* ==========================================================================
   6. DYNAMIC INPUTS
   Rebuild the per-group cells (Type / Category / Remarks / Attachments /
   Geo block / Action) every time the row layout changes.
   ========================================================================== */

function updateDynamicInputsForTimeOff() {
    $('#hoursTableBody tr').has('.tchk').each(function () {
        const $row           = $(this);
        const $chk           = $row.find('.tchk');
        const isChecked      = $chk.is(':checked');
        const isLocked       = $row.attr('data-locked-group') !== undefined;
        const isLeaveCovered = $row.attr('data-leave-covered') === 'true';
        const isOpenEntry    = $row.attr('data-open-entry') === 'true';
        const isSpanning     = $row.attr('data-spanning-entry') === 'true';
        const $cells         = $row.children('td');

        const $typeTd = $cells.eq(3);
        const $catTd  = $cells.eq(4);
        const $remTd  = $cells.eq(5);
        const $attTd  = $cells.eq(6);
        const $locTd  = $cells.eq(7);
        const $actTd  = $cells.eq(8);

        // Leave-covered rows keep their badge — leave everything else empty
        if (isLeaveCovered) {
            $catTd.empty();
            $remTd.empty();
            $attTd.empty();
            $locTd.empty();
            $actTd.empty();
            return;
        }

        // Open / spanning rows: their display is owned by modal-load.js
        if (isOpenEntry || isSpanning) return;

        const show = (isChecked || isLocked) && ($typeTd.is(':visible') || $typeTd.attr('rowspan'));

        if (show) {
            const rowSpan = parseInt($typeTd.attr('rowspan')) || 1;

            // Type
            if (!$typeTd.find('.type-select').length) {
                $typeTd.html(`
                    <select class="form-select form-select-sm type-select" onchange="handleTypeChange(this)">
                        <option value="">Select Type</option>
                        <option value="inoffice">In Office</option>
                        <option value="outoffice">Out of Office</option>
                    </select>
                `);
            }

            // Category
            if (!$catTd.find('.category-select').length) {
                $catTd.html(`
                    <select class="form-select form-select-sm category-select" style="display:none;" onchange="handleCategoryChange(this)">
                        <option value="">Select Category</option>
                        <option value="fieldwork"  data-attachment="not-required">Fieldwork</option>
                        <option value="meeting"    data-attachment="not-required">Meeting</option>
                        <option value="expo"       data-attachment="required">Expo</option>
                        <option value="convention" data-attachment="required">Convention</option>
                        <option value="seminar"    data-attachment="required">Seminar/Webinar</option>
                        <option value="training"   data-attachment="required">Training</option>
                        <option value="exhibit"    data-attachment="required">Exhibit</option>
                        <option value="conference" data-attachment="required">Conference</option>
                        <option value="others"     data-attachment="required">Others</option>
                    </select>
                `);
            }

            // Remarks
            const $ta = $remTd.find('.remarks-input');
            if (!$ta.length) {
                $remTd.html(`<textarea class="form-control form-control-sm remarks-input" rows="${rowSpan}" placeholder="Enter remarks..."></textarea>`);
            } else {
                $ta.attr('rows', rowSpan);
            }

            // Attachments
            if (!$attTd.find('.attachment-upload-area').length) {
                $attTd.html(`
                    <div class="attachment-upload-area" style="display:none;">
                        <div class="attachment-drop-zone" onclick="triggerFileUpload(this)" title="Click to upload files">
                            <i class="bi bi-cloud-upload" style="font-size:0.9rem;color:#999;display:block;margin-bottom:2px;"></i>
                            <span style="font-size:0.65rem;color:#666;">Upload</span>
                        </div>
                        <input type="file" class="attachment-input d-none" multiple
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.xls,.xlsx"
                               onchange="handleFileUpload(this)">
                        <div class="attachment-preview-list"></div>
                    </div>
                `);
            }

            // Geo block — create the skeleton; modal-load.js fills the values
            if (!$locTd.find('.geo-time-summary').length) {
                $locTd.html(renderGeoBlockSkeleton());
            }

            // Action buttons
            if (isLocked) {
                $typeTd.find('select').prop('disabled', true);
                $catTd.find('select').prop('disabled', true);
                $remTd.find('textarea').prop('disabled', true);
                $attTd.find('.attachment-drop-zone').css('pointer-events', 'none');
                $attTd.find('.file-actions .btn-outline-danger').prop('disabled', true);
                $attTd.find('.file-actions .btn-outline-primary').prop('disabled', false);

                if (isLockedApprovedGroup($row)) {
                    $actTd.empty();
                } else {
                    $actTd.html(`<button type="button" class="btn btn-sm btn-secondary" onclick="unlockGroupForTimeOff(this)">Unlock</button>`);
                }
            } else {
                $typeTd.find('select').prop('disabled', false);
                $catTd.find('select').prop('disabled', false);
                $remTd.find('textarea').prop('disabled', false);
                $attTd.find('.attachment-drop-zone').css('pointer-events', 'auto');

                const hasId = $row.attr('data-group-id') !== undefined;
                $actTd.html(
                    hasId
                        ? `<div class="d-flex gap-1">
                               <button type="button" class="btn btn-sm btn-primary action-btn" onclick="lockGroupForTimeOff(this)" title="Lock"><i class="bi bi-lock"></i></button>
                               <button type="button" class="btn btn-sm btn-danger action-btn" onclick="deleteGroupForTimeOff(this)" title="Delete"><i class="bi bi-trash"></i></button>
                           </div>`
                        : `<button type="button" class="btn btn-sm btn-primary action-btn" onclick="lockGroupForTimeOff(this)" title="Lock"><i class="bi bi-lock"></i></button>`
                );
            }
        } else if (!isChecked && !isLocked) {
            // Empty row — clear everything
            [3, 4, 5, 6, 7, 8].forEach(i => $cells.eq(i).empty());

            const hour = parseInt($chk.attr('id'), 10);
            if (hour === LUNCH_HOUR) {
                $cells.eq(3).html('<span class="lunch-break-badge" id="lunchBreakBadge">🍽️ Lunch Break</span>');
            }
        }
    });

    updateTimeRangeSummary();
}

/** Skeleton HTML for the geo column. modal-load.js fills in the values. */
function renderGeoBlockSkeleton() {
    const dateFormatted = state.selectedDate
        ? formatDisplayDate(state.selectedDate)
        : '—';
    const curLoc = (window.currentUserLocation && window.currentUserLocation.address)
        ? window.currentUserLocation.address
        : 'Detecting...';

    return `
        <div class="geo-time-summary">
            <div class="geo-selected-date-display" style="font-size:0.7rem;color:#666;margin-bottom:3px;">
                <i class="bi bi-calendar-check me-1"></i><span class="geo-selected-date-text">${dateFormatted}</span>
            </div>
            <div class="time-range-display" style="font-size:0.75rem;color:#333;font-weight:600;margin-bottom:2px;">
                <i class="bi bi-clock me-1"></i><span class="time-range-text">Loading...</span>
            </div>
            <div class="hours-total-display" style="font-size:0.75rem;color:var(--brand);font-weight:700;margin-bottom:3px;">
                <i class="bi bi-hourglass-split me-1"></i><span class="hours-total-text">Calculating...</span>
            </div>

            <div class="location-display" style="font-size:0.65rem;color:#6c757d;margin-bottom:3px;">
                <i class="bi bi-send me-1"></i>Submitted: <span class="geo-location-text">${curLoc}</span>
            </div>

            <div class="updated-location-display d-none" style="font-size:0.65rem;color:#0d6efd;margin-bottom:3px;">
                <i class="bi bi-pencil-square me-1"></i>Edited: <span class="updated-location-text">—</span>
            </div>

            <div class="status-display" style="font-size:0.65rem;display:none;">
                <i class="bi bi-shield-check me-1"></i>Status: <span class="status-badge-slot">—</span>
            </div>
        </div>
    `;
}

function handleTypeChange(selectElement) {
    const $row   = $(selectElement).closest('tr');
    const $cells = $row.children('td');

    const $cat   = $cells.eq(4).find('.category-select');
    const $att   = $cells.eq(6).find('.attachment-upload-area');
    const $rem   = $cells.eq(5).find('.remarks-input');
    const $badge = $cells.eq(5).find('.remarks-required-badge');
    const val    = $(selectElement).val();

    if (val === 'outoffice') {
        $cat.show();
        if ($rem.length && !$badge.length) {
            $cells.eq(5).prepend('<span class="badge bg-danger remarks-required-badge" style="font-size:0.55rem;margin-bottom:3px;display:inline-block;">Required</span>');
        }
        $rem.attr('required', 'required').attr('data-required', 'true');
    } else {
        $cat.hide().val('');
        $att.hide();
        clearAttachments($row);
        $badge.remove();
        $rem.removeAttr('required').attr('data-required', 'false');
    }
}

function handleCategoryChange(selectElement) {
    const $opt   = $(selectElement).find('option:selected');
    const req    = $opt.data('attachment');
    const $row   = $(selectElement).closest('tr');
    const $attTd = $row.children('td').eq(6);
    const $area  = $attTd.find('.attachment-upload-area');

    if (req === 'required') {
        $area.show();
        if (!$attTd.find('.attachment-required-badge').length) {
            $attTd.prepend('<span class="badge bg-danger attachment-required-badge" style="font-size:0.55rem;margin-bottom:3px;display:inline-block;">Required</span>');
        }
    } else {
        $area.hide();
        clearAttachments($row);
        $attTd.find('.attachment-required-badge').remove();
    }
}

/**
 * After a main row's start time changes, ensure a "head gap" sub-row exists
 * for the minutes between the hour's :00 boundary and the new start.
 *
 * Example: main row 9 start = 09:20 → head gap for 09:00–09:19.
 *
 * Special case — checked head gap:
 *   If a checked (or locked) head gap already exists and the main row's
 *   start is back at the hour boundary, we DO NOT remove the head gap.
 *   Instead we shift the main row's start to follow the gap's end, so the
 *   two never overlap. A checked gap represents user-claimed time and must
 *   survive.
 *
 * @param {string} rowKey  The MAIN row key (e.g. "9"). Sub-row keys are
 *                          ignored — head gaps only respond to main rows.
 */
function syncHeadGap(rowKey) {
    const $row = $('#' + rowKey).closest('tr');
    if (!$row.length) return;

    const parsed = parseRowKey(rowKey);
    if (!parsed) return;

    // Only main rows drive the head gap
    if (parsed.subIndex !== 0) return;

    const hour = parsed.hour;

    // Double-check we're actually on the main row for this hour
    if ($row.find('.tchk').attr('id') !== String(hour)) return;

    // Skip leave / open / spanning — those own their layout
    if ($row.attr('data-leave-covered') === 'true') return;
    if ($row.attr('data-open-entry') === 'true') return;
    if ($row.attr('data-spanning-entry') === 'true') return;

    // Read the main row's start value
    const s = document.getElementById(`time${rowKey}_1`);
    if (!s || !s.value) return;

    const hourStartStr = hourStart(hour);
    const start24      = to24h(s.value);
    const headEnd24    = subtractOneMinute(start24);

    // ---- Case 1: main row starts AT the hour boundary ----
    if (start24 === hourStartStr) {
        const $existingHead = getHeadGapRow(hour);

        if ($existingHead.length) {
            const $headChk      = $existingHead.find('.tchk');
            const isHeadChecked = $headChk.is(':checked') ||
                                  $existingHead.attr('data-locked-group') !== undefined;

            if (isHeadChecked) {
                // ⭐ Checked head gap = user-claimed time. Preserve it.
                //    Shift the main row's start to just after the gap's end.
                const headKey = $existingHead.find('.tchk').attr('id');
                const he = document.getElementById(`time${headKey}_2`);

                if (he && he.value) {
                    const newStart24 = addOneMinute(to24h(he.value));
                    const newStart12 = formatTo12h(newStart24);

                    s.value = newStart12;
                    if (s._flatpickr) {
                        s._flatpickr.set('minTime', newStart12);
                        s._flatpickr.set('maxTime', hourEnd(hour));
                        s._flatpickr.setDate(newStart12, false);
                    }

                    // Keep the main row's end >= its new start
                    const e = document.getElementById(`time${rowKey}_2`);
                    if (e) {
                        if (to24h(e.value) <= newStart24) {
                            const defaultEnd = hourEnd(hour);
                            e.value = defaultEnd;
                            if (e._flatpickr) e._flatpickr.setDate(defaultEnd, false);
                        }
                        if (e._flatpickr) e._flatpickr.set('minTime', newStart24);
                    }
                }
                return;
            }
        }

        // No checked head gap → safe to remove any stale unchecked gap
        removeHeadGap(hour);
        return;
    }

    // ---- Case 2: main row starts LATER than boundary → ensure head gap ----
    const coverageStart = hourStartStr;
    const coverageEnd   = headEnd24;

    if (coverageStart > coverageEnd) {
        removeHeadGap(hour);
        return;
    }

    // Does any OTHER row in this hour already cover the head region?
    let overlappedByOther = false;

    $('#hoursTableBody tr').has('.tchk').each(function () {
        const $r = $(this);
        if ($r[0] === $row[0]) return;

        const p = parseRowKey($r.find('.tchk').attr('id'));
        if (!p || p.hour !== hour) return;
        if (!$r.find('.tchk').is(':checked') && !$r.attr('data-locked-group')) return;

        // Gap rows don't count as coverage
        if ($r.attr('data-gap') === 'true') return;

        const otherKey = $r.find('.tchk').attr('id');
        const rs = document.getElementById(`time${otherKey}_1`);
        const re = document.getElementById(`time${otherKey}_2`);
        if (!rs || !re || !rs.value || !re.value) return;

        const otherStart = to24h(rs.value);
        const otherEnd   = to24h(re.value);

        // Overlap test: otherStart <= coverageEnd && otherEnd >= coverageStart
        if (otherStart <= coverageEnd && otherEnd >= coverageStart) {
            overlappedByOther = true;
            return false;   // break
        }
    });

    if (overlappedByOther) {
        removeHeadGap(hour);
        return;
    }

    // ---- Create or update the head gap ----
    let $headGap = getHeadGapRow(hour);

    if ($headGap.length) {
        // Update its end time in place
        const gapKey = $headGap.find('.tchk').attr('id');
        const he = document.getElementById(`time${gapKey}_2`);
        if (he) {
            const newEnd12 = formatTo12h(headEnd24);
            he.value = newEnd12;
            if (he._flatpickr) he._flatpickr.setDate(newEnd12, false);
        }
        $headGap.attr('data-sort-start', timeToMinutes(formatTo12h(coverageStart)));
    } else {
        // Insert a new head gap before the main row
        const $mainRow = getMainRowForHour(hour);
        if (!$mainRow.length) return;

        // Find the next free sub-index
        let nextSubIndex = 1;
        while ($('#' + getRowKey(hour, nextSubIndex)).length > 0) {
            nextSubIndex++;
            if (nextSubIndex > 10) break;   // safety
        }

        const gapKey = getRowKey(hour, nextSubIndex);

        insertSubRow(
            $mainRow,
            gapKey,
            hour,
            formatTo12h(coverageStart),
            formatTo12h(coverageEnd)
        );

        // Move the new gap BEFORE the main row so it appears first
        const $newGap = $('#' + gapKey).closest('tr');
        $newGap.insertBefore($mainRow);

        $newGap.attr('data-gap', 'true');
        $newGap.attr('data-gap-role', 'head');
        $newGap.attr('data-sort-start', timeToMinutes(formatTo12h(coverageStart)));
    }

    sortAllRowsByStartTime();
    mergeLeaveColumnsForTimeOff();     // ⭐ ADD THIS
    updateDynamicInputsForTimeOff();   // ⭐ And this, to fill in the cells
}

/** The main (unlettered) row for a given hour, or an empty jQuery set. */
function getMainRowForHour(hour) {
    let $main = $();
    $('#hoursTableBody tr').has('.tchk').each(function () {
        if ($(this).find('.tchk').attr('id') === String(hour)) {
            $main = $(this);
            return false;
        }
    });
    return $main;
}

/** The head-gap row for an hour, or an empty jQuery set. */
function getHeadGapRow(hour) {
    let $found = $();
    $('#hoursTableBody tr').each(function () {
        const $r = $(this);
        if ($r.attr('data-sub') !== 'true') return;    // ⭐ main rows can't be gaps
        if ($r.attr('data-gap') !== 'true') return;
        if ($r.attr('data-gap-role') !== 'head') return;
        const k = $r.find('.tchk').attr('id');
        const p = parseRowKey(k);
        if (p && p.hour === hour) {
            $found = $r;
            return false;
        }
    });
    return $found;
}

/**
 * Remove the head-gap row for an hour.
 *
 * By default, refuses to remove a head gap that is CHECKED or LOCKED —
 * those represent time the user explicitly claimed.
 *
 * Pass `{ force: true }` to remove regardless (used during modal reset,
 * group deletion, or other cleanup paths).
 */
function removeHeadGap(hour, options) {
    options = options || {};
    const $gap = getHeadGapRow(hour);
    if (!$gap.length) return;

    // ⭐ Never remove a main row
    if ($gap.attr('data-sub') !== 'true') return;

    if (!options.force) {
        const $chk = $gap.find('.tchk');
        const isProtected = $chk.is(':checked') ||
                            $gap.attr('data-locked-group') !== undefined;
        if (isProtected) return;
    }

    const key = $gap.find('.tchk').attr('id');
    destroyFlatpickrFor(key);
    $gap.remove();
}

/**
 * After editing a row's end, align the NEXT row's start so it sits
 * immediately after this row (end + 1 minute).
 *
 * - If the next row's start is EARLIER than expected → shift forward
 * - If the next row's start is LATER than expected → shift backward
 *
 * Only applies when:
 *   - the next row is in the same hour
 *   - the next row is NOT a gap
 *   - the next row is NOT locked
 *   - the next row is checked (a real, claimed row)
 *     OR the next row is unchecked with non-default times
 *       (i.e. it was already placed by a previous cascade)
 */
function cascadeNextRowStart(rowKey) {
    const $row = $('#' + rowKey).closest('tr');
    if (!$row.length) return;

    const parsed = parseRowKey(rowKey);
    if (!parsed) return;

    const e = document.getElementById(`time${rowKey}_2`);
    if (!e || !e.value) return;

    const thisEnd24 = to24h(e.value);
    const expectedStart24 = addOneMinute(thisEnd24);

    // Find the immediate next row
    let $next = $row.next();
    if (!$next.length) return;

    const nk = $next.find('.tchk').attr('id');
    const np = nk ? parseRowKey(nk) : null;
    if (!np || np.hour !== parsed.hour) return;

    // Skip gaps
    if ($next.attr('data-gap') === 'true') return;

    // Skip locked rows
    if ($next.attr('data-locked-group') !== undefined) return;

    const ns = document.getElementById(`time${nk}_1`);
    const ne = document.getElementById(`time${nk}_2`);
    if (!ns || !ns.value) return;

    const nextStart24 = to24h(ns.value);
    if (nextStart24 === expectedStart24) return;   // already aligned

    // ⭐ Shift the next row's start to align with this row's end + 1
    ns.value = formatTo12h(expectedStart24);
    if (ns._flatpickr) {
        ns._flatpickr.set('minTime', expectedStart24); 
        ns._flatpickr.set('maxTime', hourEnd(parsed.hour));
        ns._flatpickr.setDate(formatTo12h(expectedStart24), false);
    }

    // Keep the next row's end valid (>= its new start)
    if (ne && ne.value) {
        const currentNextEnd24 = to24h(ne.value);
        if (currentNextEnd24 < expectedStart24) {
            const newEnd = hourEnd(np.hour);
            ne.value = formatTo12h(newEnd);
            if (ne._flatpickr) ne._flatpickr.setDate(formatTo12h(newEnd), false);
        }
        if (ne._flatpickr) {
            ne._flatpickr.set('minTime', expectedStart24);
        }
    }
}

/**
 * Compute the minimum allowed start time for a row's start input.
 * It must be at least (previous row's end + 1) when the previous row
 * in the same hour is active (checked or locked).
 *
 * Returns a 24h time string (e.g. "14:21").
 */
function getMinStartTimeForRow(rowKey) {
    const parsed = parseRowKey(rowKey);
    if (!parsed) return hourStart(parsed ? parsed.hour : 0);

    const hour = parsed.hour;
    const hourStartStr = hourStart(hour);

    const $row = $('#' + rowKey).closest('tr');
    if (!$row.length) return hourStartStr;

    // Find the PREVIOUS row in the same hour (walk backward)
    let $prev = $row.prev();
    let prevEnd24 = null;

    while ($prev.length) {
        const pk = $prev.find('.tchk').attr('id');
        const pp = pk ? parseRowKey(pk) : null;
        if (!pp || pp.hour !== hour) break;

        // Skip gaps — they don't constrain us
        if ($prev.attr('data-gap') === 'true') {
            $prev = $prev.prev();
            continue;
        }

        // Skip inactive rows (unchecked, unlocked)
        const isActive = $prev.find('.tchk').is(':checked') ||
                         $prev.attr('data-locked-group') !== undefined;
        if (!isActive) {
            $prev = $prev.prev();
            continue;
        }

        const pe = document.getElementById(`time${pk}_2`);
        if (pe && pe.value) {
            prevEnd24 = to24h(pe.value);
        }
        break;
    }

    if (!prevEnd24) return hourStartStr;

    const minStart = addOneMinute(prevEnd24);

    // Don't allow a min smaller than the hour start
    return minStart < hourStartStr ? hourStartStr : minStart;
}
