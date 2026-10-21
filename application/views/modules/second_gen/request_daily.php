<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendar Modal</title>

    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet" />
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/styles.css'); ?>">
    <script src="<?= base_url('assets/js/jquery-3.7.1.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


    <style>
        .btn-delete-event {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            padding: 0 10px 8px;
            /* margin-right: 6px; */
            border-radius: 3px;
            order: 2;
        }

        .btn-delete-event:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .event-details {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            order: 1;
        }

        .day-header {
            height: 60px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            flex-shrink: 0;
        }

        .day-label {
            font-size: 18px;
            font-weight: 600;
            color: #1a73e8;
        }

        .time-grid {
            flex: 1;
            position: relative;
        }

        .time-row {
            height: 50px;
            display: grid;
            grid-template-columns: 90px 1fr;
            position: relative;
        }

        .time-label {
            text-align: right;
            /* padding-right: 9px; */
            transform: translateY(-7px);
            color: #4f555b;
            font-size: 11px;
        }

        .chk-box {
            text-align: left;

        }

        .slot-check {
            border-color: rgb(170, 170, 170);
        }

        .gmt {
            left: 8px;
            top: -15px;
            font-size: 10px;
            color: #687078;
            background: #fff;
            padding: 0 3px;
        }


        .time-slot {
            border-top: 1px solid #dce1e6;
            position: relative;
            cursor: pointer;
            box-sizing: border-box;
        }

        .time-slot:hover {
            background: #f8fbff;
        }

        /* Removes the top border when the previous slot is also checked */
        .time-slot.merge-top {
            border-top-color: transparent !important;
        }


        .event {
            position: absolute;
            left: 0;
            right: 0;
            width: 100%;
            box-sizing: border-box;
            border-radius: 4px;
            background: #4285f4;
            color: #fff;
            padding: 4px 8px;
            font-size: 12px;
            z-index: 3;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            pointer-events: auto;
        }

        .event strong {
            display: block;
            font-size: 12px;
        }

        /* Set positioning context on modal-content */
        .modal-content {
            position: relative;
        }

        /* Updated schedule popup CSS to stay inside the modal DOM tree */
        .schedule-popup {
            position: absolute;
            z-index: 1075;
            width: 330px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 35px rgba(0, 0, 0, .22);
            padding: 17px;
            display: none;
        }

        .schedule-popup.show {
            display: block;
        }

        .schedule-popup .form-control,
        .schedule-popup .form-select {
            font-size: 13px;
        }
    </style>
</head>

