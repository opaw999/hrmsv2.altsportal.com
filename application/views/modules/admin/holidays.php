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
                            Monitor holidays.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <b>Holiday Form</b>
                        </div>
                        <form id="addHoliday" autocomplete="off">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 col-md-12">
                                        <label class="form-label small fw-semibold text-muted mb-1">Date</label>
                                        <input type="text" class="form-control form-control-sm datePicker" value="" placeholder="YYYY-MM-DD" name="date">
                                    </div>
                                    <div class="col-6 col-md-12 mt-3">
                                        <label class="form-label small fw-semibold text-muted mb-1">Holiday</label>
                                        <input type="text" class="form-control form-control-sm" value="" placeholder="Holiday name..." name="holiday">
                                    </div>
                                    <div class="col-6 col-md-12 mt-3">
                                        <label class="form-label small fw-semibold text-muted mb-1">Holiday Type</label>
                                        <select class="form-control form-control-sm select2" name="type">
                                            <option value="">---</option>
                                            <option value="Legal">Legal</option>
                                            <option value="Special">Special</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-sm btn-primary">Submit </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <b>Holiday List</b>
                        </div>
                        <div class="card-body">
                            <div class="d-none d-md-block table-responsive">
                                <table class="table table-sm table-hover mb-0 align-middle" id="pageTable">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Date Changed</th>
                                            <th>Holiday</th>
                                            <th>Type</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $holidays = $this->request->getArray('tk_holidays', ['1' => 1]);
                                        foreach ($holidays as $h) {

                                            $id = $h['id'];
                                            $date = date('M. j, Y', strtotime($h['date']));
                                            $order = date('Ymd', strtotime($date));
                                            $date_change = '';
                                            if ($h['date_change'] != '0000-00-00') {
                                                $date_change = date('M. j, Y', strtotime($h['date_change']));
                                            }

                                            $o = '<span style="display:none;">' . $order . '</span>';

                                            $holiday = $h['holiday'];
                                            $type = $h['type'];

                                            $action = '<a href="javascript:;" class="btn btn-sm btn-orange rounded-2 shadow-sm" onclick="updateHolidayForm(\'' . $id . '\')">View</a>';

                                            echo '<tr>';
                                            echo '<td>' . $o . '' . $date . '</td>';
                                            echo '<td>' . $date_change . '</td>';
                                            echo '<td>' . $holiday . '</td>';
                                            echo '<td>' . $type . '</td>';
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
        </div>
    </div>
</main>


<div class="modal fade" id="updateHolidayForm">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update Holiday </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateHoliday" autocomplete="off">
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        flatpickr(".datePicker", {
            dateFormat: "Y-m-d",
        });

        $('.select2').select2({
            placeholder: "---",
            allowClear: true
        });

        $('table#pageTable').DataTable({
            "destroy": true,
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [0, 1, 2, 3, 4],
                "orderable": false,

            }, {
                "targets": [3, 4],
                "className": "text-center"
            }]
        });

        $("form#addHoliday").submit(function(e) {
            e.preventDefault();
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

                        alertRequired('Please fill the required fields.', '')
                        errMessage = true;
                    }
                    fieldsComplete = false;
                    $("select[name='" + field + "'], input[name='" + field + "']").each(function() {
                        $(this).css("border-color", "#fd3550");
                    });
                    $("select[name='" + field + "']").next().find(".select2-selection").css("border-color", "#dd4b39");
                    $("select[name='" + field + "'], input[name='" + field + "'], .select2-selection").on("focus", function() {
                        $(this).css("border-color", "");
                    });
                }
            }
            if (fieldsComplete) {
                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to add holiday?',
                    icon: 'question',
                    iconColor: '#ffc107',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    focusConfirm: false,
                    target: document.getElementById(''),
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Please wait!',
                            text: 'Adding holiday...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            target: document.getElementById(''),
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/addHoliday') ?>",
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
                                                text: 'Holiday schedule has been added.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonText: 'Ok',
                                                confirmButtonColor: '#0d6efd',
                                                allowOutsideClick: false,
                                                focusConfirm: false,
                                                target: document.getElementById(''),
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message, '')
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        console.error("Error:", data);
                                        errorAlert(data, '')
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    console.error("AJAX Error:", textStatus, errorThrown);
                                    errorAlert(errorThrown, '')
                                }
                            });
                        }, 2000);
                    }
                })
            }
        });

        $("form#updateHoliday").submit(function(e) {
            e.preventDefault();
            let modal = 'updateHolidayForm';
            var $form = $(this);
            var formData = new FormData(this);
            var fields = [];
            let notRequired = ['date_change']
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
                    $("select[name='" + field + "']").next().find(".select2-selection").css("border-color", "#dd4b39");
                    $("select[name='" + field + "'], input[name='" + field + "'], .select2-selection").on("focus", function() {
                        $(this).css("border-color", "");
                    });
                }
            }
            if (fieldsComplete) {
                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to update holiday?',
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
                            text: 'Updating holiday...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            target: document.getElementById(modal),
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/updateHoliday') ?>",
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
                                                text: 'Holiday schedule has been updated.',
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

    function updateHolidayForm(id) {
        $('#updateHolidayForm').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#updateHolidayForm').modal('show');

        $.ajax({
            url: "<?= site_url('admin/updateHolidayForm') ?>",
            type: 'POST',
            data: {
                id
            },
            beforeSend: function() {
                loader_circle('#updateHolidayForm .modal-body')
            },
            success: function(data) {
                $('#updateHolidayForm .modal-body').html(data);
                flatpickr(".datePicker", {
                    dateFormat: "Y-m-d",
                });
                flatpickr(".datePickerClear", {
                    dateFormat: "Y-m-d",
                    allowInput: true,
                });
                $('.select2_update').select2({
                    placeholder: "---",
                    dropdownParent: $('#updateHolidayForm'),
                });
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

    function alertInfo(title, message) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'info',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd',
            allowOutsideClick: false,
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