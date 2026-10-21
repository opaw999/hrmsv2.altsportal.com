/* ==========================================================================
   attendance/config.js
   Constants, API urls and shared state. Loaded FIRST.

   Plain-script conventions used by every attendance module:
   - Top-level `var` / `function` (not `const` / `let`) so the files can be
     re-executed by an SPA navigation without "already declared" errors.
   - All mutable state lives on the single `state` object below.
   ========================================================================== */

// ---------- Server-provided values (set inline in dashboard.php) ----------
var ATT_CONFIG = window.ATTENDANCE_CONFIG || {};
var API        = ATT_CONFIG.api     || {};
var BASE_URL   = ATT_CONFIG.baseUrl || '';

// ---------- Business rules ----------
var DEFAULT_LOCATION = 'Tagbilaran, City, Bohol, Philippines';
var MIN_HOURS        = 5;    // minimum hours (attendance + leave) required per day
var MAX_HOURS        = 9;    // daily display/weekly cap
var WEEKLY_TARGET    = 35;   // hours expected per week

var FIRST_HOUR = 8;          // first row in the hours table (8:00)
var LAST_HOUR  = 18;         // last row (18:00 → 19:00)
var LUNCH_HOUR = 12;         // row shown as "Lunch Break" unless checked
var HOURS      = Array.from({ length: LAST_HOUR - FIRST_HOUR + 1 }, function (_, i) { return i + FIRST_HOUR; });   // 8..18

// ---------- Leave type → short code shown in badges ----------
var LEAVE_CODE_MAP = {
    sick:     'M/SL',
    vacation: 'VL',
    medical:  'MAL',
    valid:    'GSL',
    personal: 'APL'
};

// ---------- Shared mutable state ----------
var state = {
    // FullCalendar instance
    calendar: null,

    // Time-off modal
    selectedDate: null,
    groupCounter: 1,               // id generator for locked groups
    isViewingSavedRecord: false,
    rangeStartKey: null,           // (used by the disabled range-select feature)
    currentBlockedHours: {},       // { hour: [{start, end, code, status}] } for the selected date
    allowModalClose: false,        // lets code close the modal past the min-hours guard
    userInteracting: false,        // true briefly after a real user action inside the modal

    // Calendar data caches
    allEventsCache: [],
    dailyTotalsCache: {},          // approved hours per date  → weekly totals
    dailyDisplayTotalsCache: {},   // all-status hours per date → "X hrs." chip
    holidaysCache: {},             // { 'YYYY-MM-DD': holiday }
    leavesCache: {},               // { 'YYYY-MM-DD': leave info }
    leaveHoursByDate: {},          // approved leave hours per date → weekly totals
    dayoffRequests: []
};