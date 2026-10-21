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
                        <p class="text-muted mb-0 d-none d-sm-block" style="font-size: 0.72rem;">
                            Monitor vacation leave for payment access.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header">Access Form</div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                $status = false;
                                $yearMinus = date('Y', strtotime('-1 year'));
                                $year = date('Y');
                                $a = $this->request->getRow('tk_vl_payment_access', ['year' =>  $year, 'status' => 1]);
                                if ($a) {
                                    $status = true;
                                    $year = $a['year'];
                                }
                                ?>
                                <div class="col-12 col-md-12 mb-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Year Selection</label>
                                    <input type="number" class="form-control form-control-sm" id="accessYear" min="<?= $yearMinus ?>" max="<?= date('Y') ?>" value="<?= $year ?>" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="vlAccess" <?= ($status ? 'checked' : '') ?>>
                                        <label class="form-check-label" for="vlAccess"> Click here to Activate/Deactivate</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {

        $('#accessYear').on('keydown', function(e) {
            // Allow: Tab(9), Esc(27), Enter(13), Up(38), Down(40)
            if ($.inArray(e.keyCode, [9, 27, 13, 38, 40]) !== -1) return;
            e.preventDefault();
        });

        $('#accessYear').on('input', function() {
            $('#vlAccess').prop('checked', false);
            $.ajax({
                url: '<?= base_url("admin/vlAccess") ?>',
                type: 'POST',
                data: {
                    year: 'off',
                    status: 0
                },
                success: function(data) {
                    try {
                        let response = JSON.parse(data);
                        if (response.message === 'success') {

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
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });

        $('#vlAccess').on('change', function() {
            var year = $('input#accessYear').val();
            var status = $(this).is(':checked') ? 1 : 0;

            let access = 'deactivated';
            if (status == 1) {
                access = 'activated';
            }

            $.ajax({
                url: '<?= base_url("admin/vlAccess") ?>',
                type: 'POST',
                data: {
                    year,
                    status
                },
                success: function(data) {
                    try {
                        let response = JSON.parse(data);
                        if (response.message === 'success') {

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
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
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
            focusConfirm: false,
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
            focusConfirm: false,
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
            focusConfirm: false,
        });
    }
</script>