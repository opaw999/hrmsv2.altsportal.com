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
                            Generate cutoff logs report.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <form id="generate_cutoff_report" action="<?= site_url('admin/generate_cutoff_report'); ?>" target="_blank" method="post" onsubmit="return generateCutoffReport()">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <div class="align-items-center">
                                    <?php
                                    $i = 0;
                                    $scheds = $this->request->getArray('tk_cutoff_schedule', ['status' => 1]);
                                    foreach ($scheds as $c) {
                                        $i++;
                                        $id = $c['id'];
                                        $sFC = $c['startFC'];
                                        $eFC = $c['endFC'];

                                        $sSC = $c['startSC'];
                                        $eSC = $c['endSC'];

                                        $schedule = "$sFC-$eFC / $sSC-$eSC";
                                        if ($i == 1) {
                                            echo '<a href="javascript:;" id="' . $id . '" class="btn btn-sm btn-primary process_schedule active me-1">' . $schedule . '</a>';
                                        } else {
                                            echo '<a href="javascript:;" id="' . $id . '" class="btn btn-sm btn-secondary process_schedule me-1">' . $schedule . '</a>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-12">
                                        <label class="form-label fw-semibold text-muted mb-1">Cutoff Selection</label>
                                        <select class="form-control form-control-sm" id="cutoff" name="cutoff" onchange="viewCutoffReport(this.value)">
                                            <option value="">---Select Cutoff---</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="card">
                            <div class="card-header">Deduction Report</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-12 reportList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        var co = $('a.btn.btn-primary.process_schedule.active').attr('id');
        cutoff_list(co)

        $('a.process_schedule').click(function() {
            $('#selectAll').prop('checked', false);
            $('a.process_schedule').removeClass('btn-primary active').addClass('btn-secondary');
            $(this).removeClass('btn-secondary').addClass('btn-primary active');
            var co = this.id;
            cutoff_list(co)
            $('.reportList').html('');
        });


    });

    function cutoff_list(co) {
        $.ajax({
            url: "<?= site_url('admin/generate_options') ?>",
            type: 'POST',
            data: {
                co
            },
            success: function(data) {
                console.log(data)
                $('select#cutoff').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown)
            }
        });
    }

    function viewCutoffReport(payload) {
        if (payload == '') {
            $('.reportList').html('');
            return;
        }
        $.ajax({
            url: "<?= site_url('admin/cutoffReportTable') ?>",
            type: 'POST',
            data: {
                payload
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

    function generateCutoffReport() {
        let cutoff = $('#cutoff').val();
        if (cutoff == '') {
            alertRequired('Please select a cutoff to generate.');
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