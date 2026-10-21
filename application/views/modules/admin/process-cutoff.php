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
                        <p class="text-muted mb-0 mt-1 d-none d-sm-block" style="font-size: 0.72rem;">
                            Process totals hours and days rendered.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
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
                        <div class="col-12 col-md-6">
                            <table class="table table-hover mb-0 align-middle nowrap" id="processEmployeesTable">
                                <thead>
                                    <tr>
                                        <th>HRMS ID</th>
                                        <th>NAME</th>
                                        <th><input type="checkbox" class="form-check-input" id="selectAll" onclick="selectAll()"></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="row">
                                <?php
                                $usertype = $this->session->userdata('usertype');
                                if ($usertype == 'administrator') {
                                ?>
                                    <input type="hidden" id="empId" value="">
                                    <div class="col-12 col-md-12 position-relative">
                                        <label class="form-label small fw-semibold text-muted mb-1">Search Employee (temporary kay wala pa data)</label>
                                        <input type="search" class="form-control form-control-sm" id="name" placeholder="Search Employee" onkeyup="searchEmployee(this.value)" autocomplete="off">
                                        <div class="dropdown-list"></div>
                                    </div>
                                <?php
                                }
                                ?>
                                <div class="col-12 col-md-12 mt-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Cutoff Selection</label>
                                    <select class="form-control form-control-sm" id="cutoff">
                                        <option value="">---Select Cutoff---</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-12 mt-3 text-end">
                                    <button type="button" class="btn btn-primary" onclick="generateCutoffLogs()">Generate Cutoff</button>
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
        $('input[type="search"]').on('search', function() {
            if ($(this).val() === '') {
                $('.dropdown-list').html('');
                $('#empId').val('');
            }
        });

        var co = $('a.btn.btn-primary.process_schedule.active').attr('id');
        process_list(co)
        cutoff_list(co)

        $('a.process_schedule').click(function() {
            $('#selectAll').prop('checked', false);
            $('a.process_schedule').removeClass('btn-primary active').addClass('btn-secondary');
            $(this).removeClass('btn-secondary').addClass('btn-primary active');
            var co = this.id;
            process_list(co)
            cutoff_list(co)
        });

        $('.form-check-input:not(:checked)').css('border-color', '#aaa');
        $('.form-check-input').change(function() {
            $('.form-check-input:not(:checked)').css('border-color', '#aaa');
            if (!$(this).is(':checked')) {
                $(this).css('border-color', '#aaa');
            } else {
                $(this).css('border-color', '');
            }
        });
    });

    function process_list(co) {
        $("table#processEmployeesTable").DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('admin/process_list'); ?>",
                type: "POST",
                data: {
                    co
                },
            },
            "scrollY": "300px",
            "scrollCollapse": true,
            "paging": false,
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": [0, 2],
                "orderable": false,
            }, {
                "targets": [2],
                "className": "text-center",
            }],
        });

    }

    function cutoff_list(co) {
        $.ajax({
            url: "<?= site_url('admin/process_options') ?>",
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

    function selectAll() {
        if ($('#selectAll').is(':checked')) {
            $('input[name="empIds[]"]').prop('checked', true).css('border-color', '');
            $('.form-check-input:not(:checked)').css('border-color', '');
        } else {
            $('input[name="empIds[]"]').prop('checked', false).css('border-color', '#aaa');
        }
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

    function generateCutoffLogs() {
        let empId = $('#empId').val();
        let name = $('#name').val();
        let payload = $('#cutoff').val();

        var empIds = [];
        var table = $('table#processEmployeesTable').DataTable();
        table.rows().nodes().to$().find('input[name="empIds[]"]:checked').each(function() {
            empIds.push($(this).val());
        });

        if (empId != '' && empId != undefined) {
            empIds.push(empId);
        }

        if (empIds.length == 0) {
            alertRequired('Please select an employee.');
            return;
        }

        if (payload === '') {
            alertRequired('Please select a cutoff.');
            return;
        }

        let msg1 = 'Are you sure you want to generate cutoff logs?';
        let msg2 = 'Generating cutoff logs...';
        let msg3 = 'Cutoff logs have been generated.';


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
            focusConfirm: false,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait!',
                    text: msg2,
                    focusConfirm: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('admin/process_cutoff') ?>",
                        type: 'POST',
                        data: {
                            empIds: empIds,
                            payload: payload
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
                                        focusConfirm: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    Swal.close();
                                    errorAlert(res.message)
                                }
                            } catch (e) {
                                Swal.close();
                                errorAlert(res.message)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.close();
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown)
                        }
                    });
                }, 1500);
            }
        })


        // $.ajax({
        //     url: "<?= site_url('admin/generate_cutoff_logs') ?>",
        //     type: 'POST',
        //     data: {
        //         empId: empId,
        //         name: name,
        //         cutoff: cutoff
        //     },
        //     success: function(data) {
        //         alert('Cutoff Logs have been generated.');
        //     },
        //     error: function(jqXHR, textStatus, errorThrown) {
        //         console.error("AJAX Error:", textStatus, errorThrown);
        //         alert(errorThrown || 'Network error');
        //     }
        // });
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
</script>