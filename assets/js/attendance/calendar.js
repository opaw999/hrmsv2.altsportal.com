/* ==========================================================================
   attendance/calendar.js
   FullCalendar setup, event builders, weekly-totals column, info popups.

   Depends on: config.js, helpers.js, alerts.js, jQuery, FullCalendar
   Calls into (defined elsewhere, resolved at call time):
     day-off.js    → isDateOff, openDayoffModal
     modal-load.js → handleDateClick

   Sections
     1. Setup & data loading
     2. Event handlers (click / render)
     3. Event builders
     4. Info popups (leave, holiday)
     5. Weekly totals column
   ========================================================================== */


/* ==========================================================================
   1. SETUP & DATA LOADING
   ========================================================================== */

function initializeCalendar() {
    const el = document.getElementById('calendar');
    if (!el) return;

    state.calendar = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: { left: 'prev,next today', center: '', right: 'title' },
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        eventOrder: 'order,title',
        eventOrderStrict: true,

        events:       loadCalendarEvents,
        eventContent: renderEventContent,
        dateClick:    (info) => handleDateClick(info.dateStr),
        eventClick:   handleEventClick,
        datesSet:     handleDatesSet
    });

    state.calendar.render();
    watchCalendarBody();
    setTimeout(() => {
        injectWeeklyTotalsColumn();
        renderWeeklyTotals();
    }, 100);
}

function refreshCalendarEvents() {
    if (state.calendar) state.calendar.refetchEvents();

    setTimeout(() => {
        if (isMonthView()) {
            watchCalendarBody();
            injectWeeklyTotalsColumn();
            renderWeeklyTotals();
        }
    }, 200);
}

function isMonthView() {
    return state.calendar && state.calendar.view && state.calendar.view.type === 'dayGridMonth';
}

function ajaxGet(url) {
    return $.ajax({ url: url, type: 'GET', dataType: 'json' });
}

/**
 * FullCalendar `events` source. Fetches attendance, leaves, holidays and
 * day-off requests in parallel, fills the caches, and returns every event.
 */
function loadCalendarEvents(info, success) {
    $.when(
        ajaxGet(API.events),
        ajaxGet(API.leaves),
        ajaxGet(API.holidays),
        ajaxGet(API.dayoffs)
    ).done((attRes, leaveRes, holRes, doRes) => {
        const attData   = attRes[0];
        const leaveData = leaveRes[0];
        const holData   = holRes[0];
        const doData    = doRes[0];
        const all = [];

        // 1) Attendance — fills dailyTotalsCache + dailyDisplayTotalsCache
        if (attData && attData.success && attData.data) {
            getDailyTotals(attData.data);
            all.push(...buildCalendarEvents(groupEventsByDateAndType(attData.data)));
        } else {
            state.dailyTotalsCache = {};
            state.dailyDisplayTotalsCache = {};
        }

        // 2) Leaves — folded into both caches
        state.leavesCache = {};
        state.leaveHoursByDate = {};
        if (leaveData && leaveData.success && leaveData.data) {
            all.push(...buildLeaveEvents(leaveData.data));
            foldLeavesIntoCaches(leaveData.data);
        }

        // 3) "X hrs." chips — must come AFTER leaves are folded in
        all.push(...buildTotalHoursEvents(state.dailyDisplayTotalsCache));

        // 4) Holidays
        state.holidaysCache = {};
        if (holData && holData.success && holData.data) {
            all.push(...buildHolidayEvents(holData.data));
            holData.data.forEach(h => { state.holidaysCache[h.date] = h; });
        }

        // 5) Day-off requests (pending chips) + default/approved day-off backgrounds
        if (doData && doData.success && doData.data) {
            state.dayoffRequests = doData.data;
            all.push(...buildDayoffRequestEvents());
        } else {
            state.dayoffRequests = [];
        }
        all.push(...getDayOffEvents(info.start, info.end));

        success(all);
        state.allEventsCache = all;

        setTimeout(() => {
            injectWeeklyTotalsColumn();
            renderWeeklyTotals();
            markHolidayCells();
        }, 50);
    }).fail(() => {
        // Network failure: show only the default day-off backgrounds
        const fallback = getDayOffEvents(info.start, info.end);
        state.allEventsCache = fallback;
        state.dailyTotalsCache = {};
        state.dailyDisplayTotalsCache = {};
        state.holidaysCache = {};
        state.leavesCache = {};
        state.leaveHoursByDate = {};
        state.dayoffRequests = [];
        success(fallback);
    });
}

