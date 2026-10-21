(function ($) {
  // Helper to toggle punch button states
  function setPunchButtonsState(enabled) {
    const $buttons = $('#btnTimeIn, #btnTimeOut');
    $buttons.prop('disabled', !enabled);

    if (enabled) {
      $buttons.removeClass('opacity-50 pe-none');
    } else {
      $buttons.addClass('opacity-50 pe-none');
    }
  }

  // Detect time period (Morning / Afternoon / Evening / Night)
  function getShiftPeriod(dateObj, isTimeIn) {
    const hour = dateObj.getHours();

    if (isTimeIn) {
      if (hour >= 4 && hour < 12)   return 'Morning In';
      if (hour >= 12 && hour < 18)  return 'Afternoon In';
      if (hour >= 18 && hour <= 23) return 'Evening In';
      return 'Night/Graveyard In';
    } else {
      if (hour >= 4 && hour < 12)   return 'Morning Out';
      if (hour >= 12 && hour < 18)  return 'Afternoon Out';
      if (hour >= 18 && hour <= 23) return 'Evening Out';
      return 'End Shift';
    }
  }

  // GPS Acquisition & Nominatim Geocoding[cite: 1]
  function initPageLocation() {
    const $statusEl = $('#timelogLocationStatus');
    if (!$statusEl.length) return;

    setPunchButtonsState(false);

    if (!navigator.geolocation) {
      $statusEl.html('<i class="bi bi-exclamation-circle text-warning me-1"></i> Geolocation not supported');
      return;
    }

    $statusEl.html('<i class="bi bi-arrow-repeat text-secondary me-1"></i> Acquiring GPS location...');

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;
        const accuracy = Math.round(pos.coords.accuracy);

        $('#punchLat').val(lat);
        $('#punchLng').val(lon);

        $statusEl.html('<i class="bi bi-hourglass-split text-secondary me-1"></i> Resolving address...');

        $.ajax({
          url: `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`,
          type: 'GET',
          headers: { 'Accept-Language': 'en' },
          timeout: 8000
        })
        .done(function (data) {
          const address = (data && data.display_name)
            ? data.display_name
            : `${lat.toFixed(4)}, ${lon.toFixed(4)}`;

          $('#punchLocationName').val(address);
          $statusEl.html(`
            <i class="bi bi-geo-alt-fill me-1 text-danger"></i>
            <span title="${address}">${address}</span>
            <span class="text-muted">(±${accuracy}m)</span>
          `);
          setPunchButtonsState(true);
        })
        .fail(function () {
          const fallback = `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
          $('#punchLocationName').val(fallback);
          $statusEl.html(`
            <i class="bi bi-geo-alt-fill me-1 text-danger"></i>
            <span>${fallback}</span>
          `);
          setPunchButtonsState(true);
        });
      },
      function (error) {
        let msg = 'Location unavailable';
        if (error.code === error.PERMISSION_DENIED) {
          msg = 'Location permission denied. GPS required.';
        } else if (error.code === error.POSITION_UNAVAILABLE) {
          msg = 'GPS signal unavailable. Enable device location.';
        } else if (error.code === error.TIMEOUT) {
          msg = 'Location request timed out.';
        }

        $statusEl.html(`<i class="bi bi-shield-x me-1 text-danger"></i> <span class="text-danger">${msg}</span>`);
        setPunchButtonsState(false);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  }

  function loadTodayPunchLogs() {
    const $container =$('#punchLogsContainer');
    if (!$container.length) return;

    const apiUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : $.baseUrl) + 'api/attendance/get_today_logs';

    $.ajax({
      url: apiUrl,
      type: 'GET',
      dataType: 'json',
      cache: false,
      success: function (res) {
        if (res && res.status) {
          // 1. Update Hours Rendered Card
          const rendered = parseFloat(res.hours_rendered) || 0.0;
          if ($('#summaryRenderedHours').length) {
            $('#summaryRenderedHours').html(`
              ${rendered.toFixed(2)} <small class="fw-normal" style="font-size: 0.75rem;">hrs</small>
            `);
          }

          // 2. Update Deficiency Card (7.0 hours standard)
          const requiredHours = parseFloat(res.hours_required) || 7.0;
          const deficiency = typeof res.hours_deficiency !== 'undefined'
            ? parseFloat(res.hours_deficiency)
            : Math.max(0, requiredHours - rendered);

          if ($('#summaryDeficiencyHours').length) {
            $('#summaryDeficiencyHours').html(`
              ${deficiency.toFixed(2)} <small class="fw-normal" style="font-size: 0.75rem;">hrs</small>
            `);
          }

          // 3. Render punch entries
          $container.empty();
          if (res.logs && res.logs.length > 0) {
            let html = '';
            res.logs.forEach(function (log) {
              const isTimeIn    = log.type === 'in';
              const badgeClass  = isTimeIn ? 'badge-in' : 'badge-out';
              const iconClass   = isTimeIn ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right';
              const iconWrapper = isTimeIn ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger';

              const displayLoc = isTimeIn 
                ? log.locationVal 
                : (log.updatedLocationVal || log.locationVal || 'Island City Mall, Tagbilaran, Bohol');

              const displayLat = isTimeIn ? log.lat : (log.updatedLat || log.lat);
              const displayLng = isTimeIn ? log.lng : (log.updatedLng || log.lng);

              const mapsLink = (displayLat && displayLng) 
                ? `https://www.google.com/maps?q=${displayLat},${displayLng}` 
                : '#';

              // Show cancel button ONLY if the entry is an open Time In (no Time Out completed)
              const cancelButtonHtml = log.can_cancel ? `
              <button type="button" 
                      class="btn btn-sm btn-outline-danger border-0 p-1 rounded-circle btn-cancel-punch" 
                      data-entry-id="${log.id}" 
                      data-type="${log.type}" 
                      data-title="${log.actionTitle}" 
                      data-time="${log.timeStr}" 
                      title="Cancel ${log.actionTitle}">
                <i class="bi bi-x-circle fs-5"></i>
              </button>
            ` : '';

              html += `
                <div class="timelog-item d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center mb-2 p-2 bg-white border rounded-3 position-relative gap-2">
                  
                  <!-- Left Section: Icon, Action Title, Shift Badge, Location -->
                  <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                    <div class="profile-field-icon ${iconWrapper} rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                      <i class="bi ${iconClass} fs-5"></i>
                    </div>
                    <div class="text-truncate" style="min-width: 0;">
                      <div class="d-flex align-items-center gap-2 mb-1 mb-sm-0">
                        <span class="fw-bold text-dark" style="font-size: 0.88rem;">${log.actionTitle}</span>
                        <span class="badge ${badgeClass} rounded-pill px-2 py-0" style="font-size: 0.65rem;">${log.shiftPeriod}</span>
                      </div>
                      <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                        <i class="bi bi-geo-alt-fill text-danger flex-shrink-0"></i>
                        <a href="${mapsLink}" target="_blank" class="text-truncate text-decoration-none text-muted d-inline-block" style="max-width: 250px;" title="${displayLoc}">
                          ${displayLoc}
                        </a>
                      </div>
                    </div>
                  </div>

                  <!-- Right Section: Time, Date & Single Cancel Button -->
                  <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-3 flex-shrink-0 pt-2 pt-sm-0 border-top border-sm-0 border-light timelog-right-section">
                    <div class="text-start text-sm-end timelog-time-box">
                      <div class="fw-bold text-dark fs-6" style="line-height: 1.2;">${log.timeStr}</div>
                      <div class="text-muted" style="font-size: 0.68rem;">${log.dateStr}</div>
                    </div>

                    ${cancelButtonHtml}
                  </div>

                </div>
              `;
            });
            $container.html(html);
          } else {
            $container.html(`
              <div class="text-center py-4 text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-clock-history fs-3 d-block mb-1 text-secondary opacity-50"></i>
                No time logs recorded for today yet.
              </div>
            `);
          }
        }
      }
    });
  }

  // Punch In / Out Click Handler
  $(document).on('click', '#btnTimeIn, #btnTimeOut', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const isTimeIn = $btn.attr('id') === 'btnTimeIn';
    const punchType = isTimeIn ? 'in' : 'out';

    const locationVal = $('#punchLocationName').val() ? $('#punchLocationName').val().trim() : '';
    const lat = $('#punchLat').val();
    const lng = $('#punchLng').val();
    const empId = $('#empId').val() || '';

    if (!locationVal) {
      Swal.fire({
        icon: 'warning',
        title: 'Location Required',
        text: 'Please wait for GPS location before punching.',
        confirmButtonColor: '#ff6b4a'
      });
      return;
    }

    // Time Out prompts for confirmation; Time In executes directly
    if (!isTimeIn) {
      Swal.fire({
        icon: 'question',
        title: 'Confirm Time Out',
        text: 'Are you sure you want to time out now?',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Time Out',
        cancelButtonText: 'Cancel',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          executePunch(isTimeIn, punchType, locationVal, lat, lng, empId);
        }
      });
    } else {
      executePunch(isTimeIn, punchType, locationVal, lat, lng, empId);
    }
  });

  function executePunch(isTimeIn, punchType, locationVal, lat, lng, empId) {
    // Synchronized Server Time
    const currentServerDate = (typeof window.getPhilippineDate === 'function')
      ? window.getPhilippineDate()
      : new Date();

    const shiftPeriod = getShiftPeriod(currentServerDate, isTimeIn);

    const timeFormatter24 = new Intl.DateTimeFormat('en-GB', {
      timeZone: 'Asia/Manila',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    });

    const dateFormatter = new Intl.DateTimeFormat('en-CA', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    });

    const formattedTime = timeFormatter24.format(currentServerDate);
    const formattedDate = dateFormatter.format(currentServerDate);

    setPunchButtonsState(false);

    const payload = {
      emp_id: empId,
      punch_type: punchType,
      action_type: isTimeIn ? 'TIME_IN' : 'TIME_OUT',
      date: formattedDate,
      remarks: 'Biometric Web Punch',
      dateInfo: {
        in1: isTimeIn ? formattedTime : null,
        out1: !isTimeIn ? formattedTime : null,
        in2: null,
        out2: null,
        hoursWork: 0
      },
      groups: [
        {
          punch_type: punchType,
          type: 'regular',
          category: '',
          timeRange: formattedTime,
          totalHours: 0,
          remarks: shiftPeriod,
          location: locationVal,
          latitude: lat,
          longitude: lng,
          accuracy: 0,
          hours: [],
          attachments: []
        }
      ],
      timeEntries: []
    };

    const apiUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : $.baseUrl) + 'api/attendance/save/timelog';

    $.ajax({
      url: apiUrl,
      type: 'POST',
      contentType: 'application/json; charset=utf-8',
      dataType: 'json',
      data: JSON.stringify(payload),
      success: function (res) {
        if (res.success === true || res.status === true) {
          Swal.fire({
            icon: 'success',
            title: isTimeIn ? 'Timed In Successfully!' : 'Timed Out Successfully!',
            text: res.message || `Your punch has been recorded for ${shiftPeriod}.`,
            confirmButtonColor: '#ff6b4a',
            confirmButtonText: 'OK'
          });

          loadTodayPunchLogs();
          //renderPunchCard(isTimeIn, locationVal, lat, lng, currentServerDate, shiftPeriod);
        } else {
          // Warning alert for interval restriction or duplicates
          Swal.fire({
            icon: 'warning',
            title: 'Attendance Notice',
            text: res.message || 'Unable to record punch at this time.',
            confirmButtonColor: '#ff6b4a',
            confirmButtonText: 'Understood'
          });
        }
      },
      error: function (xhr) {
        const errMsg = xhr.responseJSON?.message || 'Server error while logging attendance.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: errMsg,
          confirmButtonColor: '#dc3545'
        });
      },
      complete: function () {
        setPunchButtonsState(true);
      }
    });
  }


  $(document).on('click', '.btn-cancel-punch', function (e) {
    e.preventDefault();

    const entryId = $(this).data('entry-id');
    const punchType = $(this).data('type');
    const actionTitle = $(this).data('title');
    const timeStr = $(this).data('time');

    Swal.fire({
      icon: 'warning',
      title: `Cancel ${actionTitle}?`,
      text: `Are you sure you want to cancel your ${actionTitle} entry at ${timeStr}?`,
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, Cancel Entry',
      cancelButtonText: 'Keep it',
      reverseButtons: true
    }).then((result) => {
      if (!result.isConfirmed) return;

      const cancelUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : $.baseUrl) + 'api/attendance/cancel_punch';

      $.ajax({
        url: cancelUrl,
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        data: JSON.stringify({
          entry_id: entryId,
          type: punchType
        }),
        success: function (res) {
          if (res.status === true || res.success === true) {
            Swal.fire({
              icon: 'success',
              title: 'Entry Canceled',
              text: res.message || 'The punch entry has been canceled.',
              confirmButtonColor: '#ff6b4a'
            });
            // Refresh the attendance feed and metric cards
            loadTodayPunchLogs();
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Cancellation Failed',
              text: res.message || 'Unable to cancel this punch entry.',
              confirmButtonColor: '#ff6b4a'
            });
          }
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message || 'Server error while canceling entry.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: msg,
            confirmButtonColor: '#dc3545'
          });
        }
      });
    });
  });

  // Dynamic log entry rendering
  function renderPunchCard(isTimeIn, locationVal, lat, lng, serverDate, shiftPeriod) {
    const timeStr = serverDate.toLocaleTimeString('en-US', {
      timeZone: 'Asia/Manila',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    });

    const dateStr = serverDate.toLocaleDateString('en-US', {
      timeZone: 'Asia/Manila',
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });

    const actionTitle = isTimeIn ? 'Time In' : 'Time Out';
    const badgeClass  = isTimeIn ? 'badge-in' : 'badge-out';
    const iconClass   = isTimeIn ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right';
    const iconWrapper = isTimeIn ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger';
    const mapsLink    = (lat && lng) ? `https://www.google.com/maps?q=${lat},${lng}` : '#';

    const newLogHtml = `
      <div class="timelog-item d-flex justify-content-between align-items-center mb-2 p-2 bg-white border rounded-3" style="display: none;">
        <div class="d-flex align-items-center gap-3">
          <div class="profile-field-icon ${iconWrapper} rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class="bi ${iconClass} fs-5"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-bold text-dark" style="font-size: 0.88rem;">${actionTitle}</span>
              <span class="badge ${badgeClass} rounded-pill px-2 py-0" style="font-size: 0.65rem;">${shiftPeriod}</span>
            </div>
            <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.72rem;">
              <i class="bi bi-geo-alt-fill text-danger"></i>
              <a href="${mapsLink}" target="_blank" class="text-truncate text-decoration-none text-muted" style="max-width: 250px;" title="${locationVal}">
                ${locationVal}
              </a>
            </div>
          </div>
        </div>
        <div class="text-end">
          <div class="fw-bold text-dark fs-6">${timeStr}</div>
          <div class="text-muted" style="font-size: 0.68rem;">${dateStr}</div>
        </div>
      </div>
    `;

    const $entry = $(newLogHtml);
    $('#punchLogsContainer').prepend($entry);
    $entry.slideDown(200);
  }

  window.initPageLocation = initPageLocation;
  window.loadTodayPunchLogs = loadTodayPunchLogs;

})(jQuery);