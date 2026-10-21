 <aside class="col-lg-3 col-xl-2 sidebar-desktop">
    <div class="overflow-y-auto">
        <div class="d-flex align-items-center gap-2 mb-3">
            <img src="<?=  base_url('assets/images/hrms.png'); ?>" alt="HRMS Logo" class="brand-logo-img" />
            <span class="fs-6 fw-bold text-dark"> AGC HRMS Portal</span>
        </div>

        <div class="mb-3">
            <!-- <nav class="nav flex-column mb-3">
                <a class="nav-pill-custom" href="#">
                    <i class="bi bi-grid"></i> Dashboard
                </a>
            </nav> -->
            <?php if($userData['empid'] != "27163-2013"){?>
            <div class="nav-section-title">Timekeeping</div>
            <nav class="nav flex-column">                
                <a class="nav-pill-custom" href="#/time-log">
                    <i class="bi bi-clock"></i> Time Log
                </a>
                <a class="nav-pill-custom" href="#/dashboard">
                    <i class="bi bi-calendar"></i> Calendar
                </a>
                <a class="nav-pill-custom" href="#/form-request">
                    <i class="bi bi-file-earmark-text"></i> Form Request
                </a>
                <a class="nav-pill-custom" href="#/vl-ledger">
                    <i class="bi bi-card-checklist"></i> Vacation Leave Ledger
                </a>
                <a class="nav-pill-custom" href="#/view-logs">
                    <i class="bi bi-list-check"></i> View Logs
                </a>
            </nav>
            <?php }?>
            <?php

            if($userData['empid'] == "27163-2013"){?>
            <div class="nav-section-title">Payroll</div>
            <nav class="nav flex-column">
                <a class="nav-pill-custom" href="#/setup-hourly-rate">
                    <i class="bi bi-file-earmark-plus"></i> Setup Hourly Rate
                </a>
                <a class="nav-pill-custom" href="#/export-deduction">
                    <i class="bi bi-cloud-download"></i> Download Deduction
                </a>
            </nav>
            <?php }?>
        </div>

        <div class="mb-3">
            <div class="nav-section-title"> </div>
                <!-- <nav class="nav flex-column">
                <a class="nav-pill-custom"><i class="bi bi-question-circle"></i> Help</a>
                <a class="nav-pill-custom"><i class="bi bi-gear"></i> Settings</a>
                </nav>  -->
            </div>
        </div>

        <!-- User Profile (Bottom Left) -->
        <div class="sidebar-footer-wrapper">
            <div class="sidebar-user-container d-flex align-items-center justify-content-between dropdown">
                <div class="d-flex align-items-center gap-2">
                    <img src="<?= base_url('assets/images/2ndgen/' . basename($userData['userphoto'])); ?>" class="rounded-circle" width="28" height="28" alt="Avatar">
                    <div>
                    <div class="fw-bold" style="font-size: 0.75rem; line-height: 1.1;"><?= $userData['name']; ?></div>
                    <div class="text-muted" style="font-size: 0.65rem;"><?= $userData['position']; ?></div>
                </div>
            </div>
            
            <button class="admin-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Account Menu">
                <i class="bi bi-three-dots-vertical fs-6"></i>
            </button>
            
            <ul class="dropdown-menu dropdown-menu-end admin-dropdown-menu">
                <li>
                    <a class="dropdown-item" href="#/my-profile">
                        <i class="bi bi-person me-2 text-muted"></i>My Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1" style="border-color: #edf2f7;"></li>
                <li>
                    <a class="dropdown-item text-danger fw-semibold" href="<?= site_url('logout'); ?>" id="logoutBtn">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>