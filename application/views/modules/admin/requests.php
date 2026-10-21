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
                            Monitor and Approve Requests.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="view-page active-view" id="page-view-logs">
                <div class="row g-3 mb-3 request-page"></div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="request_view">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="request_list_title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="leave_list">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-sm mb-0 align-middle text-nowrap" id="requestsTable">
                    <thead>
                        <tr>
                            <th>Date Filed</th>
                            <th>Requested By</th>
                            <th>Leave Period</th>
                            <th>Total Days</th>
                            <th>Total Hours</th>
                            <th>Status</th>
                            <th>Action</th>
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

<div class="modal fade" id="request_pending">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="request_view">Pending Request</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="approveRequest('disapproved','request_pending')">Disapproved</button>
                <button type="button" class="btn btn-success" onclick="approveRequest('approved','request_pending')">Approved</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="request_approved">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="request_view">View Request</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="disapproved" onclick="approveRequest('disapproved','request_approved')">Disapproved</button>
                <button type="button" class="btn btn-success" id="approved" onclick="approveRequest('approved','request_approved')">Approved</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="request_change_dayoff">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="request_view">Change Dayoff List</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dayoff_list">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-sm mb-0 align-middle text-nowrap" id="cdoTable">
                    <thead>
                        <tr>
                            <th>Date Filed</th>
                            <th>Requested By</th>
                            <th>Old Dayoff</th>
                            <th>New Dayoff</th>
                            <th>Status</th>
                            <th>Action</th>
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

<div class="modal fade" id="request_pending_cdo">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="request_view">Pending Change Dayoff</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="approvechangeDayoff('disapproved','request_pending_cdo')">Disapproved</button>
                <button type="button" class="btn btn-success" onclick="approvechangeDayoff('approved','request_pending_cdo')">Approved</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="request_approved_cdo">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="cdo_view">View Change Dayoff</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewDutyHours">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="duty_list_title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="duty_list">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-sm mb-0 align-middle text-nowrap" id="dutyTable">
                    <thead>
                        <tr>
                            <th>Date Filed</th>
                            <th>Requested By</th>
                            <th>Duty Date</th>
                            <th>Category</th>
                            <th>Total Hours</th>
                            <th>Status</th>
                            <th>Action</th>
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

<div class="modal fade" id="viewDutyHoursReq">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="duty_req">Duty Hours Request</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="duty-approve-btn" onclick="dutyHoursApproval('disapproved','viewDutyHoursReq')" style="display: none;">Disapproved</button>
                <button type="button" class="btn btn-success" id="duty-disapprove-btn" onclick="dutyHoursApproval('approved','viewDutyHoursReq')" style="display: none;">Approved</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewVLperYear">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">VL for Payment</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="vl_payment_list">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">VL for Payment List</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-sm mb-0 align-middle text-nowrap" id="vl_payment_list_table">
                    <thead>
                        <tr>
                            <th>Date Filed</th>
                            <th>Requested By</th>
                            <th>Total Days</th>
                            <th>Status</th>
                            <th>Action</th>
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

