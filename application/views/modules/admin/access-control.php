<style>
    .dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
        margin-top: 2px;
    }

    .dropdown-list:empty {
        display: none;
        /* hide the box when no results */
    }

    /* Optional: styling for the result rows returned by the controller */
    .dropdown-list .dropdown-item {
        padding: .45rem .75rem;
        cursor: pointer;
        font-size: .85rem;
        white-space: normal;
        color: #212529;
        transition: background-color .15s ease, color .15s ease;
    }

    .dropdown-list .list-group-item:hover,
    .dropdown-list .list-group-item.active {
        background-color: #0d6efd;
        color: #fff;
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
                            Monitor all access control for administrator and supervisor.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="view-page" id="page-vacation-ledger">
                <div class="ledger-history-card shadow-sm">
                    <div class="align-items-center pb-2 border-bottom text-end">
                        <a href="javascript:;" class="btn btn-sm btn-success px-3 me-1" onclick="addAdminUser()"><i class="bi bi-person-plus-fill"></i> User</a>
                    </div>
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle" id="employeesTable">
                            <thead>
                                <tr>
                                    <th>HRMS ID</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Access</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $employees = $this->request->getArray('tk_user_admin', ['1' => 1]);
                                foreach ($employees as $e) {
                                    $empid = $e['emp_id'];
                                    $emp = $this->request->getRow('employee3', ['emp_id' => $empid]);
                                    $name = $emp['name'];
                                    $position = $emp['position'];
                                    $access = strtoupper($e['type']);

                                    if ($e['status'] == 1) {
                                        $status = '<span class="badge bg-light text-success border border-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">Active </span>';
                                    } else {
                                        $status = '<span class="badge bg-light text-danger border border-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">Inactive </span>';
                                    }

                                    echo '<tr>';
                                    echo '<td>' . $empid . '</td>';
                                    echo '<td>' . $name . '</td>';
                                    echo '<td>' . $position . '</td>';
                                    echo '<td>' . $access . '</td>';
                                    echo '<td>' . $status . '</td>';
                                    echo '<td class="text-center">';
                                    echo '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm me-1" onclick="accessDetails(\'' . $empid . '\')">View</a>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="access_details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Access Control Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer d-none d-md-flex">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addAdminUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-12 position-relative">
                        <label class="form-label small fw-bold  mb-1">Search User </label>
                        <input type="search" class="form-control form-control-sm" id="name" placeholder="Search user..." onkeyup="searchEmployee(this.value)" autocomplete="off">
                        <div class="dropdown-list"></div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="form-label small fw-bold  mb-1">User Name (Default)</label>
                        <input type="text" class="form-control form-control-sm" id="empId" disabled>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="form-label small fw-bold  mb-1">User Password (Default)</label>
                        <input type="text" class="form-control form-control-sm" id="password" value="Hrms2014" disabled>
                    </div>
                    <div class="col-md-12 mt-3">
                        <label class="form-label small fw-bold  mb-1">User Type</label>
                        <select class="form-control form-control-sm" id="usertype_new">
                            <option value="">---</option>
                            <option value="administrator">Administrator</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>
                    <div class="col-md-12 mt-3">
                        <table class="table table-sm table-hover mb-0 align-middle nowrap" id="accessTable">
                            <thead>
                                <tr>
                                    <th>Menu Name</th>
                                    <th class="text-center">Access</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $menus = $this->request->getArray('tk_admin_menus', ['status' => 1]);
                                foreach ($menus as $m) {
                                    $id = $m['id'];
                                    $mn = $m['menu_name'];
                                    $mc = $m['menu_code'];

                                    $action = '<input type="checkbox" class="user_menu" value="' . $id . '">';

                                    echo '<tr>';
                                    echo '<td>' . $mn . '</td>';
                                    echo '<td class="text-center">' . $action . '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="add_adminUser('addAdminUser')">Submit </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("table#employeesTable").DataTable({
            "order": [1, "asc"],
            "columnDefs": [{
                    "targets": [0, 2, 3, 4, 5],
                    "orderable": false,
                },
                {
                    "targets": [0, 1],
                    "className": "text-nowrap",
                },
                {
                    "targets": [4, 5],
                    "className": "text-center",
                },
            ],
        });

        $('input[type="search"]').on('search', function() {
            if ($(this).val() === '') {
                $('.dropdown-list').html('');
                $('#empId').val('');
            }
        });
    });

    function accessDetails(empId) {
        $('#access_details').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#access_details').modal('show');

        $.ajax({
            url: "<?= site_url('admin/access_details') ?>",
            type: 'POST',
            data: {
                empId
            },
            beforeSend: function() {
                loader_circle('#access_details .modal-body')
            },
            success: function(data) {
                $('#access_details .modal-body').html(data);
                $("table#subordinatesTable").DataTable({
                    "order": [1, "asc"],
                    "columnDefs": [{
                            "targets": [0, 2],
                            "orderable": false,
                        },
                        {
                            "targets": [2],
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

    function updateAccess(empId, id, checkbox) {
        let request = checkbox.checked ? 'add' : 'delete';
        $.ajax({
            url: "<?= site_url('admin/updateAccess') ?>",
            type: 'POST',
            data: {
                empId,
                id,
                request
            },
            success: function(data) {
                let response = JSON.parse(data);
                if (response.message === 'success') {
                    console.log('Access Control Success!');
                } else {
                    errorAlert('Access Control Failed!', 'access_details');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown, 'access_details')
            }
        });
    }

    function updateUser(empId) {
        let status = $('#userstatus').val();
        let type = $('#usertype').val();
        let modal = 'access_details';

        Swal.fire({
            title: 'Confirm!',
            text: 'Are you sure you want to update user?',
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            allowOutsideClick: false,
            focusConfirm: false,
            target: document.getElementById(modal),
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait!',
                    text: 'Updating user...',
                    allowOutsideClick: false,
                    focusConfirm: false,
                    target: document.getElementById(modal),
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('admin/update_adminUser') ?>",
                        type: 'POST',
                        data: {
                            empId,
                            status,
                            type
                        },
                        success: function(data) {
                            try {
                                let res = JSON.parse(data);
                                if (res.message === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'User has been updated.',
                                        icon: 'success',
                                        iconColor: '#15ca20',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                        focusConfirm: false,
                                        target: document.getElementById(modal),
                                    }).then((result) => {
                                        if (result.isConfirmed) {
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

    function addAdminUser() {
        $('#addAdminUser').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#addAdminUser').modal('show');
    }

    function searchEmployee(string) {
        $('#empId').val('');
        if (string.length > 0) {
            $.ajax({
                url: "<?= site_url('admin/search_employee') ?>",
                type: 'POST',
                data: {
                    string: string
                },
                success: function(data) {
                    $('.dropdown-list').html(data);
                },
                beforeSend: function() {
                    $('.dropdown-list').html(`<ul class="list-group"><span class="list-group-item">
                        <div class="spinner-border spinner-border-sm" role="status"><span class="sr-only"></span></div>
                        </span></ul>`);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown);
                    errorAlert(errorThrown)
                }
            });
        } else {
            $('.dropdown-list').html('');
        }
    }

    function selectEmployee(empId, name) {
        $('.dropdown-list').html('');
        $('#empId').val(empId);
        $('#name').val(name);
    }

    function add_adminUser(modal) {
        let empId = $('#empId').val();
        let type = $('#usertype_new').val();
        let menus = [];
        $('.user_menu:checked').each(function() {
            menus.push($(this).val());
        });

        if (empId == '') {
            errorAlert('No user provided!', modal);
            return;
        }
        if (type == '') {
            errorAlert('No user type provided!', modal);
            return;
        }

        if (menus.length == 0) {
            errorAlert('No menu selected!', modal);
            return;
        }

        let msg1 = 'Are you sure you want to add this user?';
        let msg2 = 'Adding user...';
        let msg3 = 'User has been added.';

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
                        url: "<?= site_url('admin/add_adminUser') ?>",
                        type: 'POST',
                        data: {
                            empId,
                            type,
                            menus
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

    function resetPassword(empId) {
        let modal = 'access_details';
        Swal.fire({
            title: 'Confirm!',
            text: 'Are you sure you want to reset password?',
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
                    text: 'Resetting password...',
                    allowOutsideClick: false,
                    focusConfirm: false,
                    target: document.getElementById(modal),
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('admin/resetPassword') ?>",
                        type: 'POST',
                        data: {
                            empId
                        },
                        success: function(data) {
                            try {
                                let res = JSON.parse(data);
                                if (res.message === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'Password has been reset.',
                                        icon: 'success',
                                        iconColor: '#15ca20',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                        focusConfirm: false,
                                        target: document.getElementById(modal),
                                    }).then((result) => {
                                        if (result.isConfirmed) {
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

    function updateSubordinates(approver, approvee, checkbox) {
        let request = checkbox.checked ? 'add' : 'delete';
        console.log(approver, approvee, checkbox);
        console.log(request);
        $.ajax({
            url: "<?= site_url('admin/updateSubordinates') ?>",
            type: 'POST',
            data: {
                approver,
                approvee,
                request
            },
            success: function(data) {
                let response = JSON.parse(data);
                if (response.message === 'success') {
                    console.log('Access Control Success!');
                } else {
                    errorAlert('Access Control Failed!', 'access_details');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown, 'access_details')
            }
        });
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