<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Calendar UI</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --calendar-blue: #0d6efd;
      --border: #e1e5ea;
      --muted: #6b7280;
      --bg: #f7f9fc;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      background: var(--bg);
      color: #202124;
      font-family: Arial, Helvetica, sans-serif;
      overflow: hidden;
    }

    .app {
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Top bar */
    .topbar {
      height: 72px;
      background: #fff;
      border-bottom: 1px solid #edf0f3;
      display: flex;
      align-items: center;
      padding: 0 22px;
      gap: 18px;
      flex-shrink: 0;
    }

    .menu-btn {
      width: 38px;
      height: 38px;
      border: 0;
      background: transparent;
      border-radius: 50%;
      font-size: 22px;
    }

    .menu-btn:hover { background: #f1f3f4; }

    .calendar-logo {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      background: #4285f4;
      color: #fff;
      font-size: 22px;
      display: grid;
      place-items: center;
      font-weight: 500;
    }

    .brand {
      font-size: 20px;
      margin-right: 25px;
    }

    .today-btn {
      border: 1px solid #dadce0;
      background: #fff;
      border-radius: 22px;
      padding: 9px 19px;
      font-weight: 500;
    }

    .today-btn:hover { background: #f8f9fa; }

    .nav-arrow {
      border: 0;
      background: transparent;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      font-size: 23px;
      color: #5f6368;
    }

    .nav-arrow:hover { background: #f1f3f4; }

    .current-date {
      font-size: 22px;
      white-space: nowrap;
    }

    .top-spacer { flex: 1; }

    .icon-btn {
      border: 0;
      background: transparent;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      font-size: 20px;
    }

    .icon-btn:hover { background: #f1f3f4; }

    .view-select {
      border: 1px solid #dadce0;
      border-radius: 8px;
      padding: 9px 30px 9px 13px;
      background: #fff;
      font-weight: 500;
    }

    /* Main */
    .main {
      min-height: 0;
      flex: 1;
      display: flex;
    }

    .sidebar {
      width: 255px;
      background: #fff;
      padding: 22px 20px;
      overflow: auto;
      flex-shrink: 0;
    }

    .create-btn {
      border: 0;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(60,64,67,.20);
      padding: 13px 22px;
      font-size: 15px;
      margin-bottom: 30px;
    }

    .create-btn:hover { background: #f8f9fa; }

    .mini-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .mini-month {
      font-size: 15px;
      font-weight: 500;
    }

    .mini-nav button {
      border: 0;
      background: transparent;
      font-size: 18px;
      color: #5f6368;
    }

    .mini-calendar {
      width: 100%;
      border-collapse: collapse;
      text-align: center;
      font-size: 11px;
      margin-bottom: 22px;
    }

    .mini-calendar th {
      color: #5f6368;
      font-weight: 500;
      padding-bottom: 7px;
    }

    .mini-calendar td {
      height: 26px;
      cursor: pointer;
      border-radius: 50%;
      width: 14.28%;
    }

    .mini-calendar td:hover { background: #eef3f8; }

    .mini-calendar .selected {
      background: var(--calendar-blue);
      color: #fff;
      font-weight: bold;
    }

    .people-search {
      background: #eef2f7;
      border-radius: 7px;
      padding: 11px 10px;
      color: #5f6368;
      margin-bottom: 26px;
      font-size: 14px;
    }

    .side-title {
      font-size: 14px;
      margin-bottom: 13px;
    }

    .calendar-check {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .calendar-check input {
      width: 18px;
      height: 18px;
    }

    /* Day calendar */
    .calendar-wrap {
      flex: 1;
      min-width: 0;
      padding: 15px 15px 18px 0;
    }

    .calendar-card {
      height: 100%;
      background: #fff;
      border-radius: 27px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .day-header {
      height: 84px;
      border-bottom: 1px solid var(--border);
      display: grid;
      grid-template-columns: 64px 1fr;
      flex-shrink: 0;
    }

    .day-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 3px;
    }

    .day-label .weekday {
      color: #1a73e8;
      font-size: 11px;
      font-weight: 600;
    }

    .day-label .day-number {
      width: 47px;
      height: 47px;
      background: var(--calendar-blue);
      color: #fff;
      border-radius: 50%;
      display: grid;
      place-items: center;
      font-size: 22px;
    }

    .time-grid {
      flex: 1;
      overflow-y: auto;
      position: relative;
    }

    .time-row {
      height: 40px;
      display: grid;
      grid-template-columns: 64px 1fr;
      position: relative;
    }

    .time-label {
      text-align: right;
      padding-right: 9px;
      transform: translateY(-7px);
      color: #4f555b;
      font-size: 11px;
    }

    .time-slot {
      border-top: 1px solid #dce1e6;
      position: relative;
      cursor: pointer;
    }

    .time-slot:hover { background: #f8fbff; }

    .gmt {
      position: absolute;
      left: 8px;
      top: -15px;
      font-size: 10px;
      color: #687078;
      background: #fff;
      padding: 0 3px;
    }

    .event {
      position: absolute;
      left: 7px;
      right: 7px;
      border-radius: 6px;
      background: #4285f4;
      color: #fff;
      padding: 6px 9px;
      font-size: 12px;
      z-index: 3;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,.15);
    }

    .event strong {
      display: block;
      font-size: 12px;
    }

    /* Modal-like schedule popup */
    .schedule-popup {
      position: fixed;
      z-index: 20;
      width: 330px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 35px rgba(0,0,0,.22);
      padding: 17px;
      display: none;
    }

    .schedule-popup.show { display: block; }

    .schedule-popup .form-control,
    .schedule-popup .form-select {
      font-size: 13px;
    }

    @media (max-width: 800px) {
      .sidebar { width: 210px; }
      .current-date { font-size: 17px; }
      .topbar { padding: 0 10px; gap: 8px; }
      .topbar .icon-btn { display: none; }
    }

    @media (max-width: 620px) {
      .sidebar { display: none; }
      .calendar-wrap { padding: 8px; }
      .calendar-card { border-radius: 15px; }
    }
  </style>
</head>

<body>
<div class="app">

  <!-- Top navigation -->
  <header class="topbar">
    <button class="menu-btn" title="Menu">☰</button>
    <div class="calendar-logo">3</div>
    <div class="brand">Calendar</div>

    <button id="todayBtn" class="today-btn">Today</button>
    <button id="prevBtn" class="nav-arrow">‹</button>
    <button id="nextBtn" class="nav-arrow">›</button>

    <div id="currentDate" class="current-date"></div>

    <div class="top-spacer"></div>

    <button class="icon-btn">⌕</button>
    <button class="icon-btn">?</button>
    <button class="icon-btn">⚙</button>

    <select class="view-select" id="viewSelect">
      <option>Day</option>
      <option>Week</option>
    </select>

    <button class="icon-btn">▦</button>
  </header>

  <main class="main">

    <!-- Sidebar -->
    <aside class="sidebar">
      <button id="createBtn" class="create-btn">
        <span style="font-size:22px; vertical-align:-2px;">+</span>
        &nbsp; Create
      </button>

      <div class="mini-header">
        <div id="miniMonth" class="mini-month"></div>
        <div class="mini-nav">
          <button id="miniPrev">‹</button>
          <button id="miniNext">›</button>
        </div>
      </div>

      <table class="mini-calendar">
        <thead>
          <tr>
            <th>S</th><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th>
          </tr>
        </thead>
        <tbody id="miniBody"></tbody>
      </table>

      <div class="people-search">♙ &nbsp; Search for people</div>

      <div class="side-title d-flex justify-content-between">
        <span>Booking pages</span>
        <span style="font-size:20px;">+</span>
      </div>

      <hr>

      <div class="side-title d-flex justify-content-between mt-3">
        <span>My calendars</span>
        <span>⌃</span>
      </div>

      <label class="calendar-check">
        <input type="checkbox" checked>
        <span>Arnaldo Parambita Jr.</span>
      </label>

      <label class="calendar-check">
        <input type="checkbox" checked>
        <span>Birthdays</span>
      </label>

      <label class="calendar-check">
        <input type="checkbox" checked>
        <span>Tasks</span>
      </label>
    </aside>

    <!-- Main day view -->
    <section class="calendar-wrap">
      <div class="calendar-card">

        <div class="day-header">
          <div></div>
          <div id="dayHeader" class="day-label"></div>
        </div>

        <div id="timeGrid" class="time-grid"></div>

      </div>
    </section>
  </main>
</div>

<!-- Schedule popup -->
<div id="schedulePopup" class="schedule-popup">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <strong>Set schedule</strong>
    <button id="closePopup" class="btn-close"></button>
  </div>

  <div class="mb-2">
    <label class="form-label small mb-1">Schedule</label>
    <input id="eventTitle" class="form-control" placeholder="e.g. Work schedule">
  </div>

  <div class="row g-2">
    <div class="col-6">
      <label class="form-label small mb-1">Start</label>
      <input id="startTime" type="time" class="form-control" value="08:00">
    </div>
    <div class="col-6">
      <label class="form-label small mb-1">End</label>
      <input id="endTime" type="time" class="form-control" value="12:00">
    </div>
  </div>

  <div class="d-flex gap-2 mt-3">
    <button id="saveSchedule" class="btn btn-primary btn-sm">Save</button>
    <button id="cancelSchedule" class="btn btn-light btn-sm">Cancel</button>
  </div>
</div>

<script>
  let selectedDate = new Date(2026, 8, 3); // September 3, 2026
  let miniDate = new Date(selectedDate);
  let events = [];

  const currentDate = document.getElementById('currentDate');
  const dayHeader = document.getElementById('dayHeader');
  const miniMonth = document.getElementById('miniMonth');
  const miniBody = document.getElementById('miniBody');
  const timeGrid = document.getElementById('timeGrid');
  const popup = document.getElementById('schedulePopup');

  function sameDay(a, b) {
    return a.getFullYear() === b.getFullYear() &&
           a.getMonth() === b.getMonth() &&
           a.getDate() === b.getDate();
  }

  function formatDate(date) {
    return date.toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    });
  }

  function renderHeader() {
    currentDate.textContent = formatDate(selectedDate);

    const weekday = selectedDate.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase();
    dayHeader.innerHTML = `
      <div class="weekday">${weekday}</div>
      <div class="day-number">${selectedDate.getDate()}</div>
    `;
  }

  function renderMiniCalendar() {
    const year = miniDate.getFullYear();
    const month = miniDate.getMonth();

    miniMonth.textContent = miniDate.toLocaleDateString('en-US', {
      month: 'long',
      year: 'numeric'
    });

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    let html = '';
    let day = 1;

    for (let row = 0; row < 6; row++) {
      html += '<tr>';

      for (let col = 0; col < 7; col++) {
        if ((row === 0 && col < firstDay) || day > daysInMonth) {
          html += '<td></td>';
        } else {
          const d = new Date(year, month, day);
          const selected = sameDay(d, selectedDate) ? 'selected' : '';

          html += `<td class="${selected}" data-date="${year}-${month}-${day}">${day}</td>`;
          day++;
        }
      }

      html += '</tr>';
      if (day > daysInMonth) break;
    }

    miniBody.innerHTML = html;

    miniBody.querySelectorAll('td[data-date]').forEach(cell => {
      cell.addEventListener('click', () => {
        const [y, m, d] = cell.dataset.date.split('-').map(Number);
        selectedDate = new Date(y, m, d);
        miniDate = new Date(selectedDate);
        render();
      });
    });
  }

  function hourLabel(hour) {
    if (hour === 0) return '12 AM';
    if (hour < 12) return hour + ' AM';
    if (hour === 12) return '12 PM';
    return (hour - 12) + ' PM';
  }

  function renderTimeGrid() {
    timeGrid.innerHTML = '';

    // GMT label
    const first = document.createElement('div');
    first.className = 'gmt';
    first.textContent = 'GMT+08';
    timeGrid.appendChild(first);

    for (let hour = 0; hour <= 23; hour++) {
      const row = document.createElement('div');
      row.className = 'time-row';

      row.innerHTML = `
        <div class="time-label">${hourLabel(hour)}</div>
        <div class="time-slot" data-hour="${hour}"></div>
      `;

      timeGrid.appendChild(row);
    }

    timeGrid.querySelectorAll('.time-slot').forEach(slot => {
      slot.addEventListener('click', function(e) {
        openSchedulePopup(e.clientX, e.clientY, Number(this.dataset.hour));
      });
    });

    // Draw events
    events.filter(ev => sameDay(ev.date, selectedDate)).forEach(ev => {
      const rows = [...timeGrid.querySelectorAll('.time-row')];
      const row = rows[ev.startHour];

      if (!row) return;

      const slot = row.querySelector('.time-slot');
      const duration = Math.max(0.5, ev.endHour - ev.startHour);

      const event = document.createElement('div');
      event.className = 'event';
      event.style.top = '1px';
      event.style.height = (duration * 40 - 2) + 'px';
      event.innerHTML = `<strong>${escapeHtml(ev.title)}</strong>${ev.start} – ${ev.end}`;

      slot.appendChild(event);
    });
  }

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function openSchedulePopup(x, y, hour) {
    const h = String(hour).padStart(2, '0');

    document.getElementById('eventTitle').value = '';
    document.getElementById('startTime').value = h + ':00';
    document.getElementById('endTime').value =
      String(Math.min(hour + 1, 23)).padStart(2, '0') + ':00';

    popup.style.left = Math.min(x, window.innerWidth - 350) + 'px';
    popup.style.top = Math.min(y, window.innerHeight - 250) + 'px';
    popup.classList.add('show');

    document.getElementById('eventTitle').focus();
  }

  function closePopup() {
    popup.classList.remove('show');
  }

  function render() {
    renderHeader();
    renderMiniCalendar();
    renderTimeGrid();
  }

  document.getElementById('todayBtn').addEventListener('click', () => {
    selectedDate = new Date();
    miniDate = new Date(selectedDate);
    render();
  });

  document.getElementById('prevBtn').addEventListener('click', () => {
    selectedDate.setDate(selectedDate.getDate() - 1);
    miniDate = new Date(selectedDate);
    render();
  });

  document.getElementById('nextBtn').addEventListener('click', () => {
    selectedDate.setDate(selectedDate.getDate() + 1);
    miniDate = new Date(selectedDate);
    render();
  });

  document.getElementById('miniPrev').addEventListener('click', () => {
    miniDate.setMonth(miniDate.getMonth() - 1);
    renderMiniCalendar();
  });

  document.getElementById('miniNext').addEventListener('click', () => {
    miniDate.setMonth(miniDate.getMonth() + 1);
    renderMiniCalendar();
  });

  document.getElementById('createBtn').addEventListener('click', () => {
    openSchedulePopup(
      window.innerWidth / 2 - 165,
      window.innerHeight / 2 - 120,
      8
    );
  });

  document.getElementById('closePopup').addEventListener('click', closePopup);
  document.getElementById('cancelSchedule').addEventListener('click', closePopup);

  document.getElementById('saveSchedule').addEventListener('click', () => {
    const title = document.getElementById('eventTitle').value.trim() || 'Schedule';
    const start = document.getElementById('startTime').value;
    const end = document.getElementById('endTime').value;

    if (!start || !end || end <= start) {
      alert('Please select a valid start and end time.');
      return;
    }

    const startParts = start.split(':').map(Number);
    const endParts = end.split(':').map(Number);

    events.push({
      date: new Date(selectedDate),
      title,
      start,
      end,
      startHour: startParts[0] + startParts[1] / 60,
      endHour: endParts[0] + endParts[1] / 60
    });

    closePopup();
    renderTimeGrid();
  });

  document.addEventListener('click', e => {
    if (popup.classList.contains('show') &&
        !popup.contains(e.target) &&
        !e.target.closest('.time-slot') &&
        !e.target.closest('#createBtn')) {
      closePopup();
    }
  });

  render();
</script>
</body>
</html>
