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
    <div class="header-datetime-badge d-flex align-items-center flex-nowrap gap-2 gap-md-3 px-3 px-md-4 py-1 py-md-2 bg-white border rounded-pill shadow-sm"
         id="headerDateTimeContainer"
         data-server-timestamp="<?= time() * 1000; ?>">
      <div class="d-flex align-items-center text-muted fs-5 flex-nowrap">
        <i class="bi bi-calendar-event me-1 me-md-2" style="color: var(--primary-orange, #ff5e3a);"></i>
        <span id="headerLiveDate" class="fw-medium text-nowrap"><?= date('l, F j, Y'); ?></span>
      </div>
      <span class="text-muted opacity-50 fs-5">|</span>
      <div class="d-flex align-items-center fw-bold text-dark fs-5 flex-nowrap">
        <i class="bi bi-clock me-1 me-md-2 text-secondary"></i>
        <span id="headerLiveTime" class="text-nowrap">00:00:00</span>
      </div>
    </div>

  </header>
</div>

<!-- Scrollable Main Content Frame -->
<div class="content-scrollable">
  <div class="view-page active-view" id="page-export-deductions">
    
    <div class="row justify-content-center">
      <div class="col-12 col-xl-8 col-xxl-7">
        
        <!-- Main Export Card -->
        <div class="export-card border bg-white rounded-4 shadow-sm p-3 p-md-4">
          
          <!-- Card Header Banner -->
          <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
              <div class="export-icon-box rounded-3 d-flex align-items-center justify-content-center text-white">
                <i class="bi bi-file-earmark-arrow-down-fill fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Export Deductions Report</h5>
                <span class="text-muted" style="font-size: 0.75rem;">Generate and download employee deduction statements</span>
              </div>
            </div>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 d-none d-sm-inline-block" style="font-size: 0.72rem;">
              <i class="bi bi-shield-check text-success me-1"></i> Payroll Ready
            </span>
          </div>

          <!-- Form Grid -->
          <form id="exportDeductionsForm" action="<?= base_url('deductions/export_csv'); ?>" method="POST">
            <div class="row g-3">

              <!-- Dropdown 1: Cutoff Range -->
              <div class="col-12 col-md-4">
                <label class="form-label-custom">
                  <i class="bi bi-calendar2-range me-1" style="color: var(--primary-orange, #ff5e3a);"></i> Cutoff Period
                </label>
                <div class="input-select-wrapper">
                  <select class="form-select custom-select-ui" name="cutoff_period" id="selectCutoff" required>
                    <option value="" selected disabled>Select cutoff...</option>
                    <option value="09_to_23">09 to 23</option>
                    <option value="24_to_08">24 to 08</option>
                  </select>
                </div>
              </div>

              <!-- Dropdown 2: Month -->
              <div class="col-12 col-md-4">
                <label class="form-label-custom">
                  <i class="bi bi-calendar3 me-1" style="color: var(--primary-orange, #ff5e3a);"></i> Month
                </label>
                <div class="input-select-wrapper">
                  <select class="form-select custom-select-ui" name="cutoff_month" id="selectMonth" required>
                    <option value="" selected disabled>Select month...</option>
                    <?php 
                      $months = [
                        '01' => 'January', '02' => 'February', '03' => 'March',
                        '04' => 'April',   '05' => 'May',      '06' => 'June',
                        '07' => 'July',    '08' => 'August',   '09' => 'September',
                        '10' => 'October', '11' => 'November', '12' => 'December'
                      ];
                      foreach ($months as $num => $name): 
                    ?>
                      <option value="<?= $num; ?>"><?= $name; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Dropdown 3: Year (Database Historical Years - Not Current Year) -->
              <div class="col-12 col-md-4">
                <label class="form-label-custom">
                  <i class="bi bi-calendar-event me-1" style="color: var(--primary-orange, #ff5e3a);"></i> Year
                </label>
                <div class="input-select-wrapper">
                  <select class="form-select custom-select-ui" name="cutoff_year" id="selectYear" required>
                    <option value="" disabled>Select year...</option>
                    <?php 
                      $current_year = date('Y');

                      if (!empty($dateCutOff) && is_array($dateCutOff)) {
                        foreach ($dateCutOff as $yr) {
                          $isSelected = ($yr == $current_year) ? 'selected' : '';
                          echo "<option value=\"{$yr}\" {$isSelected}>{$yr}</option>";
                        }
                      } else {
                        // Fallback example years if database returns empty
                        $fallback_years = [2026, 2025, 2024, 2023, 2022];
                        foreach ($fallback_years as $yr) {
                          $isSelected = ($yr == $current_year) ? 'selected' : '';
                          echo "<option value=\"{$yr}\" {$isSelected}>{$yr}</option>";
                        }
                      }
                    ?>
                  </select>
                </div>
              </div>

              <!-- Selection Summary Indicator -->
              <div class="col-12">
                <div class="summary-preview-box rounded-3 p-2 px-3 d-flex align-items-center justify-content-between bg-light border">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-muted"></i>
                    <span class="text-muted" style="font-size: 0.75rem;">Target Batch:</span>
                    <strong class="text-dark" id="selectionSummary" style="font-size: 0.8rem;">Select parameters above</strong>
                  </div>
                  <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 0.65rem;">.XLSX / .CSV</span>
                </div>
              </div>

              <!-- Submit & Reset Action Buttons -->
              <div class="col-12 pt-2 d-flex flex-column flex-sm-row justify-content-end gap-2">
                <button type="reset" class="btn btn-light border rounded-3 px-3 py-2 fw-semibold text-secondary" id="btnResetExport">
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Clear
                </button>
                <button type="submit" class="btn btn-orange rounded-3 px-4 py-2 fw-semibold shadow-sm" id="btnSubmitExport">
                  <i class="bi bi-cloud-arrow-down-fill me-1"></i> Export Deductions
                </button>
              </div>

            </div>
          </form>

        </div>

      </div>
    </div>

  </div>
