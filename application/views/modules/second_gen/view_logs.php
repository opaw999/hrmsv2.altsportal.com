<style>
/* ============================================================
   TABLE OF CONTENTS
   1. Modals — shared scrollable layout
   2. Modals — card design (leave + entries)
   3. Loading overlay
   4. Prev-DTR dropdowns
   5. Mobile DTR cards
   6. Responsive — Mobile (≤575.98px)
   7. Responsive — Tablet (576px – 991.98px)
   ============================================================ */


/* ============================================================
   MODALS — shared scrollable layout (mobile-safe)
   ============================================================ */
.log-modal-dialog {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);

    max-height: 85vh;         /* fallback for old browsers */
    max-height: 85dvh;        /* dynamic viewport height — mobile-safe */
}

.log-modal-dialog .modal-content {
    width: 100%;

    max-height: 85vh;
    max-height: 85dvh;

    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.log-modal-dialog .modal-header,
.log-modal-dialog .modal-footer {
    flex-shrink: 0;
}

.log-modal-dialog .modal-body {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;   /* smooth iOS scroll */
    flex: 1 1 auto;
    min-height: 0;                        /* critical for flex scroll */
}

.log-modal-dialog .modal-body::-webkit-scrollbar { width: 8px; }
.log-modal-dialog .modal-body::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 8px;
}
.log-modal-dialog .modal-body::-webkit-scrollbar-thumb {
    background: #cbd3da;
    border-radius: 8px;
}
.log-modal-dialog .modal-body::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}


/* ============================================================
   2. MODALS — CARD DESIGN (leave + entries)
   ============================================================ */

/* --- Header accent --- */
#leaveModal .modal-header,
#entriesModal .modal-header {
    background: linear-gradient(135deg, #fff9f4, #fff);
    border-bottom-color: #f1e9e0 !important;
}

