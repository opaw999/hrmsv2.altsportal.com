<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schedule Modal</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f3f6fa;
            font-family: Arial, sans-serif;
        }

        .calendar-preview {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Modal */
        .schedule-modal .modal-dialog {
            max-width: 850px;
        }

        .schedule-modal .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
        }

        .schedule-modal .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .schedule-modal .modal-body {
            padding: 0;
        }

        /* Date header */
        .date-header {
            height: 75px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid #e1e5e9;
        }

        .date-header-inner {
            text-align: center;
        }

        .weekday {
            color: #1a73e8;
            font-size: 11px;
            font-weight: 600;
        }

        .date-number {
            width: 46px;
            height: 46px;
            margin: 2px auto 0;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 21px;
        }

        /* Time area */
        .time-calendar {
            height: 500px;
            overflow-y: auto;
            position: relative;
        }

        .time-row {
            height: 60px;
            display: grid;
            grid-template-columns: 70px 1fr;
        }

        .time-label {
            text-align: right;
            padding-right: 12px;
            padding-top: 0;
            transform: translateY(-7px);
            color: #4b5563;
            font-size: 12px;
            user-select: none;
        }

        .time-slot {
            border-top: 1px solid #dce1e6;
            position: relative;
            cursor: pointer;
        }

        .time-slot:hover {
            background: #f5f9ff;
        }

        /* Half-hour line */
        .time-slot::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 30px;
            border-top: 1px dotted #edf0f3;
            pointer-events: none;
        }

        /* Selected range */
        .selected-range {
            position: absolute;
            left: 6px;
            right: 8px;
            background: #dbeafe;
            border-left: 4px solid #0d6efd;
            border-radius: 5px;
            z-index: 5;
            padding: 7px 10px;
            color: #174ea6;
            font-size: 12px;
            pointer-events: none;
        }

        /* Appointment/event */
        .appointment {
            position: absolute;
            left: 6px;
            right: 8px;
            background: #4285f4;
            color: white;
            border-radius: 5px;
            padding: 7px 10px;
            z-index: 10;
            font-size: 12px;
        }

        .appointment-title {
            font-weight: 600;
        }

        .schedule-form {
            border-top: 1px solid #e5e7eb;
            background: #fff;
            padding: 16px 20px;
        }

        .schedule-form .form-label {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .schedule-form .form-control,
        .schedule-form .form-select {
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .schedule-modal .modal-dialog {
                margin: 0;
                max-width: 100%;
                height: 100%;
            }

            .schedule-modal .modal-content {
                height: 100%;
                border-radius: 0;
            }

            .time-calendar {
                height: calc(100vh - 300px);
            }
        }
    </style>
</head>

<body>

    <div class="calendar-preview">
        <button class="btn btn-primary px-4"
            data-bs-toggle="modal"
            data-bs-target="#scheduleModal">
            Open Schedule
        </button>
    </div>


    <!-- ================= MODAL ================= -->
    <div class="modal fade schedule-modal" id="scheduleModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Schedule</h5>
                        <small class="text-muted" id="selectedDateText">
                            September 3, 2026
                        </small>
                    </div>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>


                <div class="modal-body">

                    <!-- Date -->
                    <div class="date-header">
                        <div class="date-header-inner">
                            <div class="weekday">THU</div>
                            <div class="date-number">3</div>
                        </div>
                    </div>


                    <!-- TIME CALENDAR -->
                    <div id="timeCalendar" class="time-calendar">

                        <div id="calendarRows"></div>

                        <!-- Selected time range -->
                        <div id="selectedRange"
                            class="selected-range"
                            style="display:none;">
                        </div>

                        <!-- Existing appointments -->
                        <div class="appointment"
                            style="top:480px;height:90px;">
                            <div class="appointment-title">
                                Existing Appointment
                            </div>
                            <div>8:00 AM - 9:30 AM</div>
                        </div>

                    </div>


                    <!-- FORM -->
                    <div class="schedule-form">

                        <div class="row g-2">

                            <div class="col-md-5">
                                <label class="form-label">
                                    Type
                                </label>

                                <select id="scheduleType"
                                    class="form-select">
                                    <option value="appointment">
                                        Appointment
                                    </option>
                                    <option value="task">
                                        Task
                                    </option>
                                    <option value="leave">
                                        Leave
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label">
                                    Title
                                </label>

                                <input id="scheduleTitle"
                                    type="text"
                                    class="form-control"
                                    placeholder="Task or appointment title">
                            </div>

                        </div>

                        <div class="row g-2 mt-1">

                            <div class="col-6">
                                <label class="form-label">
                                    Start
                                </label>

                                <input id="startTime"
                                    type="time"
                                    class="form-control"
                                    value="08:00">
                            </div>

                            <div class="col-6">
                                <label class="form-label">
                                    End
                                </label>

                                <input id="endTime"
                                    type="time"
                                    class="form-control"
                                    value="09:00">
                            </div>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button id="saveBtn"
                        type="button"
                        class="btn btn-primary">
                        Save Schedule
                    </button>

                </div>

            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

    <script>
        const calendarRows = document.getElementById('calendarRows');
        const selectedRange = document.getElementById('selectedRange');

        const startInput = document.getElementById('startTime');
        const endInput = document.getElementById('endTime');

        let selectedStart = null;


        /* Create 12 AM - 11 PM */
        for (let hour = 0; hour < 24; hour++) {

            const row = document.createElement('div');
            row.className = 'time-row';

            const label = document.createElement('div');
            label.className = 'time-label';

            let displayHour;

            if (hour === 0) {
                displayHour = '12 AM';
            } else if (hour < 12) {
                displayHour = hour + ' AM';
            } else if (hour === 12) {
                displayHour = '12 PM';
            } else {
                displayHour = (hour - 12) + ' PM';
            }

            label.textContent = displayHour;


            const slot = document.createElement('div');
            slot.className = 'time-slot';
            slot.dataset.hour = hour;


            /*
               Clicking an hour sets the START time.
               The next click sets the END time.
            */
            slot.addEventListener('click', function() {

                const hour = Number(this.dataset.hour);

                if (selectedStart === null) {

                    selectedStart = hour;

                    startInput.value =
                        String(hour).padStart(2, '0') + ':00';

                    endInput.value =
                        String(Math.min(hour + 1, 23))
                        .padStart(2, '0') + ':00';

                    showRange(hour, hour + 1);

                } else {

                    let endHour = hour;

                    if (endHour <= selectedStart) {
                        alert('End time must be after the start time.');
                        return;
                    }

                    endInput.value =
                        String(endHour).padStart(2, '0') + ':00';

                    showRange(selectedStart, endHour);

                    selectedStart = null;
                }
            });


            row.appendChild(label);
            row.appendChild(slot);

            calendarRows.appendChild(row);
        }


        /* Show selected blue time range */
        function showRange(start, end) {

            const top = start * 60;
            const height = (end - start) * 60;

            selectedRange.style.display = 'block';
            selectedRange.style.top = top + 'px';
            selectedRange.style.height = height + 'px';

            selectedRange.innerHTML =
                '<strong>' +
                formatHour(start) +
                ' - ' +
                formatHour(end) +
                '</strong>';
        }


        function formatHour(hour) {

            if (hour === 0) return '12:00 AM';
            if (hour === 12) return '12:00 PM';

            if (hour < 12) {
                return hour + ':00 AM';
            }

            return (hour - 12) + ':00 PM';
        }


        /* If user manually changes the time inputs */
        startInput.addEventListener('change', updateRangeFromInputs);
        endInput.addEventListener('change', updateRangeFromInputs);

        function updateRangeFromInputs() {

            if (!startInput.value || !endInput.value) return;

            const start = parseInt(startInput.value.split(':')[0]);
            const end = parseInt(endInput.value.split(':')[0]);

            if (end > start) {
                showRange(start, end);
            }
        }


        /* Save */
        document.getElementById('saveBtn')
            .addEventListener('click', function() {

                const title =
                    document.getElementById('scheduleTitle').value.trim();

                if (!title) {
                    alert('Please enter a title.');
                    return;
                }

                if (!startInput.value || !endInput.value) {
                    alert('Please select a time.');
                    return;
                }

                alert(
                    'Saved: ' +
                    title +
                    '\n' +
                    startInput.value +
                    ' - ' +
                    endInput.value
                );
            });


        /* Reset when modal closes */
        document.getElementById('scheduleModal')
            .addEventListener('hidden.bs.modal', function() {

                selectedStart = null;

                selectedRange.style.display = 'none';

                document.getElementById('scheduleTitle').value = '';
            });
    </script>

</body>

</html>