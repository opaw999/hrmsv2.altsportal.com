<!-- Mobile Bottom Navigation Bar -->
<nav class="mobile-bottom-nav">
    <?php
    $menus = $this->request->getArray('tk_admin_menus', ['status' => 1]);
    foreach ($menus as $m) {
        $id = $m['id'];
        $mn = $m['menu_name'];
        $mc = $m['menu_code'];
        $icon = $m['icon'];

        $empId = $this->session->userdata('empid');
        $cond = ['menu_id' => $id, 'emp_id' => $empId, 'status' => 1];
        $check = $this->request->getRow('tk_admin_menu_access', $cond);
        if ($check) {
            echo '<a class="mobile-nav-btn ' . ($page == $mc ? 'active' : '') . '" href="' . base_url('admin/' . $mc) . '">';
            echo '<i class="' . $icon . '"></i> <span>' . $mn . '</span>';
            echo '</a>';
        }
    }
    ?>
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
            <span class="fs-6 fw-bold text-dark">HRMS Portal</span>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
        <div>
            <div class="nav-section-title">Menu</div>
            <nav class="nav flex-column mb-3">
                <?php
                foreach ($menus as $m) {
                    $id = $m['id'];
                    $mn = $m['menu_name'];
                    $mc = $m['menu_code'];
                    $icon = $m['icon'];

                    $empId = $this->session->userdata('empid');
                    $cond = ['menu_id' => $id, 'emp_id' => $empId, 'status' => 1];
                    $check = $this->request->getRow('tk_admin_menu_access', $cond);
                    if ($check) {
                        echo '<a class="nav-pill-custom ' . ($page == $mc ? 'active' : '') . '" href="' . base_url('admin/' . $mc) . '">';
                        echo '<i class="' . $icon . '"></i> <span>' . $mn . '</span>';
                        echo '</a>';
                    }
                }
                ?>

            </nav>

            <div class="nav-section-title">Others</div>
            <nav class="nav flex-column">
                <a class="nav-pill-custom" data-target="page-help" data-bs-dismiss="offcanvas"><i class="bi bi-question-circle"></i> Help</a>
                <a class="nav-pill-custom" data-target="page-settings" data-bs-dismiss="offcanvas"><i class="bi bi-gear"></i> Settings</a>
            </nav>
        </div>

        <div class="pt-3 border-top d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <img src="<?= base_url('assets/images/admin.png'); ?>" class="rounded-circle" width="32" height="32" alt="Avatar">
                <div>
                    <div class="fw-bold" style="font-size: 0.8rem;"><?= $this->session->userdata('name'); ?></div>
                    <div class="text-muted" style="font-size: 0.7rem;"><?= ucwords($this->session->userdata('usertype')); ?></div>
                </div>
            </div>
            <a class="btn btn-sm btn-outline-secondary" id="mobileLogoutBtn" href="<?= site_url('admin/logout'); ?>">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

</body>

</html>