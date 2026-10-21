<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AGC HRMS - Modern Portal</title>
  <meta name="base-url" content="<?= base_url(); ?>">
  
  <!-- Bootstrap 5 CSS -->
  <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css'); ?>" />
  <!-- Toastr -->
  <link rel="stylesheet" href="<?= base_url('assets/css/toastr.min.css'); ?>" />

  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css'); ?>">
</head>

<body>

<!-- 1. MODERN MINIMAL LOGIN SCREEN -->
<div id="authView">
  <div class="clean-login-card">
    <div class="text-center mb-4">
      <div class="login-logo-container">
        <img src="<?=  base_url('assets/images/hrms.png'); ?>" alt="HRMS Logo" />
      </div>
      <h5 class="fw-bold text-dark mb-1">AGC HRMS Portal</h5>
      <p class="text-muted small mb-0">Sign in to your hrms account</p>
    </div>

    <form id="loginForm">
      <!-- Hidden coordinates required for submission -->
      <input type="hidden" name="latitude" id="loginLatitude">
      <input type="hidden" name="longitude" id="loginLongitude">
      <input type="hidden" name="location" id="loginLocationName">
      
      <div class="field-wrapper">
        <label class="field-label">Username</label>
        <div class="field-input-box">
          <i class="bi bi-person-circle"></i>
          <input
            type="text"
            name="username"
            placeholder="Enter Username"
            autocomplete="off"
            autofocus="true"
            required>
        </div>
      </div>

      <div class="field-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="field-label mb-0">Password</label>
          <!-- <a href="#" class="text-decoration-none text-muted" style="font-size: 0.68rem;">Forgot password?</a> -->
        </div>
        <div class="field-input-box">
          <i class="bi bi-lock"></i>
          <input
            type="password"
            id="loginPassword"
            name="password"
            placeholder="••••••••"
            required>
          <button type="button" class="toggle-pass-btn" id="togglePasswordBtn">
            <i class="bi bi-eye m-0" id="togglePasswordIcon"></i>
          </button>
        </div>
      </div>

      <div class="form-check mb-4 mt-2">
        <input class="form-check-input" type="checkbox" id="rememberMe" checked>
        <label class="form-check-label text-muted" for="rememberMe" style="font-size: 0.72rem;">Stay signed in</label>
      </div>

      <button type="submit" class="btn-login-submit">
        <span>Sign In</span>
        <i class="bi bi-arrow-right ms-1"></i>
      </button>
    </form>
  </div>
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="appToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body text-white" id="toastMsg">
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script src="<?=  base_url('assets/js/jquery-3.7.1.min.js'); ?>"></script>
<script src="<?=  base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?=  base_url('assets/js/toastr.min.js'); ?>"></script>
<script src="<?=  base_url('assets/js/helper.min.js'); ?>"></script>
<script src="<?=  base_url('assets/js/login.min.js'); ?>"></script>
</body>
</html>