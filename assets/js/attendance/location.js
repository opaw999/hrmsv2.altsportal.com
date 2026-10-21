/* ==========================================================================
   attendance/location.js
   Browser geolocation + reverse geocoding (OpenStreetMap Nominatim).
   Depends on: jQuery
   ========================================================================== */

/** Region / island-group names dropped from displayed addresses. */
var LOCATION_NOISE_WORDS = [
    'visayas', 'luzon', 'mindanao', 'national capital region', 'metro manila',
    'calabarzon', 'mimaropa', 'bicol', 'cordillera', 'ilocos',
    'cagayan valley', 'central luzon'
];

/**
 * Get the user's position and a readable address.
 * callback receives either:
 *   { lat, lng, accuracy, address, coords }   on success
 *   { error, code }                           on failure
 * If reverse geocoding fails, `address` falls back to the raw coordinates.
 */
function getUserLocation(callback) {
    if (!navigator.geolocation) {
        callback({ error: 'Geolocation is not supported by your browser', code: -1 });
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            var lat      = pos.coords.latitude;
            var lng      = pos.coords.longitude;
            var accuracy = Math.round(pos.coords.accuracy);
            var coords   = lat.toFixed(6) + ', ' + lng.toFixed(6);

            $.ajax({
                url: 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng,
                type: 'GET',
                headers: { 'Accept-Language': 'en' },
                timeout: 10000
            }).done(function (data) {
                callback({ lat: lat, lng: lng, accuracy: accuracy, address: buildAddress(data), coords: coords });
            }).fail(function () {
                callback({ lat: lat, lng: lng, accuracy: accuracy, address: coords, coords: coords });
            });
        },
        function (error) {
            var messages = {};
            messages[error.PERMISSION_DENIED]    = 'User denied the request for Geolocation.';
            messages[error.POSITION_UNAVAILABLE] = 'Location information is unavailable.';
            messages[error.TIMEOUT]              = 'The request to get user location timed out.';
            callback({ error: messages[error.code] || 'Unknown error', code: error.code });
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

/** Nominatim response → "street, city, province" (falls back to display_name). */
function buildAddress(data) {
    var address = 'Unknown location';
    if (data && data.address) {
        var a = data.address;
        var parts = [
            a.road || a.suburb || a.neighbourhood || a.quarter || a.village || a.hamlet || '',
            a.city || a.town || a.municipality || a.city_district || a.county || '',
            a.state || a.province || a.region || ''
        ].filter(Boolean);
        address = parts.length ? parts.join(', ') : (data.display_name || address);
    } else if (data && data.display_name) {
        address = data.display_name;
    }
    return address;
}

/** Trim a long address for the table: drops regions, "Philippines" and ZIP codes, keeps 3 parts. */
function shortenLocationForDisplay(full) {
    if (!full) return 'Unknown location';

    var cleaned = full.split(',').map(function (p) { return p.trim(); }).filter(function (part) {
        var lower = part.toLowerCase();
        if (LOCATION_NOISE_WORDS.some(function (w) { return lower.indexOf(w) !== -1; })) return false;
        if (['philippines', 'phi', 'ph'].indexOf(lower) !== -1) return false;
        if (/^\d{4,5}$/.test(part)) return false;
        return true;
    });

    return cleaned.slice(0, 3).join(', ') || full;
}