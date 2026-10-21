<!-- Top Fixed Header -->
<div class="header-wrapper">
    <header class="app-header-box d-flex justify-content-between align-items-center">
        <!-- Left: Title and Mobile Toggle -->
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-light border d-lg-none py-1 px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer">
                <i class="bi bi-list fs-6"></i>
            </button>
            <div>
                <h1 class="h6 fw-bold mb-0 text-dark" id="headerTitle"><?= $page_title; ?></h1>
                <p class="text-muted mb-0 d-none d-sm-block" style="font-size: 0.72rem;" id="headerSubtitle">
                    <?= $page_sub_title; ?>
                </p>
            </div>
        </div>

        <!-- Right: Real-time Date & Time Badge -->
        <!-- <div class="header-datetime-badge d-flex align-items-center gap-3 px-4 py-2 bg-white border rounded-pill shadow-sm"
            id="headerDateTimeContainer"
            data-server-time="<?= date('Y-m-d\TH:i:s'); ?>">
            <div class="d-flex align-items-center text-muted fs-5">
                <i class="bi bi-calendar-event me-2" style="color: var(--primary-orange);"></i>
                <span id="headerLiveDate" class="fw-medium"><?= date('l, F j, Y'); ?></span>
            </div>
            <span class="text-muted opacity-50 fs-5">|</span>
            <div class="d-flex align-items-center fw-bold text-dark fs-5">
                <i class="bi bi-clock me-2 text-secondary"></i>
                <span id="headerLiveTime"><?= date('h:i:s A'); ?></span>
            </div>
        </div> -->

    </header>
</div>

