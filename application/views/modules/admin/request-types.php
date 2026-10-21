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
                            Monitor all types of leave request.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="view-page" id="page-vacation-ledger">
                <div class="ledger-history-card shadow-sm">
                    <div class="align-items-center pb-2 border-bottom text-end">
                        <a href="javascript:;" class="btn btn-sm btn-success me-1" onclick="addRequestForm()"><i class="bi bi-plus-circle"></i> Request</a>
                    </div>
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle" id="pageTable">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Acronym</th>
                                    <th>Fixed Hours</th>
                                    <th>Fixed Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $requests = $this->request->getArray('tk_request_type', ['1' => 1]);
                                foreach ($requests as $r) {
                                    $id = $r['tkrtId'];
                                    $request = $r['request'];
                                    $acronym = $r['acroname'];
                                    $fixed_hour = $r['fixed_hour'];


                                    if ($r['fixed_type'] == 1) {
                                        $fixed_type = 'Full Day';
                                    } else if ($r['fixed_type'] == 2) {
                                        $fixed_type = 'Hourly';
                                    } else {
                                        $fixed_type = 'Full Day/Hourly';
                                    }

                                    if ($r['status'] == 'active') {
                                        $status = '<span class="badge bg-light text-success border border-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">Active </span>';
                                    } else {
                                        $status = '<span class="badge bg-light text-danger border border-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">Inactive </span>';
                                    }
                                    $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="updateRequestForm(\'' . $id . '\')">View</a>';

                                    echo '<tr>';
                                    echo '<td>' . $request . '</td>';
                                    echo '<td>' . $acronym . '</td>';
                                    echo '<td>' . $fixed_hour . '</td>';
                                    echo '<td>' . $fixed_type . '</td>';
                                    echo '<td>' . $status . '</td>';
                                    echo '<td>' . $action . '</td>';
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

<div class="modal fade" id="updateRequestForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateRequest" autocomplete="off">
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addRequestForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRequest" autocomplete="off">
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("table#pageTable").DataTable({
            "order": [0, "asc"],
            "columnDefs": [{
                    "targets": [1, 2, 3, 4, 5],
                    "orderable": false,
                },
                {
                    "targets": [1, 2, 3, 4, 5],
                    "className": "text-center",
                },
            ],
        });

        $("form#updateRequest").submit(function(e) {
            e.preventDefault();
            let modal = 'updateRequestForm';
            var $form = $(this);
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
                // var value = $("select[name='" + field + "'],input[name='" + field + "']").val();
                var $field = $form.find("select[name='" + field + "'], input[name='" + field + "']"); // ← scoped
                var value = $field.val();

                if (value === '') {

                    if (!errMessage) {

                        alertRequired('Please fill the required fields.', modal)
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
                    text: 'Are you sure you want to update request?',
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
                            text: 'Updating request...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            target: document.getElementById(modal),
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/updateRequest') ?>",
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
                                                text: 'Request has been updated.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonText: 'Ok',
                                                confirmButtonColor: '#0d6efd',
                                                allowOutsideClick: false,
                                                focusConfirm: false,
                                                target: document.getElementById(modal),
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message, modal)
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        console.error("Error:", data);
                                        errorAlert(data, modal)
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    console.error("AJAX Error:", textStatus, errorThrown);
                                    errorAlert(errorThrown, modal)
                                }
                            });
                        }, 2000);
                    }
                })
            }
        });

        $("form#addRequest").submit(function(e) {
            e.preventDefault();
            let modal = 'addRequestForm';
            var $form = $(this);
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
                // var value = $("select[name='" + field + "'],input[name='" + field + "']").val();
                var $field = $form.find("select[name='" + field + "'], input[name='" + field + "']"); // ← scoped
                var value = $field.val();

                if (value === '') {

                    if (!errMessage) {

                        alertRequired('Please fill the required fields.', modal)
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
                    text: 'Are you sure you want to add request?',
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
                            text: 'Adding request...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            target: document.getElementById(modal),
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/addRequest') ?>",
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
                                                text: 'Request has been added.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonText: 'Ok',
                                                confirmButtonColor: '#0d6efd',
                                                allowOutsideClick: false,
                                                focusConfirm: false,
                                                target: document.getElementById(modal),
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message, modal)
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        console.error("Error:", data);
                                        errorAlert(data, modal)
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    console.error("AJAX Error:", textStatus, errorThrown);
                                    errorAlert(errorThrown, modal)
                                }
                            });
                        }, 2000);
                    }
                })
            }
        });
    });

    function updateRequestForm(id) {
        $('#updateRequestForm').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#updateRequestForm').modal('show');

        $.ajax({
            url: "<?= site_url('admin/updateRequestForm') ?>",
            type: 'POST',
            data: {
                id
            },
            beforeSend: function() {
                loader_circle('#updateRequestForm .modal-body')
            },
            success: function(data) {
                $('#updateRequestForm .modal-body').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function addRequestForm() {
        $('#addRequestForm').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#addRequestForm').modal('show');

        $.ajax({
            url: "<?= site_url('admin/addRequestForm') ?>",
            type: 'POST',
            data: {},
            beforeSend: function() {
                loader_circle('#addRequestForm .modal-body')
            },
            success: function(data) {
                $('#addRequestForm .modal-body').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function alertRequired(message, modal) {
        Swal.fire({
            title: 'Required!',
            text: message,
            icon: 'warning',
            iconColor: '#ffc107',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd',
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