(function ($) {
  // Offset in milliseconds: serverTime - clientLocalTime
  let serverTimeOffset = null;
  let isClockRunning = false;
  let isFetchingServerTime = false;

  function fetchServerTime(callback) {
    if (isFetchingServerTime) return;
    isFetchingServerTime = true;

    const requestStart = Date.now();
    const apiUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'api/server_time';

    $.ajax({
      url: apiUrl,
      type: 'GET',
      dataType: 'json',
      cache: false,
      success: function (res) {
        if (res && res.timestamp) {
          const requestEnd = Date.now();

          // Half of round-trip time accounts for network latency[cite: 1]
          const latency = Math.round((requestEnd - requestStart) / 2);
          const accurateServerTime = Number(res.timestamp) + latency;

          // Calculate offset between accurate atomic time and user's local PC clock[cite: 1]
          serverTimeOffset = accurateServerTime - requestEnd;

          // Paint immediately to synchronize without waiting for the 1s interval[cite: 1]
          renderClock();
        }
      },
      error: function () {
        // Fallback if network or endpoint fails[cite: 1]
        if (serverTimeOffset === null) {
          const $container = $('#headerDateTimeContainer');
          const rawStamp = $container.attr('data-server-timestamp') || $container.data('server-timestamp');
          serverTimeOffset = (rawStamp && !isNaN(Number(rawStamp))) 
            ? Number(rawStamp) - Date.now() 
            : 0;
          renderClock();
        }
      },
      complete: function () {
        isFetchingServerTime = false;
        if (typeof callback === 'function') {
          callback();
        }
      }
    });
  }

  function getPhilippineDate() {
    // Current epoch adjusted by the server offset[cite: 1]
    const currentEpoch = Date.now() + (serverTimeOffset || 0);
    return new Date(currentEpoch);
  }

  function renderClock() {
    const currentStamp = getPhilippineDate();
    const isMobile = window.innerWidth <= 576;

    // Standard long and short date formatters[cite: 1, 2]
    const dateOptions = isMobile
      ? { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', timeZone: 'Asia/Manila' }
      : { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric', timeZone: 'Asia/Manila' };

    const timeOptions = {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true,
      timeZone: 'Asia/Manila'
    };

    const dateStr = currentStamp.toLocaleDateString('en-US', dateOptions);
    const timeStr = currentStamp.toLocaleTimeString('en-US', timeOptions);

    // 1. Top Header elements[cite: 1]
    $('#headerLiveDate').text(dateStr);
    $('#headerLiveTime').text(timeStr);

    // 2. Time Log View elements[cite: 1]
    const $timelogDate = $('#timelogLiveDate');
    const $timelogTime = $('#timelogLiveTime');
    if ($timelogDate.length) $timelogDate.text(dateStr);
    if ($timelogTime.length) $timelogTime.text(timeStr);

    // 3. Dashboard Welcome Subtitle Date[cite: 2]
    const $dashHeroDate = $('#dashHeroDate span');
    if ($dashHeroDate.length) $dashHeroDate.text(dateStr);

    // 4. Dashboard Live Clock Card Elements (Digits, Seconds, AM/PM, Date)[cite: 2]
    const $clockTime = $('#liveClockTime');
    if ($clockTime.length) {
      const timePartsFormatter = new Intl.DateTimeFormat('en-US', {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });

      const parts = timePartsFormatter.formatToParts(currentStamp);
      const timeParts = {};
      parts.forEach(p => timeParts[p.type] = p.value);

      $clockTime.text(`${timeParts.hour}:${timeParts.minute}`);
      $('#liveClockSeconds').text(`:${timeParts.second}`);
      $('#liveClockAmPm').text(timeParts.dayPeriod || '');

      const widgetDateStr = currentStamp.toLocaleDateString('en-US', {
        timeZone: 'Asia/Manila',
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });

      $('#liveClockDate').html(`<i class="bi bi-calendar-event me-1 text-warning"></i>${widgetDateStr}`);
    }
  }

  function initLiveClock() {
    // Synchronously paint whatever elements are currently in DOM[cite: 1]
    renderClock();

    // If offset is not yet synchronized via the server endpoint, fetch it[cite: 1]
    if (serverTimeOffset === null) {
      fetchServerTime(function () {
        renderClock();
      });
    }

    // Keep single interval running continuously across the entire app[cite: 1]
    if (!isClockRunning) {
      setInterval(renderClock, 1000);
      isClockRunning = true;
    }
  }

  $(document).ready(function () {
    initLiveClock();
  });

  // Expose globally for Sammy.js view rendering and punch log tracking[cite: 1]
  window.getPhilippineDate = getPhilippineDate;
  window.initLiveClock = initLiveClock;

})(jQuery);