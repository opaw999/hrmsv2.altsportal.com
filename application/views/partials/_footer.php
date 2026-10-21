<!-- Mobile Bottom Navigation Bar -->
<nav class="mobile-bottom-nav">
    <?php if($userData['empid'] != "27163-2013"){?>    
    <a class="mobile-nav-btn active" href="#/time-log">
        <i class="bi bi-clock"></i>
        <span>Time Log</span>
    </a>
    <a class="mobile-nav-btn" href="#/dashboard">
        <i class="bi bi-calendar"></i>
        <span>Calendar</span>
    </a>
    <a class="mobile-nav-btn" href="#/form-request">
        <i class="bi bi-file-earmark-text"></i>
        <span>Form Request</span>
    </a>
    <a class="mobile-nav-btn" href="#/vl-ledger">
        <i class="bi bi-card-checklist"></i>
        <span>VL Ledger</span>
    </a>
    <?php } ?>
    <?php if($userData['empid'] == "27163-2013"){?>
    <a class="mobile-nav-btn active" href="#/setup-hourly-rate">
        <i class="bi bi-file-earmark-plus"></i> <span> Setup Hourly Rate </span>
    </a>
    <a class="mobile-nav-btn" href="#/export-deduction">
        <i class="bi bi-cloud-download"></i> <span> Download Deduction </span>
    </a>
    <?php } ?>
    <a class="mobile-nav-btn" href="javascript:void(0)" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer">
        <i class="bi bi-three-dots"></i>
        <span>More</span>
    </a>
</nav>

</div>

<!-- Mobile Offcanvas Drawer -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebarDrawer" style="width: 280px;">
    <div class="offcanvas-header border-bottom py-2">
        <div class="d-flex align-items-center gap-2">
            <img src="<?= base_url('assets/images/hrms.png'); ?>" alt="HRMS Logo" class="brand-logo-img" />
            <span class="fs-6 fw-bold text-dark">AGC HRMS Portal</span>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
        <div>
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
            <?php if($userData['empid'] == "27163-2013"){?>
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

        <div class="pt-3 border-top d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" class="rounded-circle" width="32" height="32" alt="Avatar">
                <div>
                    <div class="fw-bold" style="font-size: 0.8rem;"><?= $userData['name']; ?></div>
                    <div class="text-muted" style="font-size: 0.7rem;">Admin</div>
                </div>
            </div>
            <a class="btn btn-sm btn-outline-danger" id="mobileLogoutBtn" href="<?= site_url('logout'); ?>">

                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

</body>

</html>