<div class="modal fade" id="viewVLpaymentReq">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">VL Payment Request</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="vlPayment-approved-btn" onclick="vlPaymentApproval('1','viewVLpaymentReq')" style="display: none;">Approve</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="view_attachments">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
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
        requestPage();
    });

    function requestPage() {
        $.ajax({
            url: "<?= site_url('admin/requestPage') ?>",
            type: 'POST',
            data: {},
            success: function(data) {
                $('.request-page').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function viewRequests(code, request, status) {
        $('#request_view').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#request_view').modal('show');
        $('#request_list_title').text(request + ' List');

        $.ajax({
            url: "<?= site_url('admin/view_requests') ?>",
            type: 'POST',
            data: {
                code: code,
                status: status
            },
            beforeSend: function() {
                loader_circle('#request_view .modal-body')
            },
            success: function(data) {
                $('#request_view .modal-body').html(data);

                var payload = $('a.btn.btn-primary.co_schedule.active').attr('id');

                cutoffTable(payload)

                $('a.co_schedule').click(function() {
                    $('a.co_schedule').removeClass('btn-primary active').addClass('btn-secondary');
                    $(this).removeClass('btn-secondary').addClass('btn-primary active');
                    var payload = this.id;
                    cutoffTable(payload)
                });

                $('table#cutoffTable').on('click', 'a.leave_list', function() {
                    $('#leave_list').modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                    $('#leave_list').modal('show');

                    let [co, cutoff, startDate, endDate] = this.id.split('|');

                    $("#leave_list .modal-title").html('Leave Request List (<b>' + startDate + ' - ' + endDate + '</b>)');
                    leave_list(co, cutoff, startDate, endDate, code, status);
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function cutoffTable(payload) {
        var table = $('table#cutoffTable').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/requestCutoffTable'); ?>",
                type: "POST",
                data: {
                    payload
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3],
                "orderable": false,

            }, {
                "targets": [2, 3],
                "className": "text-center"
            }]
        });

        $('#request_view').on('shown.bs.modal', function() {
            table.columns.adjust().draw(false);
        });
    }

    function leave_list(co, cutoff, startDate, endDate, code, status) {
        var table = $('table#requestsTable').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/request_leave_list'); ?>",
                type: "POST",
                data: {
                    co,
                    cutoff,
                    startDate,
                    endDate,
                    code,
                    status
                },
            },
            "order": [0, "asc"],
            "columnDefs": [{
                    "targets": [1, 2, 3, 4, 5, 6],
                    "orderable": false,
                },
                {
                    "targets": [3, 4, 5, 6],
                    "className": "text-center",
                },
            ],
        });

        $('#leave_list').on('shown.bs.modal', function() {
            table.columns.adjust().draw(false);
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

    function viewRequestPending(leaveId, groupId, code) {
        $('#request_pending').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#request_pending').modal('show');

        $.ajax({
            url: "<?= site_url('admin/view_pending_requests') ?>",
            type: 'POST',
            data: {
                leaveId: leaveId,
                groupId: groupId,
                code: code
            },
            beforeSend: function() {
                loader_circle('#request_pending .modal-body')
            },
            success: function(data) {
                let res = JSON.parse(data);
                $('#request_pending .modal-body').html(res.form);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function viewRequestApproved(leaveId, groupId, code) {

        $('#request_approved').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#request_approved').modal('show');
        $.ajax({
            url: "<?= site_url('admin/view_approved_requests') ?>",
            type: 'POST',
            data: {
                leaveId: leaveId,
                groupId: groupId,
                code: code
            },
            beforeSend: function() {
                loader_circle('#request_approved .modal-body')
            },
            success: function(data) {
                let res = JSON.parse(data);
                $('#request_approved .modal-body').html(res.form);
                if (res.status == 'approved') {
                    $('#approved').hide();
                    $('#disapproved').show();
                } else {
                    $('#approved').show();
                    $('#disapproved').hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function approveRequest(status, modal) {

        let ogStat = '';
        if (modal == 'request_pending') {
            ogStat = $('input#original_status').val();
        } else {
            ogStat = $('input#approval_status').val();
        }

        let leaveId = $('input#leaveId').val();
        let groupId = $('input#groupId').val();
        let remarks = $('textarea#approverComments').val();

        if (leaveId == '') {
            errorAlert('No leave ID provided!');
            return;
        }
        let msg1 = '';
        let msg2 = '';
        let msg3 = '';
        if (status == 'approved') {
            msg1 = 'Are you sure you want to approve this leave?';
            msg2 = 'Approving leave...';
            msg3 = 'Leave request has been approved.';
        } else {
            msg1 = 'Are you sure you want to disapprove this leave?';
            msg2 = 'Disapproving leave...';
            msg3 = 'Leave request has been disapproved.';
        }

        Swal.fire({
            title: 'Confirm!',
            text: msg1,
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            allowOutsideClick: false,
            target: document.getElementById(modal),
            focusConfirm: false,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait!',
                    text: msg2,
                    allowOutsideClick: false,
                    target: document.getElementById(modal),
                    focusConfirm: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('admin/approveRequest') ?>",
                        type: 'POST',
                        data: {
                            leaveId: leaveId,
                            remarks: remarks,
                            groupId: groupId,
                            status: status
                        },
                        success: function(data) {
                            try {
                                let res = JSON.parse(data);
                                if (res.message === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: msg3,
                                        icon: 'success',
                                        iconColor: '#15ca20',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                        target: document.getElementById(modal),
                                        focusConfirm: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // requestPage();
                                            // viewRequests(res.code, res.request, ogStat)
                                            // $('#leave_list').modal('hide');
                                            // $('#' + modal).modal('hide');
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    Swal.close();
                                    errorAlert(res.message, modal)
                                }
                            } catch (e) {
                                Swal.close();
                                errorAlert(res.message, modal)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.close();
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown, modal)
                        }
                    });
                }, 1500);
            }
        })
    }

    function viewChangeDayoff(status) {
        $('#request_change_dayoff').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#request_change_dayoff').modal('show');
        $.ajax({
            url: "<?= site_url('admin/view_change_dayoff') ?>",
            type: 'POST',
            data: {
                status: status
            },
            beforeSend: function() {
                loader_circle('#request_change_dayoff .modal-body')
            },
            success: function(data) {
                $('#request_change_dayoff .modal-body').html(data);
                var payload = $('a.btn.btn-primary.do_schedule.active').attr('id');
                console.log(payload);
                doCutoffTable(payload)

                $('a.do_schedule').click(function() {
                    $('a.do_schedule').removeClass('btn-primary active').addClass('btn-secondary');
                    $(this).removeClass('btn-secondary').addClass('btn-primary active');
                    var payload = this.id;
                    doCutoffTable(payload)
                });

                $('table#doCutoffTable').on('click', 'a.dayoff_list', function() {
                    $('#dayoff_list').modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                    $('#dayoff_list').modal('show');

                    let [co, cutoff, startDate, endDate] = this.id.split('|');

                    $("#dayoff_list .modal-title").html('Change Dayoff List (<b>' + startDate + ' - ' + endDate + '</b>)');
                    dayoff_list(co, cutoff, startDate, endDate, status);
                });

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function doCutoffTable(payload) {
        var table = $('table#doCutoffTable').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/doCutoffTable'); ?>",
                type: "POST",
                data: {
                    payload
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3],
                "orderable": false,

            }, {
                "targets": [2, 3],
                "className": "text-center"
            }]
        });

        $('#request_change_dayoff').on('shown.bs.modal', function() {
            table.columns.adjust().draw(false);
        });
    }

    function dayoff_list(co, cutoff, startDate, endDate, status) {
        var table = $('table#cdoTable').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/request_dayoff_list'); ?>",
                type: "POST",
                data: {
                    co,
                    cutoff,
                    startDate,
                    endDate,
                    status
                },
            },
            "order": [0, "asc"],
            "columnDefs": [{
                    "targets": [1, 2, 3, 4, 5],
                    "orderable": false,
                },
                {
                    "targets": [4, 5],
                    "className": "text-center",
                },
            ],
        });

        $('#dayoff_list').on('shown.bs.modal', function() {
            table.columns.adjust().draw(false);
        });
    }

    function viewCDOPending(cdoId) {
        $('#request_pending_cdo').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#request_pending_cdo').modal('show');

        $.ajax({
            url: "<?= site_url('admin/view_pending_cdo') ?>",
            type: 'POST',
            data: {
                cdoId: cdoId
            },
            beforeSend: function() {
                loader_circle('#request_pending_cdo .modal-body')
            },
            success: function(data) {
                let res = JSON.parse(data);
                $('#request_pending_cdo .modal-body').html(res.form);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function viewCDOApproved(cdoId) {
        $('#request_approved_cdo').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#request_approved_cdo').modal('show');
        $.ajax({
            url: "<?= site_url('admin/view_approved_cdo') ?>",
            type: 'POST',
            data: {
                cdoId: cdoId
            },
            beforeSend: function() {
                loader_circle('#request_approved_cdo .modal-body')
            },
            success: function(data) {
                let res = JSON.parse(data);
                $('#request_approved_cdo .modal-body').html(res.form);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function approvechangeDayoff(status, modal) {
        let cdoId = $('input#cdoId').val();
        let remarks = $('textarea#approverComments').val();

        if (cdoId == '') {
            errorAlert('No change dayoff ID provided!');
            return;
        }

        let msg1 = '';
        let msg2 = '';
        let msg3 = '';
        if (status == 'approved') {
            msg1 = 'Are you sure you want to approve this change dayoff?';
            msg2 = 'Approving change dayoff...';
            msg3 = 'Change dayoff has been approved.';
        } else {
            msg1 = 'Are you sure you want to disapprove this change dayoff?';
            msg2 = 'Disapproving change dayoff...';
            msg3 = 'Change dayoff has been disapproved.';
        }

        Swal.fire({
            title: 'Confirm!',
            text: msg1,
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            allowOutsideClick: false,
            target: document.getElementById(modal),
            focusConfirm: false,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait!',
                    text: msg2,
                    allowOutsideClick: false,
                    target: document.getElementById(modal),
                    focusConfirm: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('admin/approvechangeDayoff') ?>",
                        type: 'POST',
                        data: {
                            cdoId: cdoId,
                            remarks: remarks,
                            status: status
                        },
                        success: function(data) {
                            try {
                                let res = JSON.parse(data);
                                if (res.message === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: msg3,
                                        icon: 'success',
                                        iconColor: '#15ca20',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                        target: document.getElementById(modal),
                                        focusConfirm: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // requestPage();
                                            // viewChangeDayoff('pending');
                                            // $('#' + modal).modal('hide');
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    Swal.close();
                                    errorAlert(res.message, modal)
                                }
                            } catch (e) {
                                Swal.close();
                                errorAlert(res.message, modal)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.close();
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown, modal)
                        }
                    });
                }, 1500);
            }
        })
    }

    function viewDutyHours(status) {
        $('#viewDutyHours').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#viewDutyHours').modal('show');
        $('#duty_list_title').text('Out of Office List');

        $.ajax({
            url: "<?= site_url('admin/view_duty') ?>",
            type: 'POST',
            data: {
                status: status
            },
            beforeSend: function() {
                loader_circle('#viewDutyHours .modal-body')
            },
            success: function(data) {
                $('#viewDutyHours .modal-body').html(data);
                var payload = $('a.btn.btn-primary.duty_schedule.active').attr('id');
                console.log(payload);
                dutyCutoffTable(payload)

                $('a.duty_schedule').click(function() {
                    $('a.duty_schedule').removeClass('btn-primary active').addClass('btn-secondary');
                    $(this).removeClass('btn-secondary').addClass('btn-primary active');
                    var payload = this.id;
                    dutyCutoffTable(payload)
                });

                $('table#dutyCutoffTable').on('click', 'a.duty_list', function() {
                    $('#duty_list').modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                    $('#duty_list').modal('show');

                    let [co, cutoff, startDate, endDate] = this.id.split('|');

                    $("#duty_list .modal-title").html('Out of Office List (<b>' + startDate + ' - ' + endDate + '</b>)');
                    duty_list(co, cutoff, startDate, endDate, status);
                });

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function dutyCutoffTable(payload) {
        var table = $('table#dutyCutoffTable').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/dutyCutoffTable'); ?>",
                type: "POST",
                data: {
                    payload
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3],
                "orderable": false,

            }, {
                "targets": [2, 3],
                "className": "text-center"
            }]
        });

        $('#viewDutyHours').on('shown.bs.modal', function() {
            table.columns.adjust().draw(false);
        });
    }

    function duty_list(co, cutoff, startDate, endDate, status) {
        var table = $('table#dutyTable').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/request_duty_list'); ?>",
                type: "POST",
                data: {
                    co,
                    cutoff,
                    startDate,
                    endDate,
                    status
                },
            },
            "order": [0, "asc"],
            "columnDefs": [{
                    "targets": [1, 2, 3, 4, 5, 6],
                    "orderable": false,
                },
                {
                    "targets": [4, 5, 6],
                    "className": "text-center",
                },
            ],
        });
        $('#duty_list').on('shown.bs.modal', function() {
            table.columns.adjust().draw(false);
        });
    }

    function viewDutyHoursReq(id, gIndex, status) {
        $('#viewDutyHoursReq').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#viewDutyHoursReq').modal('show');
        $('#duty_req').text('View Duty Hours Request');

        $.ajax({
            url: "<?= site_url('admin/view_duty_request') ?>",
            type: 'POST',
            data: {
                id: id,
                gIndex: gIndex,
                status: status
            },
            beforeSend: function() {
                loader_circle('#viewDutyHoursReq .modal-body')
            },
            success: function(data) {
                let res = JSON.parse(data);
                $('#viewDutyHoursReq .modal-body').html(res.form);
                if (status == 'pending') {
                    $('#duty-approve-btn').show();
                    $('#duty-disapprove-btn').show();
                } else {
                    $('#duty-approve-btn').hide();
                    $('#duty-disapprove-btn').hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function dutyHoursApproval(status, modal) {
        let remarks = $('textarea#approversComments').val();
        let recordsId = $('input#recordsId').val();
        let groupId = $('input#groupId').val();

        if (recordsId == '') {
            errorAlert('No Duty ID provided!');
            return;
        }

        let msg1 = '';
        let msg2 = '';
        let msg3 = '';
        if (status == 'approved') {
            msg1 = 'Are you sure you want to approve this duty hours request?';
            msg2 = 'Approving change dayoff...';
            msg3 = 'Duty hours request has been approved.';
        } else {
            msg1 = 'Are you sure you want to disapprove this duty hours request?';
            msg2 = 'Disapproving duty hours request...';
            msg3 = 'Duty hours request has been disapproved.';
        }

        Swal.fire({
            title: 'Confirm!',
            text: msg1,
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            allowOutsideClick: false,
            target: document.getElementById(modal),
            focusConfirm: false,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait!',
                    text: msg2,
                    allowOutsideClick: false,
                    target: document.getElementById(modal),
                    focusConfirm: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('admin/approve_duty_hours') ?>",
                        type: 'POST',
                        data: {
                            recordsId: recordsId,
                            remarks: remarks,
                            status: status,
                            groupId: groupId
                        },
                        success: function(data) {
                            try {
                                let res = JSON.parse(data);
                                if (res.message === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: msg3,
                                        icon: 'success',
                                        iconColor: '#15ca20',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                        target: document.getElementById(modal),
                                        focusConfirm: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // requestPage();
                                            // viewDutyHours('pending');
                                            // $('#' + modal).modal('hide');
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    Swal.close();
                                    errorAlert(res.message, modal)
                                }
                            } catch (e) {
                                Swal.close();
                                errorAlert(res.message, modal)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.close();
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown, modal)
                        }
                    });
                }, 1500);
            }
        })
    }

    function viewVLperYear(status) {
        $('#viewVLperYear').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#viewVLperYear').modal('show');

        $.ajax({
            url: "<?= site_url('admin/viewVLperYear') ?>",
            type: 'POST',
            data: {
                status
            },
            beforeSend: function() {
                loader_circle('#viewVLperYear .modal-body')
            },
            success: function(data) {
                $('#viewVLperYear .modal-body').html(data);
                $('table#vlPerYearTable').DataTable({
                    "destroy": true,
                    "order": [
                        [0, "desc"]
                    ],
                    "columnDefs": [{
                            "targets": [1, 2],
                            "orderable": false,

                        },
                        {
                            "targets": [0, 1, 2],
                            "className": "text-center"
                        }
                    ]
                });

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function vl_payment_list(year, status) {
        console.log(year, status)
        $('#vl_payment_list').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#vl_payment_list').modal('show');
        $('#vl_payment_list .modal-title').text('VL for Payment List (' + year + ')');

        $('table#vl_payment_list_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/vl_payment_list'); ?>",
                type: "POST",
                data: {
                    year,
                    status
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3, 4],
                "orderable": false,

            }, {
                "targets": [2, 3, 4],
                "className": "text-center"
            }]
        });
    }

    function viewVLpaymentReq(id) {
        $('#viewVLpaymentReq').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#viewVLpaymentReq').modal('show');

        $.ajax({
            url: "<?= site_url('admin/viewVLpayment') ?>",
            type: 'POST',
            data: {
                id
            },
            beforeSend: function() {
                loader_circle('#viewVLpaymentReq .modal-body')
            },
            success: function(data) {
                let res = JSON.parse(data);
                $('#viewVLpaymentReq .modal-body').html(res.form);
                if (res.status == 0) {
                    $('#vlPayment-approved-btn').show();
                } else {
                    $('#vlPayment-approved-btn').hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function vlPaymentApproval(status, modal) {

        let id = $('input#vlPaymentId').val();
        let comments = $('textarea#vlPaymentComments').val();

        if (id == '') {
            errorAlert('No VL Payment ID provided!');
            return;
        }
        console.log(status, comments)
        Swal.fire({
            title: 'Confirm!',
            text: 'Are you sure you want to approve this VL Payment?',
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            allowOutsideClick: false,
            target: document.getElementById(modal),
            focusConfirm: false,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait!',
                    text: 'Approving VL payment...',
                    allowOutsideClick: false,
                    target: document.getElementById(modal),
                    focusConfirm: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('admin/vlPaymentApproval') ?>",
                        type: 'POST',
                        data: {
                            id,
                            status,
                            comments
                        },
                        success: function(data) {
                            try {
                                let res = JSON.parse(data);
                                if (res.message === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'VL Payment has been approved.',
                                        icon: 'success',
                                        iconColor: '#15ca20',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                        target: document.getElementById(modal),
                                        focusConfirm: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            requestPage();
                                            viewVLperYear('pending')
                                            vl_payment_list(res.year, 'pending')
                                            $('#' + modal).modal('hide');
                                        }
                                    });
                                } else {
                                    Swal.close();
                                    errorAlert(res.message, modal)
                                }
                            } catch (e) {
                                Swal.close();
                                errorAlert(res.message, modal)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.close();
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown, modal)
                        }
                    });
                }, 1500);
            }
        })
    }

    function alertRequired(modal) {
        Swal.fire({
            title: 'Required!',
            text: 'Please fillup the required fields!',
            icon: 'warning',
            iconColor: '#ffc107',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd',
            allowOutsideClick: false,
            focusConfirm: false,
            target: document.getElementById(modal),
        });
    }

    function errorAlert(message, modal) {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            iconColor: '#dc3545',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#dc3545',
            allowOutsideClick: false,
            focusConfirm: false,
            target: document.getElementById(modal),
        })
    }

    function alertInfo(title, message, modal) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'info',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd',
            allowOutsideClick: false,
            focusConfirm: false,
            target: document.getElementById(modal),
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