/**
 * Index non-cancelled, non-rejected leaves by date and add their hours to:
 *  - leaveHoursByDate        (approved per-day slice → weekly totals)
 *  - dailyDisplayTotalsCache (all-status per-day slice → "X hrs." chip)
 */
function foldLeavesIntoCaches(leaves) {
    leaves.forEach(l => {
        if (l.status === 'cancelled' || l.status === 'rejected') return;

        const date = l.startDate;

        state.leavesCache[date] = {
            leaveId:       l.leaveId,
            type:          l.type,
            isFixed:       l.isFixed,
            typeLabel:     l.typeLabel,
            hours:         l.totalHours,
            approvedTotal: l.approvedHours,
            payment:       l.payment,
            reason:        l.reason,
            status:        l.status,
            dayIndex:      l.dayIndex,
            totalDays:     l.totalDays,
            isWholeDay:    l.isWholeDay || false
        };

        const approvedHrs = parseFloat(l.totalHours) || 0;
        state.leaveHoursByDate[date] = (state.leaveHoursByDate[date] || 0) + approvedHrs;

        const displayHrs = parseFloat(l.displayHours) || 0;
        state.dailyDisplayTotalsCache[date] = (state.dailyDisplayTotalsCache[date] || 0) + displayHrs;
    });

    // Re-cap and round after folding leaves in
    Object.keys(state.dailyDisplayTotalsCache).forEach(d => {
        if (state.dailyDisplayTotalsCache[d] > MAX_HOURS) state.dailyDisplayTotalsCache[d] = MAX_HOURS;
        state.dailyDisplayTotalsCache[d] = Math.round(state.dailyDisplayTotalsCache[d] * 100) / 100;
    });
}

/** Cap + round every value of a { date: hours } map (mutates and returns it). */
function capDailyHours(totals) {
    Object.keys(totals).forEach(d => {
        if (totals[d] > MAX_HOURS) totals[d] = MAX_HOURS;
        totals[d] = Math.round(totals[d] * 100) / 100;
    });
    return totals;
}

/** Fill the two daily-hours caches from raw attendance rows. */
function getDailyTotals(raw) {
    const approved = {};   // approved only → weekly total
    const display  = {};   // every status  → chip under the day number

    raw.forEach(ev => {
        const hours = parseFloat(ev.totalHours) || 0;
        if (hours <= 0) return;

        display[ev.date] = (display[ev.date] || 0) + hours;
        if (isApprovedStatus(ev.status)) {
            approved[ev.date] = (approved[ev.date] || 0) + hours;
        }
    });

    state.dailyTotalsCache        = capDailyHours(approved);
    state.dailyDisplayTotalsCache = capDailyHours(display);
    return state.dailyTotalsCache;
}


/* ==========================================================================
   2. EVENT HANDLERS
   ========================================================================== */

/** Adds a status icon (⏳ / ✔) to in-office / out-of-office chips. */
function renderEventContent(arg) {
    const status = arg.event.extendedProps.groupStatus;
    if (!status) return;   // other chip types use the default rendering

    let icon = '';
    if (status === 'pending')  icon = '⏳';
    if (status === 'approved') icon = '<i class="bi bi-check-circle-fill"></i>';

    return { html: `${icon} ${arg.event.title}` };
}

