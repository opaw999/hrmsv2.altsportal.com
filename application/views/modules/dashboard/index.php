<?php
/**
 * Attendance dashboard view
 *
 * Expects: $page_title, $page_sub_title, $userData['name']
 *
 * Layout:
 *   1. Assets (CSS + flatpickr)
 *   2. Fixed header
 *   3. Main content (welcome card + calendar)
 *   4. Modals: time-off / attendance, document viewer, day-off
 *   5. Scripts (clock, PHP-driven config, attendance modules)
 */
?>

<!-- ======================================================================
     1. ASSETS
     ====================================================================== -->
<link rel="stylesheet" href="<?= base_url('assets/css/attendance-calendar.css'); ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


<!-- ======================================================================
     2. TOP FIXED HEADER
     ====================================================================== -->
<div class="header-wrapper">
    <header class="app-header-box d-flex justify-content-between align-items-center">

        <!-- Title + mobile sidebar toggle -->
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-light border d-lg-none py-1 px-2"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mobileSidebarDrawer">
                <i class="bi bi-list fs-6"></i>
            </button>
            <div>
                <h1 class="h6 fw-bold mb-0 text-dark" id="headerTitle"><?= $page_title; ?></h1>
                <p class="text-muted mb-0 d-none d-sm-block" style="font-size: 0.72rem;" id="headerSubtitle">
                    <?= $page_sub_title; ?>
                </p>
            </div>
        </div>

        <!-- Live date/time (updated by clock.js from data-server-time) -->
        <div class="header-datetime-badge d-flex align-items-center gap-3 px-4 py-2 bg-white border rounded-pill shadow-sm"
             id="headerDateTimeContainer"
             data-server-time="<?= date('Y-m-d\TH:i:s'); ?>">
            <div class="d-flex align-items-center text-muted fs-5">
                <i class="bi bi-calendar-event me-2" style="color: var(--primary-orange);"></i>
                <span id="headerLiveDate" class="fw-medium"><?= date('l, F j, Y'); ?></span>
            </div>
            <span class="text-muted opacity-50 fs-5">|</span>
            <div class="d-flex align-items-center fw-bold text-dark fs-5">
                <i class="bi bi-clock me-2 text-secondary"></i>
                <span id="headerLiveTime"><?= date('h:i:s A'); ?></span>
            </div>
        </div>

    </header>
</div>


<!-- ======================================================================
     3. MAIN CONTENT
     ====================================================================== -->
<div class="content-scrollable">
    <div class="view-page" id="page-dashboard">
        <div class="row g-3">
            <div class="col-12">
                <div class="plant-hero-card d-flex flex-column justify-content-between">

                    <!-- Welcome -->
                    <div class="mb-4">
                        <h2 class="plant-hero-title mb-1">Welcome back <?= htmlspecialchars($userData['name']); ?></h2>
                        <p class="plant-hero-sub mb-0">
                            <i class="bi bi-calendar-event me-1"></i><?= date('l, F j, Y'); ?>
                        </p>
                    </div>

                    <!-- Calendar -->
                    <div class="calendar-card shadow-sm">
                        <div class="calendar-legend">
                            Legend:
                            <span class="legend-item">
                                <span class="legend-icon approved"><i class="bi bi-check-circle-fill"></i></span>Approved
                            </span>
                            <span class="legend-item">
                                <span class="legend-icon pending">⏳</span>Pending
                            </span>
                            <span class="legend-item">
                                <span class="legend-icon rejected"><i class="bi bi-x-circle-fill"></i></span>Disapproved
                            </span>
                        </div>

                        <div class="calendar-scroll-wrap">
                            <div id="calendar"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- ======================================================================
     4a. MODAL — TIME OFF / ATTENDANCE
     ====================================================================== -->
