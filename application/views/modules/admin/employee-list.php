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
                            Monitor all employee.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="view-page" id="page-vacation-ledger">
                <div class="ledger-history-card shadow-sm">

                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle" id="employeesTable">
                            <thead>
                                <tr>
                                    <th>HRMS ID</th>
                                    <th>Salary NO.</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Registered Location</th>
                                    <!-- <th class="text-center">Action</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $employees = $this->request->getArray('masterlist_2ndgen', ['status' => 1]);
                                foreach ($employees as $e) {
                                    $empid = $e['emp_id'];
                                    $emp = $this->request->getRow('employee3', ['emp_id' => $empid]);
                                    $name = $emp['name'];
                                    $position = $emp['position'];
                                    $location = $e['location'];
                                    $salary = $emp['payroll_no'];
                                    echo '<tr>';
                                    echo '<td>' . $empid . '</td>';
                                    echo '<td>' . $salary . '</td>';
                                    echo '<td>' . $name . '</td>';
                                    echo '<td>' . $position . '</td>';
                                    echo '<td>' . $location . '</td>';
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

<div class="modal fade" id="employee_vl" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee VL Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer d-none d-md-flex">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="vl_list" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
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
        $("table#employeesTable").DataTable({
            "order": [2, "asc"],
            "columnDefs": [{
                    "targets": [0, 1, 3, 4],
                    "orderable": false,
                },
                {
                    "targets": [0, 1, 2],
                    "className": "text-nowrap",
                },
                {
                    "targets": [1],
                    "className": "text-center",
                },
            ],
        });
    });

    function viewEmployee(empId) {
        $('#employee_vl').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#employee_vl').modal('show');

        $.ajax({
            url: "<?= site_url('admin/employee_vl') ?>",
            type: 'POST',
            data: {
                emp_id: empId
            },
            success: function(data) {
                $('#employee_vl .modal-body').html(data);
                $("#employee_vl_year_table").DataTable({
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
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function vl_list(empId, year) {

        $('#vl_list').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#vl_list').modal('show');
        $('#vl_list .modal-title').text('Vacation Leave - ' + year);

        $.ajax({
            url: "<?= site_url('admin/vl_list') ?>",
            type: 'POST',
            data: {
                empId,
                year
            },
            success: function(data) {
                $('#vl_list .modal-body').html(data);
                $("#vl_list_table").DataTable({
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
</script>