function handleEventClick(info) {
    const cls   = info.event.classNames;
    const props = info.event.extendedProps;

    // Pending day-off request chip
    if (cls.includes('dayoff-request-event')) {
        info.jsEvent.preventDefault();
        openDayoffModal(props.oldDate);
        return;
    }

    if (cls.includes('holiday-event')) {
        info.jsEvent.preventDefault();
        showHolidayInfo(props);
        return;
    }

    if (cls.some(c => c.startsWith('leave-'))) {
        info.jsEvent.preventDefault();

        // Prefer the cache (most accurate); fall back to the event's own props
        const dateStr = info.event.startStr;
        if (state.leavesCache[dateStr]) {
            showLeaveInfo(state.leavesCache[dateStr]);
        } else {
            showLeaveInfo({
                type:      props.leaveType,
                typeLabel: props.leaveTypeLabel,
                hours:     props.leaveHours,
                payment:   props.leavePayment,
                reason:    props.leaveReason,
                status:    'approved',
                dayIndex:  props.leaveDayIndex,
                totalDays: props.leaveDays
            });
        }
        return;
    }

    if (cls.includes('dayoff-event')) return;

    if (cls.includes('total-hours-event')) {
        info.jsEvent.preventDefault();
        return;
    }

    // In office / out of office chip → open the attendance modal
    handleDateClick(info.event.startStr);
}

/** Runs after the visible range changes (prev / next / today). */
function handleDatesSet() {
    setTimeout(() => {
        if (!isMonthView()) return;
        injectWeeklyTotalsColumn();
        renderWeeklyTotals();
        watchCalendarBody();
        markHolidayCells();
    }, 150);
}

/** Add .has-holiday / .has-dayoff classes to the day cells. */
function markHolidayCells() {
    $('#calendar .fc-daygrid-day').removeClass('has-holiday has-dayoff');

    $('#calendar .fc-daygrid-day').each(function () {
        const date = $(this).attr('data-date');
        if (!date) return;

        if (state.holidaysCache[date]) $(this).addClass('has-holiday');
        if (isDateOff(date))           $(this).addClass('has-dayoff');
    });
}


/* ==========================================================================
   3. EVENT BUILDERS
   ========================================================================== */

/**
 * Group raw attendance rows by date + display type.
 * Biometric "regular" rows are shown as In Office, so they merge with any
 * in-office group on the same day into ONE chip.
 */
function groupEventsByDateAndType(raw) {
    const grouped = {};

    raw.forEach(ev => {
        const displayType = (ev.type === 'regular') ? 'inoffice' : ev.type;
        const key = ev.date + '_' + displayType;

        if (!grouped[key]) {
            grouped[key] = { date: ev.date, type: displayType, count: 0, timeRanges: [], totalHours: 0, rows: [] };
        }
        const g = grouped[key];
        g.count++;
        if (ev.timeRange) g.timeRanges.push(ev.timeRange);
        g.totalHours += parseFloat(ev.totalHours) || 0;
        g.rows.push(ev);
    });

    Object.values(grouped).forEach(g => {
        g.totalHours = Math.round(g.totalHours * 100) / 100;
        g.status = resolveGroupStatus(g.rows);
    });

    return Object.values(grouped);
}

/** In Office / Out of Office chips ("In Office (8)"). Displayed hours are capped. */
function buildCalendarEvents(grouped) {
    return grouped.map(g => {
        const isOut     = g.type === 'outoffice';
        const className = isOut ? 'out-of-office-event' : 'in-office-event';
        const baseTitle = isOut ? 'Out of Office' : 'In Office';
        const displayHours = Math.min(g.totalHours, MAX_HOURS);

        return {
            title: `${baseTitle} (${displayHours})`,
            start: g.date,
            allDay: true,
            className,
            order: 2,
            extendedProps: {
                totalHours: g.totalHours || 0,   // keep the real (uncapped) value
                groupStatus: g.status
            }
        };
    });
}