<div class="modal fade" id="timeOffModal" tabindex="-1" aria-labelledby="timeOffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="timeOffModalLabel">
                    Attendance for <span id="selectedDateDisplay"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="modal-loading-overlay" id="modalLoadingOverlay">
                    <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted mb-0" style="font-size: 0.85rem;">Loading attendance...</p>
                </div>

                <!-- Date summary (In/Out + hours worked) -->
                <div class="mb-3">
                    <table class="table table-bordered table-sm mb-0" id="dateInfoTable">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>In 1</th>
                                <th>Out 1</th>
                                <th>In 2</th>
                                <th>Out 2</th>
                                <th>Hours Work</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="infoDate"></td>
                                <td id="infoDay"></td>
                                <td id="infoIn1">-</td>
                                <td id="infoOut1">-</td>
                                <td id="infoIn2">-</td>
                                <td id="infoOut2">-</td>
                                <td id="infoHoursWork">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Hour selection -->
                <div id="timeSelectionContainer">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0" id="step2Title" style="font-size: 0.85rem;">Select Hours</h6>
                        <div id="tableActions" style="display: none;">
                            <button type="button" class="btn btn-xs btn-outline-primary"
                                    id="btnSelectAllHours"
                                    style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Select All</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                    id="btnClearAllHours"
                                    style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Clear All</button>
                        </div>
                    </div>

                    <!-- Hours table (rows 8:00–18:00 are generated by JS: buildHoursTableBody) -->
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-bordered table-hover table-sm mb-0" id="hoursTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;" class="text-center checkbox-column">
                                        <input type="checkbox" class="form-check-input" id="checkAllHours">
                                    </th>
                                    <th style="width: 100px;">Time Entry</th>
                                    <th style="width: 100px;">End Time</th>
                                    <th style="width: 120px;">Type</th>
                                    <th style="width: 130px;">Category</th>
                                    <th>Remarks/Description</th>
                                    <th style="width: 140px;">Attachments</th>
                                    <th style="width: 180px;">Geo Location</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="hoursTableBody"></tbody>
                        </table>
                    </div>

                    <!-- Registered location -->
                    <div class="mt-3">
                        <div class="card">
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-1" style="font-size: 0.8rem;">
                                            <i class="bi bi-building me-1"></i>Registered Location
                                        </label>
                                        <div class="form-control form-control-sm bg-light" style="font-size: 0.8rem;" id="registered-location">
                                            <?= $location; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary w-100" id="btnConfirmAttendance">
                            <i class="bi bi-check-circle me-1"></i> Confirm
                        </button>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


<!-- ======================================================================
     4b. MODAL — DOCUMENT VIEWER (attachment preview)
     ====================================================================== -->
<div class="modal fade" id="documentViewerModal" tabindex="-1" aria-labelledby="documentViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h6 class="modal-title" id="documentViewerModalLabel">
                    <i class="bi bi-file-earmark me-1"></i>
                    <span id="viewerFileName">Document</span>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnDownloadFile" class="btn btn-sm btn-outline-primary" download>
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-0" style="height: 80vh;">
                <div id="documentViewerContent" class="w-100 h-100 d-flex align-items-center justify-content-center"></div>
            </div>

        </div>
    </div>
</div>


<!-- ======================================================================
     4c. MODAL — DAY-OFF REQUEST
     ====================================================================== -->
<div class="modal fade" id="modalDayoff" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalDayoffTitle">Change Day-Off</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="formDayoff">
                    <input type="hidden" id="offId" value="">
                    <input type="hidden" id="offOldDate" value="">

                    <!-- Shown only when a pending request exists -->
                    <div id="offStatusBanner" class="alert alert-warning py-2 px-3 mb-3 d-none" style="font-size: 0.85rem;">
                        <i class="bi bi-hourglass-split me-1"></i>
                        <span id="offStatusText">You have a pending request.</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted mb-2 d-block">Current Day-Off</label>
                            <input type="text" class="form-control form-control-sm" id="old_do" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted mb-2 d-block">New Day-Off</label>
                            <select class="form-select form-select-sm" id="new_do" required></select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold text-muted mb-2 d-block">Remarks</label>
                            <textarea class="form-control form-control-sm" id="dayoffRemarks" rows="2"
                                      placeholder="Optional remarks..."></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-danger btn-sm me-auto" id="cancelDayoff">Cancel Request</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveDayoff">Save</button>
            </div>

        </div>
    </div>
</div>


<!-- ======================================================================
     5. SCRIPTS
     ====================================================================== -->
<script src="<?= base_url('assets/js/clock.js'); ?>"></script>

<!-- PHP-driven config: the ONLY place the JS needs server-side values -->
<script>
window.ATTENDANCE_CONFIG = {
    baseUrl: '<?= base_url() ?>',
    api: {
        load:              '<?= site_url('api/attendance/load') ?>',
        events:            '<?= site_url('api/attendance/events') ?>',
        save:              '<?= site_url('api/attendance/save') ?>',
        upload:            '<?= site_url('api/attendance/upload') ?>',
        deleteGroup:       '<?= site_url('api/attendance/delete-group') ?>',
        cancelOpenEntry:   '<?= site_url('api/attendance/cancel-open-entry') ?>',
        leaves:            '<?= site_url('api/attendance/leaves') ?>',
        holidays:          '<?= site_url('api/attendance/holidays') ?>',
        dayoffs:           '<?= site_url('api/attendance/dayoffs') ?>',
        attendanceDates:   '<?= site_url('api/attendance/attendance-dates') ?>',
        saveDayoff:        '<?= site_url('api/attendance/save-dayoff') ?>',
        cancelDayoff:      '<?= site_url('api/attendance/cancel-dayoff') ?>',
        leaveBlockedHours: '<?= site_url('api/attendance/leave-blocked-hours') ?>'
    }
};
</script>

<!-- Attendance modules (created in the JS step; order matters) -->
<script src="<?= base_url('assets/js/attendance/config.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/helpers.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/alerts.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/location.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/calendar.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/day-off.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/modal-rows.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/attachments.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/modal-load.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/modal-save.js'); ?>"></script>
<script src="<?= base_url('assets/js/attendance/boot.js'); ?>"></script>