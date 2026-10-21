<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HRMS - Modern Portal</title>
    <meta name="base-url" content="<?= base_url(); ?>">
    <!-- Bootstrap 5 CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css'); ?>" />

    <link rel="stylesheet" href="<?= base_url('assets/css/styles.css'); ?>">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

    <link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet" />
    <script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>
    <script>
        var BASE_URL = '<?= base_url(); ?>';
    </script>

    <script src="<?= base_url('assets/js/jquery-3.7.1.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/sammy-latest.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/clock.js'); ?>"></script>
    <script src="<?= base_url('assets/js/attendance.js'); ?>"></script>
    <script src="<?= base_url('assets/js/payroll.js'); ?>"></script>
    <script src="<?= base_url('assets/js/routes.js'); ?>"></script>
    <script src="<?= base_url('assets/js/toastr.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/helper.min.js'); ?>"></script>

    <link href="<?= base_url('assets/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/plugins/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') ?>" rel="stylesheet" />
    <script src="<?= base_url('assets/plugins/datatables.net/js/dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') ?>"></script>

</head>

<body>

    <div class="container-fluid p-0" id="appView">
        <div class="row g-0">
            <!-- Top Loading Bar Indicator -->
            <div id="spa-page-loader"></div>