/** One chip per leave day ("Leave (4 hrs)"). */
function buildLeaveEvents(leaves) {
    const deductibleClass = {
        sick: 'non-deductible', medical: 'non-deductible', vacation: 'leave-vacation',
        personal: 'non-deductible', valid: 'non-deductible'
    };

    return leaves.map(leave => {
        const payment = (leave.payment || '').toLowerCase();

        let className = 'leave-event ' + (deductibleClass[leave.type] || 'leave-default');
        className += payment === 'paid' ? ' leave-paid' : ' leave-unpaid';
        if (leave.hasPending) className += ' leave-pending';

        // Title with that day's hours
        const titleHours = (leave.displayHours !== undefined && leave.displayHours !== null)
            ? parseFloat(leave.displayHours)
            : parseFloat(leave.totalHours);

        let title = 'Leave';
        if (titleHours && titleHours > 0) {
            title += ` (${titleHours} hr${titleHours !== 1 ? 's' : ''})`;
        }

        const event = {
            title,
            start: leave.startDate,
            allDay: true,
            className,
            order: 4,
            extendedProps: {
                leaveId: leave.leaveId,
                isLeave: true,
                leaveType: leave.type,
                leavePayment: payment,
                leaveReason: leave.reason,
                leaveDays: leave.totalDays,
                leaveHours: leave.totalHours,
                hasPending: leave.hasPending
            }
        };

        // Events are normally single-day; only a real multi-day range needs `end`
        if (leave.startDate !== leave.endDate) {
            const endDate = new Date(leave.endDate + 'T00:00:00');
            endDate.setDate(endDate.getDate() + 1);   // FullCalendar end is exclusive
            event.end = endDate.toISOString().slice(0, 10);
        }

        return event;
    });
}

/** Underlined "8 hrs." text under each day number. */
function buildTotalHoursEvents(dailyTotals) {
    return Object.keys(dailyTotals).map(date => ({
        title: `${parseFloat(Number(dailyTotals[date]).toFixed(2))} hrs.`,
        start: date,
        allDay: true,
        className: 'total-hours-event',
        order: 1,
        extendedProps: { isTotalHours: true, totalHours: dailyTotals[date] }
    }));
}

function buildHolidayEvents(holidays) {
    return holidays.map(h => ({
        title: h.name,
        start: h.date,
        allDay: true,
        className: 'holiday-event holiday-' + (h.type || 'regular'),
        order: 0,   // above everything
        extendedProps: {
            isHoliday: true,
            holidayName: h.name,
            holidayType: h.type,
            holidayDescription: h.description || ''
        }
    }));
}

/** Pending day-off requests, shown on the OLD date. */
function buildDayoffRequestEvents() {
    return state.dayoffRequests
        .filter(req => req.status === 'pending')
        .map(req => ({
            title: `⏳ Day Off → ${formatShortDate(req.new_date)}`,
            start: req.old_date,
            allDay: true,
            className: 'dayoff-request-event dayoff-request-pending',
            order: 0,
            extendedProps: {
                isDayoffRequest: true,
                requestId: req.id,
                oldDate: req.old_date,
                newDate: req.new_date,
                status: req.status,
                remarks: req.remarks
            }
        }));
}

/**
 * "Day Off" background events between startDate and endDate.
 * Default day off is Sunday; the latest APPROVED request per week moves it.
 */
