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
                            Monitor all render type limits.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="view-page" id="page-vacation-ledger">
                <div class="ledger-history-card shadow-sm">
                    <div class="align-items-center pb-2 border-bottom text-end">
                        <a href="javascript:;" class="btn btn-sm btn-success me-1" onclick="addRender()"><i class="bi bi-plus-circle"></i> Render Limit</a>
                    </div>
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle" id="rendersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Render Type</th>
                                    <th>Limit Type</th>
                                    <th>Limit Duration</th>
                                    <th>Hours Rendered</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $renders = $this->request->getArray('tk_render_limits', ['1' => 1]);
                                foreach ($renders as $r) {
                                    $id = $r['tkrlId'];
                                    $name = $r['name'];
                                    $render_type = $r['render_type'];
                                    $limit_type = ucwords($r['limit_type']);
                                    $limit_duration = ucwords($r['limit_duration']);
                                    $hours_rendered = $r['hours_rendered'];
                                    if ($r['status'] == 1) {
                                        $status = '<span class="badge bg-light text-success border border-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">Active </span>';
                                    } else {
                                        $status = '<span class="badge bg-light text-danger border border-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">Inactive </span>';
                                    }

                                    echo '<tr>';
                                    echo '<td>' . $name . '</td>';
                                    echo '<td>' . $render_type . '</td>';
                                    echo '<td>' . $limit_type . '</td>';
                                    echo '<td>' . $limit_duration . '</td>';
                                    echo '<td>' . $hours_rendered . '</td>';
                                    echo '<td>' . $status . '</td>';
                                    echo '<td class="text-center">';
                                    echo '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="viewRender(\'' . $id . '\')">View</a>';
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

<div class="modal fade" id="viewRender" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Render Limit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateRender" autocomplete="off">
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addRenderForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Render Limit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRender" autocomplete="off">
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
        $("table#rendersTable").DataTable({
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

        $("form#updateRender").submit(function(e) {
            e.preventDefault();
            let modal = 'viewRender';
            var $form = $(this);
            var formData = new FormData(this);
            var fields = [];
            let notRequired = []
            for (let entry of formData.entries()) {
                if (!notRequired.includes(entry[0])) {
                    fields.push(entry[0]);
                }
            }

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
                    text: 'Are you sure you want to update render limits?',
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
                            text: 'Updating render limits...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            target: document.getElementById(modal),
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/updateRender') ?>",
                                type: 'POST',
                                data: formData,
                                // cache: false,
                                contentType: false,
                                processData: false,
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data); // Attempt to parse JSON
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Render limits has been updated.',
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

        $("form#addRender").submit(function(e) {
            e.preventDefault();
            let modal = 'addRenderForm';
            var $form = $(this);
            var formData = new FormData(this);
            var fields = [];
            let notRequired = []
            for (let entry of formData.entries()) {
                if (!notRequired.includes(entry[0])) {
                    fields.push(entry[0]);
                }
            }

            var fieldsComplete = true;
            var errMessage = false;
            for (var i = 0; i < fields.length; i++) {

                var field = fields[i];
                // var value = $("select[name='" + field + "'],input[name='" + field + "']").val();
                var $field = $form.find("select[name='" + field + "'], input[name='" + field + "']");
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
                    text: 'Are you sure you want to add render limit?',
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
                            text: 'Adding render limit...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            target: document.getElementById(modal),
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/addRender') ?>",
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data); // Attempt to parse JSON
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Render limit has been added.',
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

    function viewRender(id) {
        $('#viewRender').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#viewRender').modal('show');

        $.ajax({
            url: "<?= site_url('admin/viewRender') ?>",
            type: 'POST',
            data: {
                id
            },
            beforeSend: function() {
                loader_circle('#viewRender .modal-body')
            },
            success: function(data) {
                $('#viewRender .modal-body').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert(errorThrown || 'Network error');
            }
        });
    }

    function addRender() {
        $('#addRenderForm').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#addRenderForm').modal('show');

        $.ajax({
            url: "<?= site_url('admin/viewRender') ?>",
            type: 'POST',
            data: {
                id: 'add'
            },
            beforeSend: function() {
                loader_circle('#addRenderForm .modal-body')
            },
            success: function(data) {
                $('#addRenderForm .modal-body').html(data);
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