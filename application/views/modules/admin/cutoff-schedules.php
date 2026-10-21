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
                            Monitor cutoff schedules.
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
                            <b>Cutoff Schedule Form</b>
                        </div>
                        <form id="addCutoff">
                            <div class="card-body">
                                <h6>1st. Cutoff</h6>
                                <div class="row mb-3">
                                    <label for="startFC" class="col-sm-5 col-form-label text-muted">Date From</label>
                                    <div class="form-group col-sm-7">
                                        <select class="form-control select2" name="startFC" id="startFC" onchange="firstDate(this.value)">
                                            <option value="">---</option>
                                            <?php
                                            for ($i = 1; $i <= 31; $i++) {

                                                $day = $i;
                                                if ($day < 10) {
                                                    $day = '0' . $i;
                                                }
                                                echo ' <option value="' . $day . '">' . $day . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="endFC" class="col-sm-5 col-form-label text-muted">Date To</label>
                                    <div class="form-group col-sm-7">
                                        <select class="form-control select2" name="endFC" id="endFC" onchange="secondDate(this.value)">
                                            <option value="">---</option>
                                            <?php
                                            for ($i = 1; $i <= 31; $i++) {

                                                $day = $i;
                                                if ($day < 10) {
                                                    $day = '0' . $i;
                                                }
                                                echo ' <option value="' . $day . '">' . $day . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <h6>2nd. Cutoff</h6>
                                <div class="row mb-3">
                                    <label for="startSC" class="col-sm-5 col-form-label text-muted">Date From</label>
                                    <div class="form-group col-sm-7">
                                        <input type="text" class="form-control form-control-sm" value="" id="startSC" name="startSC" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="endSC" class="col-sm-5 col-form-label text-muted">Date To</label>
                                    <div class="form-group col-sm-7">
                                        <input type="text" class="form-control form-control-sm" value="" id="endSC" name="endSC" readonly>
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
                            <b>Cutoff Schedule List</b>
                        </div>
                        <div class="card-body">
                            <div class="d-none d-md-block table-responsive">
                                <table class="table table-sm table-hover mb-0 align-middle" id="pageTable">
                                    <thead>
                                        <tr>
                                            <th>1st. Cutoff</th>
                                            <th>2nd. Cutoff</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 0;
                                        $scheds = $this->request->getArray('tk_cutoff_schedule', ['1' => 1]);
                                        foreach ($scheds as $c) {
                                            $i++;
                                            $id = $c['id'];
                                            $sFC = $c['startFC'];
                                            $eFC = $c['endFC'];

                                            $sSC = $c['startSC'];
                                            $eSC = $c['endSC'];

                                            if ($c['status'] == 1) {
                                                $status = '<span class="badge bg-light text-success border border-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">Active </span>';
                                            } else {
                                                $status = '<span class="badge bg-light text-danger border border-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">Inactive </span>';
                                            }
                                            $order = '<span style="display:none;">' . $id . '</span>';

                                            echo '<tr>';
                                            echo '<td>' . $order . '' . $sFC . ' - ' . $eFC . '</td>';
                                            echo '<td>' . $sSC . ' - ' . $eSC . '</td>';
                                            echo '<td>' . $status . '</td>';
                                            echo '<td></td>';
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
        $('.select2').select2({
            placeholder: "---",
            allowClear: true
        });

        $('table#pageTable').DataTable({
            "destroy": true,
            "order": [
                [0, "asc"]
            ],
            "columnDefs": [{
                "targets": [0, 1, 2, 3],
                "orderable": false,

            }, {
                "targets": [2, 3],
                "className": "text-center"
            }]
        });

        $("form#addCutoff").submit(function(e) {
            e.preventDefault();
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
                var value = $("select[name='" + field + "'],input[name='" + field + "']").val();

                if (value === '') {

                    if (!errMessage) {

                        alertRequired('Please fill the required fields.')
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
                    text: 'Are you sure you want to add cutoff schedule?',
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
                            text: 'Adding cutoff schedule...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/addCutoff') ?>",
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
                                                text: 'Cutoff schedule has been added.',
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
        });
    });

    function firstDate(startFC) {
        if (startFC != '') {

            var subC = Number(startFC) - 1;
            if (subC < 10) {
                subC = '0' + subC;
            }

            $('input[name="endSC"]').val(subC).css("border-color", "");
            if (startFC == 1) {
                $('input[name="endSC"]').val('31').css("border-color", "");
            }
        } else {
            $('input[name="endSC"]').val('').css("border-color", "");
        }
    }

    function secondDate(endFC) {
        if (endFC != '') {
            var startFC = $('select[name="startFC"]').val();
            if (startFC == '') {
                $('select[name="startFC"]').focus();
                $('select[name="endFC"]').val('');
                alertRequired('First Cutoff (Start) is required!')
                return;
            }

            if (startFC == endFC) {
                $('input[name="startSC"]').val('');
                $('select[name="endFC"]').val('');
                alertRequired('First Cutoff (Start and End) should not be the same!')
                return;
            }

            var addC = Number(endFC) + 1;
            if (addC < 10) {
                addC = '0' + addC;
            }

            $('input[name="startSC"]').val(addC).css("border-color", "");
            if (endFC == 31) {
                $('input[name="startSC"]').val('1').css("border-color", "");
            }
        } else {
            $('input[name="startSC"]').val('').css("border-color", "");
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