<main class="col-12 col-lg-9 col-xl-10 main-viewport">
    <div class="page-wrap">
        <div class="header-wrapper">
            <header class="app-header-box d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-light border d-lg-none py-1 px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer">
                        <i class="bi bi-list fs-6"></i>
                    </button>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" id="headerTitle"><?= $page_title; ?></h5>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="row">
                <div class="col-12 col-md-8 mb-3">
                    <div class="card">
                        <div class="card-header">Details</div>
                        <div class="card-body">
                            <?php
                            $empId = $this->session->userdata('empid');
                            $emp = $this->request->getRow('employee3', ['emp_id' => $empId]);
                            $name = $emp['name'];
                            $position = $emp['position'];

                            $userType = ucwords($this->session->userdata('usertype'));
                            $user = $this->request->getRow('tk_user_admin', ['emp_id' => $empId]);

                            $photo = 'assets/images/admin.png';
                            $img = $this->request->getRow('applicant', ['app_id' => $empId])['photo'];
                            if ($img) {
                                $p = explode('/', $img);
                                $photo = 'assets/images/2ndgen/' . $p[3];
                                if (!file_exists($photo)) {
                                    $photo = 'assets/images/admin.png';
                                }
                            }

                            echo '<div class="row g-2">';
                            echo '<div class="col-md-12">';
                            echo '<div class="d-flex justify-content-right gap-3">';
                            echo '<a href="javascript:;">';
                            echo '<img src="' . base_url($photo) . '" alt="Avatar" class="img-thumbnail" style="width: 120px; height: 120px;">';
                            echo '</a>';
                            echo '<div>';
                            echo '<div class="fw-bold text-dark fs-5">' . $name . '</div>';
                            echo '<div class="text-muted"><i>' . $empId . '</i></div>';
                            echo '<div class="text-muted"><i>' . $userType . '</i></div>';
                            echo '<div class="text-muted mb-2"><i>' . $position . '</i></div>';
                            echo '<span class="badge bg-light text-success border border-success rounded-pill px-2 py-1" style="font-size: 0.7rem;"><i class="bi bi-circle-fill me-1"></i>Active </span>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4 d-flex">
                    <div class="card w-100">
                        <div class="card-header">Change Username</div>
                        <form id="updateUsernameAdmin" autocomplete="off" class="d-flex flex-column flex-grow-1">
                            <div class="card-body flex-grow-1">
                                <div class="row">
                                    <div class="col-12 col-md-12 mb-3">
                                        <label class="form-label small fw-semibold text-muted mb-1">Current Username</label>
                                        <input type="text" class="form-control form-control-sm" value="<?= $user['username'] ?>" disabled>
                                    </div>
                                    <div class="col-12 col-md-12">
                                        <label class="form-label small fw-semibold text-muted mb-1">New Username</label>
                                        <input type="hidden" name="userId" value="<?= $user['tkuaId'] ?>">
                                        <input type="hidden" name="request" value="username">
                                        <input type="text" class="form-control form-control-sm" name="username" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-md-4 d-flex">
                    <div class="card w-100">
                        <div class="card-header">Change Password</div>
                        <form id="updatePasswordAdmin" autocomplete="off" class="d-flex flex-column flex-grow-1">
                            <div class="card-body flex-grow-1">
                                <div class="row">
                                    <div class="col-12 col-md-12 mb-3">
                                        <label class="form-label small fw-semibold text-muted mb-1">Current Password</label>
                                        <input type="password" class="form-control form-control-sm" name="currentPassword" value="">
                                    </div>
                                    <div class="col-12 col-md-12 mb-3">
                                        <label class="form-label small fw-semibold text-muted mb-1">New Password</label>
                                        <input type="hidden" name="userId" value="<?= $user['tkuaId'] ?>">
                                        <input type="hidden" name="request" value="password">
                                        <input type="password" class="form-control form-control-sm" name="password" value="">
                                    </div>
                                    <div class="col-12 col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="showPassword">
                                            <label class="form-check-label" for="showPassword">Show Password</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        $('#showPassword').on('change', function() {
            var status = $(this).is(':checked') ? 1 : 0;
            if (status == 1) {
                $('input[name="currentPassword"]').attr('type', 'text');
                $('input[name="password"]').attr('type', 'text');
            } else {
                $('input[name="currentPassword"]').attr('type', 'password');
                $('input[name="password"]').attr('type', 'password');
            }
        })

        $('form#updateUsernameAdmin').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var fields = [];
            let notRequired = []
            for (let entry of formData.entries()) {
                if (!notRequired.includes(entry[0])) {
                    fields.push(entry[0]);
                }
            }
            console.log(fields)
            var fieldsComplete = true;
            var errMessage = false;
            for (var i = 0; i < fields.length; i++) {

                var field = fields[i];
                var value = $("select[name='" + field + "'],input[name='" + field + "']").val();

                if (value === '') {

                    if (!errMessage) {

                        alertRequired('Please fill the required fields!')
                        errMessage = true;
                    }
                    fieldsComplete = false;
                    $("select[name='" + field + "'], input[name='" + field + "']").each(function() {
                        $(this).css("border-color", "#fd3550");
                    });
                    $("select[name='" + field + "'], input[name='" + field + "']").on("focus", function() {
                        $(this).css("border-color", "");
                    });
                }
            }
            if (fieldsComplete) {
                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to update username?',
                    icon: 'question',
                    iconColor: '#ffc107',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    focusConfirm: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Please wait!',
                            text: 'Updating username...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/updateAdminAccount') ?>",
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data);
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Username has been updated. You will be logged out.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonText: 'Ok',
                                                confirmButtonColor: '#0d6efd',
                                                focusConfirm: false,
                                            }).then(() => {
                                                // window.location.reload();
                                                window.location.href = '<?= base_url('admin/logout') ?>';
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message)
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        console.error("Error:", data);
                                        errorAlert(data)
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    console.error("AJAX Error:", textStatus, errorThrown);
                                    errorAlert(errorThrown)
                                }
                            });
                        }, 2000);
                    }
                })
            }
        });

        $('form#updatePasswordAdmin').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var fields = [];
            let notRequired = []
            for (let entry of formData.entries()) {
                if (!notRequired.includes(entry[0])) {
                    fields.push(entry[0]);
                }
            }
            console.log(fields)
            var fieldsComplete = true;
            var errMessage = false;
            for (var i = 0; i < fields.length; i++) {

                var field = fields[i];
                var value = $("select[name='" + field + "'],input[name='" + field + "']").val();

                if (value === '') {

                    if (!errMessage) {

                        alertRequired('Please fill the required fields!')
                        errMessage = true;
                    }
                    fieldsComplete = false;
                    $("select[name='" + field + "'], input[name='" + field + "']").each(function() {
                        $(this).css("border-color", "#fd3550");
                    });
                    $("select[name='" + field + "'], input[name='" + field + "']").on("focus", function() {
                        $(this).css("border-color", "");
                    });
                }
            }
            if (fieldsComplete) {
                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to update password?',
                    icon: 'question',
                    iconColor: '#ffc107',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    focusConfirm: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Please wait!',
                            text: 'Updating password...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/updateAdminAccount') ?>",
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data);
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Password has been updated. You will be logged out.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonText: 'Ok',
                                                confirmButtonColor: '#0d6efd',
                                                focusConfirm: false,
                                            }).then(() => {
                                                // window.location.reload();
                                                window.location.href = '<?= base_url('admin/logout') ?>';
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message)
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        console.error("Error:", data);
                                        errorAlert(data)
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    console.error("AJAX Error:", textStatus, errorThrown);
                                    errorAlert(errorThrown)
                                }
                            });
                        }, 2000);
                    }
                })
            }
        });
    });

    function alertRequired(message) {
        Swal.fire({
            title: 'Required!',
            text: message,
            icon: 'warning',
            iconColor: '#ffc107',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd',
            allowOutsideClick: false,
        });
    }

    function errorAlert(message) {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            iconColor: '#dc3545',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#dc3545',
            allowOutsideClick: false,
        })
    }

    function alertInfo(title, message) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'info',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd',
            allowOutsideClick: false,
        });
    }
</script>