function getDayOffEvents(startDate, endDate) {
    const events = [];

    // Latest approved request per week
    const latestApprovedPerWeek = {};
    state.dayoffRequests.forEach(req => {
        if (req.status !== 'approved') return;
        const weekKey = req.week_start;
        if (!latestApprovedPerWeek[weekKey] || req.id > latestApprovedPerWeek[weekKey].id) {
            latestApprovedPerWeek[weekKey] = req;
        }
    });

    const offDates     = new Set();
    const workingDates = new Set();

    Object.values(latestApprovedPerWeek).forEach(req => {
        offDates.add(req.new_date);
        // Sunday becomes a working day only if the off day moved away from it
        if (req.new_date !== req.week_start) workingDates.add(req.week_start);
    });

    const cur = new Date(startDate);
    const end = new Date(endDate);

    while (cur <= end) {
        const dateStr = toDateStr(cur);

        let isOff = false;
        if (offDates.has(dateStr))          isOff = true;
        else if (workingDates.has(dateStr)) isOff = false;
        else if (cur.getDay() === 0)        isOff = true;

        if (isOff) {
            events.push({
                title: 'Day Off',
                start: dateStr,
                allDay: true,
                className: 'dayoff-event',
                order: 3,
                display: 'background'
            });
        }
        cur.setDate(cur.getDate() + 1);
    }

    return events;
}


/* ==========================================================================
   4. INFO POPUPS
   ========================================================================== */

/** Badge + notice text + Swal icon for a leave status. */
function buildLeaveStatusView(status, extMessage) {
    const notice = (iconClass, text) => `
        <p class="text-muted mt-3 mb-0" style="font-size:0.8rem;">
            <i class="bi ${iconClass} me-1"></i>
            ${text}
            ${extMessage}
        </p>`;

    if (status === 'pending') {
        return {
            badge: '<span class="badge bg-warning text-dark">Pending</span>',
            notice: notice('bi-hourglass-split', 'You have a <strong>pending leave</strong> on this date.'),
            swalIcon: 'warning'
        };
    }
    if (status === 'approved') {
        return {
            badge: '<span class="badge bg-success">Approved</span>',
            notice: notice('bi-check-circle', 'You are on <strong>approved leave</strong> for this date.'),
            swalIcon: 'info'
        };
    }
    if (status === 'disapproved' || status === 'rejected') {
        return {
            badge: '<span class="badge bg-danger">Disapproved</span>',
            notice: notice('bi-x-circle', 'Your leave request for this date has been <strong>disapproved</strong>.'),
            swalIcon: 'error'
        };
    }
    return {
        badge: '<span class="badge bg-secondary">' + status + '</span>',
        notice: notice('bi-info-circle', ''),
        swalIcon: 'info'
    };
}

function showLeaveInfo(leave) {
    const typeLabel = leave.typeLabel || (leave.type
        ? leave.type.charAt(0).toUpperCase() + leave.type.slice(1)
        : 'Leave');

    const status = (leave.status || '').toLowerCase();

    const extMessage = leave.isWholeDay
        ? 'Attendance cannot be filed on this date.'
        : 'Attendance can be filed only for hours not covered by leave.';

    const view = buildLeaveStatusView(status, extMessage);

    const payment = (leave.payment || '').toLowerCase();
    const paymentBadge = payment === 'paid'
        ? '<span class="badge bg-success ms-1">Paid</span>'
        : (payment === 'unpaid' ? '<span class="badge bg-secondary ms-1">Unpaid</span>' : '');

    const dayInfo = (leave.totalDays && leave.totalDays > 1)
        ? `<p class="mb-1"><strong>Day:</strong> ${leave.dayIndex} of ${leave.totalDays}</p>`
        : '';

    const hours = leave.hours
        ? `<p class="mb-1"><strong>Hours:</strong> ${parseFloat(Number(leave.hours).toFixed(2))}</p>`
        : '';

    const reasonHtml = leave.reason
        ? `<p class="text-muted mb-0 mt-2" style="font-size:0.85rem;"><strong>Remarks: </strong>${leave.reason}</p>`
        : '';

    Swal.fire({
        icon: view.swalIcon,
        title: '🌴 On Leave',
        html: `
            <p class="mb-2">${view.badge}${paymentBadge}</p>
            <div class="text-start" style="font-size:0.9rem;">
                <p class="mb-1"><strong>Type:</strong> ${typeLabel}</p>
                ${dayInfo}
                ${hours}
                ${reasonHtml}
            </div>
            ${view.notice}
        `,
        confirmButtonColor: BRAND_COLOR,
        confirmButtonText: 'Got it'
    });
}

