
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
<!-- Scrollable Main Content Frame -->
<div class="content-scrollable">
    <div class="view-page active-view" id="page-hourly-rate">
  
        <!-- Header Bar -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
            <h5 class="fw-bold text-dark mb-0">Hourly Rate Assignment</h5>
            <small class="text-muted" style="font-size: 0.75rem;">Configure and maintain confidential employee wage records</small>
            </div>
            
            <!-- Bulk Mask/Unmask Toggle -->
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-2" id="btnToggleAllRates">
            <i class="bi bi-eye-slash-fill" id="allRateIcon"></i>
            <span id="allRateText">Show All Rates</span>
            </button>
        </div>

        <div class="row g-3">
            
            <!-- LEFT PANEL: Rate Editor / Direct Assignment -->
            <div class="col-12 col-lg-4">
              <div class="rate-editor-card shadow-sm sticky-top" style="top: 1rem;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                  <div class="rounded-3 bg-light p-2 d-flex align-items-center justify-content-center text-primary-orange" style="width: 34px; height: 34px;">
                      <i class="bi bi-pencil-square" style="color: var(--primary-orange);"></i>
                  </div>
                  <div>
                      <h6 class="fw-bold text-dark mb-0">Setup Rate</h6>
                      <small class="text-muted" style="font-size: 0.7rem;">Enter salary number and new rate</small>
                  </div>
                </div>

                <form id="hourlyRateForm">
                  <!-- SAL NO. Selector / Input -->
                  <div class="mb-3">
                      <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 0.72rem;">SALARY NUM (SAL NO.)</label>
                      <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-hash"></i></span>
                      <input type="text" class="form-control border-start-0 ps-0" disabled id="inputSalNo" name="salary_num" required>
                      <input type="hidden" id="salNo" name="salNo">
                      <input type="hidden" id="empID" name="empID">
                      </div>
                      <div class="form-text text-muted" id="selectedEmployeeName" style="font-size: 0.7rem;">Select from table or type manually</div>
                  </div>

                  <!-- HOURLY RATE Input -->
                  <div class="mb-3">
                      <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 0.72rem;">HOURLY RATE (PHP ₱)</label>
                      <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light text-muted border-end-0">₱</span>
                      <input 
                        type="number" 
                        step="0.01" 
                        min="0.01" 
                        max="1999.99" 
                        class="form-control border-start-0 ps-0 fw-bold" 
                        id="inputHourlyRate" 
                        name="hourly_rate" 
                        placeholder="0.00" 
                        required>
                    <span class="input-group-text bg-light text-muted">/ hr</span>
                      </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="d-flex gap-2 pt-2">
                      <button type="button" class="btn btn-sm btn-light border text-muted flex-fill" id="btnResetForm">Clear</button>
                      <button type="submit" class="btn btn-sm btn-orange flex-fill fw-semibold py-2">
                      <i class="bi bi-check2-circle me-1"></i> SAVE
                      </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- RIGHT PANEL: Salary Master Table -->
            <div class="col-12 col-lg-8">
              <div class="rate-table-card shadow-sm">
                  
                  <!-- Table Search & Filter Bar -->
                  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                        Employee Directory <span class="badge bg-light text-muted border ms-1" id="recordCountBadge">15 records</span>
                    </div>
                    <div class="search-input-group" style="max-width: 250px;">
                        <i class="bi bi-search text-muted me-2" style="font-size: 0.75rem;"></i>
                        <input type="text" id="employeeRateSearch" placeholder="Search name or SAL NO..." />
                    </div>
                  </div>

                  <!-- Desktop & Tablet Table View -->
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="rateMasterTable">
                        <thead class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                          <tr>
                              <th>EMP ID</th>
                              <th>SALARY NUM</th>
                              <th>NAME</th>
                              <th>HOURLY RATE</th>
                              <th class="text-end">ACTION</th>
                          </tr>
                        </thead>
                        <tbody id="rateMasterTableBody" style="font-size: 0.82rem;">
                          <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                              <div class="spinner-border spinner-border-sm text-secondary me-1" role="status"></div>
                              Loading employees...
                            </td>
                          </tr>
                        </tbody>
                    </table>
                  </div>

              </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Rate Change History / Audit Log -->
<div class="modal fade" id="modalRateLog" tabindex="-1" ata-bs-keyboard="false" data-bs-backdrop="static" aria-labelledby="modalRateLogTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 border-0 shadow">
      
      <div class="modal-header border-bottom pb-2">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-3 bg-light p-2 d-flex align-items-center justify-content-center text-primary-orange" style="width: 32px; height: 32px;">
            <i class="bi bi-clock-history" style="color: var(--primary-orange, #ff5e3a);"></i>
          </div>
          <div>
            <h6 class="modal-title fw-bold text-dark mb-0" id="modalRateLogTitle">Rate Adjustment History</h6>
            <small class="text-muted" id="modalRateLogSubtitle" style="font-size: 0.72rem;">Loading employee details...</small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
            <thead class="bg-light text-muted" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
              <tr>
                <th class="ps-3">Date & Time</th>
                <th>From Rate</th>
                <th>To Rate</th>
                <th>Updated By</th>
              </tr>
            </thead>
            <tbody id="rateLogsTableBody">
              <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-secondary me-1" role="status"></div>
                  Loading rate history...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer border-top pt-2">
        <button type="button" class="btn btn-sm btn-light border rounded-3 px-3" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