</div>

<script>
  $(document).ready(function () {
    const monthNames = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];

    function updateExportSummary() {
      const cutoffVal = $('#selectCutoff').val();
      const monthVal  = $('#selectMonth').val(); // '01' - '12'
      const yearVal   = $('#selectYear').val();

      if (cutoffVal && monthVal && yearVal) {
        const selectedMonthIdx  = parseInt(monthVal, 10) - 1; // 0 (Jan) to 11 (Dec)
        const selectedMonthName = monthNames[selectedMonthIdx];
        const selectedYear      = parseInt(yearVal, 10);

        let displayText = '';

        if (cutoffVal === '09_to_23') {
          // Same month: March 09 to 24 2026
          displayText = `<span class="badge bg-light-orange text-orange me-1" style="background:#fff0eb; color:#ff5e3a;">09 to 23</span> ${selectedMonthName} 09 to 23 ${selectedYear}`;
        } else if (cutoffVal === '24_to_08') {
          // Starts on the 24th of the selected month and ends on the 08th of the next month
          let nextMonthIdx = selectedMonthIdx + 1;
          let nextYear     = selectedYear;

          // Handle year rollover if selected month is December
          if (nextMonthIdx > 11) {
            nextMonthIdx = 0; // January
            nextYear     = selectedYear + 1;
          }

          const nextMonthName = monthNames[nextMonthIdx];

          if (nextYear !== selectedYear) {
            displayText = `<span class="badge bg-light-orange text-orange me-1" style="background:#fff0eb; color:#ff5e3a;">24 to 08</span> ${selectedMonthName} 24 ${selectedYear} to ${nextMonthName} 08 ${nextYear}`;
          } else {
            displayText = `<span class="badge bg-light-orange text-orange me-1" style="background:#fff0eb; color:#ff5e3a;">24 to 08</span> ${selectedMonthName} 24 to ${nextMonthName} 08 ${selectedYear}`;
          }
        }

        $('#selectionSummary').html(displayText);
      } else {
        $('#selectionSummary').text('Select parameters above');
      }
    }

    $('#selectCutoff, #selectMonth, #selectYear').on('change', updateExportSummary);

    $('#exportDeductionsForm').on('reset', function () {
      setTimeout(function () {
        $('#selectionSummary').text('Select parameters above');
      }, 50);
    });
  });
</script>