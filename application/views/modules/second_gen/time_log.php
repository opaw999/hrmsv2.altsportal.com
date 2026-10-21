
<!-- Top Fixed Header -->
<div class="header-wrapper">
  <header class="app-header-box d-flex justify-content-between align-items-center">
    
    <!-- Left: Title and Mobile Toggle -->
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

    <!-- Right: Real-time Date & Time Badge -->
    <div class="header-datetime-badge d-flex align-items-center gap-3 px-4 py-2 bg-white border rounded-pill shadow-sm"
        id="headerDateTimeContainer"
        data-server-timestamp="<?= time() * 1000; ?>">
        <div class="d-flex align-items-center text-muted fs-5">
            <i class="bi bi-calendar-event me-2" style="color: var(--primary-orange);"></i>
            <span id="headerLiveDate" class="fw-medium"><?= date('l, F j, Y'); ?></span>
        </div>
        <span class="text-muted opacity-50 fs-5">|</span>
        <div class="d-flex align-items-center fw-bold text-dark fs-5">
            <i class="bi bi-clock me-2 text-secondary"></i>
            <span id="headerLiveTime">00:00:00</span>
        </div>
    </div>

  </header>
</div>
<input type="hidden" id="empId" value="<?= isset($userData['empid']) ? $userData['empid'] : ''; ?>">
<input type="hidden" id="punchLat" value="">
<input type="hidden" id="punchLng" value="">
<input type="hidden" id="punchLocationName" value="">
<!-- Scrollable Main Content Frame -->
<div class="content-scrollable">
    <div class="view-page active-view" id="page-time-log">
        <div class="row g-3">
            
            <!-- LEFT PANEL: Time In / Time Out Action Station -->
            <!-- LEFT PANEL: Simplified Punch Action Station -->
            <div class="col-12 col-xl-12">
              <div class="timelog-punch-card d-flex flex-column justify-content-between h-75 shadow-sm mb-4">
                  
                  <!-- Header & Employee Preview -->
                  <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-white text-dark border rounded-pill px-3 py-1 shadow-sm" style="font-size: 0.72rem;">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Biometric Web Punch
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 d-flex align-items-center gap-1" style="font-size: 0.7rem;">
                        <span class="status-pulse-dot bg-success"></span> Active Shift
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-3 p-2 bg-white rounded-3 border">
                        <img src="<?= base_url('assets/images/2ndgen/' . basename($userData['userphoto'])); ?>" class="rounded-circle" width="42" height="42" alt="Avatar">
                        <div>
                        <div class="fw-bold text-dark fs-6 mb-0"><?= $userData['name']; ?></div>
                        <small class="text-muted" style="font-size: 0.72rem;">ID: <?= $userData['empid']; ?> • <?= $userData['position']; ?></small>
                        </div>
                    </div>
                  </div>

                  <!-- Direct Time In & Time Out Action Buttons -->
                  <div class="my-4">
                    <!-- Prominent Live Digital Clock Display -->
                    <div class="punch-clock-container text-center mb-3 py-2">
                      <div class="d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-clock-fill punch-clock-icon fs-1"></i>
                        <span id="timelogLiveTime" class="punch-clock-time">00:00:00</span>
                      </div>
                      <div class="punch-clock-date text-muted mt-1">
                        <i class="bi bi-calendar3 me-1"></i>
                        <span id="timelogLiveDate"><?= date('l, F j, Y'); ?></span>
                      </div>
                    </div>
                    <div class="d-flex justify-content-center align-items-center gap-3 w-100">
                      <!-- TIME IN -->
                      <button class="btn btn-punch-in punch-btn-prominent d-flex align-items-center justify-content-center gap-2" id="btnTimeIn" disabled>
                        <i class="bi bi-box-arrow-in-right fs-1"></i>
                        <span>TIME IN</span>
                      </button>

                      <!-- TIME OUT -->
                      <button class="btn btn-punch-out punch-btn-prominent d-flex align-items-center justify-content-center gap-2" id="btnTimeOut" disabled>
                        <i class="bi bi-box-arrow-right fs-1"></i>
                        <span>TIME OUT</span>
                      </button>
                    </div>

                    <div class="text-center mt-3">
                      <small class="text-muted" style="font-size: 0.88rem;" id="timelogLocationStatus">
                        <i class="bi bi-pin-map me-1 text-success"></i> Synchronized with Philippine Time (Asia/Manila)
                      </small>
                    </div>
                  </div>
              </div>
            <!-- BOTTOM PANEL: Daily Work Hour Metrics & Deficiencies -->
            <div class="col-12 col-xl-12">
              <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
                <div class="row g-3 align-items-center">
                  
                  <!-- Metric 1: Required Hours -->
                  <div class="col-12 col-sm-4">
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-light border">
                      <div class="d-flex align-items-center justify-content-center rounded-3 bg-white text-secondary shadow-sm" style="width: 42px; height: 42px;">
                        <i class="bi bi-briefcase-fill fs-5"></i>
                      </div>
                      <div>
                        <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Required Hours</div>
                        <div class="fw-bold text-dark fs-6" id="summaryRequiredHours">
                          7.0 <small class="text-muted fw-normal" style="font-size: 0.75rem;">hrs/day</small>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Metric 2: Rendered Hours -->
                  <div class="col-12 col-sm-4">
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 border" style="background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.25) !important;">
                      <div class="d-flex align-items-center justify-content-center rounded-3 bg-success text-white shadow-sm" style="width: 42px; height: 42px;">
                        <i class="bi bi-clock-history fs-5"></i>
                      </div>
                      <div>
                        <div class="text-success text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Hours Rendered</div>
                        <div class="fw-bold text-success fs-6" id="summaryRenderedHours">
                          5.0 <small class="fw-normal" style="font-size: 0.75rem;">hrs</small>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Metric 3: Deficiency -->
                  <div class="col-12 col-sm-4">
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 border" style="background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.25) !important;">
                      <div class="d-flex align-items-center justify-content-center rounded-3 bg-danger text-white shadow-sm" style="width: 42px; height: 42px;">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                      </div>
                      <div>
                        <div class="text-danger text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Deficiency</div>
                        <div class="fw-bold text-danger fs-6" id="summaryDeficiencyHours">
                          -2.0 <small class="fw-normal" style="font-size: 0.75rem;">hrs</small>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            
            <!-- RIGHT PANEL: Shift Logs & Today's Attendance Feed -->
            <div class="col-12 col-xl-12">
              <div class="timelog-history-card shadow-sm h-100 d-flex flex-column">
                
                <!-- Top Controls & Filters -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                  <div>
                    <h6 class="fw-bold text-dark mb-0">Attendance &amp; Punch Log</h6>
                    <small class="text-muted" style="font-size: 0.72rem;">Review your daily shift entries and time logs</small>
                  </div>
                </div>
                
                <!-- Scrollable Log List -->
                <div class="d-flex flex-column gap-2 overflow-y-auto pe-1" style="max-height: 480px;" id="punchLogsContainer">
                  
                    
                  
                  </div>

                </div>
              </div>
            
        </div>
    </div>
</div>
