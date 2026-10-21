(function ($) {
  let showAll = false;

  // 1. Fetch employee list from API
  function loadHourlyRates() {
    const $tbody =$('#rateMasterTableBody');
    if (!$tbody.length) return;

    const apiUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : ($.baseUrl || '')) + 'api/payroll/masterfile/list';

    $.ajax({
      url: apiUrl,
      type: 'GET',
      dataType: 'json',
      cache: false,
      beforeSend: function () {
        $tbody.html(`
          <tr>
            <td colspan="5" class="text-center py-4 text-muted">
              <div class="spinner-border spinner-border-sm text-secondary me-1" role="status"></div>
              Loading employees...
            </td>
          </tr>
        `);
      },
      success: function (res) {
        if (res && res.status && res.data) {
          $('#recordCountBadge').text(`${res.total} records`);
          $tbody.empty();

          if (res.data.length === 0) {
            $tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted">No employee records found.</td></tr>');
            return;
          }

          let rowsHtml = '';
          res.data.forEach(function (emp) {
            const formattedRate = parseFloat(emp.hourly_rate || 0).toFixed(2);
            const displayRate = showAll ? `₱${formattedRate}` : '******';
            const maskClass = showAll ? 'unmasked-rate' : 'masked-rate';
            const btnText = showAll ? '<i class="bi bi-eye-slash"></i> HIDE' : '<i class="bi bi-eye"></i> SHOW';

            rowsHtml += `
                <tr class="rate-item-row" data-empid="${emp.emp_id}" data-salno="${emp.salary_num}" data-name="${emp.name}" data-rate="${formattedRate}">
                  <td><span class="salary-num-badge">${emp.emp_id}</span></td>
                  <td>${emp.salary_num}</td>
                  <td class="fw-semibold text-dark">${emp.name}</td>
                  <td>
                    <span class="rate-display ${maskClass}" data-actual="₱${formattedRate}">${displayRate}</span>
                  </td>
                  <td class="text-end" style="white-space: nowrap;">
                    <div class="dropdown d-inline-block">
                      <button class="btn btn-sm btn-light border py-1 px-2 dropdown-toggle-custom" 
                              type="button" 
                              data-bs-toggle="dropdown" 
                              aria-expanded="false" 
                              title="Row actions">
                        <i class="bi bi-three-dots-vertical"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow-sm border p-1" style="font-size: 0.78rem; min-width: 140px; border-radius: 10px;">
                        <li>
                          <button type="button" class="dropdown-item rounded-2 py-1 px-2 btn-toggle-show">
                           <span class="toggle-text">${btnText}</span>
                          </button>
                        </li>
                        <li>
                          <button type="button" class="dropdown-item rounded-2 py-1 px-2 btn-select-row text-secondary">
                            <i class="bi bi-pencil-square me-2 text-muted"></i>Select
                          </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                          <button type="button" class="dropdown-item rounded-2 py-1 px-2 btn-select-view text-primary fw-medium">
                            <i class="bi bi-clock-history me-2"></i>View Log
                          </button>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              `;
          });

          $tbody.html(rowsHtml);
        }
      },
      error: function () {
        $tbody.html('<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load employee directory.</td></tr>');
      }
    });
  }

  // 2. Single Row Show / Hide Mask Toggle
  $(document).off('click', '.btn-toggle-show').on('click', '.btn-toggle-show', function (e) {
    e.stopPropagation(); // Keeps dropdown state predictable
    const btn = $(this);
    const row = btn.closest('.rate-item-row');
    const displaySpan = row.find('.rate-display');
    const textSpan = btn.find('.toggle-text');
    const icon = btn.find('i');

    const isMasked = displaySpan.hasClass('masked-rate') || displaySpan.text().trim() === '******';

    if (isMasked) {
      displaySpan.text(displaySpan.data('actual')).removeClass('masked-rate').addClass('unmasked-rate');
      textSpan.text('Hide');
      icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
      displaySpan.text('******').removeClass('unmasked-rate').addClass('masked-rate');
      textSpan.text('Show');
      icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
  });

  // 3. Bulk Show All / Hide All Toggle
  $(document).off('click', '#btnToggleAllRates').on('click', '#btnToggleAllRates', function () {
    showAll = !showAll;

    $('.rate-item-row').each(function () {
      const displaySpan = $(this).find('.rate-display');
      if (showAll) {
        displaySpan.text(displaySpan.data('actual')).removeClass('masked-rate').addClass('unmasked-rate');
        $(this).find('.btn-toggle-show').html('<i class="bi bi-eye-slash"></i> HIDE');
      } else {
        displaySpan.text('******').removeClass('unmasked-rate').addClass('masked-rate');
        $(this).find('.btn-toggle-show').html('<i class="bi bi-eye"></i> SHOW');
      }
    });

    $('#allRateIcon').toggleClass('bi-eye-slash-fill bi-eye-fill', showAll);
    $('#allRateText').text(showAll ? 'Hide All Rates' : 'Show All Rates');
  });

  // Click Handler: Open Modal & Fetch Rate History Log
$(document).off('click', '.btn-select-view').on('click', '.btn-select-view', function () {
    const row = $(this).closest('.rate-item-row');
    const empID = row.data('empid');
    const salNo = row.data('salno');
    const name  = row.data('name');

    // Update modal header info
    $('#modalRateLogSubtitle').text(`${name} (${salNo})`);

    // Reset table to loading state
    const $tbody =$('#rateLogsTableBody');
    $tbody.html(`
      <tr>
        <td colspan="4" class="text-center py-4 text-muted">
          <div class="spinner-border spinner-border-sm text-secondary me-1" role="status"></div>
          Loading rate history...
        </td>
      </tr>
    `);

    // Open the Bootstrap 5 Modal
    const modalEl = document.getElementById('modalRateLog');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();

    // Endpoint to fetch logs
    const rootUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : ($.baseUrl || '');
    const apiUrl = rootUrl + (rootUrl.endsWith('/') ? '' : '/') + 'payroll/get_rate_logs';

    // Request logs via AJAX
    $.ajax({
      url: apiUrl,
      type: 'GET',
      data: { emp_id: empID },
      dataType: 'json',
      cache: false,
      success: function (res) {
        if (res && res.status && Array.isArray(res.data)) {
          if (res.data.length === 0) {
            $tbody.html('<tr><td colspan="4" class="text-center py-4 text-muted">No rate change logs found for this employee.</td></tr>');
            return;
          }

          let rowsHtml = '';
          res.data.forEach(function (log) {
            const fromRate = parseFloat(log.rate_from || 0).toFixed(2);
            const toRate   = parseFloat(log.rate_to || 0).toFixed(2);
            const dateStr  = log.created_at || '---';
            const editedBy = log.edited_by_name || log.edited_by || 'System';

            rowsHtml += `
              <tr>
                <td class="ps-3 text-dark fw-medium">${dateStr}</td>
                <td class="text-muted"><del>₱${fromRate}</del></td>
                <td><span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">₱${toRate}</span></td>
                <td class="text-muted">${editedBy}</td>
              </tr>
            `;
          });

          $tbody.html(rowsHtml);
        } else {
          $tbody.html('<tr><td colspan="4" class="text-center py-4 text-warning">Unable to parse rate history.</td></tr>');
        }
      },
      error: function (xhr, status, error) {
        console.error('Fetch rate logs error:', xhr.status, xhr.responseText);
        $tbody.html('<tr><td colspan="4" class="text-center py-4 text-danger">Failed to retrieve rate history.</td></tr>');
      }
    });
  });

  // 4. Select row into left form (delegated listener)
  $(document).off('click', '.btn-select-row').on('click', '.btn-select-row', function () {
    const row = $(this).closest('tr');
    const empID = row.data('empid');
    const salNo = row.data('salno');
    const name = row.data('name');
    const rate = row.data('rate');

    $('#inputSalNo').val(salNo);
    $('#empID').val(empID);
    $('#salNo').val(salNo);
    $('#inputHourlyRate').val(rate).focus();
    $('#selectedEmployeeName').html(`Editing: <strong class="text-danger">${name}</strong>`);
  });

  // 5. Clear Form
  $(document).off('click', '#btnResetForm').on('click', '#btnResetForm', function () {
    $('#hourlyRateForm')[0].reset();
    $('#selectedEmployeeName').text('Select from table or type manually');
  });

  // 6. Search Filter
  $(document).off('keyup', '#employeeRateSearch').on('keyup', '#employeeRateSearch', function () {
    const val = $(this).val().toLowerCase();$('#rateMasterTable .rate-item-row').each(function () {
      const rowText = $(this).text().toLowerCase();$(this).toggle(rowText.indexOf(val) > -1);
    });
  });

  // 1. Prevent typing a value above 1999.99 in real time
  $(document).off('input', '#inputHourlyRate').on('input', '#inputHourlyRate', function () {
    const val = parseFloat($(this).val());
    if (val > 1999.99) {
        $(this).val('1999.99');
    }
  });

  // 7. Save / Update Rate Form Submission
  $(document).off('submit', '#hourlyRateForm').on('submit', '#hourlyRateForm', function (e) {
    e.preventDefault();

    const salNo = $('#inputSalNo').val();
    const rateInput = $('#inputHourlyRate').val();
    const rateVal = parseFloat(rateInput);

    // Validate range: 0.01 to 1999.99
    if (isNaN(rateVal) || rateVal <= 0) {
        Swal.fire({
        icon: 'warning',
        title: 'Invalid Rate',
        text: 'Hourly rate must be greater than ₱0.00.',
        confirmButtonColor: '#ff6b4a'
        });
        return;
    }

    if (rateVal > 1999.99) {
        Swal.fire({
        icon: 'warning',
        title: 'Rate Exceeded',
        text: 'Hourly rate cannot exceed ₱1,999.99.',
        confirmButtonColor: '#ff6b4a'
        });
        $('#inputHourlyRate').val('1999.99').focus();
        return;
    }

    const newRate = rateVal.toFixed(2);
    const rootUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : ($.baseUrl || '');
    const apiUrl = rootUrl + (rootUrl.endsWith('/') ? '' : '/') + 'api/payroll/masterfile/update';

    $.ajax({
        url: apiUrl,
        type: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        success: function (res) {
        if (res.status === true) {
            Swal.fire({
            icon: 'success',
            title: 'Rate Updated',
            text: `Salary Num ${salNo} updated to ₱${newRate}/hr.`,
            confirmButtonColor: '#ff6b4a'
            });

            const matchedRow = $(`.rate-item-row[data-salno="${salNo}"]`);
            if (matchedRow.length) {
            matchedRow.data('rate', newRate);
            matchedRow.find('.rate-display').data('actual', `₱${newRate}`);
            if (matchedRow.find('.rate-display').hasClass('unmasked-rate')) {
                matchedRow.find('.rate-display').text(`₱${newRate}`);
            }
            } else {
            loadHourlyRates();
            }
        } else {
            Swal.fire({
            icon: 'error',
            title: 'Save Failed',
            text: res.message || 'Could not update hourly rate.',
            confirmButtonColor: '#dc3545'
            });
        }
        },
        error: function () {
        Swal.fire({
            icon: 'error',
            title: 'Server Error',
            text: 'An error occurred while saving the rate.',
            confirmButtonColor: '#dc3545'
        });
        }
    });
  });

  // Main page initializer triggered by SPA router
  function initHourlyRatePage() {
    showAll = false;
    $('#allRateIcon').removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
    $('#allRateText').text('Show All Rates');
    loadHourlyRates();
  }

  // Globally exposed
  window.initHourlyRatePage = initHourlyRatePage;
  window.loadHourlyRates = loadHourlyRates;

})(jQuery);