/** Accepts either a holiday record or an event's extendedProps. */
function showHolidayInfo(holiday) {
    const name        = holiday.holidayName        || holiday.name        || 'Holiday';
    const type        = holiday.holidayType        || holiday.type        || 'Holiday';
    const description = holiday.holidayDescription || holiday.description || '';
    const typeLabel   = type.charAt(0).toUpperCase() + type.slice(1);

    return Swal.fire({
        icon: 'info',
        title: '🎉 ' + name,
        html: `
            <p class="mb-1"><strong>Type:</strong> ${typeLabel}</p>
            ${description ? `<p class="text-muted mb-0" style="font-size:0.85rem;">${description}</p>` : ''}
            <p class="text-muted mt-3 mb-0" style="font-size:0.8rem;">
                <i class="bi bi-info-circle me-1"></i>Attendance cannot be filed on holidays.
            </p>
        `,
        confirmButtonColor: BRAND_COLOR,
        confirmButtonText: 'Got it'
    });
}


/* ==========================================================================
   5. WEEKLY TOTALS COLUMN
   FullCalendar has no "total" column, so we inject an 8th <th>/<td> into the
   rendered month grid and re-inject whenever FullCalendar re-renders it.
   ========================================================================== */

/** Add the "Total" header + one totals cell per week row (idempotent). */
function injectWeeklyTotalsColumn() {
    if (!isMonthView()) return;

    // 7 day columns + 1 total column, all equal width
    normalizeColgroup('#calendar .fc-col-header colgroup');
    normalizeColgroup('#calendar .fc-daygrid-body colgroup');

    // Header cell
    const $headerRow = $('#calendar .fc-col-header thead tr').first();
    if ($headerRow.length && !$headerRow.find('.weekly-total-th').length) {
        $headerRow.append(`
            <th class="fc-col-header-cell weekly-total-th" role="columnheader">
                <div class="fc-scrollgrid-sync-inner">
                    <a class="fc-col-header-cell-cushion weekly-total-th-cushion" aria-label="Weekly Total">Total</a>
                </div>
            </th>
        `);
    }

    // One cell per week row
    $('#calendar .fc-daygrid-body table tbody tr').each(function (idx) {
        const $row = $(this);
        if ($row.find('.weekly-total-cell').length) return;

        $row.append(`
            <td class="weekly-total-cell" data-week-idx="${idx}">
                <div class="weekly-total-content">
                    <div class="wt-line wt-work">Hrs wrk: <span class="wt-val">—</span></div>
                    <div class="wt-line wt-leave">Leave: <span class="wt-val">—</span></div>
                    <div class="wt-line wt-deficient">Deficit: <span class="wt-val">—</span></div>
                </div>
            </td>
        `);
    });
}

/** Fill a <colgroup> with `count` equal-width <col>s (default 8 = 7 days + total). */
function normalizeColgroup(selector, count = 8) {
    const colgroup = document.querySelector(selector);
    if (!colgroup) return;

    // Already normalized
    if (colgroup.children.length === count && colgroup.querySelector('.weekly-total-col')) return;

    colgroup.innerHTML = '';
    const pct = (100 / count).toFixed(4);

    for (let i = 0; i < count; i++) {
        const col = document.createElement('col');
        col.style.width = `${pct}%`;
        if (i === count - 1) col.className = 'weekly-total-col';   // marker for idempotency
        colgroup.appendChild(col);
    }
}

