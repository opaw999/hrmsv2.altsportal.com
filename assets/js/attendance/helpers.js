/* ==========================================================================
   attendance/helpers.js
   Pure helper functions (no DOM, no state). Depends on: config.js

   Time formats used throughout:
     "24h"   → "13:05"            (HH:mm)
     "12h"   → "1:05 PM"          (what flatpickr displays)
     "DB"    → "13:05:00"         (HH:mm:ss from the API)
   ========================================================================== */


/* ---------------------------------------------------------------------------
   Hour boundaries
   --------------------------------------------------------------------------- */

/** "08:00" — first minute of an hour row. */
function hourStart(hour) {
    return String(hour).padStart(2, '0') + ':00';
}

/** "08:59" — last minute of an hour row (the last row ends at "19:00"). */
function hourEnd(hour) {
    return hour === LAST_HOUR ? '19:00' : String(hour).padStart(2, '0') + ':59';
}


/* ---------------------------------------------------------------------------
   Time conversion
   --------------------------------------------------------------------------- */

/** DB time "13:05:00" → "1:05 PM". Returns null for empty / "00:00:00". */
function to12h(t) {
    if (!t || t === '00:00:00') return null;
    var parts = t.split(':');
    var hour  = parseInt(parts[0], 10);
    var ampm  = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12 || 12;
    return hour + ':' + parts[1] + ' ' + ampm;
}

/** 24h "13:05" → "1:05 PM". Returns '' for empty input. */
function formatTo12h(t24) {
    if (!t24) return '';
    var parts = t24.split(':');
    var hour  = parseInt(parts[0], 10);
    var ampm  = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12 || 12;
    return hour + ':' + parts[1] + ' ' + ampm;
}

/** "1:05 PM" → "13:05". */
function to24h(t12) {
    var pieces   = t12.split(' ');
    var time     = pieces[0];
    var modifier = pieces[1];
    var hm = time.split(':');
    var h  = hm[0];
    var m  = hm[1];
    if (h === '12') h = '00';
    if (modifier === 'PM') h = parseInt(h, 10) + 12;
    return String(h).padStart(2, '0') + ':' + m;
}

/** DB time "11:00:00" → "11:00". */
function to24hFromDB(t) {
    if (!t) return '';
    return t.substr(0, 5);
}

/** "8:30 AM" → minutes from midnight (510). */
function timeToMinutes(time12h) {
    if (!time12h) return 0;
    var pieces   = time12h.split(' ');
    var modifier = pieces[1];
    var hm = pieces[0].split(':').map(Number);
    var h  = hm[0];
    var m  = hm[1];
    if (modifier && modifier.toUpperCase() === 'PM' && h < 12) h += 12;
    if (modifier && modifier.toUpperCase() === 'AM' && h === 12) h = 0;
    return h * 60 + m;
}

/** Hour (0–23) of a 12h string like "1:05 PM" → 13. */
function hourFrom12h(s) {
    if (!s) return 0;
    var pieces   = s.split(' ');
    var modifier = pieces[1] || '';
    var h = parseInt(pieces[0].split(':')[0], 10);
    if (modifier.toUpperCase() === 'PM' && h < 12) h += 12;
    if (modifier.toUpperCase() === 'AM' && h === 12) h = 0;
    return h;
}

