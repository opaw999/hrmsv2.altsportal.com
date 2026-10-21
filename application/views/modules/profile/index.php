<!-- Top Fixed Header -->
<div class="header-wrapper">
  <header class="app-header-box d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-sm btn-light border d-lg-none py-1 px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer">
        <i class="bi bi-list fs-6"></i>
      </button>
      <div>
        <h1 class="h6 fw-bold mb-0 text-dark" id="headerTitle">My Profile</h1>
        <p class="text-muted mb-0 d-none d-sm-block" style="font-size: 0.72rem;" id="headerSubtitle">View your personal details</p>
      </div>
    </div>
  </header>
</div>

<!-- Scrollable Main Content Frame -->
<div class="content-scrollable">

  <!-- PAGE: MY PROFILE -->
  <div class="view-page" id="page-profile">
    
    <!-- Profile Main Header Card -->
    <div class="bg-white border rounded-4 overflow-hidden shadow-sm mb-3">
      
      <!-- Top Visual Banner -->
      <div class="profile-cover-banner d-flex align-items-end justify-content-end p-3">
        <span class="badge bg-white bg-opacity-20 text-success border border-white border-opacity-25 rounded-pill px-3 py-1" style="font-size: 0.7rem; backdrop-filter: blur(4px);">
          <i class="bi bi-shield-check text-warning me-1"></i> Active Employee
        </span>
      </div>

      <!-- Header Avatar & Identity -->
      <div class="px-3 pb-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
          
          <div class="d-flex align-items-end gap-3">
            <div class="profile-avatar-wrapper">
              <img src="<?= base_url('assets/images/2ndgen/' . basename($userData['userphoto'])); ?>"
              alt="Avatar" class="profile-avatar-img">
            </div>

            <div class="pt-2">
              <h4 class="fw-bold text-dark mb-0"><?= isset($userData['name']) ? $userData['name'] : 'John Holland'; ?></h4>
              <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                <span class="badge badge-theme-orange" style="font-size: 0.7rem;">
                  <i class="bi bi-briefcase me-1"></i><?= isset($userData['position']) ? $userData['position'] : 'Head Chef / Admin'; ?>
                </span>
                <span class="text-muted" style="font-size: 0.72rem;">
                  <i class="bi bi-buildings me-1 text-secondary"></i><?= isset($userData['department']) ? $userData['department'] : 'Kitchen & Operations'; ?>
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>

    <!-- Detailed Employee Credentials Grid -->
    <div class="row g-2 mb-3">
      
      <!-- HRMS ID -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="profile-data-card highlight-id d-flex align-items-center gap-3">
          <div class="profile-field-icon">
            <i class="bi bi-fingerprint"></i>
          </div>
          <div>
            <div class="profile-info-label">HRMS ID</div>
            <div class="profile-info-val" style="color: var(--primary-orange);">
              <?= $userData['empid']; ?></div>
          </div>
        </div>
      </div>

      <!-- Full Name -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="profile-data-card d-flex align-items-center gap-3">
          <div class="profile-field-icon">
            <i class="bi bi-person-fill"></i>
          </div>
          <div>
            <div class="profile-info-label">Full Name</div>
            <div class="profile-info-val"><?= $userData['name']; ?></div>
          </div>
        </div>
      </div>

      <!-- Age -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="profile-data-card d-flex align-items-center gap-3">
          <div class="profile-field-icon">
            <i class="bi bi-hourglass-split"></i>
          </div>
          <div>
            <div class="profile-info-label">Age</div>
            <div class="profile-info-val"><?= $userData['userage'] . ' yrs old'; ?></div>
          </div>
        </div>
      </div>  
      
      <!-- Company Location -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="profile-data-card d-flex align-items-center gap-3">
          <div class="profile-field-icon">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <div>
            <div class="profile-info-label"> Company </div>
            <div class="profile-info-val"><?= $userData['company']; ?></div>
          </div>
        </div>
      </div>

      <!-- Business Unit Location -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="profile-data-card d-flex align-items-center gap-3">
          <div class="profile-field-icon">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <div>
            <div class="profile-info-label"> Business Unit </div>
            <div class="profile-info-val"><?= $userData['bunit']; ?></div>
          </div>
        </div>
      </div>

      <!-- Department -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="profile-data-card d-flex align-items-center gap-3">
          <div class="profile-field-icon">
            <i class="bi bi-diagram-3-fill"></i>
          </div>
          <div>
            <div class="profile-info-label">Department</div>
            <div class="profile-info-val"><?= $userData['department']; ?></div>
          </div>
        </div>
      </div>

      <!-- Position -->
      <div class="col-12 col-sm-12 col-xl-12">
        <div class="profile-data-card d-flex align-items-center gap-3">
          <div class="profile-field-icon">
            <i class="bi bi-award-fill"></i>
          </div>
          <div>
            <div class="profile-info-label">Position / Designation</div>
            <div class="profile-info-val"><?= $userData['position']; ?></div>
          </div>
        </div>
      </div>

      

    </div>

  </div>
</div>