<body class="p-4">

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#calendarModal">
        Open Daily Scheduler
    </button>

    <div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daily Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <section class="calendar-wrap">
                                <div class="calendar-card">
                                    <div class="day-header">
                                        <div id="dayHeader" class="day-label"></div>
                                    </div>
                                    <div id="timeGrid" class="time-grid"></div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div id="schedulePopup" class="schedule-popup">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>Set Time Schedule</strong>
                        <button id="closePopup" class="btn-close"></button>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small mb-1">Details</label>
                        <textarea name="eventTitle" id="eventTitle" class="form-control" placeholder="e.g. Work schedule"></textarea>
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

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
                </div>
            </div>
        </div>
    </div>

    <button id="responsiveActionBtn" class="btn btn-primary" type="button">Open Content</button>

    <div id="mobileCenteredCard" class="d-none">
        <div class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 1050;" onclick="toggleMobileCard()"></div>
        <div class="position-fixed top-50 start-50 translate-middle w-100 p-3" style="z-index: 1060; max-width: 400px;">
            <div class="card shadow-lg border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Set Time Schedule</h5>
                    <button type="button" class="btn-close" onclick="toggleMobileCard()"></button>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small mb-1">Details</label>
                        <textarea name="eventTitle" id="eventTitle" class="form-control" placeholder="e.g. Work schedule"></textarea>
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
            </div>
        </div>
    </div>
    <div class="modal fade" id="desktopModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Desktop Web View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    This content is strictly reserved for the desktop modal experience.
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('responsiveActionBtn').addEventListener('click', function() {
            const isMobile = window.innerWidth < 768;

            if (isMobile) {
                toggleMobileCard();
            } else {
                // Desktop: Launch native Bootstrap modal
                const desktopModal = document.getElementById('desktopModal');
                const bsModal = bootstrap.Modal.getOrCreateInstance(desktopModal);
                bsModal.show();
            }
        });

        // Helper function to show/hide the mobile centered card
        function toggleMobileCard() {
            const mobileCard = document.getElementById('mobileCenteredCard');
            mobileCard.classList.toggle('d-none');
        }



        let selectedDate = new Date(2026, 7, 31);
        let events = [];

        const dayHeader = document.getElementById('dayHeader');
        const timeGrid = document.getElementById('timeGrid');
        const popup = document.getElementById('schedulePopup');
        const modalContent = document.querySelector('#calendarModal .modal-content');

        function sameDay(a, b) {
            return a.getFullYear() === b.getFullYear() &&
                a.getMonth() === b.getMonth() &&
                a.getDate() === b.getDate();
        }

        function renderHeader() {
            const fullDate = selectedDate.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            dayHeader.textContent = fullDate;
        }

        function hourLabel(hour) {
            let period = hour >= 12 ? 'PM' : 'AM';

            // Convert 24-hour to 12-hour format
            let displayHour = hour % 12;
            if (displayHour === 0) displayHour = 12;

            // Pad single digits with a leading zero
            let paddedHour = displayHour < 10 ? '0' : '';
            paddedHour += displayHour;

            return paddedHour + ':00 ' + period;
        }

        function renderTimeGrid() {
            timeGrid.innerHTML = '';

            const first = document.createElement('div');
            first.className = 'gmt mb-3';
            timeGrid.appendChild(first);

            for (let hour = 8; hour <= 19; hour++) {
                const row = document.createElement('div');
                row.className = 'time-row';

                if (hour == 19) {
                    row.innerHTML = `<div class="time-label">${hourLabel(hour)}</div>
                             <div class="time-slot" data-hour="${hour}"></div>`;
                } else {
                    row.innerHTML = `
                            <div class="time-label">${hourLabel(hour)} 
                                <div class="chk-box my-1 ms-1">
                                    <input type="checkbox" class="form-check-input slot-check" data-hour="${hour}">
                                </div>
                            </div>
                            <div class="time-slot ms-3" data-hour="${hour}"></div>
                        `;
                }

                timeGrid.appendChild(row);
            }

            // Helper to hide border-top for consecutive checked slots
            function updateMergedBorders() {
                const checkedHours = new Set(
                    [...timeGrid.querySelectorAll('.slot-check:checked')].map(cb => Number(cb.dataset.hour))
                );

                timeGrid.querySelectorAll('.time-slot').forEach(slot => {
                    const hour = Number(slot.dataset.hour);
                    // If both this hour and the previous hour are checked, hide this slot's top border
                    if (checkedHours.has(hour) && checkedHours.has(hour - 1)) {
                        slot.classList.add('merge-top');
                    } else {
                        slot.classList.remove('merge-top');
                    }
                });
            }

            // Auto-fill intermediate checkboxes and update border styles
            timeGrid.querySelectorAll('.slot-check').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedBoxes = [...timeGrid.querySelectorAll('.slot-check:checked')]
                        .map(cb => Number(cb.dataset.hour))
                        .sort((a, b) => a - b);

                    if (checkedBoxes.length > 1) {
                        const minHour = checkedBoxes[0];
                        const maxHour = checkedBoxes[checkedBoxes.length - 1];

                        timeGrid.querySelectorAll('.slot-check').forEach(cb => {
                            const cbHour = Number(cb.dataset.hour);
                            if (cbHour >= minHour && cbHour <= maxHour) {
                                cb.checked = true;
                            }
                        });
                    }

                    // Update border states on checkbox toggle
                    updateMergedBorders();
                });
            });

            // Attach click listener for opening modal with overlap protection
            timeGrid.querySelectorAll('.time-slot').forEach(slot => {
                slot.addEventListener('click', function(e) {
                    const row = this.closest('.time-row');
                    const checkbox = row.querySelector('.slot-check');

                    if (checkbox && !checkbox.checked) {
                        return;
                    }

                    const clickedHour = Number(this.dataset.hour);

                    const dayEvents = events
                        .filter(ev => sameDay(ev.date, selectedDate))
                        .sort((a, b) => a.startHour - b.startHour);

                    const isOccupied = dayEvents.some(ev => clickedHour >= ev.startHour && clickedHour < ev.endHour);
                    if (isOccupied) {
                        alert('This time slot is already occupied by another event.');
                        return;
                    }

                    const checkedBoxes = [...timeGrid.querySelectorAll('.slot-check:checked')]
                        .map(cb => Number(cb.dataset.hour))
                        .sort((a, b) => a - b);

                    let startHour = clickedHour;
                    let endHour = clickedHour + 1;

                    if (checkedBoxes.length > 0) {
                        const blocks = [];
                        let currentBlock = [checkedBoxes[0]];

                        for (let i = 1; i < checkedBoxes.length; i++) {
                            if (checkedBoxes[i] === checkedBoxes[i - 1] + 1) {
                                currentBlock.push(checkedBoxes[i]);
                            } else {
                                blocks.push(currentBlock);
                                currentBlock = [checkedBoxes[i]];
                            }
                        }
                        blocks.push(currentBlock);

                        const activeBlock = blocks.find(block => block.includes(clickedHour));

                        if (activeBlock) {
                            startHour = activeBlock[0];
                            endHour = activeBlock[activeBlock.length - 1] + 1;
                        }
                    }

                    dayEvents.forEach(ev => {
                        if (ev.startHour > clickedHour && ev.startHour < endHour) {
                            endHour = ev.startHour;
                        }
                        if (ev.endHour <= clickedHour && ev.endHour > startHour) {
                            startHour = ev.endHour;
                        }
                    });

                    openSchedulePopup(e, startHour, endHour);
                });
            });

            function formatTime(decimalHour) {
                const hours24 = Math.floor(decimalHour);
                const minutes = Math.round((decimalHour - hours24) * 60);
                const ampm = hours24 >= 12 ? 'PM' : 'AM';
                let hours12 = hours24 % 12;
                hours12 = hours12 ? hours12 : 12;
                const minutesStr = minutes < 10 ? '0' + minutes : minutes;
                return `${hours12}:${minutesStr} ${ampm}`;
            }

            events.filter(ev => sameDay(ev.date, selectedDate)).forEach(ev => {
                const baseHour = Math.floor(ev.startHour);
                const rows = [...timeGrid.querySelectorAll('.time-row')];
                const rowIndex = baseHour - 8;
                const row = rows[rowIndex];
                if (!row) return;

                const slot = row.querySelector('.time-slot');
                const minuteOffset = (ev.startHour - baseHour) * 50;
                const duration = Math.max(0.25, ev.endHour - ev.startHour);

                const startTimeFormatted = formatTime(ev.startHour);
                const endTimeFormatted = formatTime(ev.endHour);

                const event = document.createElement('div');
                event.className = 'event';
                event.style.top = (1 + minuteOffset) + 'px';
                event.style.height = (duration * 50 - 2) + 'px';

                event.innerHTML = `
                        <button class="btn-delete-event fs-2" title="Delete event">&times;</button>
                        <div class="event-details">
                            <strong>${startTimeFormatted} – ${endTimeFormatted}</strong> <i>${escapeHtml(ev.title)}</i>
                        </div>
                    `;

                const deleteBtn = event.querySelector('.btn-delete-event');
                deleteBtn.addEventListener('click', function(e) {
                    e.stopPropagation();

                    const eventIndex = events.indexOf(ev);
                    if (eventIndex > -1) {
                        events.splice(eventIndex, 1);
                    }

                    const checkedHours = [...document.querySelectorAll('.slot-check:checked')].map(cb => cb.dataset.hour);

                    renderTimeGrid();

                    checkedHours.forEach(hour => {
                        const cb = document.querySelector(`.slot-check[data-hour="${hour}"]`);
                        if (cb) cb.checked = true;
                    });
                    updateMergedBorders();
                });

                slot.appendChild(event);
            });

            // Run once after rendering grid to preserve border state on restored checkmarks
            updateMergedBorders();
        }

        // Updated openSchedulePopup to receive both start and end hours
        function openSchedulePopup(e, startHour, endHour) {
            const formatHour = (h) => String(h).padStart(2, '0') + ':00';

            document.getElementById('eventTitle').value = '';
            document.getElementById('startTime').value = formatHour(startHour);
            document.getElementById('endTime').value = formatHour(endHour);

            const rect = modalContent.getBoundingClientRect();
            const posX = e.clientX - rect.left;
            const posY = e.clientY - rect.top;

            popup.style.left = Math.min(posX, rect.width - 340) + 'px';
            popup.style.top = Math.min(posY, rect.height - 250) + 'px';
            popup.classList.add('show');

        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }


        function closePopup() {
            popup.classList.remove('show');
        }

        function render() {
            renderHeader();
            renderTimeGrid();
        }

        document.getElementById('closePopup').addEventListener('click', closePopup);
        document.getElementById('cancelSchedule').addEventListener('click', closePopup);
        document.getElementById('saveSchedule').addEventListener('click', () => {
            const title = document.getElementById('eventTitle').value.trim() || 'No details added...';
            const start = document.getElementById('startTime').value;
            const end = document.getElementById('endTime').value;

            if (!start || !end || end <= start) {
                alert('Please select a valid start and end time.');
                return;
            }

            const startParts = start.split(':').map(Number);
            const endParts = end.split(':').map(Number);

            // Save ALL checked hours before re-rendering the grid
            const checkedHours = [...document.querySelectorAll('.slot-check:checked')].map(cb => cb.dataset.hour);

            events.push({
                date: new Date(selectedDate),
                title,
                start,
                end,
                startHour: startParts[0] + startParts[1] / 60,
                endHour: endParts[0] + endParts[1] / 60
            });

            closePopup();

            // Re-render the time grid
            renderTimeGrid();

            // Re-apply every checkbox state back to checked
            checkedHours.forEach(hour => {
                const cb = document.querySelector(`.slot-check[data-hour="${hour}"]`);
                if (cb) cb.checked = true;
            });
        });

        document.addEventListener('click', e => {
            if (popup.classList.contains('show') &&
                !popup.contains(e.target) &&
                !e.target.closest('.time-slot')) {
                closePopup();
            }
        });

        document.getElementById('calendarModal').addEventListener('shown.bs.modal', render);
    </script>
</body>

</html>