/** "13:05" + 1 minute → "13:06" (rolls over the hour). */
function addOneMinute(t) {
    var hm = t.split(':').map(Number);
    var h  = hm[0];
    var m  = hm[1] + 1;
    if (m >= 60) { m = 0; h++; }
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

/** "13:00" − 1 minute → "12:59" (never below 00:xx). */
function subtractOneMinute(t) {
    var hm = t.split(':').map(Number);
    var h  = hm[0];
    var m  = hm[1] - 1;
    if (m < 0) { m = 59; h--; }
    if (h < 0) h = 0;
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

/** "8:00 AM" → a Date set to that time today (or null). */
function parseTimeString(timeStr) {
    if (!timeStr) return null;
    var today    = new Date();
    var pieces   = timeStr.split(' ');
    var modifier = pieces[1] || '';
    var hm = pieces[0].split(':').map(Number);
    var h  = hm[0];
    var m  = hm[1];
    if (modifier.toUpperCase() === 'PM' && h < 12) h += 12;
    if (modifier.toUpperCase() === 'AM' && h === 12) h = 0;
    today.setHours(h, m, 0, 0);
    return today;
}

/**
 * Hours between two 12h strings, rounded to 2 decimals.
 * An end time of XX:59 counts as XX:59:59 so a full row (8:00 → 8:59) is 1.00.
 */
function calculateHoursBetween(start, end) {
    if (!start || !end) return 0;
    var s = parseTimeString(start);
    var e = parseTimeString(end);
    if (!s || !e) return 0;
    if (e.getMinutes() === 59 && e.getSeconds() === 0) e.setSeconds(59);
    return Math.round(((e - s) / 3600000) * 100) / 100;
}


/* ---------------------------------------------------------------------------
   Date formatting
   --------------------------------------------------------------------------- */

/** Date → "2026-10-01" (local time, no UTC shift). */
function toDateStr(d) {
    var y   = d.getFullYear();
    var m   = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
}

/** "2026-10-01" → "Oct 1, 2026". */
function formatDisplayDate(dateStr) {
    return new Date(dateStr + 'T00:00:00')
        .toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/** "2026-10-01" → "Oct 1". */
function formatShortDate(dateStr) {
    return new Date(dateStr + 'T00:00:00')
        .toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

/** "2026-10-01 13:05:00" → "Oct 1, 2026 01:05 PM" (or "—"). */
function formatDateTimeDisplay(dt) {
    if (!dt) return '—';
    var d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
}


/* ---------------------------------------------------------------------------
   Files
   --------------------------------------------------------------------------- */

/** 1536 → "1.5 KB". */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    var k     = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB'];
    var i     = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/** Bootstrap-icons class (+ color) for a file type. */
function getFileIcon(type, name) {
    if (type.startsWith('image/'))      return 'bi-file-earmark-image text-info';
    if (type === 'application/pdf')     return 'bi-file-earmark-pdf text-danger';
    if (type.includes('word')  || name.endsWith('.doc') || name.endsWith('.docx')) return 'bi-file-earmark-word text-primary';
    if (type.includes('excel') || name.endsWith('.xls') || name.endsWith('.xlsx')) return 'bi-file-earmark-excel text-success';
    return 'bi-file-earmark text-secondary';
}


/* ---------------------------------------------------------------------------
   Row keys
   Hour 8 → "8"; its sub-rows → "8a", "8b", ...
   --------------------------------------------------------------------------- */

function getRowKey(hour, subIndex) {
    return subIndex === 0 ? String(hour) : hour + String.fromCharCode(96 + subIndex);
}

/** "8b" → { hour: 8, subIndex: 2 }; returns null if malformed. */
function parseRowKey(key) {
    var m = String(key).match(/^(\d+)([a-z]?)$/);
    if (!m) return null;
    return {
        hour:     parseInt(m[1], 10),
        subIndex: m[2] ? m[2].charCodeAt(0) - 96 : 0
    };
}


/* ---------------------------------------------------------------------------
   Time intervals ({ start: "HH:mm", end: "HH:mm" })
   --------------------------------------------------------------------------- */

/** Merge overlapping or adjacent (1-minute gap) intervals. */
function mergeIntervals(intervals) {
    if (!intervals.length) return [];

    var sorted = intervals.slice().sort(function (a, b) { return a.start.localeCompare(b.start); });
    var merged = [sorted[0]];

    for (var i = 1; i < sorted.length; i++) {
        var last = merged[merged.length - 1];
        var curr = sorted[i];

        if (curr.start <= addOneMinute(last.end)) {
            if (curr.end > last.end) last.end = curr.end;
        } else {
            merged.push({ start: curr.start, end: curr.end });
        }
    }
    return merged;
}

/** Free gaps inside [rangeStart, rangeEnd] not covered by the (sorted, merged) intervals. */
function findGaps(intervals, rangeStart, rangeEnd) {
    var gaps   = [];
    var cursor = rangeStart;

    intervals.forEach(function (iv) {
        if (iv.start > cursor) {
            gaps.push({ start: cursor, end: subtractOneMinute(iv.start) });
        }
        cursor = iv.end > cursor ? addOneMinute(iv.end) : cursor;
    });

    if (cursor <= rangeEnd) {
        gaps.push({ start: cursor, end: rangeEnd });
    }
    return gaps;
}


/* ---------------------------------------------------------------------------
   Status & leave labels
   --------------------------------------------------------------------------- */

function isApprovedStatus(s) {
    return String(s || '').toLowerCase() === 'approved';
}

/** Group status from its rows: any pending/empty → 'pending'; all approved → 'approved'. */
function resolveGroupStatus(rows) {
    var anyPending = rows.some(function (r) {
        var s = String(r.status || '').toLowerCase();
        return s === 'pending' || s === '';
    });
    if (anyPending) return 'pending';
    return rows.every(function (r) { return isApprovedStatus(r.status); }) ? 'approved' : 'pending';
}

/** 'sick' → 'M/SL', unknown → 'Leave'. */
function leaveShortCode(type) {
    return LEAVE_CODE_MAP[String(type || '').toLowerCase()] || 'Leave';
}

/* ---------------------------------------------------------------------------
   Modal-specific status checks
   --------------------------------------------------------------------------- */

/** Approved out-of-office groups are frozen — cannot be unlocked, deleted, or edited. */
function isLockedApprovedGroup($row) {
    var type   = String($row.attr('data-type') || '').toLowerCase();
    var status = String($row.attr('data-status') || '').toLowerCase();
    return type === 'outoffice' && status === 'approved';
}

/* ---------------------------------------------------------------------------
   Modal-specific helpers
   --------------------------------------------------------------------------- */

/**
 * Find the group's first row (walking up from a sub-row if needed) and
 * mark it with `data-group-dirty="true"`.
 */
function markGroupDirty($row) {
    var groupId = $row.attr('data-saved-group') || $row.attr('data-locked-group');
    if (!groupId) return;

    var $first = $row;
    while (
        $first.prev('tr').length &&
        (
            $first.prev('tr').attr('data-saved-group') === groupId ||
            $first.prev('tr').attr('data-locked-group') === groupId
        )
    ) {
        $first = $first.prev('tr');
    }

    $first.attr('data-group-dirty', 'true');
}

/**
 * Add a small "Has Entry" chip to a row that already displays an
 * in-office / out-of-office group. Used when a biometric punch overlaps
 * with a manually-filed group — the biometric merges into the same row
 * instead of creating a duplicate.
 *
 * @param {jQuery} $row           The row to annotate.
 * @param {string} biometricRange "10:36 AM to 1:01 PM" — for the tooltip.
 */
function annotateRowWithHasEntry($row, biometricRange) {
    const $typeTd = $row.children('td').eq(3);
    if ($typeTd.find('.has-entry-chip').length) return;

    const tooltip = biometricRange
        ? 'Biometric punch also covers this hour: ' + biometricRange
        : 'Biometric punch also covers this hour';

    $typeTd.append(
        ' <span class="has-entry-chip" title="' + tooltip + '">' +
        '<i class="bi bi-fingerprint me-1"></i>Has Entry</span>'
    );
}

/**
 * Post-render pass: walk every spanning (biometric) group and, for each
 * hour it touches, if that hour's row is already an in-office / out-of-
 * office group, add a "Has Entry" chip to it.
 *
 * Runs AFTER all groups have rendered and been sorted, so it never gets
 * clobbered by a later re-render.
 */
function annotateOverlappingBiometrics(record) {
    if (!record.groups) return;

    record.groups.forEach(g => {
        if (!g.isSpanning) return;

        const entry = g.rawEntries && g.rawEntries[0];
        if (!entry || !entry.startTime || !entry.endTime) return;

        const startH = hourFrom12h(entry.startTime);
        const endH   = hourFrom12h(entry.endTime);

        for (let h = startH; h <= endH; h++) {
            const $row = $('#' + h).closest('tr');
            if (!$row.length) continue;

            const rowType = String($row.attr('data-type') || '').toLowerCase();
            if (rowType !== 'inoffice' && rowType !== 'outoffice') continue;

            const rowGroupId = String($row.attr('data-group-id') || '');
            if (!rowGroupId) continue;
            if (rowGroupId === String(g.id)) continue;

            annotateRowWithHasEntry($row, entry.startTime + ' to ' + entry.endTime);
        }
    });
}

/**
 * On load, rebuild head/tail gap sub-rows for every hour that has active
 * (checked/locked) rows but doesn't fully cover the hour.
 *
 * - If the earliest active row starts after the hour boundary → head gap
 * - If the latest active row ends before the hour boundary → tail gap
 *
 * Existing gaps (head or tail) are respected and updated in place.
 * Fully-covered hours (edge-to-edge) get no gaps.
 * Hours with no active rows get no gaps.
 */
function rebuildMissingHourGaps() {
    HOURS.forEach(hour => {
        const hourStartStr = hourStart(hour);
        const hourEndStr   = hourEnd(hour);

        const $rowsInHour = $('#hoursTableBody tr').has('.tchk').filter(function () {
            const p = parseRowKey($(this).find('.tchk').attr('id'));
            return p && p.hour === hour;
        });

        const $active = $rowsInHour.filter(function () {
            const $r = $(this);
            if ($r.attr('data-gap') === 'true') return false;
            return $r.find('.tchk').is(':checked') || $r.attr('data-locked-group');
        });

        // No active rows → remove any stray gaps and return
        if (!$active.length) {
            removeAllGapsForHour(hour);
            return;
        }

        // Collect merged coverage
        const ranges = [];
        $active.each(function () {
            const k = $(this).find('.tchk').attr('id');
            const s = document.getElementById(`time${k}_1`);
            const e = document.getElementById(`time${k}_2`);
            if (!s || !e || !s.value || !e.value) return;
            ranges.push({ start: to24h(s.value), end: to24h(e.value) });
        });
        if (!ranges.length) return;

        const merged = mergeIntervals(ranges);
        const gaps   = findGaps(merged, hourStartStr, hourEndStr);

        // If the whole hour is one giant gap, there's nothing to rebuild
        if (gaps.length === 1 &&
            gaps[0].start === hourStartStr &&
            gaps[0].end   === hourEndStr) {
            return;
        }

        rebuildGapsForHour(hour, gaps);
    });
}

function rebuildGapsForHour(hour, requiredGaps) {
    // Existing gap rows for this hour
    const $existingGaps = $('#hoursTableBody tr').has('.tchk').filter(function () {
        const $r = $(this);
        if ($r.attr('data-gap') !== 'true') return false;
        const p = parseRowKey($r.find('.tchk').attr('id'));
        return p && p.hour === hour;
    });

    // Protected gaps (checked/locked) — never touched
    const $protectedGaps = $existingGaps.filter(function () {
        const $r = $(this);
        return $r.find('.tchk').is(':checked') || $r.attr('data-locked-group');
    });

    // Working set: unprotected gaps we can reuse or discard
    const $reusableGaps = $existingGaps.not($protectedGaps);

    // Track which reusable gaps were claimed by a required gap
    const claimed = new Set();

    // ⭐ Declare $mainRow once, reuse throughout
    const $mainRow = getMainRowForHour(hour);

    // For each required gap, try to match it with an existing reusable gap
    requiredGaps.forEach(gap => {
        // Skip if a protected gap already covers this region
        let coveredByProtected = false;
        $protectedGaps.each(function () {
            const k = $(this).find('.tchk').attr('id');
            const gs = document.getElementById(`time${k}_1`);
            const ge = document.getElementById(`time${k}_2`);
            if (!gs || !ge) return;
            const ps = to24h(gs.value);
            const pe = to24h(ge.value);
            if (ps <= gap.start && pe >= gap.end) coveredByProtected = true;
        });
        if (coveredByProtected) return;

        // Find a reusable gap with matching times
        let $match = $();
        $reusableGaps.each(function () {
            const $r = $(this);
            if (claimed.has($r[0])) return;
            const k = $r.find('.tchk').attr('id');
            const gs = document.getElementById(`time${k}_1`);
            const ge = document.getElementById(`time${k}_2`);
            if (!gs || !ge) return;
            if (to24h(gs.value) === gap.start && to24h(ge.value) === gap.end) {
                $match = $r;
                return false;
            }
        });

        if ($match.length) {
            claimed.add($match[0]);
            return;
        }

        // ⭐ Try to use the main row as this gap when it's inactive
        if ($mainRow.length &&
            $mainRow.attr('data-gap') !== 'true' &&
            !$mainRow.find('.tchk').is(':checked') &&
            !$mainRow.attr('data-locked-group')) {

            const mainKey = $mainRow.find('.tchk').attr('id');
            const ms = document.getElementById(`time${mainKey}_1`);
            const me = document.getElementById(`time${mainKey}_2`);

            if (ms && me && ms.value && me.value) {
                const mStart = to24h(ms.value);
                const mEnd   = to24h(me.value);

                // Main row's current range must fully contain the gap
                if (mStart <= gap.start && mEnd >= gap.end) {
                    const start12 = formatTo12h(gap.start);
                    const end12   = formatTo12h(gap.end);

                    ms.value = start12;
                    if (ms._flatpickr) ms._flatpickr.setDate(start12, false);

                    me.value = end12;
                    if (me._flatpickr) me._flatpickr.setDate(end12, false);

                    $mainRow.attr('data-gap', 'true');
                    $mainRow.attr('data-gap-role',
                        gap.start === hourStart(hour) ? 'head' :
                        gap.end   === hourEnd(hour)   ? 'tail' : 'middle'
                    );
                    $mainRow.attr('data-sort-start', timeToMinutes(start12));
                    return;   // main row was used, no sub-row needed
                }
            }
        }

        // No match anywhere → create a new sub-row for the gap
        if (!$mainRow.length) return;

        let nextSubIndex = 1;
        while ($('#' + getRowKey(hour, nextSubIndex)).length > 0) {
            nextSubIndex++;
            if (nextSubIndex > 10) return;
        }

        const start12 = formatTo12h(gap.start);
        const end12   = formatTo12h(gap.end);
        const gapKey  = getRowKey(hour, nextSubIndex);
        insertSubRow($mainRow, gapKey, hour, start12, end12);

        const $newGap = $('#' + gapKey).closest('tr');
        $newGap.attr('data-gap', 'true');
        $newGap.attr('data-gap-role',
            gap.start === hourStart(hour) ? 'head' :
            gap.end   === hourEnd(hour)   ? 'tail' : 'middle'
        );
        $newGap.attr('data-sort-start', timeToMinutes(start12));

        claimed.add($newGap[0]);
    });

    // Remove any reusable gaps that weren't claimed
    $reusableGaps.each(function () {
        if (claimed.has(this)) return;
        const k = $(this).find('.tchk').attr('id');
        destroyFlatpickrFor(k);
        $(this).remove();
    });

    sortAllRowsByStartTime();
}

function getTailGapRow(hour) {
    let $found = $();
    $('#hoursTableBody tr').each(function () {
        const $r = $(this);
        if ($r.attr('data-sub') !== 'true') return;    // ⭐
        if ($r.attr('data-gap') !== 'true') return;
        if ($r.attr('data-gap-role') !== 'tail') return;
        const k = $r.find('.tchk').attr('id');
        const p = parseRowKey(k);
        if (p && p.hour === hour) {
            $found = $r;
            return false;
        }
    });
    return $found;
}

function removeAllGapsForHour(hour) {
    $('#hoursTableBody tr').has('.tchk').each(function () {
        const $r = $(this);
        if ($r.attr('data-gap') !== 'true') return;
        const p = parseRowKey($r.find('.tchk').attr('id'));
        if (!p || p.hour !== hour) return;
        if ($r.find('.tchk').is(':checked') || $r.attr('data-locked-group')) return;
        const k = $r.find('.tchk').attr('id');
        destroyFlatpickrFor(k);
        $r.remove();
    });
}

/**
 * Compute the maximum allowed end time for a row's end input.
 *
 * Rules:
 *   - Never exceed the hour boundary (`hourEnd`)
 *   - Never exceed (next active non-gap row's start − 1 minute)
 *   - Always at least (this row's start + 1 minute)
 *
 * @param {string} rowKey  The row whose end-input we're capping
 * @returns {string}       24h time string (e.g. "08:22")
 */
function getMaxEndTimeForRow(rowKey) {
    const parsed = parseRowKey(rowKey);
    if (!parsed) return '23:59';

    const hour = parsed.hour;
    const hourEndStr = hourEnd(hour);

    const $row = $('#' + rowKey).closest('tr');
    if (!$row.length) return hourEndStr;

    // The end must be > start
    const s = document.getElementById(`time${rowKey}_1`);
    const start24 = (s && s.value) ? to24h(s.value) : hourStart(hour);
    const minEnd24 = addOneMinute(start24);

    // Find the next active (checked or locked) non-gap row after this one
    let nextStart24 = null;
    const $allRows = $('#hoursTableBody tr').has('.tchk');
    const thisIdx = $allRows.index($row);

    if (thisIdx >= 0) {
        $allRows.slice(thisIdx + 1).each(function () {
            const $r = $(this);
            const rk = $r.find('.tchk').attr('id');
            const p = parseRowKey(rk);
            if (!p) return;

            const isActive = $r.find('.tchk').is(':checked') ||
                             $r.attr('data-locked-group') !== undefined;
            if (!isActive) return;

            // Gaps don't constrain the previous row's end — they're derived
            if ($r.attr('data-gap') === 'true') return;

            const rs = document.getElementById(`time${rk}_1`);
            if (!rs || !rs.value) return;

            nextStart24 = to24h(rs.value);
            return false;   // stop at first match
        });
    }

    let maxEnd = hourEndStr;

    if (nextStart24) {
        const cap = subtractOneMinute(nextStart24);
        if (cap < maxEnd) maxEnd = cap;
    }

    if (maxEnd < minEnd24) maxEnd = minEnd24;

    return maxEnd;
}

/**
 * Clamp a 12h end-time string to `getMaxEndTimeForRow(rowKey)`.
 * Returns the clamped 12h string.
 */
function clampEndTime(rowKey, end12) {
    if (!end12) return end12;

    const parsed = parseRowKey(rowKey);
    if (!parsed) return end12;

    const hour = parsed.hour;

    const s = document.getElementById(`time${rowKey}_1`);
    const start24 = (s && s.value) ? to24h(s.value) : hourStart(hour);
    const minEnd24 = addOneMinute(start24);

    const max24 = getMaxEndTimeForRow(rowKey);
    const end24 = to24h(end12);

    // ⭐ Enforce lower bound (end must be > start)
    if (end24 < minEnd24) return formatTo12h(minEnd24);

    // Enforce upper bound
    if (end24 > max24) return formatTo12h(max24);

    return end12;
}

/**
 * Format a 12h time string for display in the summary.
 * Rounds any time ending in :59 up to the next hour's :00.
 *
 *   "11:59 AM" → "12:00 PM"
 *   "5:59 PM"  → "6:00 PM"
 *   "10:20 AM" → "10:20 AM"  (unchanged)
 */
function formatSummaryTime(time12) {
    if (!time12 || time12 === '-') return time12;

    // Match "H:MM AM/PM" or "HH:MM AM/PM"
    const m = time12.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (!m) return time12;

    let hour   = parseInt(m[1], 10);
    const min  = parseInt(m[2], 10);
    let period = m[3].toUpperCase();

    if (min !== 59) return time12;   // only :59 rolls up

    // Roll up one hour
    hour += 1;

    // Handle AM/PM boundary
    if (hour === 12 && period === 'AM') {
        period = 'PM';               // 11:59 AM → 12:00 PM
    } else if (hour === 13) {
        hour = 1;                    // 12:59 PM → 1:00 PM
    }

    return hour + ':00 ' + period;
}

/**
 * Round any :59 in a "X to Y" time range string to :00 of the next hour.
 * Only affects the string we send to the backend — the DB's time_entries
 * keep their exact :59 values.
 */
function roundTimeRangeForSave(rangeText) {
    if (!rangeText) return rangeText;

    return rangeText.replace(/(\d{1,2}):(\d{2})\s*(AM|PM)/gi, (match, hh, mm, ap) => {
        if (mm !== '59') return match;

        let h = parseInt(hh, 10);
        let period = ap.toUpperCase();

        h += 1;
        if (h === 12 && period === 'AM') period = 'PM';
        else if (h === 13) { h = 1; period = 'PM'; }

        return `${h}:00 ${period}`;
    });
}