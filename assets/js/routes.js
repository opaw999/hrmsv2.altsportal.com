(function ($) {
    var RegularFormSubmitFix = function(app) {
        app.defaultCheckFormSubmission = this._checkFormSubmission;
        return app._checkFormSubmission = function(form) {
            var $form = $(form);
            var path = $form.attr("action") || "";
            
            // Allow AJAX forms through jQuery without Sammy intercepting
            if (path === "" || path === "#" || $form.data('ajax') === true || $form.data('ajax') === "true") {
                return false;
            }

            return this.defaultCheckFormSubmission(form);
        };
    };

    var parseParam = function (param, key) {
        var paramStr = "";
        if (param instanceof String || param instanceof Number || param instanceof Boolean) {
            paramStr += "&" + key + "=" + encodeURIComponent(param);
        } else {
            $.each(param, function (i) {
                var k = key == null ? i : key + (param instanceof Array ? "[" + i + "]" : "." + i);
                paramStr += '&' + parseParam(this, k);
            });
        }
        return paramStr.substr(1);
    };

    var loadURL = function (uri) {
        // 1. Close open Bootstrap modals if any
        if (typeof bootstrap !== 'undefined') {
            $('.modal.show').each(function() {
                const modal = bootstrap.Modal.getInstance(this);
                if (modal) modal.hide();
            });
        }

        // 2. Sync Sidebar & Mobile Active Tabs
        var currentHash = window.location.hash || '#/dashboard';
        if (currentHash === '#/') currentHash = '#/dashboard';

        $('.nav-pill-custom, .mobile-nav-btn, .admin-dropdown-menu .dropdown-item').removeClass('active');
        $('a[href="' + currentHash + '"]').addClass('active');

        // 3. Fallback URL resolver (uses global BASE_URL or helper $.baseUrl)
        var rootUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : ($.baseUrl || '');

        // 4. Progress bar start (if element exists)
        $('#spa-page-loader').css({ 'width': '35%', 'opacity': '1' });

        // 5. Fetch and Render View
        $('.page-wrap').load(rootUrl + uri, function (response, status, xhr) {
            
            // Complete & hide progress bar
            $('#spa-page-loader').css('width', '100%');
            setTimeout(function() {
                $('#spa-page-loader').css('opacity', '0');
                setTimeout(function() {
                    $('#spa-page-loader').css('width', '0%');
                }, 300);
            }, 200);

            // Immediately repaint the new page elements with existing server time
            if (typeof window.initLiveClock === 'function') {
                window.initLiveClock();
            }

            // 2. Trigger Geolocation if Time Log view was loaded
            if ($('#page-time-log').length && typeof window.initPageLocation === 'function') {
                window.initPageLocation();
                window.loadTodayPunchLogs();
            }

            // ------------------------------------------------------------------
            // Trigger Hourly Rate setup if Hourly Rate view was loaded
            // ------------------------------------------------------------------
            if ($('#page-hourly-rate').length && typeof window.initHourlyRatePage === 'function') {
                window.initHourlyRatePage();
            }



            // Force visibility on any view-page elements loaded into the DOM
            $('.page-wrap .view-page').show().addClass('active-view');

            if (status === 'error') {
                var msg = "Sorry but there was an error: " + xhr.status + " " + xhr.statusText;
                var html = `
                    <div class="p-4 text-center">
                        <h1 class="display-1 fw-bold mb-2" style="color: var(--primary-orange);">404</h1>
                        <h2 class="card-title h4 mb-3">Page Not Found</h2>
                        <p class="text-muted mb-4">${msg}</p>
                        <a href="#/dashboard" class="btn btn-sm btn-orange px-3">Back to Dashboard</a>
                    </div>
                `;
                $('.page-wrap').html(html);
            } else {
                if (response.indexOf('{"success":') !== -1) {
                    var json = $.parseJSON(response);
                    if (!json.success && json.msg) {
                        var source = `
                            <section class="content p-3">
                                <div class="alert alert-info alert-dismissible">
                                    <h4><i class="bi bi-info-circle me-1"></i> Notice</h4>
                                    ${json.msg}
                                </div>
                            </section>
                        `;
                        $('.page-wrap').html(source);
                    }
                }
            }
        });
    };

    var app = $.sammy('body', function () {
        this.use(RegularFormSubmitFix);
        this.disable_push_state = true;

        this.get('#/', function () {
            loadURL('dashboard');
        });
        
        this.get('#/(.*)', function () {
            var splat = this.params['splat'];
            var request_uri = parseParam(this.params);
            request_uri = request_uri.substring(0, request_uri.indexOf('&splat'));
            
            var request_url = request_uri ? ('?c=' + splat + '&' + request_uri) : splat;
            loadURL(request_url);
        });
    });

    $(function () {
        app.run('#/');
    });
})(jQuery);