<div class="content-scrollable">
    <div class="view-page active-view" id="page-vacation-ledger">
        <?php
        $empid = $this->session->userdata('empid');
        $asOf = 'as of ' . date('F j, Y');
        $year = date('Y');
        $a = $this->request_model->getRow('tk_vl_payment_access', ['status' => 1]);
        if ($a) {
            $year = $a['year'];
            if ($year != date('Y')) {
                $asOf = 'for the year ' . $year;
            }
        }

        $cond = ['emp_id' => $empid, 'year' => $year];
        $cvlb = $this->request_model->getRow('tk_vacation_leave_balance', $cond);

        $button = '';
        if ($a && $cvlb['paidDaysRemaining']) {
            $button = '<a href="javascript:;" class="btn btn-sm btn-success rounded-pill mb-2" onclick="vlPayment(\'' . $year . '\',\'' . $cvlb['paidDaysRemaining'] . '\')">For Payment</a>';
        }
        ?>
        <div class="row g-2 mb-3">

            <!-- Remaining Balance -->
            <div class="col-12 col-sm-4 ">
                <div class="ledger-stat-card stat-remaining shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-bold small text-uppercase" style="font-size: 0.68rem;">Total Remaining Vacation Leave</span>
                        <div class="ledger-stat-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                    <div class="ledger-stat-value" style="color: var(--primary-orange);">
                        <?= $cvlb['totalDaysRemaining']; ?> <span class="fs-6 fw-semibold text-muted">Days</span>
                    </div>
                    <small class="text-muted " style="font-size: 0.68rem;"><?= $asOf ?></small>
                </div>
            </div>

            <!-- Paid Leaves -->
            <div class="col-12 col-sm-4 ">
                <div class="ledger-stat-card stat-paid shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-bold small text-uppercase" style="font-size: 0.68rem;">Paid (Remaining)</span>
                        <div class="ledger-stat-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.88rem;">
                        <div class="ledger-stat-value text-success">
                            <?= $button ?> <span> <?= $cvlb['paidDaysRemaining']; ?></span> <span class="fs-6 fw-semibold text-muted">Days</span>
                        </div>
                    </div>
                    <small class="text-muted " style="font-size: 0.68rem;"><?= $asOf ?></small>
                </div>
            </div>

            <!-- Unpaid Leaves -->
            <div class="col-12 col-sm-4 ">
                <div class="ledger-stat-card stat-unpaid shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-bold small text-uppercase" style="font-size: 0.68rem;">Unpaid (Remaining)</span>
                        <div class="ledger-stat-icon">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                    </div>
                    <div class="ledger-stat-value text-danger">
                        <?= $cvlb['unpaidDaysRemaining']; ?> <span class="fs-6 fw-semibold text-muted">Days</span>
                    </div>
                    <small class="text-muted " style="font-size: 0.68rem;"><?= $asOf ?></small>
                </div>
            </div>

        </div>

        <!-- Detailed Usage Breakdown Section -->
        <div class="ledger-history-card shadow-sm">

            <!-- Card Header -->
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold text-dark mb-0">Vacation Leave Breakdown</h6>
                    <small class="text-muted" style="font-size: 0.72rem;">History of filed vacation leaves per year</small>
                </div>
                <!-- <a href="#/form-request" class="btn btn-sm btn-orange rounded-3 px-3 fw-semibold shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> File Leave
                </a> -->
            </div>

            <div class="d-none d-md-block table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle nowrap" id="vlYearTable">
                    <thead>
                        <tr>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>Total Remaining Leave</th>
                            <th>Total Used Paid Leave</th>
                            <th>Total Used Unpaid Leave</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cond = ['emp_id' => $empid];
                        $vl_y = $this->request_model->get_vl('tk_vacation_leave_balance', $cond);
                        foreach ($vl_y as $row) {
                            $year = $row['year'];
                            $df = date('F j, Y', strtotime("$year-01-01"));
                            $dt  = date('F j, Y', strtotime("$year-12-31"));

                            $total = $row['totalDaysRemaining'];
                            $paid = $row['paidDaysUsed'];
                            $unpaid = $row['unpaidDaysUsed'];
                            $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="empLeaves(\'' . $year . '\')">View</a>';

                            echo '<tr>';
                            echo    '<td><span style="display: none;">' . date('Y', strtotime($df)) . '</span>' . $df . '</td>';
                            echo    '<td>' . $dt . '</td>';
                            echo    '<td class="fw-bold">' . $total . '</td>';
                            echo    '<td class="fw-bold text-success">' . $paid . '</td>';
                            echo    '<td class="fw-bold text-danger">' . $unpaid . '</td>';
                            echo    '<td>' . $action . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- 2. MOBILE VIEW (Visible on mobile screens) -->
            <div class="d-flex d-md-none flex-column gap-2">
                <?php
                foreach ($vl_y as $row) {
                    $year = $row['year'];
                    $df = date('F j, Y', strtotime("$year-01-01"));
                    $dt  = date('F j, Y', strtotime("$year-12-31"));

                    $total = $row['totalDaysRemaining'];
                    $paid = $row['paidDaysUsed'];
                    $unpaid = $row['unpaidDaysUsed'];
                    $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="empLeaves(\'' . $year . '\')">View</a>';

                    echo '<a href="javascript:;" class="text-decoration-none" onclick="empLeaves(\'' . $year . '\')">';
                    echo '<div class="ledger-mobile-item">';

                    echo '<div class="d-flex align-items-center mb-1">';
                    echo '<div class="fw-bold text-dark" style="font-size: 1rem;">' . $df . ' - ' . $dt . '</div>';
                    echo '</div>';

                    echo '<div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.88rem;">';
                    echo '<div><span class="fw-medium">Total Remaining Leave</span></div>';
                    echo '<div class="d-flex align-items-center gap-1">';
                    echo '<span class="fw-bold" style="color: var(--primary-orange);">' . $total . ' day(s)</span>';
                    echo '</div>';
                    echo '</div>';

                    echo '<div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.88rem;">';
                    echo '<div><span class="fw-medium">Total Used Paid Leave</span></div>';
                    echo '<div class="d-flex align-items-center gap-1">';
                    echo '<span class="fw-bold text-success">' . $paid . ' day(s)</span>';
                    echo '</div>';
                    echo '</div>';

                    echo '<div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.88rem;">';
                    echo '<div><span class="fw-medium">Total Used Unpaid Leave</span></div>';
                    echo '<div class="d-flex align-items-center gap-1">';
                    echo '<span class="fw-bold text-danger">' . $unpaid . ' day(s)</span>';
                    echo '</div>';
                    echo '</div>';

                    echo '</div>';
                    echo '</a>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="empLeaves">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTitle">Vacation Leave Requests</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewFixedRequest">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">View Request</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="view_attachments">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Attachments </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<!-- <script src="<?= base_url('assets/js/clock.js'); ?>"></script> -->
<script>
    $(document).ready(function() {
        $("#vlYearTable").DataTable({
            "order": [0, "desc"],
            "columnDefs": [{
                    "targets": [1, 2, 3, 4, 5],
                    "orderable": false,
                },
                {
                    "targets": [2, 3, 4, 5],
                    "className": "text-center",
                },
            ],
        });
    });

    function empLeaves(year) {
        $('#empLeaves').modal({
            backdrop: 'static',
            keyboard: false
        });
        $('#empLeaves').modal('show');
        $('#empLeaves .modal-title').text('Vacation Leave Requests (' + year + ')');

        $.ajax({
            url: "<?= site_url('emp_vl') ?>",
            type: 'POST',
            data: {
                year
            },
            beforeSend: function() {
                loader_circle('#empLeaves .modal-body')
            },
            success: function(data) {
                $('#empLeaves .modal-body').html(data);
                $("#vacationTable").DataTable({
                    "order": [0, "desc"],
                    "columnDefs": [{
                            "targets": [1, 2, 3, 4, 5, 6, 7],
                            "orderable": false,
                        },
                        {
                            "targets": [3, 4, 5, 6, 7],
                            "className": "text-center",
                        },
                    ],
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function viewFixedRequest(leaveId, date) {
        $('#viewFixedRequest').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#viewFixedRequest').modal('show');

        $.ajax({
            url: "<?= site_url('viewFixedRequest') ?>",
            type: 'POST',
            data: {
                leaveId: leaveId,
                date: date
            },
            beforeSend: function() {
                loader_circle('#viewFixedRequest .modal-body')
            },
            success: function(data) {
                let res = JSON.parse(data);
                $('#viewFixedRequest .modal-body').html(res.form);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function vl_details(leaveId) {
        $('#viewFixedRequest').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#viewFixedRequest').modal('show');

        $.ajax({
            url: "<?= site_url('vl_details') ?>",
            type: 'POST',
            data: {
                leaveId: leaveId,
            },
            beforeSend: function() {
                loader_circle('#viewFixedRequest .modal-body')
            },
            success: function(data) {
                let res = JSON.parse(data);
                $('#viewFixedRequest .modal-body').html(res.form);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
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

    function vlPayment(year, paid) {
        console.log(year, paid)
        Swal.fire({
            title: 'Confirm!',
            text: 'Request payment for the remaining ' + paid + ' day(s) paid vacation leave of ' + year + '?',
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
                    text: 'Submitting request...',
                    allowOutsideClick: false,
                    focusConfirm: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('request_vlPayment') ?>",
                        type: 'POST',
                        data: {
                            year
                        },
                        success: function(data) {
                            try {
                                let response = JSON.parse(data); // Attempt to parse JSON
                                if (response.message === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'Request has been submitted.',
                                        icon: 'success',
                                        iconColor: '#15ca20',
                                        confirmButtonText: 'Ok',
                                        confirmButtonColor: '#0d6efd',
                                        allowOutsideClick: false,
                                        focusConfirm: false,
                                    }).then(() => {
                                        window.location.reload();
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

    function loader_circle(page) {
        $(page).html(
            '<div style="text-align: center;">' +
            '<img src="<?= base_url('assets/images/loader_seq.gif') ?>">' +
            '</div>'
        );
    }
</script>