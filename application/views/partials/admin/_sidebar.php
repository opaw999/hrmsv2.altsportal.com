<style>
    .submenu-caret {
        font-size: .75rem;
        transition: transform .25s ease;
        transform: rotate(0deg);
    }

    .nav-pill-custom[aria-expanded="true"] .submenu-caret {
        transform: rotate(-90deg);
    }

    .submenu {
        margin-left: 24px;
        margin-top: 4px;
        margin-bottom: 4px;
    }
</style>
<aside class="col-lg-3 col-xl-2 sidebar-desktop">
    <div class="overflow-y-auto">
        <div class="d-flex align-items-center gap-2 mb-3">
            <img src="<?= base_url('assets/images/hrms.png'); ?>" alt="HRMS Logo" class="brand-logo-img" />
            <span class="fs-5 fw-bold text-dark">HRMS Portal</span>
        </div>

        <div class="mb-3">
            <nav class="nav flex-column mb-3">
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

                        $cond = ['menu_id' => $id, 'status' => 1];
                        $submenus = $this->request->getArray('tk_admin_submenus', $cond);
                        if ($submenus) {

                            $hasActiveChild = false;
                            foreach ($submenus as $s) {
                                if ($page == $s['submenu_code']) {
                                    $hasActiveChild = true;
                                    break;
                                }
                            }

                            echo '<a class="nav-pill-custom ' . ($hasActiveChild ? 'active' : '') . ' d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#' . $mc . '" aria-expanded="' . ($hasActiveChild ? 'true' : 'false') . '" aria-controls="' . $mc . '">';
                            echo '<span><i class="bi bi-gear me-2"></i> ' . $mn . '</span>';
                            echo '<i class="bi bi-chevron-left submenu-caret"></i>';
                            echo '</a>';

                            echo '<div class="collapse' . ($hasActiveChild ? ' show' : '') . '" id="' . $mc . '">';
                            echo '<div class="submenu">';
                            foreach ($submenus as $s) {
                                $sub_name = $s['submenu_name'];
                                $sub_code = $s['submenu_code'];
                                echo '<a class="nav-pill-sub submenu-item ' . ($page == $sub_code ? 'active' : '') . '" href="' . base_url('admin/menu/' . $sub_code) . '">';
                                echo '<i class="bi bi-circle"></i> ' . $sub_name;
                                echo '</a>';
                            }
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo '<a class="nav-pill-custom ' . ($page == $mc ? 'active' : '') . '" href="' . base_url('admin/menu/' . $mc) . '">';
                            echo '<i class="' . $icon . '"></i> ' . $mn;
                            echo '</a>';
                        }
                    }
                }
                ?>
            </nav>
        </div>
        <?php
        $usertype = $this->session->userdata('usertype');
        if ($usertype == 'administrator') {
            echo '<div class="mb-3">';
            echo '<div class="nav-section-title">Others</div>';
            echo '<nav class="nav flex-column">';

            echo '<a class="nav-pill-custom ' . ($page == 'access-control' ? 'active' : '') . '" href="' . base_url('admin/access-control') . '">';
            echo '<i class="bi bi-person-fill-lock"></i> Access Control';
            echo '</a>';

            echo '</nav>';
            echo '</div>';
        }
        ?>
    </div>

    <div class="sidebar-footer-wrapper">
        <div class="sidebar-user-container d-flex align-items-center justify-content-between dropdown">
            <div class="d-flex align-items-center gap-2">
                <img src="<?= base_url('assets/images/admin.png'); ?>" class="rounded-circle" width="32" height="32" alt="Avatar">
                <div>
                    <div class="fw-bold" style="font-size: 0.75rem; line-height: 1.1;"><?= $this->session->userdata('username'); ?></div>
                    <div class="text-muted" style="font-size: 0.65rem;"><?= ucwords($this->session->userdata('usertype')); ?></div>
                </div>
            </div>

            <button class="admin-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Account Menu">
                <i class="bi bi-three-dots-vertical fs-6"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end admin-dropdown-menu">
                <li>
                    <a class="dropdown-item text-primary fw-semibold" href="<?= base_url('admin/profile'); ?>">
                        <i class="bi bi-person-bounding-box me-2"></i>My Profile
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider my-1" style="border-color: #edf2f7;">
                </li>
                <li>
                    <a class="dropdown-item text-danger fw-semibold" href="<?= site_url('admin/logout'); ?>" id="logoutBtn">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>