/** Write work / leave / deficit into each week's totals cell. */
function renderWeeklyTotals() {
    const weeks = getVisibleWeekRanges();
    if (!weeks.length) return;

    injectWeeklyTotalsColumn();

    const $cells = $('#calendar .weekly-total-cell');
    const hrs = (n) => `${n} hr${n !== 1 ? 's' : ''}`;

    weeks.forEach((week, idx) => {
        const s     = computeWeeklyTotal(week.start, week.end);
        const $cell = $cells.eq(idx);
        if (!$cell.length) return;

        $cell.find('.wt-work .wt-val')
            .text(s.work > 0 ? hrs(s.work) : '—')
            .toggleClass('muted', s.work === 0);

        $cell.find('.wt-leave .wt-val')
            .text(s.leave > 0 ? hrs(s.leave) : '—')
            .toggleClass('muted', s.leave === 0);

        const $def = $cell.find('.wt-deficient');
        if (s.deficient > 0) {
            $def.find('.wt-val').text(hrs(s.deficient));
            $def.show();
        } else {
            $def.hide();
        }

        $cell.attr('title',
            `${week.label}\n` +
            `Work: ${s.work} hrs\n` +
            `Leave: ${s.leave} hrs\n` +
            `Total: ${s.total} / ${s.target} hrs\n` +
            (s.deficient > 0 ? `Deficit: ${s.deficient} hrs` : 'Target met ✓')
        );
    });
}

/** Sum approved work + approved leave for [startDate, endDate] and compare to the weekly target. */
function computeWeeklyTotal(startDate, endDate) {
    const startStr = toDateStr(startDate);
    const endStr   = toDateStr(endDate);

    const sumInRange = (byDate) => {
        let sum = 0;
        for (const date in byDate) {
            if (date >= startStr && date <= endStr) sum += byDate[date];
        }
        return Math.round(sum * 100) / 100;
    };

    const work      = sumInRange(state.dailyTotalsCache);
    const leave     = sumInRange(state.leaveHoursByDate);
    const total     = Math.round((work + leave) * 100) / 100;
    const deficient = Math.max(0, Math.round((WEEKLY_TARGET - total) * 100) / 100);

    return { work, leave, total, deficient, target: WEEKLY_TARGET };
}

/** The 7-day ranges (Sun–Sat) covered by the current month view. */
function getVisibleWeekRanges() {
    if (!state.calendar) return [];

    const view   = state.calendar.view;
    const start  = new Date(view.activeStart);
    const end    = new Date(view.activeEnd);
    const weeks  = [];
    const cursor = new Date(start);
    const fmt    = (d) => d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

    while (cursor < end) {
        const weekStart = new Date(cursor);
        const weekEnd   = new Date(cursor);
        weekEnd.setDate(weekEnd.getDate() + 6);

        weeks.push({ start: weekStart, end: weekEnd, label: `${fmt(weekStart)} – ${fmt(weekEnd)}` });
        cursor.setDate(cursor.getDate() + 7);
    }
    return weeks;
}

/**
 * FullCalendar rebuilds its grid on some interactions, wiping the injected
 * column. Watch the grid and re-inject (debounced) when that happens.
 */
function watchCalendarBody() {
    if (!window.MutationObserver) return;

    // Old observers point at dead nodes after a re-render
    if (window.__calObservers) window.__calObservers.forEach(o => o.disconnect());
    window.__calObservers = [];

    const targets = [
        document.querySelector('#calendar .fc-col-header'),
        document.querySelector('#calendar .fc-daygrid-body')
    ].filter(Boolean);

    // Month grid not in the DOM (week/day view) — retry shortly
    if (!targets.length) {
        setTimeout(watchCalendarBody, 200);
        return;
    }

    let scheduled = false;
    const reinject = () => {
        if (scheduled) return;
        scheduled = true;
        setTimeout(() => {
            scheduled = false;
            injectWeeklyTotalsColumn();
            renderWeeklyTotals();
        }, 100);
    };

    targets.forEach(target => {
        const observer = new MutationObserver(reinject);
        observer.observe(target, { childList: true, subtree: true });
        window.__calObservers.push(observer);
    });
}