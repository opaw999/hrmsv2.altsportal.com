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
                        <p class="text-muted mb-0 mt-1 d-none d-sm-block" style="font-size: 0.72rem;">
                            Generate VL payment report.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header">Year Selection</div>
                        <form id="generate_vl_payment" action="<?= site_url('admin/generate_vl_payment'); ?>" target="_blank" method="post" onsubmit="return generateVLpaymentReport()">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-12">
                                        <select class="form-control form-control-sm" name="year" onchange="viewVLpaymentReport(this.value)">
                                            <option value="">---Select Year---</option>
                                            <?php
                                            $access = $this->request->getArray('tk_vl_payment_access', ['1' => 1]);
                                            if ($access) {
                                                foreach ($access as $a) {
                                                    $year = $a['year'];
                                                    echo '<option value="' . $year . '">' . $year . '</option>';
                                                }
                                            } else {
                                                $year = date('Y');
                                                echo '<option value="' . $year . '">' . $year . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-header">VL Payment Report</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-12 reportList"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {});

    function viewVLpaymentReport(year) {
        if (year == '') {
            $('.reportList').html('');
            return;
        }
        $.ajax({
            url: "<?= site_url('admin/vlpaymentReportTable') ?>",
            type: 'POST',
            data: {
                year
            },

            success: function(data) {
                $('.reportList').html(data);
                $("table#reportTable").DataTable({
                    "destroy": true,
                    "order": [],
                    "columnDefs": [{
                        "targets": [0, 1, 2],
                        "orderable": false,
                    }, {
                        "targets": [2],
                        "className": "text-center",
                    }],
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function generateVLpaymentReport() {
        let year = $('select[name="year"]').val();
        if (year == '') {
            alertRequired('Please select a year to generate.');
            return false;
        }
    }

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