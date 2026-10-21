<style>
    .duty-stat-value {
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.5px;
        font-variant-numeric: tabular-nums;
    }

    .duty-stat-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 1.25rem;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .duty-stat-card.stat-approved {
        border-left: 4px solid #10b981;
    }

    .duty-stat-card.stat-disapproved {
        border-left: 4px solid #ef4444;
        /* red */
    }

    .duty-stat-card.stat-pending {
        border-left: 4px solid #f59e0b;
        /* yellow/amber */
    }

    .stat-approved .ledger-stat-icon {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }

    .stat-disapproved .ledger-stat-icon {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }

    .stat-pending .ledger-stat-icon {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
</style>
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
                            Monitor all DTR data.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="view-page" id="page-vacation-ledger">
                <div class="ledger-history-card shadow-sm">
                    <div class="align-items-center pb-2 border-bottom">
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
                                echo '<a href="javascript:;" id="' . $id . '" class="btn btn-sm btn-primary co_schedule active me-1">' . $schedule . '</a>';
                            } else {
                                echo '<a href="javascript:;" id="' . $id . '" class="btn btn-sm btn-secondary co_schedule me-1">' . $schedule . '</a>';
                            }
                        }
                        ?>
                    </div>
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle" id="cutoffTable">
                            <thead>
                                <tr>
                                    <th>Date From</th>
                                    <th>Date To</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="employee_list" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body table-responsive">
                <table id="co_employee_list" class="table table-hover table-sm align-middle">
                    <thead>
                        <tr>
                            <th width="10%">HRMS ID</th>
                            <th width="25%">Name</th>
                            <th>Position</th>
                            <!-- <th>Registered Location</th> -->
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="view_dtr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daily Time Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <form id="generate_dtr" action="<?= base_url('admin/generate_dtr') ?>" method="get" target="_blank">
                <input type="hidden" name="empId" value="">
                <input type="hidden" name="payload" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Generate Pdf </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="dtr_request" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dtr_duty" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="view_attachments">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="attachment">Attachments </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var co = $('a.btn.btn-primary.co_schedule.active').attr('id');
        cutoffTable(co)

        $('a.co_schedule').click(function() {
            $('a.co_schedule').removeClass('btn-primary active').addClass('btn-secondary');
            $(this).removeClass('btn-secondary').addClass('btn-primary active');
            var co = this.id;
            cutoffTable(co)
        });

        $('table#cutoffTable').on('click', 'a.emp_list', function() {
            $('#employee_list').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#employee_list').modal('show');

            let [co, cutoff, dateFrom, dateTo] = this.id.split('|');
            $("#employee_list .modal-title").html('Employee List (<b>' + dateFrom + ' - ' + dateTo + '</b>)');
            employee_list(co, cutoff, dateFrom, dateTo);
        });

        $('table#co_employee_list').on('click', 'a.view_dtr', function() {
            $('#view_dtr').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#view_dtr').modal('show');

            let [empId, co, cutoff, dateFrom, dateTo] = this.id.split('|');
            let payload = co + '|' + cutoff + '|' + dateFrom + '|' + dateTo;
            $('input[name="empId"]').val(empId);
            $('input[name="payload"]').val(payload);
            view_dtr(empId, payload);
        });

    });

    function cutoffTable(co) {
        $('table#cutoffTable').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/cutoffTable'); ?>",
                type: "POST",
                data: {
                    co
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2],
                "orderable": false,

            }, {
                "targets": [2],
                "className": "text-center"
            }]
        });
    }

    function employee_list(co, cutoff, dateFrom, dateTo) {
        var table = $('table#co_employee_list').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/co_employee_list'); ?>",
                type: "POST",
                data: {
                    co,
                    cutoff,
                    dateFrom,
                    dateTo
                },
            },
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": [0, 2, 3],
                "orderable": false,
            }, {
                "targets": [3],
                "className": "text-center"
            }, {
                "targets": [0, 1],
                "className": "text-nowrap"
            }],
        });

        $('#employee_list').on('shown.bs.modal', function() {
            table.columns.adjust().draw(false);
        });
    }

    function view_dtr(empId, payload) {

        console.log(payload)
        $.ajax({
            url: "<?= site_url('admin/view_dtr'); ?>",
            type: 'POST',
            data: {
                empId,
                payload
            },
            beforeSend: function() {
                loader_circle('#view_dtr .modal-body')
            },
            success: function(data) {
                let response = JSON.parse(data);
                $('#view_dtr .modal-body').html(response.form);
                dtr_details(empId, payload)
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown)
            }
        });
    }

    function dtr_totals(empId, payload) {
        $.ajax({
            url: "<?= site_url('admin/dtr_totals') ?>",
            type: 'POST',
            data: {
                empId: empId,
                payload: payload
            },
            success: function(data) {
                let response = JSON.parse(data);
                $('.dtr_totals').html(response.form);
            },
            beforeSend: function() {},
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown)
            }
        });
    }

    function dtr_details(empId, payload) {
        $.ajax({
            url: "<?= site_url('admin/dtr_details') ?>",
            type: 'POST',
            data: {
                empId: empId,
                payload: payload
            },
            beforeSend: function() {
                loader_circle('.dtr_details')
            },
            success: function(data) {
                let response = JSON.parse(data);
                $('.dtr_details').html(response.form);
                dtr_totals(empId, payload)
            },
            beforeSend: function() {},
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown)
            }
        });
    }

    function dtr_request(empId, leaveId, groupId, request) {
        $('#dtr_request .modal-title').html(request);

        $('#dtr_request').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#dtr_request').modal('show');

        $.ajax({
            url: "<?= site_url('admin/dtr_request'); ?>",
            type: 'POST',
            data: {
                empId,
                leaveId,
                groupId
            },
            beforeSend: function() {
                loader_circle('#dtr_request .modal-body')
            },
            success: function(data) {
                let response = JSON.parse(data);
                $('#dtr_request .modal-body').html(response.form);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown)
            }
        });
    }

    function dtr_duty(id, date, day) {
        $('#dtr_duty .modal-title').html('<i>' + date + ' (' + day + ')</i>');

        $('#dtr_duty').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#dtr_duty').modal('show');

        $.ajax({
            url: "<?= site_url('admin/dtr_duty'); ?>",
            type: 'POST',
            data: {
                id
            },
            beforeSend: function() {
                loader_circle('#dtr_duty .modal-body')
            },
            success: function(data) {
                let response = JSON.parse(data);
                $('#dtr_duty .modal-body').html(response.form);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown)
            }
        });
    }

    function viewAttachments(link) {
        $('#view_attachments').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#view_attachments').modal('show');

        $("#view_attachments .modal-body").html('<img src="' + link + '" class="d-block w-100" />');
    }

    function loader_circle(page) {
        $(page).html(
            '<div style="text-align: center;">' +
            '<img src="<?= base_url('assets/images/loader_seq.gif') ?>">' +
            '</div>'
        );
    }
</script>