/* --- Card base --- */
.leave-card {
    position: relative;
    padding: 14px 16px 14px 18px;
    margin-bottom: 10px;
    background: #fff;
    border: 1px solid #eef0f3;
    border-radius: 14px;
    box-shadow: 0 1px 2px rgba(15,23,42,.04);
    transition: box-shadow .18s, transform .18s;
    overflow: hidden;
}
.leave-card:hover {
    box-shadow: 0 4px 14px rgba(15,23,42,.07);
    transform: translateY(-1px);
}
.leave-card::before {
    content: '';
    position: absolute;
    top: 12px; bottom: 12px; left: 0;
    width: 4px;
    border-radius: 0 4px 4px 0;
    background: #cbd5e1;
}
.leave-card.is-pending::before  { background: linear-gradient(180deg,#fbbf24,#f59e0b); }
.leave-card.is-approved::before { background: linear-gradient(180deg,#34d399,#10b981); }
.leave-card.is-rejected::before { background: linear-gradient(180deg,#f87171,#ef4444); }
.leave-card.is-done::before     { background: linear-gradient(180deg,#60a5fa,#3b82f6); }

/* --- Icon circle --- */
.leave-icon {
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 10px;
    background: #f1f5f9;
    color: #475569;
    flex-shrink: 0;
}
.leave-card.is-pending  .leave-icon { background: #fef3c7; color: #b45309; }
.leave-card.is-approved .leave-icon { background: #d1fae5; color: #047857; }
.leave-card.is-rejected .leave-icon { background: #fee2e2; color: #b91c1c; }
.leave-card.is-done     .leave-icon { background: #dbeafe; color: #1d4ed8; }

/* --- Segment type icon colors (entries modal) --- */
.leave-card.is-regular   .leave-icon { background: #e0e7ff; color: #4338ca; }
.leave-card.is-outoffice .leave-icon { background: #cffafe; color: #0e7490; }
.leave-card.is-fieldwork .leave-icon { background: #dcfce7; color: #15803d; }
.leave-card.is-overtime  .leave-icon { background: #fef3c7; color: #b45309; }

/* --- Text --- */
.leave-title {
    font-size: .9rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.2;
}
.leave-sub {
    font-size: .68rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.leave-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 14px;
    margin-top: 10px;
    font-size: .78rem;
    color: #475569;
}
.leave-meta i { color: #94a3b8; }

/* --- Pills & chips --- */
.leave-pill,
.leave-hours {
    font-size: .72rem;
    font-weight: 500;
    padding: 3px 9px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #334155;
}
.leave-hours {
    font-weight: 700;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}

/* --- Status badge --- */
.leave-status {
    font-size: .62rem;
    font-weight: 600;
    letter-spacing: .4px;
    text-transform: uppercase;
    padding: 3px 9px;
    border-radius: 999px;
    border: 1px solid transparent;
    background: #f1f5f9;
    color: #475569;
}
.leave-status.is-pending  { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.leave-status.is-approved { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.leave-status.is-rejected { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
.leave-status.is-done     { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }

/* --- Remarks --- */
.leave-remarks {
    margin-top: 10px;
    padding: 8px 11px;
    background: #f8fafc;
    border-radius: 10px;
    border-left: 3px solid #e2e8f0;
    font-size: .78rem;
    font-style: italic;
    color: #64748b;
}

/* --- Location --- */
.leave-location {
    margin-top: 8px;
    font-size: .78rem;
    color: #475569;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}
.leave-location i {
    color: #ef4444;
    margin-top: 2px;
}

/* --- Approver footer --- */
.leave-approver {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px dashed #e2e8f0;
    font-size: .72rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}
.leave-approver i { color: #10b981; }

/* --- Empty state --- */
.leave-empty {
    text-align: center;
    color: #94a3b8;
    padding: 40px 16px;
    font-size: .85rem;
}
.leave-empty i {
    font-size: 2rem;
    display: block;
    margin-bottom: 8px;
    opacity: .6;
}


/* ============================================================
   3. LOADING OVERLAY
   ============================================================ */
.log-content-card {
    position: relative;
}
.log-loader-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 10px;
    z-index: 10;
    border-radius: 1rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.18s ease;
}
.log-loader-overlay.is-visible {
    opacity: 1;
    pointer-events: all;
}
.log-loader-overlay .spinner-border {
    width: 2.25rem;
    height: 2.25rem;
    border-width: 0.22rem;
    color: var(--primary-orange, #f97316);
}
.log-loader-overlay .loader-text {
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    letter-spacing: 0.3px;
}


/* ============================================================
   4. PREV-DTR DROPDOWNS
   ============================================================ */
.prev-dtr-filters {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}
.prev-dtr-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    padding: 7px 34px 7px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background-color: #fff;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2 4l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;

    font-size: 0.82rem;
    font-weight: 500;
    color: #1e293b;
    cursor: pointer;
    width: auto;
    min-width: 120px;
    max-width: 320px;
    transition: border-color .15s, box-shadow .15s, transform .15s;
}
.prev-dtr-select:hover {
    border-color: #cbd5e1;
}
.prev-dtr-select:focus {
    outline: none;
    border-color: var(--primary-orange, #f97316);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}
.prev-dtr-select option {
    padding: 6px;
    font-size: 0.85rem;
}


/* ============================================================
   5. MOBILE DTR CARDS
   ============================================================ */
#mobileLogContainer {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.mob-card {
    position: relative;
    background: #fff;
    border: 1px solid #eef0f3;
    border-radius: 14px;
    padding: 12px 14px 12px 16px;
    overflow: hidden;
    transition: box-shadow .15s;
}
.mob-card:hover {
    box-shadow: 0 3px 12px rgba(15,23,42,.06);
}
.mob-card::before {
    content: '';
    position: absolute;
    top: 10px; bottom: 10px; left: 0;
    width: 4px;
    border-radius: 0 4px 4px 0;
    background: transparent;
    transition: background .2s;
}
.mob-card.is-approved::before { background: linear-gradient(180deg,#34d399,#10b981); }
.mob-card.is-pending::before  { background: linear-gradient(180deg,#fbbf24,#f59e0b); }
.mob-card.is-rejected::before { background: linear-gradient(180deg,#f87171,#ef4444); }

/* --- Header row --- */
.mob-row-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
}
.mob-date {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.15;
}
.mob-day {
    font-size: 0.7rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

/* --- Hours block --- */
.mob-hw-block {
    text-align: right;
    padding: 4px 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    min-width: 62px;
}
.mob-hw-value {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
}
.mob-hw-label {
    font-size: 0.6rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

/* --- Tags --- */
.mob-tag {
    font-size: 0.62rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 3px 9px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid transparent;
    white-space: nowrap;
}
.mob-tag.is-regular { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.mob-tag.is-warning { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.mob-tag.is-holiday { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
.mob-tag.is-dayoff  { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }

/* --- Punch grid --- */
.mob-punch-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 4px;
    padding: 8px 4px;
    background: #f8fafc;
    border-radius: 10px;
}
.mob-punch {
    text-align: center;
    padding: 4px 2px;
}
.mob-punch-label {
    font-size: 0.55rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 3px;
}
.mob-punch-value {
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1.1;
}
.mob-punch-value.is-in  { color: #059669; }
.mob-punch-value.is-out { color: #dc2626; }

/* --- No-entry message --- */
.mob-no-entry {
    padding: 10px;
    background: #fef3c7;
    border-radius: 10px;
    font-size: 0.74rem;
    color: #92400e;
    text-align: center;
    font-style: italic;
}

/* --- Leave badges area --- */
.mob-badges {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #e2e8f0;
}

/* --- Holiday --- */
.mob-holiday-name {
    padding: 8px 10px;
    background: #fef2f2;
    border-radius: 10px;
    font-size: 0.78rem;
    color: #b91c1c;
    font-weight: 500;
}
.mob-card.is-holiday {
    border-color: #fecaca;
}
.mob-card.is-holiday::before {
    background: linear-gradient(180deg,#f87171,#ef4444);
}

/* --- Day off --- */
.mob-card.is-dayoff {
    opacity: 0.85;
}
.mob-card.is-dayoff::before {
    background: #cbd5e1;
}
.mob-dayoff-label {
    padding: 8px;
    text-align: center;
    font-size: 0.78rem;
    color: #94a3b8;
    font-style: italic;
}

/* --- Action button --- */
.mob-action {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    margin-top: 10px;
    padding: 8px 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, border-color .15s, color .15s;
}
.mob-action:hover {
    background: var(--primary-orange, #f97316);
    border-color: var(--primary-orange, #f97316);
    color: #fff;
}
.mob-action:active {
    transform: scale(0.98);
}


/* ============================================================
   6. RESPONSIVE — MOBILE (≤575.98px)
   ============================================================ */
@media (max-width: 575.98px) {

    /* --- Modal sizing --- */
    .log-modal-dialog,
    .log-modal-dialog .modal-content {
        max-height: 92vh;
        max-height: 92dvh;
    }
    .log-modal-dialog {
        margin: 0.5rem auto;
        min-height: calc(100% - 1rem);
    }
    .log-modal-dialog .modal-body {
        padding: 0.75rem !important;
    }

    /* --- Heading --- */
    #page-view-logs h5 {
        font-size: 1.05rem;
        margin-bottom: 2px !important;
    }
    #page-view-logs > div > small {
        display: none;
    }

    /* --- Cutoff filters — 2×2 grid --- */
    .log-filter-bar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        background: transparent;
        padding: 0;
        border-radius: 0;
        width: 100%;
    }
    .log-filter-bar .log-filter-btn {
        width: 100%;
        justify-content: center;
        padding: 10px 8px;
        font-size: 0.72rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #475569;
    }
    .log-filter-bar .log-filter-btn.active {
        background: var(--primary-orange, #f97316);
        color: #fff;
        border-color: var(--primary-orange, #f97316);
        box-shadow: 0 4px 10px rgba(249, 115, 22, 0.25);
    }
    .log-filter-bar .log-filter-btn i {
        font-size: 0.78rem;
    }

    /* --- Content card --- */
    .log-content-card {
        padding: 0.75rem !important;
        border-radius: 14px !important;
    }
    #activePeriodLabel {
        font-size: 0.78rem !important;
        line-height: 1.3;
    }

    /* --- Full-width View Segments button --- */
    #mobileLogContainer .btn-view-entries {
        width: 100%;
        justify-content: center;
        padding: 8px 12px;
    }

    /* --- Prev-DTR dropdowns --- */
    .prev-dtr-filters {
        width: 100%;
        justify-content: stretch;
    }
    .prev-dtr-select {
        flex: 1 1 auto;
        min-width: 0;
        max-width: none;
        font-size: 0.72rem;
        line-height: 1.2;
    }
    .prev-dtr-select option {
        font-size: 0.78rem;
        padding: 6px 10px;
        line-height: 1.3;
        color: #1e293b;
        background: #fff;
    }

    /* --- Summary cards --- */
    .log-stat-card {
        padding: 0.3rem 0.5rem;
        gap: 0.4rem !important;
    }
    .log-stat-card .small {
        font-size: 0.42rem !important;
        letter-spacing: 0;
        line-height: 1;
    }
    .log-stat-card .fs-5 {
        font-size: 0.7rem !important;
        line-height: 1.05;
    }
    .log-stat-card .log-stat-icon {
        width: 20px;
        height: 20px;
        font-size: 0.58rem;
        border-radius: 6px;
    }
}


/* ============================================================
   7. RESPONSIVE — TABLET (576px – 991.98px)
   ============================================================ */
@media (min-width: 576px) and (max-width: 991.98px) {

    /* --- Filter bar --- */
    .log-filter-bar {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        background: transparent;
        padding: 0;
        border-radius: 0;
        justify-content: flex-start;
    }
    .log-filter-bar .log-filter-btn {
        flex: 0 1 auto;
        font-size: 0.75rem;
        padding: 7px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #475569;
    }
    .log-filter-bar .log-filter-btn.active {
        background: var(--primary-orange, #f97316);
        color: #fff;
        border-color: var(--primary-orange, #f97316);
        box-shadow: 0 4px 10px rgba(249, 115, 22, 0.22);
    }

    /* --- Heading typography --- */
    #page-view-logs h5 {
        font-size: 1.1rem;
    }
    #page-view-logs > div > small {
        font-size: 0.72rem;
    }
}
</style>


<!-- ============================================================
     TOP FIXED HEADER
     ============================================================ -->
<div class="header-wrapper">
  <header class="app-header-box d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-sm btn-light border d-lg-none py-1 px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer">
        <i class="bi bi-list fs-6"></i>
      </button>
      <div>
        <h1 class="h6 fw-bold mb-0 text-dark" id="headerTitle"><?= $page_title; ?></h1>
        <p class="text-muted mb-0 d-none d-sm-block" style="font-size: 0.72rem;" id="headerSubtitle">
          <?= $page_sub_title; ?>
        </p>
      </div>
    </div>

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


<!-- ============================================================
     SCROLLABLE MAIN CONTENT
     ============================================================ -->
<div class="content-scrollable">
  <div class="view-page active-view" id="page-view-logs">

    <!-- Page header + period filter -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-3">
      <div>
        <h5 class="fw-bold text-dark mb-0">Attendance Log History</h5>
        <small class="text-muted" style="font-size: 0.75rem;">View and track your weekly and monthly time records</small>
      </div>

      <div class="log-filter-bar" id="logPeriodFilters">
        <button type="button" class="log-filter-btn active" data-period="first-cutoff">
          <i class="bi bi-calendar-week me-1"></i>First Cutoff
        </button>
        <button type="button" class="log-filter-btn" data-period="second-cutoff">
          <i class="bi bi-calendar-week me-1"></i>Second Cutoff
        </button>
        <button type="button" class="log-filter-btn" data-period="third-cutoff">
          <i class="bi bi-calendar-week me-1"></i>Third Cutoff
        </button>
        <button type="button" class="log-filter-btn" data-period="prev-dtrs">
          <i class="bi bi-calendar-week me-1"></i>Previous DTRs
        </button>
      </div>
    </div>

    <!-- Summary metric cards -->
    <div class="row g-2 mb-3">
      <div class="col-4">
        <div class="log-stat-card shadow-sm d-flex align-items-center gap-3">
          <div class="log-stat-icon" style="background: var(--primary-orange-light); color: var(--primary-orange);">
            <i class="bi bi-clock-history"></i>
          </div>
          <div>
            <div class="text-muted small fw-semibold" style="font-size: 0.68rem; text-transform: uppercase;">Total Hours</div>
            <div class="fw-bold text-dark fs-5" id="summaryTotalHours">—</div>
          </div>
        </div>
      </div>

      <div class="col-4">
        <div class="log-stat-card shadow-sm d-flex align-items-center gap-3">
          <div class="log-stat-icon bg-success bg-opacity-10 text-success">
            <i class="bi bi-calendar-check"></i>
          </div>
          <div>
            <div class="text-muted small fw-semibold" style="font-size: 0.68rem; text-transform: uppercase;">Total Leaves</div>
            <div class="fw-bold text-dark fs-5" id="summaryLeaveHours">0</div>
          </div>
        </div>
      </div>

      <div class="col-4">
        <div class="log-stat-card shadow-sm d-flex align-items-center gap-3">
          <div class="log-stat-icon bg-danger bg-opacity-10 text-danger">
            <i class="bi bi-dash-circle"></i>
          </div>
          <div>
            <div class="text-muted small fw-semibold" style="font-size: 0.68rem; text-transform: uppercase;">Total Deductions</div>
            <div class="fw-bold text-dark fs-5" id="summaryDeduction">0</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Log content card -->
    <div class="bg-white border rounded-4 shadow-sm p-3 log-content-card">

        <!-- Loading overlay -->
        <div class="log-loader-overlay" id="logLoaderOverlay">
          <div class="spinner-border" role="status" aria-hidden="true"></div>
          <div class="loader-text">Loading DTR entries...</div>
        </div>

        <!-- Header row: active period + dropdowns -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 pb-2 border-bottom">
          <div class="fw-bold text-dark" style="font-size: 0.9rem;" id="activePeriodLabel">
            <i class="bi bi-calendar4-range me-1" style="color: var(--primary-orange);"></i>
            Loading period...
          </div>

            <div class="prev-dtr-filters" id="prevDtrFilters">
                <select id="yearSelect" class="prev-dtr-select" style="display: none;">
                    <!-- Years will be populated by JavaScript -->
                </select>
                <select id="cutoff" class="prev-dtr-select" style="display: none;">
                    <!-- Cutoffs will be populated by JavaScript -->
                </select>
            </div>
        </div>

        <!-- Desktop table -->
        <div class="table-responsive d-none d-md-block">
          <div class="panel panel-default" style="width:100%; margin:0 auto; font-family:Calibri;">
            <div class="panel-body">
              <table class="table table-bordered table-striped table-hover" id="tbl_OTA" cellspacing="0" width="100%">
                <thead>
                  <tr style="text-align:center; font-weight:bold; font-size:100%;" class="info">
                    <td>DATE</td>
                    <td>DAY</td>
                    <td>IN 1</td>
                    <td>OUT 1</td>
                    <td>IN 2</td>
                    <td>OUT 2</td>
                    <td>HW</td>
                    <td>LEAVE REQUESTS</td>
                    <td>INFO</td>
                  </tr>
                </thead>
                <tbody id="desktopLogTableBody" style="text-align:center;"></tbody>
                <tfoot>
                  <tr style="text-align:center; font-weight:bold; font-size:90%;" class="info">
                    <td colspan="6" style="text-align:right; padding-right:25px;">T O T A L</td>
                    <td id="totalHoursCell">0 hrs</td>
                    <td id="totalLeavesCell">0 hrs</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <!-- Mobile cards -->
        <div class="d-flex d-md-none flex-column gap-2" id="mobileLogContainer"></div>

    </div>  <!-- ✅ card closes HERE, after everything -->

  </div>
</div>


<!-- ============================================================
     MODAL: Daily Attendance Segments
     ============================================================ -->
<div class="modal fade" id="entriesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg log-modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom">
        <div>
          <h6 class="modal-title fw-bold mb-0">
            <i class="bi bi-list-check me-1" style="color: var(--primary-orange);"></i>
            Attendance Segments
          </h6>
          <small class="text-muted" id="entriesModalDate" style="font-size:0.75rem;">—</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3" id="entriesModalBody">
        <div class="text-center text-muted py-5">
          <div class="spinner-border spinner-border-sm me-2"></div>Loading...
        </div>
      </div>
      <div class="modal-footer border-top">
        <small class="text-muted me-auto" id="entriesModalCount" style="font-size:0.72rem;">—</small>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     MODAL: Leave Details
     ============================================================ -->
<div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg log-modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom">
        <div>
          <h6 class="modal-title fw-bold mb-0">
            <i class="bi bi-calendar-check me-1" style="color: var(--primary-orange);"></i>
            Leave Details
          </h6>
          <small class="text-muted" id="leaveModalSubtitle" style="font-size:0.75rem;">—</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3" id="leaveModalBody">
        <div class="text-center text-muted py-5">
          <div class="spinner-border spinner-border-sm me-2"></div>Loading...
        </div>
      </div>
      <div class="modal-footer border-top">
        <small class="text-muted me-auto" id="leaveModalFooter" style="font-size:0.72rem;"></small>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script src="<?= base_url('assets/js/clock.js'); ?>"></script>
<script>

// ============================================================
// MODAL CLEANUP — prevent stale backdrops + body lock
// ============================================================

// 1. When ANY modal fully hides, if no other modal is visible,
//    clean up Bootstrap's leftover body styles + backdrops.
document.querySelectorAll('.modal').forEach(function (el) {
    el.addEventListener('hidden.bs.modal', function () {
        if (!document.querySelector('.modal.show')) {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
        }
    });
});

// 2. Global helper — close any open modal before opening a new one.
window.closeAllModals = function () {
    document.querySelectorAll('.modal.show').forEach(function (el) {
        var inst = bootstrap.Modal.getInstance(el);
        if (inst) inst.hide();
    });

    // Immediately scrub leftover artefacts so the new modal starts clean
    if (!document.querySelector('.modal.show')) {
        document.querySelectorAll('.modal-backdrop').forEach(function (b) {
            b.remove();
        });
    }
};

// 3. Safety net — if the user ever ends up with a locked body and
//    no visible modal, tapping anywhere resets the state.
document.addEventListener('click', function () {
    if (!document.querySelector('.modal.show')
        && document.body.classList.contains('modal-open')) {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        document.querySelectorAll('.modal-backdrop').forEach(function (b) {
            b.remove();
        });
    }
});
// ============================================================
// 1. HELPERS
// ============================================================

function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// "2026 Sep 24" -> "2026-09-24" (no Date object → no timezone shift)
function toISO(str) {
    const bits = String(str).trim().split(/\s+/);
    if (bits.length < 3) return null;
    const year = bits[0], monthName = bits[1], day = bits[2].padStart(2, '0');
    const months = {
        Jan:'01', Feb:'02', Mar:'03', Apr:'04', May:'05', Jun:'06',
        Jul:'07', Aug:'08', Sep:'09', Oct:'10', Nov:'11', Dec:'12'
    };
    const mm = months[monthName];
    return mm ? year + '-' + mm + '-' + day : null;
}

// "08:00:00" -> "08:00 AM"
function formatTime(t) {
    if (!t) return '';
    const parts = String(t).split(':');
    if (parts.length < 2) return t;
    let h = parseInt(parts[0], 10);
    const m = parts[1];
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return String(h).padStart(2, '0') + ':' + m + ' ' + ampm;
}

// "2026-09-11 14:49:04" -> "Sep 11, 2026 02:49 PM"
function formatDateTime(dt) {
    if (!dt) return '—';
    const d = new Date(String(dt).replace(' ', 'T'));
    if (isNaN(d.getTime())) return dt;
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    let h = d.getHours();
    const m = String(d.getMinutes()).padStart(2, '0');
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear()
         + ' ' + String(h).padStart(2, '0') + ':' + m + ' ' + ampm;
}

// "2026-09-05" -> "Saturday"
function dayName(yyyymmdd) {
    if (!yyyymmdd) return '';
    const d = new Date(yyyymmdd + 'T00:00:00');
    if (isNaN(d.getTime())) return '';
    return d.toLocaleDateString('en-US', { weekday: 'long' });
}

// Render leave badges (whole-day = red, hourly = yellow)
function renderLeaveBadges(badges) {
    if (!badges || badges.length === 0) return '';

    return badges.map(function (b) {
        const hasId     = (b.leave_id !== undefined && b.leave_id !== null && b.leave_id !== '' && b.leave_id !== 0);
        const idAttr    = hasId ? ' data-leave-id="' + esc(b.leave_id) + '"' : '';
        const clickable = hasId ? ' btn-view-leave' : '';
        const cursor    = hasId ? 'cursor:pointer;' : 'cursor:default;';

        // --- Status → color class ---
        const status = String(b.status || '').toLowerCase();
        let colorCls = 'bg-secondary bg-opacity-10 text-secondary border-secondary';
        let statusLabel = 'Unknown';

        if (status === 'approved') {
            colorCls    = 'bg-success bg-opacity-10 text-success border-success';
            statusLabel = 'Approved';
        } else if (status === 'pending') {
            colorCls    = 'bg-warning bg-opacity-10 text-warning border-warning';
            statusLabel = 'Pending';
        } else if (status === 'rejected' || status === 'disapproved') {
            colorCls    = 'bg-danger bg-opacity-10 text-danger border-danger';
            statusLabel = 'Disapproved';
        }

        // --- Payment suffix ---
        const payment = String(b.payment || '').toLowerCase();
        let paymentTxt = '';
        if (payment === 'paid') {
            paymentTxt = ' <span class="badge-payment is-paid">Paid</span>';
        } else if (payment === 'unpaid') {
            paymentTxt = ' <span class="badge-payment is-unpaid">Unpaid</span>';
        }
        // If payment is empty/unknown → no suffix

        const title  = hasId ? 'Click to view leave details • ' + statusLabel : 'Leave details unavailable';
        const iconCls = (b.type === 'wholeday') ? 'calendar-x' : 'clock-history';

        return '<span class="badge ' + colorCls + ' border rounded-pill me-1 mb-1'
             + clickable + '" style="font-size:0.65rem;' + cursor + '" '
             + 'title="' + title + '"' + idAttr + '>'
             + '<i class="bi bi-' + iconCls + ' me-1"></i>'
             + esc(b.label)
             + paymentTxt
             + '</span>';
    }).join('');
}


// ============================================================
// 2. CUTOFF MENU
// ============================================================

function get_cutoff_menu() {
    $.ajax({
        url: "<?= base_url('api/viewlogs/get_cutoff_menu'); ?>",
        type: "POST",
        dataType: "json",
        success: function (response) {
            if (!response.success) { console.error(response.message); return; }

            $('#logPeriodFilters [data-period="first-cutoff"]')
                .html('<i class="bi bi-calendar-week me-1"></i>' + response.menu.first);
            $('#logPeriodFilters [data-period="second-cutoff"]')
                .html('<i class="bi bi-calendar-week me-1"></i>' + response.menu.second);
            $('#logPeriodFilters [data-period="third-cutoff"]')
                .html('<i class="bi bi-calendar-week me-1"></i>' + response.menu.third);

            $('#logPeriodFilters [data-period="first-cutoff"]').trigger('click');
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', status, error);
        }
    });
}

// Cutoff button click → parse dates → fetch entries
$(document).on('click', '.log-filter-btn', function () {
    const period = $(this).data('period');

    if( period === 'prev-dtrs') {
        $('#yearSelect').show();
        $('#cutoff').show();
        populatePreviousCutoffOptions();
    } else {
        const rawLabel = $(this).clone().children().remove().end().text().trim();
        const parts = rawLabel.split(' to ');
        if (parts.length !== 2) { console.warn('Bad label:', rawLabel); return; }

        const startDate = toISO(parts[0]);
        const endDate   = toISO(parts[1]);
        if (!startDate || !endDate) { console.error('Parse failed:', rawLabel); return; }
        $('#yearSelect').hide();
        $('#cutoff').hide();
        get_cutoff_entries(startDate, endDate);
    }
    $('.log-filter-btn').removeClass('active');
    $(this).addClass('active');
});


// ============================================================
// 3. MAIN FETCH
// ============================================================

function get_cutoff_entries(startDate, endDate) {
    // Show overlay + skeleton table
    $('#logLoaderOverlay').addClass('is-visible');
    $('#desktopLogTableBody').html(
        '<tr><td colspan="9" class="text-center text-muted py-4" style="visibility:hidden;">.</td></tr>'
    );

    $.ajax({
        url: "<?= base_url('api/viewlogs/get_cutoff_entries'); ?>",
        type: "POST",
        dataType: "json",
        data: { start_date: startDate, end_date: endDate },

        success: function (response) {
            if (!response.success) {
                console.error(response.message);
                $('#desktopLogTableBody').html(
                    '<tr><td colspan="9" class="text-center text-danger py-4">'
                    + esc(response.message || 'Failed to load entries.')
                    + '</td></tr>'
                );
                return;
            }

            renderDesktopTable(response.rows, {
                hours:      response.total_hours,
                leaveHours: response.total_leave_hours
            });
            renderMobileCards(response.rows);

            // Safely extract cutoff data (array-of-rows OR object)
            const cutoff = Array.isArray(response.cutoff_summary)
                ? (response.cutoff_summary[0] || {})
                : (response.cutoff_summary || {});

            $('#summaryTotalHours').text((parseFloat(cutoff.co_hours) || 0) + ' hrs');
            $('#summaryLeaveHours').text((parseFloat(cutoff.co_leaves) || 0) + ' hrs');
            $('#summaryDeduction').text((parseFloat(cutoff.deficit_hours) || 0) + ' hrs');

            const label = response.start_date + ' to ' + response.end_date;
            $('#activePeriodLabel').html(
                '<i class="bi bi-calendar4-range me-1" style="color: var(--primary-orange);"></i> '
                + label + ' Daily Time Record '
            );
        },

        error: function (xhr, status, error) {
            console.error('AJAX error:', status, error);
            $('#desktopLogTableBody').html(
                '<tr><td colspan="9" class="text-center text-danger py-4">'
                + 'Network error. Please try again.'
                + '</td></tr>'
            );
        },

        complete: function () {
            // Always hide the overlay, success or fail
            $('#logLoaderOverlay').removeClass('is-visible');
        }
    });
}

// ============================================================
// 4. DESKTOP TABLE RENDER
// ============================================================

function renderDesktopTable(rows, totals) {
    let html = '';

    rows.forEach(function (r) {
        let cells = '';

        if (r.is_holiday) {
            cells = '<td colspan="5" style="color:red;font-style:italic;text-align:center;">'
                  + esc(r.holiday) + '</td>';
        } else if (r.is_day_off && !r.has_entry) {
            cells = '<td colspan="5" style="color:red;font-style:italic;text-align:center;">'
                  + '<i>Day Off</i></td>';
        } else {
            cells = ''
                + '<td>' + formatTime(r.in1)  + '</td>'
                + '<td>' + formatTime(r.out1) + '</td>'
                + '<td>' + formatTime(r.in2)  + '</td>'
                + '<td>' + formatTime(r.out2) + '</td>'
                + '<td>' + (r.hours_worked !== '' ? r.hours_worked : '') + '</td>';
        }

        const badgesHtml = renderLeaveBadges(r.leave_badges);

        html += '<tr class="trHover" style="font-size:90%;">'
             +  '<td>' + esc(r.date_label) + '</td>'
             +  '<td>' + esc(r.day) + '</td>'
             +  cells
             +  '<td style="vertical-align:middle;">' + badgesHtml + '</td>'
             +  '<td>'
             +    (r.has_entry
                    ? '<span class="bi bi-info-circle btn-view-entries" '
                      + 'style="cursor:pointer;color:#0d6efd;font-size:1rem;" '
                      + 'title="View attendance segments" '
                      + 'data-date="' + esc(r.date) + '"></span>'
                    : '')
             +  '</td>'
             +  '</tr>';
    });

    $('#desktopLogTableBody').html(html);
    $('#totalHoursCell').text(totals.hours + ' hrs');
    $('#totalLeavesCell').text(totals.leaveHours + ' hrs');
}


// ============================================================
// 5. MOBILE CARD RENDER
// ============================================================

function renderMobileCards(rows) {
    let html = '';

    rows.forEach(function (r) {
        const badgesHtml = renderLeaveBadges(r.leave_badges);

        // Determine dominant leave status (for the left accent bar)
        let accent = '';
        if (r.leave_badges && r.leave_badges.length > 0) {
            const statuses = r.leave_badges.map(function (b) {
                return String(b.status || '').toLowerCase();
            });
            if (statuses.indexOf('rejected') >= 0 || statuses.indexOf('disapproved') >= 0) {
                accent = 'is-rejected';
            } else if (statuses.indexOf('pending') >= 0) {
                accent = 'is-pending';
            } else if (statuses.indexOf('approved') >= 0) {
                accent = 'is-approved';
            }
        }

        // ---------- Holiday ----------
        if (r.is_holiday) {
            html += '<div class="mob-card is-holiday">'
                 +    '<div class="mob-row-top">'
                 +      '<div>'
                 +        '<div class="mob-date">' + esc(r.date_label) + '</div>'
                 +        '<div class="mob-day">' + esc(r.day) + '</div>'
                 +      '</div>'
                 +      '<span class="mob-tag is-holiday">Holiday</span>'
                 +    '</div>'
                 +    '<div class="mob-holiday-name">'
                 +      '<i class="bi bi-flag-fill me-1"></i>' + esc(r.holiday)
                 +    '</div>'
                 +    (badgesHtml ? '<div class="mob-badges">' + badgesHtml + '</div>' : '')
                 +  '</div>';
            return;
        }

        // ---------- Day Off ----------
        if (r.is_day_off && !r.has_entry) {
            html += '<div class="mob-card is-dayoff">'
                 +    '<div class="mob-row-top">'
                 +      '<div>'
                 +        '<div class="mob-date">' + esc(r.date_label) + '</div>'
                 +        '<div class="mob-day">' + esc(r.day) + '</div>'
                 +      '</div>'
                 +      '<span class="mob-tag is-dayoff">Day Off</span>'
                 +    '</div>'
                 +    '<div class="mob-dayoff-label">Rest Day</div>'
                 +    (badgesHtml ? '<div class="mob-badges">' + badgesHtml + '</div>' : '')
                 +  '</div>';
            return;
        }

        // ---------- Regular / has-entry ----------
        const in1  = formatTime(r.in1);
        const out1 = formatTime(r.out1);
        const in2  = formatTime(r.in2);
        const out2 = formatTime(r.out2);
        const hw   = r.hours_worked !== '' ? parseFloat(r.hours_worked).toFixed(2) : '0.00';

        const statusTag = r.has_entry
            ? '<span class="mob-tag is-regular">Regular</span>'
            : '<span class="mob-tag is-warning">No Entry</span>';

        html += '<div class="mob-card ' + accent + '">'

             // Header: date / day / status
             +    '<div class="mob-row-top">'
             +      '<div>'
             +        '<div class="mob-date">' + esc(r.date_label) + '</div>'
             +        '<div class="mob-day">' + esc(r.day) + '</div>'
             +      '</div>'
             +      '<div class="mob-hw-block">'
             +        '<div class="mob-hw-value">' + hw + '</div>'
             +        '<div class="mob-hw-label">hours</div>'
             +      '</div>'
             +    '</div>';

        // Punch grid (4 columns)
        if (r.has_entry) {
            html += '<div class="mob-punch-grid">'
                 +    '<div class="mob-punch">'
                 +      '<div class="mob-punch-label">IN 1</div>'
                 +      '<div class="mob-punch-value is-in">' + (in1 || '—') + '</div>'
                 +    '</div>'
                 +    '<div class="mob-punch">'
                 +      '<div class="mob-punch-label">OUT 1</div>'
                 +      '<div class="mob-punch-value is-out">' + (out1 || '—') + '</div>'
                 +    '</div>'
                 +    '<div class="mob-punch">'
                 +      '<div class="mob-punch-label">IN 2</div>'
                 +      '<div class="mob-punch-value is-in">' + (in2 || '—') + '</div>'
                 +    '</div>'
                 +    '<div class="mob-punch">'
                 +      '<div class="mob-punch-label">OUT 2</div>'
                 +      '<div class="mob-punch-value is-out">' + (out2 || '—') + '</div>'
                 +    '</div>'
                 +  '</div>';
        } else {
            html += '<div class="mob-no-entry">No time entries recorded</div>';
        }

        // Leave badges
        if (badgesHtml) {
            html += '<div class="mob-badges">' + badgesHtml + '</div>';
        }

        // Action button
        if (r.has_entry) {
            html += '<button type="button" class="mob-action btn-view-entries" '
                 +   'data-date="' + esc(r.date) + '">'
                 +   '<i class="bi bi-list-check me-1"></i>View Info'
                 + '</button>';
        }

        html += '</div>';
    });

    $('#mobileLogContainer').html(html);
}


// ============================================================
// 6. MODAL: ATTENDANCE SEGMENTS
// ============================================================

$(document).on('click', '.btn-view-entries', function () {
    const date = $(this).data('date');
    if (!date) return;

    const modalEl = document.getElementById('entriesModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    $('#entriesModalDate').text(dayName(date) + ', ' + date);
    $('#entriesModalCount').text('—');
    $('#entriesModalBody').html(
        '<div class="text-center text-muted py-5">'
        + '<div class="spinner-border spinner-border-sm me-2"></div>Loading...'
        + '</div>'
    );

    closeAllModals();
    modal.show();

    $.ajax({
        url: "<?= base_url('api/viewlogs/get_daily_entries'); ?>",
        type: "POST",
        dataType: "json",
        data: { date: date },

        success: function (response) {
            if (!response.success) {
                $('#entriesModalBody').html(
                    '<div class="text-center text-danger py-4">'
                    + esc(response.message || 'Failed to load entries.')
                    + '</div>'
                );
                return;
            }
            renderEntriesModal(response.entries);
        },

        error: function (xhr, status, error) {
            console.error('AJAX error:', status, error);
            $('#entriesModalBody').html(
                '<div class="text-center text-danger py-4">Network error. Please try again.</div>'
            );
        }
    });
});

function renderEntriesModal(entries) {
    if (!entries || entries.length === 0) {
        $('#entriesModalBody').html(
            '<div class="leave-empty">'
            + '<i class="bi bi-inbox"></i>'
            + 'No attendance segments recorded for this day.'
            + '</div>'
        );
        $('#entriesModalCount').text('0 segments');
        return;
    }

    const typeMeta = {
        regular:   { icon: 'building',      cls: 'is-regular'   },
        outoffice: { icon: 'geo-alt',       cls: 'is-outoffice' },
        fieldwork: { icon: 'map',           cls: 'is-fieldwork' },
        overtime:  { icon: 'clock-history', cls: 'is-overtime'  }
    };
    const statusIcons = {
        approved: 'patch-check-fill',
        pending:  'hourglass-split',
        rejected: 'x-circle-fill',
        done:     'check2-circle'
    };

    let html = '';

    entries.forEach(function (e, i) {
        const status    = String(e.status || '').toLowerCase();
        const statusMod = ['pending','approved','rejected','done'].indexOf(status) >= 0 ? status : '';
        const statusCls = statusMod ? 'is-' + statusMod : '';
        const statusTxt = e.status ? e.status.charAt(0).toUpperCase() + e.status.slice(1).toLowerCase() : '—';
        const statusIcon = statusIcons[statusMod] || 'file-text';

        const type    = String(e.type || '').toLowerCase();
        const meta    = typeMeta[type] || { icon: 'briefcase', cls: '' };
        const typeTxt = e.type ? e.type.charAt(0).toUpperCase() + e.type.slice(1) : 'Segment';
        const hours   = (parseFloat(e.total_hours) || 0).toFixed(2);

        html += '<div class="leave-card ' + statusCls + ' ' + meta.cls + '">'
             +   '<div class="d-flex align-items-start gap-3">'
             +     '<div class="leave-icon"><i class="bi bi-' + meta.icon + '"></i></div>'
             +     '<div class="flex-grow-1">'
             +       '<div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">'
             +         '<div>'
             +           '<div class="leave-title">' + esc(typeTxt) + '</div>'
             +           '<div class="leave-sub mt-1">Segment ' + (i + 1) + ' of ' + entries.length + '</div>'
             +         '</div>'
             +         '<div class="d-flex align-items-center gap-2">'
             +           '<span class="leave-status ' + statusCls + '">' + esc(statusTxt) + '</span>'
             +           '<span class="leave-hours">' + hours + ' hrs</span>'
             +         '</div>'
             +       '</div>'
             +     '</div>'
             +   '</div>'
             +   '<div class="leave-meta">'
             +     (e.time_range
                        ? '<span class="leave-pill"><i class="bi bi-clock me-1"></i>' + esc(e.time_range) + '</span>'
                        : '')
             +     (e.category
                        ? '<span><i class="bi bi-tag me-1"></i>' + esc(e.category) + '</span>'
                        : '')
             +   '</div>';

        if (e.location) {
            html += '<div class="leave-location">'
                 +    '<i class="bi bi-geo-alt"></i>'
                 +    '<span>' + esc(e.location) + '</span>'
                 +  '</div>';
        }

        if (e.remarks) {
            html += '<div class="leave-remarks">'
                 +    '<i class="bi bi-chat-left-quote me-1"></i>'
                 +    esc(e.remarks)
                 +  '</div>';
        }

        if (status === 'approved' && (e.approved_by || e.approved_at)) {
            html += '<div class="leave-approver">'
                 +    '<i class="bi bi-patch-check-fill"></i>'
                 +    '<span>Approved by <strong>' + esc(e.approved_by || '—') + '</strong>'
                 +    (e.approved_at ? ' • ' + formatDateTime(e.approved_at) : '')
                 +    '</span>'
                 +  '</div>';
        }

        html += '</div>';
    });

    $('#entriesModalBody').html(html);
    const n = entries.length;
    $('#entriesModalCount').text(n + ' segment' + (n === 1 ? '' : 's'));
}


// ============================================================
// 7. MODAL: LEAVE DETAILS
// ============================================================

$(document).on('click', '.btn-view-leave', function (e) {
    e.stopPropagation();

    const leaveId = $(this).data('leave-id');
    if (!leaveId) { console.warn('Badge has no leave_id.'); return; }

    const modalEl = document.getElementById('leaveModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    $('#leaveModalSubtitle').text('Loading...');
    $('#leaveModalFooter').text('');
    $('#leaveModalBody').html(
        '<div class="text-center text-muted py-5">'
        + '<div class="spinner-border spinner-border-sm me-2"></div>Loading...'
        + '</div>'
    );

    closeAllModals();
    modal.show();

    $.ajax({
        url: "<?= base_url('api/viewlogs/get_leave_details'); ?>",
        type: "POST",
        dataType: "json",
        data: { leave_id: leaveId },

        success: function (response) {
            if (!response.success) {
                $('#leaveModalBody').html(
                    '<div class="text-center text-danger py-4">'
                    + esc(response.message || 'Failed to load leave details.')
                    + '</div>'
                );
                return;
            }
            renderLeaveModal(response.leave);
        },

        error: function (xhr, status, error) {
            console.error('AJAX error:', status, error);
            $('#leaveModalBody').html(
                '<div class="text-center text-danger py-4">Network error. Please try again.</div>'
            );
        }
    });
});

function renderLeaveModal(leave) {
    const isWholeDay = String(leave.isFixed) === '1';
    const typeLabel  = isWholeDay ? 'Whole Day' : 'Hourly';

    $('#leaveModalSubtitle').text((leave.code_label || leave.code || 'Leave') + ' • ' + typeLabel);

    const rows = isWholeDay ? (leave.dates || []) : (leave.details || []);

    if (rows.length === 0) {
        $('#leaveModalBody').html('<div class="leave-empty"><i class="bi bi-inbox"></i>No detailed entries for this leave.</div>');
        $('#leaveModalFooter').text('');
        return;
    }

    const statusIcons = {
        approved: 'patch-check-fill',
        pending:  'hourglass-split',
        rejected: 'x-circle-fill',
        done:     'check2-circle'
    };

    // ============================================================
    // WHOLE-DAY: status/reason/location come from the PARENT leave
    // ============================================================
    if (isWholeDay) {
        const status    = String(leave.status || '').toLowerCase();
        const statusMod = ['pending','approved','rejected'].indexOf(status) >= 0 ? status : '';
        const statusCls = statusMod ? 'is-' + statusMod : '';
        const statusTxt = leave.status ? leave.status.charAt(0).toUpperCase() + leave.status.slice(1).toLowerCase() : '—';
        const statusIcon = statusIcons[statusMod] || 'file-text';
        const codeTxt   = leave.code_label || leave.code || '—';

        let html = '';

        // One card per covered date
        rows.forEach(function (d, i) {
            const dateTxt = d.date || '—';

            html += '<div class="leave-card ' + statusCls + '">'

                // Header: icon + title + status + hours
                +   '<div class="d-flex align-items-start gap-3">'
                +     '<div class="leave-icon"><i class="bi bi-' + statusIcon + '"></i></div>'
                +     '<div class="flex-grow-1">'
                +       '<div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">'
                +         '<div>'
                +           '<div class="leave-title">' + esc(codeTxt) + '</div>'
                +           '<div class="leave-sub mt-1">Day ' + (i + 1) + ' of ' + rows.length + '</div>'
                +         '</div>'
                +         '<div class="d-flex align-items-center gap-2">'
                +           '<span class="leave-status ' + statusCls + '">' + esc(statusTxt) + '</span>'
                +           '<span class="leave-hours">7.00 hrs</span>'
                +         '</div>'
                +       '</div>'
                +     '</div>'
                +   '</div>'

                // Meta: the specific date
                +   '<div class="leave-meta">'
                +     '<span><i class="bi bi-calendar3 me-1"></i>' + esc(dateTxt) + '</span>'
                +     '<span><i class="bi bi-calendar-week me-1"></i>' + esc(dayName(dateTxt)) + '</span>'
                +   '</div>';

            // Parent reason (same for every day of the leave)
            if (leave.reason) {
                html += '<div class="leave-remarks">'
                    +    '<i class="bi bi-chat-left-quote me-1"></i>'
                    +    esc(leave.reason)
                    +  '</div>';
            }

            // Parent geo location (same for every day)
            if (leave.geo_location) {
                html += '<div class="leave-location">'
                    +    '<i class="bi bi-geo-alt"></i>'
                    +    '<span>' + esc(leave.geo_location) + '</span>'
                    +  '</div>';
            }

            html += '</div>';
        });

        $('#leaveModalBody').html(html);
        const n = rows.length;
        $('#leaveModalFooter').text(n + ' date' + (n === 1 ? '' : 's'));
        return;
    }

    // ============================================================
    // HOURLY: one card per detail row (status/remarks/location per row)
    // ============================================================
    let html = '';

    rows.forEach(function (d, i) {
        const status  = String(d.status || '').toLowerCase();
        const mod     = ['pending','approved','rejected','done'].indexOf(status) >= 0 ? status : '';
        const cls     = mod ? 'is-' + mod : '';
        const text    = d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1).toLowerCase() : '—';
        const icon    = statusIcons[mod] || 'file-text';
        const dateTxt = leave.startDate || '—';
        const hrs     = (parseFloat(d.group_hours) || 0).toFixed(2);
        const codeTxt = d.code_label || d.code || '—';

        html += '<div class="leave-card ' + cls + '">'
             +   '<div class="d-flex align-items-start gap-3">'
             +     '<div class="leave-icon"><i class="bi bi-' + icon + '"></i></div>'
             +     '<div class="flex-grow-1">'
             +       '<div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">'
             +         '<div>'
             +           '<div class="leave-title">' + esc(codeTxt) + '</div>'
             +           '<div class="leave-sub mt-1">Entry ' + (i + 1) + ' of ' + rows.length + '</div>'
             +         '</div>'
             +         '<div class="d-flex align-items-center gap-2">'
             +           '<span class="leave-status ' + cls + '">' + esc(text) + '</span>'
             +           '<span class="leave-hours">' + hrs + ' hrs</span>'
             +         '</div>'
             +       '</div>'
             +     '</div>'
             +   '</div>'
             +   '<div class="leave-meta">'
             +     '<span><i class="bi bi-calendar3 me-1"></i>' + esc(dateTxt) + '</span>'
             +     (d.time_range
                        ? '<span class="leave-pill"><i class="bi bi-clock me-1"></i>' + esc(d.time_range) + '</span>'
                        : '')
             +   '</div>'
             +   (d.location
                    ? '<div class="leave-location">'
                    +    '<i class="bi bi-geo-alt"></i>'
                    +    '<span>' + esc(d.location) + '</span>'
                    +  '</div>'
                    : '')
             +   (d.remarks
                    ? '<div class="leave-remarks"><i class="bi bi-chat-left-quote me-1"></i>' + esc(d.remarks) + '</div>'
                    : '')
             + '</div>';
    });

    $('#leaveModalBody').html(html);
    $('#leaveModalFooter').text(rows.length + ' entr' + (rows.length === 1 ? 'y' : 'ies'));
}

// Previous cutoff functions
function populatePreviousCutoffOptions() {
    var yearSelect = document.getElementById('yearSelect');
    if (!yearSelect) return;

    $.ajax({
        url: "<?= base_url('api/viewlogs/get_previous_cutoffs'); ?>",
        type: "GET",
        dataType: "json",
        data: {},   // no year yet — server picks the latest
        success: function (response) {
            if (!response.success) {
                console.warn('Prev cutoffs: no data');
                return;
            }

            // Build year dropdown from the actual list
            var years = (response.year_range && response.year_range.years)
                        ? response.year_range.years
                        : [];

            yearSelect.innerHTML = '';
            years.forEach(function (y) {
                var opt = document.createElement('option');
                opt.value = y;
                opt.textContent = y;
                if (y === response.year) opt.selected = true;
                yearSelect.appendChild(opt);
            });

            // Wire up
            $('#yearSelect').off('change').on('change', function () {
                loadCutoffOptions(this.value);
            });

            // Build the cutoff dropdown from the response we just got
            populateCutoffDropdown(response);
        },
        error: function (xhr, status, error) {
            console.error("Error loading year range:", error);
        }
    });
}

function loadCutoffOptions(year) {
    $.ajax({
        url: "<?= base_url('api/viewlogs/get_previous_cutoffs'); ?>",
        type: "GET",
        dataType: "json",
        data: { selectedYear: year },
        success: function (response) {
            populateCutoffDropdown(response);
        },
        error: function (xhr, status, error) {
            console.error("Error loading cutoffs:", error);
        }
    });
}

function populateCutoffDropdown(response) {
    var cutoffSelect = $('#cutoff');
    if (!cutoffSelect.length) return;

    cutoffSelect.empty();

    var cutoffs = (response && response.cutoffs) ? response.cutoffs : [];

    if (cutoffs.length === 0) {
        cutoffSelect.append('<option value="">No cutoff periods available</option>');
        cutoffSelect.show();
        return;
    }

    cutoffSelect.append('<option value="" selected disabled>-- Select Payroll Period --</option>');

    cutoffs.forEach(function (c) {
        cutoffSelect.append($('<option>', {
            value: c.start + '|' + c.end,
            text:  c.display_full
        }));
    });

    cutoffSelect.show();

    // Auto-select the latest cutoff
    cutoffSelect.val(cutoffs[cutoffs.length - 1].start + '|' + cutoffs[cutoffs.length - 1].end);
    setTimeout(function () { cutoffSelect.trigger('change'); }, 100);

    setupCutoffChangeListener();
}

function setupCutoffChangeListener() {
    $('#cutoff').off('change').on('change', function() {
        var selectedValue = $(this).val();
        
        if (selectedValue) {
            var dates = selectedValue.split('|');
            get_cutoff_entries(dates[0], dates[1]);
        }
    });
}

// ============================================================
// 8. BOOT
// ============================================================

$(document).ready(function () {
    get_cutoff_menu();
});
</script>