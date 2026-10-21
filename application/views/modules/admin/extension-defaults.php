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
                            Monitor allowed extension time for request approval.
                        </p>
                    </div>
                </div>
            </header>
        </div>
        <div class="content-scrollable">
            <div class="view-page" id="page-vacation-ledger">
                <div class="row g-3 mb-3">
                    <?php
                    $cutoffs = $this->request->getArray('tk_cutoff_schedule', ['1' => 1]);
                    foreach ($cutoffs as $c) {
                        $id = $c['id'];
                        $sFC = $c['startFC'];
                        $eFC = $c['endFC'];

                        $sSC = $c['startSC'];
                        $eSC = $c['endSC'];

                        $schedule = "[ $sFC-$eFC / $sSC-$eSC ]";

                        $startDate = '';
                        $endDate = '';
                        $cond = ['co_schedule' => $id];
                        $e = $this->request->getRow('tk_cutoff_extension', $cond);
                        if ($e) {
                            $startDate = $e['startDate'];
                            $endDate = $e['endDate'];
                        }

                        echo '<div class="col-12 col-md-3">';
                        echo '<div class="card">
                                <div class="card-header">
                                   <b>' . $schedule . '</b>
                                </div>
                                    <form id="updateExtension_' . $id . '">
                                        <input type="hidden" name="co_schedule" value="' . $id . '">
                                        <input type="hidden" name="schedule" value="' . $schedule . '">
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <label for="dateFrom_' . $id . '" class="col-sm-5 col-form-label">Date From</label>
                                                <div class="form-group col-sm-7">
                                                    <input type="text" class="form-control datePicker" value="' . $startDate . '" id="startDate_' . $id . '" name="startDate_' . $id . '" placeholder="yyyy-mm-dd" autocomplete="off" required="">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label for="dateTo_' . $id . '" class="col-sm-5 col-form-label">Date To</label>
                                                <div class="form-group col-sm-7">
                                                    <input type="text" class="form-control datePicker" value="' . $endDate . '" id="endDate_' . $id . '" name="endDate_' . $id . '" placeholder="yyyy-mm-dd" autocomplete="off" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                                        </div>
                                    </form>
                            </div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        flatpickr(".datePicker", {
            dateFormat: "Y-m-d",
        });

        $("form[id^='updateExtension']").submit(function(e) {
            e.preventDefault();
            const formId = $(this).attr('id');
            console.log(formId)

            let shedule = $('input[name="schedule"]').val();
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
                    $("select[name='" + field + "'], input[name='" + field + "']").on("focus", function() {
                        $(this).css("border-color", "");
                    });
                }
            }
            if (fieldsComplete) {
                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to update extension?',
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
                            text: 'Updating extension...',
                            allowOutsideClick: false,
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('admin/updateExtension') ?>",
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
                                                text: 'Extension has been updated.',
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