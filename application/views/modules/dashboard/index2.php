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
        <span id="headerLiveTime"><?= date('h:i:s A'); ?></span>
      </div>
    </div>
  </header>
</div>

<!-- Scrollable Main Content Frame -->
<div class="content-scrollable">
  <div class="view-page" id="page-dashboard">
    <div class="row g-3">
      
      <!-- Left Hero Section with Gradient Background -->
      <div class="col-12 col-xl-8">
        <div class="plant-hero-card d-flex flex-column justify-content-between">
          <div class="mb-4">
            <h2 class="plant-hero-title mb-1">Welcome back <?= $userData['name']; ?></h2>
            <p class="plant-hero-sub mb-0" id="dashHeroDate">
              <i class="bi bi-calendar-event me-1"></i><span><?= date('l, F j, Y'); ?></span>
            </p>
          </div>

          <div class="my-auto py-3">
            <div class="mb-2">
              <i class="bi bi-quote fs-1" style="color: var(--primary-orange); line-height: 1;"></i>
            </div>
            <p class="quote-body-text mb-3">
              "The biggest skill we need to learn is how to collaborate with technology-to see it as a value-adding partner in productivity."
            </p>
            <div>
              <span class="quote-author-tag">— Dr. Dieter Veldsman</span>
              <div class="quote-author-title mt-1">HR Thought Leader &amp; Organizational Development Expert</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side Analytics Widgets -->
      <div class="col-12 col-xl-4 d-flex flex-column gap-3">
        
        <!-- Live Philippine Time (PST/PHT) Clock Card -->
        <div class="time-clock-card shadow-sm">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <h6 class="fw-bold mb-0 text-white">Philippine Standard Time</h6>
              <small class="text-white-50" style="font-size: 0.7rem;">Asia/Manila (UTC+8)</small>
            </div>
            <span class="badge clock-badge-live rounded-pill px-2 py-1">
              <i class="bi bi-broadcast me-1 text-danger"></i> LIVE
            </span>
          </div>

          <div class="my-3 py-1">
            <div class="d-flex align-items-center">
              <div class="clock-digits" id="liveClockTime">--:--</div>
              <div class="clock-seconds" id="liveClockSeconds">:--</div>
              <div class="clock-ampm text-uppercase" id="liveClockAmPm">--</div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-25 text-white-50" style="font-size: 0.68rem;">
            <span id="liveClockDate"><i class="bi bi-calendar-event me-1 text-warning"></i>--</span>
            <span class="text-white fw-semibold">
              <i class="bi bi-geo-alt me-1" style="color: var(--primary-orange);"></i>PH (GMT+8)
            </span>
          </div>
        </div>

        <!-- Calendar Card -->
        <div class="calendar-card shadow-sm">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <h6 class="fw-bold text-dark mb-0" id="calMonthYear"><?= date('F Y'); ?></h6>
              <small class="text-muted" style="font-size: 0.68rem;" id="calTodayLabel">Today: <?= date('l, M j, Y'); ?></small>
            </div>
            <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 0.65rem;">
              <i class="bi bi-calendar3 me-1" style="color: var(--primary-orange);"></i> Calendar
            </span>
          </div>

          <div class="calendar-grid mb-1">
            <div class="calendar-day-header">Su</div>
            <div class="calendar-day-header">Mo</div>
            <div class="calendar-day-header">Tu</div>
            <div class="calendar-day-header">We</div>
            <div class="calendar-day-header">Th</div>
            <div class="calendar-day-header">Fr</div>
            <div class="calendar-day-header">Sa</div>
          </div>

          <div class="calendar-grid" id="calendarDaysGrid">
            <?php
            $daysInMonth = (int)date('t');
            $firstDayIndex = (int)date('w', strtotime(date('Y-m-01')));
            $currentDay = (int)date('j');

            for ($i = 0; $i < $firstDayIndex; $i++) {
              echo '<div class="calendar-day-cell empty"></div>';
            }
            for ($day = 1; $day <= $daysInMonth; $day++) {
              $activeClass = ($day === $currentDay) ? ' active' : '';
              echo '<div class="calendar-day-cell' . $activeClass . '">' . $day . '</div>';
            }
            ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>