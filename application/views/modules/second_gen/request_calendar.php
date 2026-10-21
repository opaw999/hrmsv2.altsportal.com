<!-- code 1 -->
<style>
    .calendar-cell.weekend {
        background-color: #f8f9fa;
    }

    .calendar-cell.weekend .day-number {
        color: #6c757d;
    }

    .day-off-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        text-align: center;
        margin-bottom: 4px;
        opacity: 0.85;
    }

    /* Holiday cell */
    .calendar-cell.holiday {
        background-color: #fff3cd;
    }

    .calendar-cell.holiday .day-number {
        color: #b45309;
        font-weight: 700;
    }

    /* Holiday label */
    .holiday-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #b45309;
        text-align: center;
        margin-bottom: 4px;
        opacity: 0.9;
    }

    /* If a holiday falls on a weekend, blend both */
    .calendar-cell.weekend.holiday {
        background-color: #fff3cd;
    }

    .leave-details {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px 14px;
        background: #fbfbfc;
        border: 1px solid #e9ecef;
        border-radius: 10px;
    }

    .ld-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: #fff;
        border: 1px solid #eef0f2;
        border-radius: 8px;
        font-size: 0.8rem;
    }

    .ld-item i {
        font-size: 0.95rem;
    }

    .ld-label {
        color: #8a8f99;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.4px;
    }

    .ld-value {
        font-weight: 700;
    }

    .calendar-grid {
        display: grid;
        /* grid-template-columns: repeat(7, 1fr) minmax(58px, 0.85fr); */
        grid-template-columns: repeat(8, 1fr);
        /* 7 days + Total */
        gap: 8px;
        text-align: left;
        max-width: 100%;
        width: 100%;
    }

    /* VIBRANT RAINBOW DAY HEADERS */
    .day-header {
        text-align: center;
        font-weight: 700;
        padding: 10px 0;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: default;
    }

    .day-header:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Each day header gets its own rainbow color */
    .day-header:nth-child(1) {
        background: linear-gradient(135deg, #ffe0b2 0%, #ffcc80 100%);
        color: #e65100;
        box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
    }

    .day-header:nth-child(2) {
        background: linear-gradient(135deg, #fff9c4 0%, #fff176 100%);
        color: #f57f17;
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    .day-header:nth-child(3) {
        background: linear-gradient(135deg, #dcedc8 0%, #aed581 100%);
        color: #33691e;
        box-shadow: 0 2px 8px rgba(139, 195, 74, 0.3);
    }

    .day-header:nth-child(4) {
        background: linear-gradient(135deg, #b2dfdb 0%, #80cbc4 100%);
        color: #004d40;
        box-shadow: 0 2px 8px rgba(38, 198, 218, 0.3);
    }

    .day-header:nth-child(5) {
        background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%);
        color: #0d47a1;
        box-shadow: 0 2px 8px rgba(66, 165, 245, 0.3);
    }

    .day-header:nth-child(6) {
        background: linear-gradient(135deg, #c5cae9 0%, #9fa8da 100%);
        color: #1a237e;
        box-shadow: 0 2px 8px rgba(121, 134, 203, 0.3);
    }

    .day-header:nth-child(7) {
        background: linear-gradient(135deg, #e1bee7 0%, #ce93d8 100%);
        color: #4a148c;
        box-shadow: 0 2px 8px rgba(206, 147, 216, 0.3);
    }

    /* 8th rainbow header = Total column */
    .day-header:nth-child(8) {
        background: linear-gradient(135deg, #ffccbc 0%, #ffab91 100%);
        color: #bf360c;
        box-shadow: 0 2px 8px rgba(255, 112, 67, 0.35);
    }

    /* CALENDAR CELLS - Clean Material Style */
    .calendar-cell {
        min-height: 100px;
        border: 0.5px solid #e8e8e8;
        border-radius: 12px;
        padding: 8px;
        background-color: #fff;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .calendar-cell:hover {
        background-color: #fff5f2;
        border-color: rgba(255, 107, 74, 0.3);
        box-shadow: inset 0 0 0 2px rgba(255, 107, 74, 0.3);
        transform: translateY(-1px);
    }

    .calendar-cell.other-month {
        background-color: #fdfdfe;
        color: #adb5bd;
        opacity: 0.5;
    }

    .calendar-cell.other-month:hover {
        opacity: 0.7;
    }

    /* TODAY - Vibrant gradient with rainbow accent */
    .calendar-cell.today {
        background: linear-gradient(135deg, #fff0ec 0%, #ffe4d6 100%);
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(255, 107, 74, 0.25);
        position: relative;
        overflow: hidden;
    }

    .calendar-cell.today .day-number {
        color: #ff6b4a;
        font-weight: 700;
        background: white;
        box-shadow: 0 2px 8px rgba(255, 107, 74, 0.4);
        border-radius: 50%;
        width: 27px;
        height: 27px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Week-total cell — reuses .calendar-cell sizing but is inert */
    .calendar-cell.week-total-cell {
        background: #fafafa;
        border: 0.5px dashed #dcdcdc;
        cursor: default;
        pointer-events: none;
        /* kills :hover + click-to-open */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        text-align: center;
    }

    .week-total-cell .wt-hours {
        font-size: 0.65rem;
        font-weight: 700;
        color: #ff6b4a;
        line-height: 1.1;
    }

    .week-total-cell .duty-hours {
        font-size: 0.65rem;
        font-weight: 700;
        color: #0f5132;
        line-height: 1.1;
    }

    .week-total-cell .leave-hours {
        font-size: 0.65rem;
        font-weight: 700;
        color: #0f5132;
        line-height: 1.1;
    }

    .week-total-cell .deficit-hours {
        font-size: 0.65rem;
        font-weight: 700;
        color: #b71c1c;
        line-height: 1.1;
    }

    .week-total-cell .wt-label {
        font-size: 0.58rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #adb5bd;
    }

    .week-total-cell .duty-label {
        font-size: 0.58rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #adb5bd;
    }

    .week-total-cell .leave-label {
        font-size: 0.58rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #adb5bd;
    }

    .week-total-cell .deficit-label {
        font-size: 0.58rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #adb5bd;
    }

    .day-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 6px;
    }

    /* ===== DAY NUMBER + TOTAL HOURS - Material circle style ===== */

    /* TOTAL HOURS - text label on the right */
    .total-hours {
        font-size: 0.7rem;
        font-weight: 500;
        white-space: nowrap;
        text-decoration: underline;
    }

    /* DAY NUMBER - Material circle style */
    .day-number {
        font-weight: 500;
        font-size: 0.75rem;
        margin-bottom: 6px;
        color: #555;
        width: 27px;
        height: 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .calendar-cell:hover .day-number {
        background: #ff6b4a;
        color: white !important;
        box-shadow: 0 2px 8px rgba(255, 107, 74, 0.4);
    }

    /* LEAVE BADGES - Vibrant gradient style */
    .leave-badge,
    .dayoff-badge {
        font-size: 0.65rem;
        padding: 3px 8px;
        border-radius: 8px;
        display: flex;
        /* was: block */
        align-items: center;
        /* vertically center */
        justify-content: space-between;
        /* icon left, text right */
        gap: 6px;
        /* minimum spacing */
        margin-bottom: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        /* text-align: center;  ← removed (not needed with flex) */
        font-weight: 600;
        letter-spacing: 0.2px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        line-height: 1.3;
    }

    .duty-badge {
        font-size: 0.65rem;
        padding: 3px 8px;
        border-radius: 8px;
        /* display: flex; */
        /* was: block */
        align-items: center;
        /* vertically center */
        justify-content: space-between;
        /* icon left, text right */
        gap: 6px;
        /* minimum spacing */
        margin-bottom: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        /* text-align: center;  ← removed (not needed with flex) */
        font-weight: 600;
        letter-spacing: 0.2px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        line-height: 1.3;
    }

    /* Icon shouldn't shrink; label can ellipsis if too long */
    .leave-badge>i,
    .dayoff-badge>i,
    .duty-badge>i {
        flex: 0 0 auto;
        font-size: 0.85em;
    }

    .leave-badge>span,
    .dayoff-badge>span,
    .duty-badge>span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: right;
    }

    .leave-badge:hover,
    .dayoff-badge:hover,
    .duty-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Shine effect on hover */
    .leave-badge::after,
    .dayoff-badge::after,
    .duty-badge::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg,
                transparent 0%,
                rgba(255, 255, 255, 0.4) 50%,
                transparent 100%);
        transition: left 0.5s ease;
    }

    .leave-badge:hover::after,
    .dayoff-badge:hover::after,
    .duty-badge:hover::after {
        left: 100%;
    }

    /* Leave type specific gradients */
    .approved-label {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        color: #0f5132;
        border-left: 3px solid #4caf50;
    }

    .approved-label:hover {
        background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
        color: #1b5e20;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }

    .disapproved-label {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: #b71c1c;
        border-left: 3px solid #f44336;
    }

    .disapproved-label:hover {
        background: linear-gradient(135deg, #ffcdd2 0%, #ef9a9a 100%);
        color: #b71c1c;
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
    }

    .pending-label {
        background: linear-gradient(135deg, #fff9c4 0%, #fff176 100%);
        color: #7a5f00;
        border-left: 3px solid #fbc02d;
    }

    .pending-label:hover {
        background: linear-gradient(135deg, #fff176 0%, #ffee58 100%);
        color: #5d4700;
        box-shadow: 0 4px 12px rgba(251, 192, 45, 0.35);
    }

    .v-paid {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        color: #0f5132;
        border-left: 3px solid #4caf50;
    }

    .v-paid:hover {
        background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
        color: #1b5e20;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }

    .v-unpaid {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: #b71c1c;
        border-left: 3px solid #f44336;
    }

    .v-unpaid:hover {
        background: linear-gradient(135deg, #ffcdd2 0%, #ef9a9a 100%);
        color: #b71c1c;
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
    }

    .other-leave {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        color: #0f5132;
        border-left: 3px solid #4caf50;
    }

    .other-leave:hover {
        background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
        color: #1b5e20;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }

    .dayoff-approved {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        color: #0f5132;
        border-left: 3px solid #4caf50;
    }

    .dayoff-approved:hover {
        background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
        color: #1b5e20;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }

    .dayoff-pending {
        background: linear-gradient(135deg, #fff9c4 0%, #fff176 100%);
        color: #7a5f00;
        border-left: 3px solid #fbc02d;
    }

    .dayoff-pending:hover {
        background: linear-gradient(135deg, #fff176 0%, #ffee58 100%);
        color: #5d4700;
        box-shadow: 0 4px 12px rgba(251, 192, 45, 0.35);
    }

    .log-inoffice {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #0d47a1;
        border-left: 3px solid #1976d2;
        box-shadow: 0 4px 12px rgba(13, 71, 161, 0.25);
    }

    .log-inoffice:hover {
        background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%);
        color: #0d47a1;
        box-shadow: 0 6px 16px rgba(13, 71, 161, 0.35);
        transform: translateY(-2px);
    }

    .log-outoffice {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        color: #bf360c;
        border-left: 3px solid #e64a19;
        box-shadow: 0 4px 12px rgba(191, 54, 12, 0.25);
    }

    .log-outoffice:hover {
        background: linear-gradient(135deg, #ffe0b2 0%, #ffcc80 100%);
        color: #bf360c;
        box-shadow: 0 6px 16px rgba(191, 54, 12, 0.35);
        transform: translateY(-2px);
    }

    /* ===== ATTACHMENT styles ===== */
    .attachment-preview {
        font-size: 0.72rem;
        word-break: break-all;
    }

    .attachment-item {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 3px;
        padding: 2px 4px;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        background: #fbfbfb;
    }

    .attachment-item .att-name {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .attachment-item .att-btn {
        padding: 0 4px;
        font-size: 0.7rem;
        line-height: 1;
    }

    .attachment-empty {
        font-size: 0.7rem;
        color: #999;
        font-style: italic;
    }

    /* ===== END ATTACHMENT styles ===== */

    /* ===== MATERNITY / PATERNITY MODE ===== */
    /* When a Maternity / Paternity leave is selected, hide everything
       tagged with .mat-pat-hide and force-show the geo card. */
    #leaveForm.mat-pat-active .mat-pat-hide {
        display: none !important;
    }

    #leaveForm.mat-pat-active #fixedGeoContainer {
        display: block !important;
    }


    .calendar-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem 1rem;
        padding: 0.5rem;
        font-size: 0.72rem;
        color: #666;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-weight: 500;
    }

    .legend-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        font-size: 0.7rem;
        line-height: 1;
    }

    .legend-icon.approved {
        background: #c8e6c9;
        color: #2e7d32;
    }

    .legend-icon.pending {
        background: #fff176;
        color: #827717;
    }

    .legend-icon.rejected {
        background: #ffcdd2;
        color: #c62828;
    }

    .legend-icon.locked {
        background: #bbdefb;
        color: #0d47a1;
    }

    .legend-icon.entry {
        background: #ffe0b2;
        color: #e65100;
    }

    @media (max-width: 480px) {
        .calendar-legend {
            font-size: 0.6rem;
            gap: 0.4rem 0.6rem;
            padding: 0.3rem;
        }

        .legend-icon {
            width: 15px;
            height: 15px;
            font-size: 0.6rem;
        }
    }

    /* Responsive adjustments for calendar */
    @media (max-width: 768px) {
        .calendar-grid {
            /* grid-template-columns: repeat(7, 1fr) minmax(42px, 0.8fr); */
            grid-template-columns: repeat(8, 1fr);
            gap: 4px;
        }

        .day-header {
            font-size: 0.55rem;
            padding: 6px 2px;
            border-radius: 8px;
        }

        .calendar-cell {
            min-height: 60px;
            padding: 4px;
            border-radius: 8px;
        }

        .day-number {
            font-size: 0.6rem;
            width: 20px;
            height: 20px;
            margin-bottom: 3px;
        }

        .calendar-cell.today .day-number {
            width: 20px;
            height: 20px;
        }

        .week-total-cell {
            white-space: nowrap;
        }

        .leave-badge,
        .dayoff-badge,
        .duty-badge {
            font-size: 0.5rem;
            padding: 2px 4px;
            border-radius: 4px;
            border-left-width: 2px;
            gap: 3px;
            /* tighter gap on small screens */
        }

        .week-total-cell .wt-label {
            font-size: 0.48rem;
        }

        .week-total-cell .duty-label {
            font-size: 0.48rem;
        }

        .week-total-cell .leave-label {
            font-size: 0.48rem;
        }

        .week-total-cell .deficit-label {
            font-size: 0.48rem;
        }
    }

    @media (max-width: 480px) {
        .calendar-grid {
            /* grid-template-columns: repeat(7, 1fr) minmax(34px, 0.75fr); */
            grid-template-columns: repeat(8, 1fr);
            gap: 3px;
        }

        .day-header {
            font-size: 0.45rem;
            padding: 4px 1px;
            border-radius: 6px;
        }

        .calendar-cell {
            min-height: 45px;
            padding: 3px;
            border-radius: 6px;
        }

        .day-number {
            font-size: 0.5rem;
            width: 16px;
            height: 16px;
            margin-bottom: 2px;
        }

        .calendar-cell.today .day-number {
            width: 16px;
            height: 16px;
        }

        .leave-badge,
        .dayoff-badge,
        .duty-badge {
            font-size: 0.4rem;
            padding: 1px 3px;
            border-radius: 3px;
            border-left-width: 2px;
            gap: 2px;
            /* tightest gap on tiny screens */
        }

        .week-total-cell {
            white-space: nowrap;
        }

        .week-total-cell .wt-hours {
            font-size: 0.4rem;
        }

        .week-total-cell .duty-hours {
            font-size: 0.4rem;
        }

        .week-total-cell .leave-hours {
            font-size: 0.4rem;
        }

        .week-total-cell .deficit-hours {
            font-size: 0.4rem;
        }

    }

    @media (max-width: 768px) {
        .calendar-scroll-wrap {
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            display: block !important;
            padding-bottom: 8px !important;
        }

        /* ⭐ Force the inner grid to a min-width — this is what makes
             it wider than the wrapper, so the wrapper scrolls. */
        .calendar-scroll-wrap .calendar-grid {
            min-width: 700px !important;
            width: 700px !important;
        }

        .calendar-scroll-wrap::-webkit-scrollbar {
            height: 6px;
        }

        .calendar-scroll-wrap::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 3px;
        }

        .calendar-scroll-wrap::-webkit-scrollbar-thumb {
            background: #ff6b4a;
            border-radius: 3px;
        }
    }

    @media (max-width: 480px) {
        .calendar-scroll-wrap .calendar-grid {
            min-width: 620px !important;
            width: 620px !important;
        }
    }

    @media (max-width: 768px) {

        /* Make the table scroll horizontally */
        #leaveForm table {
            display: block !important;
            overflow-x: auto !important;
            white-space: nowrap !important;
            -webkit-overflow-scrolling: touch !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        #leaveForm table thead,
        #leaveForm table tbody,
        #leaveForm table tr {
            display: table !important;
            width: 100% !important;
        }

        /* Keep the row structure so columns line up */
        #leaveForm table tr {
            display: table-row !important;
        }

        /* Cell sizing so it doesn't shrink */
        #leaveForm table th,
        #leaveForm table td {
            white-space: nowrap !important;
            padding: 4px 6px !important;
            font-size: 0.7rem !important;
            vertical-align: middle !important;
        }

        #leaveForm table th:nth-child(1),
        #leaveForm table td:nth-child(1) {
            min-width: 40px;
        }

        #leaveForm table th:nth-child(2),
        #leaveForm table td:nth-child(2) {
            min-width: 120px;
        }

        #leaveForm table th:nth-child(3),
        #leaveForm table td:nth-child(3) {
            min-width: 90px;
        }

        #leaveForm table th:nth-child(4),
        #leaveForm table td:nth-child(4) {
            min-width: 110px;
        }

        #leaveForm table th:nth-child(5),
        #leaveForm table td:nth-child(5) {
            min-width: 130px;
        }

        #leaveForm table th:nth-child(6),
        #leaveForm table td:nth-child(6) {
            min-width: 90px;
        }

        #leaveForm table th:nth-child(7),
        #leaveForm table td:nth-child(7) {
            min-width: 160px;
        }

        #leaveForm table th:nth-child(8),
        #leaveForm table td:nth-child(8) {
            min-width: 90px;
        }

        /* Inputs inside the table shouldn't blow up on mobile */
        #leaveForm table input[type="time"] {
            min-width: 80px;
            font-size: 0.7rem !important;
            padding: 2px 4px !important;
        }

        /* Scrollbar styling */
        #leaveForm table::-webkit-scrollbar {
            height: 6px;
        }

        #leaveForm table::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 3px;
        }

        #leaveForm table::-webkit-scrollbar-thumb {
            background: #ff6b4a;
            border-radius: 3px;
        }
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="header-wrapper">
    <header class="app-header-box d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-light border d-lg-none py-1 px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer">
                <i class="bi bi-list fs-6"></i>
            </button>
            <div>
                <h1 class="h6 fw-bold mb-0 text-dark" id="headerTitle"><?= $page_title; ?></h1>
                <p class="text-muted mb-0 d-none d-sm-block" style="font-size: 0.72rem;" id="headerSubtitle">
                    <?= $page_sub_title; ?>
                </p>
            </div>
        </div>

        <div class="header-datetime-badge d-flex align-items-center gap-3 px-4 py-2 bg-white border rounded-pill shadow-sm" id="headerDateTimeContainer" data-server-time="<?= date('Y-m-d\TH:i:s'); ?>">
            <div class="d-flex align-items-center text-muted fs-5">
                <i class="bi bi-calendar-event me-2" style="color: var(--primary-orange);"></i>
                <span id="headerLiveDate" class="fw-medium"><?= date('l, F j, Y'); ?></span>
            </div>
            <span class="text-muted opacity-50 fs-5">|</span>
            <div class="d-flex align-items-center fw-bold text-dark fs-5">
                <i class="bi bi-clock me-2 text-secondary"></i>
                <span id="headerLiveTime"><?= date('h:i:s A'); ?></span>
            </div>
        </div>

    </header>
</div>

<div class="content-scrollable">
    <div class="view-page active-view" id="page-form-request">
        <div class="row mb-3">
            <div class="col-12 col-md-8">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Leave Scheduler</h5>
                    <small class="text-muted" style="font-size: 0.75rem;">Click any date on the calendar to setup your leaves.</small>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="d-flex justify-content-center align-items-center mt-3 mt-md-0 gap-2">
                    <button class="btn btn-outline-secondary btn-sm" id="prevMonthBtn"><i class="bi bi-chevron-left"></i></button>
                    <h4 id="monthYearDisplay" class="mb-0 text-center fw-semibold" style="min-width: 180px;">Month Year</h4>
                    <button class="btn btn-outline-secondary btn-sm" id="nextMonthBtn"><i class="bi bi-chevron-right"></i></button>
                    <button class="btn btn-primary btn-sm ms-2" id="todayBtn">Today</button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-3">
                                <div class="calendar-legend mb-3">
                                    Legend:
                                    <span class="legend-item"><span class="legend-icon approved"><i class="bi bi-check-circle-fill"></i></span>Approved</span>
                                    <span class="legend-item"><span class="legend-icon pending">⏳</span>Pending</span>
                                    <span class="legend-item"><span class="legend-icon rejected"><i class="bi bi-x-circle-fill"></i></span>Disapproved</span>
                                </div>
                                <div class="calendar-scroll-wrap">
                                    <div class="calendar-grid mb-2">
                                        <div class="day-header">Sun</div>
                                        <div class="day-header">Mon</div>
                                        <div class="day-header">Tue</div>
                                        <div class="day-header">Wed</div>
                                        <div class="day-header">Thu</div>
                                        <div class="day-header">Fri</div>
                                        <div class="day-header">Sat</div>
                                        <div class="day-header">Total</div>
                                    </div>
                                    <div id="calendarGrid" class="calendar-grid"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4" style="display: none;">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title h6 mb-0 fw-bold">Scheduled Leaves</h5>
                            </div>
                            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                <div id="scheduledLeavesList" class="list-group list-group-flush">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Schedule Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="leaveForm">
                    <input type="hidden" id="leaveId">
                    <input type="hidden" id="curDate">
                    <div class="mb-3">
                        <label for="leaveType" class="form-label small fw-semibold text-muted mb-2 d-block">Request Type</label>
                        <select class="form-select form-select-sm" id="leaveType" onchange="changeLeave_(this.value)" required>
                            <?php
                            $request = $this->request_model->getArray('tk_request_type', ['status' => 'active']);
                            foreach ($request as $r) {
                                $code = $r['code'];
                                $request = $r['request'];
                                echo '<option value="' . $code . '">' . $request . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3" id="maternityLeaveContainer" style="display: none;">
                        <label class="form-label small fw-semibold mb-2 d-block">MATERNITY LEAVE FORM</label>
                        <div class="col-12 col-md-12">
                            <label for="matDelivery" class="form-label small fw-semibold text-muted mb-2 d-block">Delivery Type</label>
                            <select class="form-select form-select-sm" id="matDelivery" onchange="window.matDelivery(this.value)" required>
                                <option value="">---</option>
                                <!-- <option value="normal">Normal</option>
                                <option value="cesarean">Cesarean</option>
                                <option value="miscarriage">Miscarriage</option> -->
                                <?php
                                $cond = ['code' => 'maternity', 'status' => 1];
                                $options = $this->request_model->getArray('tk_matpat_details', $cond);
                                foreach ($options as $o) {
                                    $delivery_type = $o['delivery_type'];

                                    echo '<option value="' . $delivery_type . '">' . strtoupper($delivery_type) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="matStart" class="form-label small fw-semibold text-muted mb-2 d-block">Date Start</label>
                            <input type="date" class="form-control form-control-sm date1" id="matStart" placeholder="YYYY-MM-DD" required>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="matEnd" class="form-label small fw-semibold text-muted mb-2 d-block">Date End</label>
                            <input type="date" class="form-control form-control-sm date2" id="matEnd" placeholder="YYYY-MM-DD" required disabled>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="matDays" class="form-label small fw-semibold text-muted mb-2 d-block">No. of Days</label>
                            <input type="text" class="form-control form-control-sm" id="matDays" required disabled>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="matBack" class="form-label small fw-semibold text-muted mb-2 d-block">Date Back to Work</label>
                            <input type="date" class="form-control form-control-sm date2" id="matBack" placeholder="YYYY-MM-DD" required>
                        </div>
                        <div class="col-6 col-md-12">
                            <label for="matDetails" class="form-label small fw-semibold text-muted mb-2 d-block">Details</label>
                            <textarea class="form-control form-control-sm" id="matDetails" rows="2" placeholder="Maternity details..."></textarea>
                        </div>
                    </div>
                    <div class="row g-2 mb-3" id="paternityLeaveContainer" style="display: none;">
                        <label class="form-label small fw-semibold mb-2 d-block">PATERNITY LEAVE FORM</label>
                        <div class="col-6 col-md-6">
                            <label for="patDate" class="form-label small fw-semibold text-muted mb-2 d-block">Delivery on or before</label>
                            <input type="date" class="form-control form-control-sm date1" id="patDate" placeholder="YYYY-MM-DD" required>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="patApplication" class="form-label small fw-semibold text-muted mb-2 d-block">Application</label>
                            <select class="form-select form-select-sm" id="patApplication" required>
                                <option value="">---</option>
                                <option value="Miscarriage">Miscarriage</option>
                                <option value="First">First</option>
                                <option value="Second">Second</option>
                                <option value="Third">Third</option>
                                <option value="Fourth">Fourth</option>
                                <option value="Fifth">Fifth</option>
                                <option value="Sixth">Sixth</option>
                                <option value="Seventh">Seventh</option>
                                <option value="Eighth">Eighth</option>
                                <option value="Ninth">Ninth</option>
                                <option value="Tenth">Tenth</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="patStart" class="form-label small fw-semibold text-muted mb-2 d-block">Date Start</label>
                            <input type="date" class="form-control form-control-sm date1" id="patStart" placeholder="YYYY-MM-DD" required>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="patEnd" class="form-label small fw-semibold text-muted mb-2 d-block">Date End</label>
                            <input type="date" class="form-control form-control-sm date2" id="patEnd" placeholder="YYYY-MM-DD" required disabled>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="patDays" class="form-label small fw-semibold text-muted mb-2 d-block">No. of Days</label>
                            <input type="text" class="form-control form-control-sm" id="patDays" required disabled>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="patDetails" class="form-label small fw-semibold text-muted mb-2 d-block">Details</label>
                            <textarea class="form-control form-control-sm" id="patDetails" rows="1" placeholder="Paternity details..."></textarea>
                        </div>
                    </div>
                    <div class="row g-2 mb-3 mat-pat-hide">
                        <div class="col-6 col-md-6">
                            <label for="startDate" class="form-label small fw-semibold text-muted mb-2 d-block">Start Date</label>
                            <input type="date" class="form-control form-control-sm date1" id="startDate" required>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="endDate" class="form-label small fw-semibold text-muted mb-2 d-block">End Date</label>
                            <input type="date" class="form-control form-control-sm date2" id="endDate" required>
                        </div>
                    </div>
                    <div class="row g-2 mat-pat-hide">
                        <small>Default Hours per Day is ( 7 hours )</small>
                    </div>
                    <div class="mb-3 form-check mat-pat-hide">
                        <input type="checkbox" class="form-check-input" id="isTimeRange" onclick="timeTable()">
                        <label class="form-check-label small" for="isTimeRange">Set specific time range for all date range selected</label>
                    </div>
                    <div class="row g-2 mb-3 mat-pat-hide" id="vacationPayment" style="display: none;">
                        <label class="form-label small fw-semibold text-muted mb-2 d-block">Payment Status</label>
                        <div class="">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payment" id="statusPaid" value="paid" checked disabled>
                                <label class="form-check-label small" for="statusPaid">Paid</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payment" id="statusUnpaid" value="unpaid" disabled>
                                <label class="form-check-label small" for="statusUnpaid">Unpaid</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mb-3 mat-pat-hide" id="medicalAssistedPerson" style="display: none;">
                        <div class="col-12 col-md-6">
                            <label for="assistedPerson" class="form-label small fw-semibold text-muted mb-2 d-block">Person Being Assisted</label>
                            <select class="form-select form-select-sm" id="assistedPerson" required></select>
                        </div>
                    </div>
                    <div class="row g-2 mat-pat-hide" id="timeRangeContainer" style="display: none;">
                        <table class="table table-sm table-bordered align-items-center text-wrap" style="vertical-align: middle;">
                            <tr>
                                <th colspan="3" class="text-center" style=" width: 25%;">Manual Time Entries</th>
                                <th>Leave Type</th>
                                <th>Attachment</th>
                                <th>Remarks/Description</th>
                                <th>Geo Location</th>
                                <th class="text-center">Action</th>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="8" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_8" name="group_8[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp8_1" id="time8_1" value="08:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp8_2" id="time8_2" value="08:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="9" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_9" name="group_9[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp9_1" id="time9_1" value="09:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp9_2" id="time9_2" value="09:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="10" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_10" name="group_10[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp10_1" id="time10_1" value="10:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp10_2" id="time10_2" value="10:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="11" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_11" name="group_11[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp11_1" id="time11_1" value="11:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp11_2" id="time11_2" value="11:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="12" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_12" name="group_12[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp12_1" id="time12_1" value="12:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp12_2" id="time12_2" value="12:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="13" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_13" name="group_13[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp13_1" id="time13_1" value="13:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp13_2" id="time13_2" value="13:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="14" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_14" name="group_14[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp14_1" id="time14_1" value="14:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp14_2" id="time14_2" value="14:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="15" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_15" name="group_15[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp15_1" id="time15_1" value="15:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp15_2" id="time15_2" value="15:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="16" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_16" name="group_16[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp16_1" id="time16_1" value="16:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp16_2" id="time16_2" value="16:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="17" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_17" name="group_17[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp17_1" id="time17_1" value="17:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp17_2" id="time17_2" value="17:59" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input tchk" id="18" onclick="activateTime(this.id)">
                                    <input type="hidden" class="groups_row" id="g_18" name="group_18[]" value="">
                                </td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp18_1" id="time18_1" value="18:00" disabled></td>
                                <td class="text-center"><input type="time" class="form-control form-control-sm input_chk tp18_2" id="time18_2" value="19:00" disabled></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="row g-2 mb-3 mat-pat-hide">
                        <div class="col-md-6" id="fixedAttachmentContainer" style="display: none;">
                            <label for="leaveAttachment" class="form-label small fw-semibold text-muted mb-2 d-block">Attachment</label>
                            <div id="fixedAttachmentViewer"></div>
                            <input type="file" class="form-control form-control-sm" id="leaveAttachment" multiple accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="fixednotesContainer" style="display: none;">
                                <label for="leaveNotes" class="form-label small fw-semibold text-muted mb-2 d-block">Remarks</label>
                                <textarea class="form-control form-control-sm" id="leaveNotes" rows="2" placeholder="Remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="card" id="fixedGeoContainer" style="display: none;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-3">
                                        <label class="form-label small fw-semibold text-dark mb-2 d-block">Current Location</label>
                                        <input type="text" class="form-control form-control-sm" id="geoTagLocation"
                                            placeholder="Tagbilaran, City, Bohol, Philippines" style="font-size: 0.8rem;" readonly>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label class="form-label small fw-semibold text-dark mb-2 d-block">Registered Location</label>
                                        <div class="form-control form-control-sm bg-light" id="registered_loc" style="font-size: 0.8rem;">Tagbilaran, City, Bohol, Philippines</div>
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>Coordinates: <span id="currentCoordinates">Detecting...</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="showLeavestatusContainer" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" id="deleteDateBtn" style="display: none;">Delete This Date</button>
                <button type="button" class="btn btn-danger btn-sm me-auto" id="deleteLeaveBtn" style="display: none;">Delete Leave Request</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveLeaveBtn">Save Leave</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dayoffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Change Dayoff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="dayoffForm">
                    <input type="hidden" id="dayoffId" value="">
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-6">
                            <label for="old_do" class="form-label small fw-semibold text-muted mb-2 d-block">Current Dayoff Date</label>
                            <input type="date" class="form-control form-control-sm old_do" id="old_do" placeholder="YYYY-MM-DD" required>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="new_do" class="form-label small fw-semibold text-muted mb-2 d-block">New Dayoff Date</label>
                            <!-- <input type="new_do" class="form-control form-control-sm new_do" id="new_do" placeholder="YYYY-MM-DD" required> -->
                            <select class="form-select form-select-sm new_do" id="new_do" required></select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-12">
                            <label for="dayoffRemarks" class="form-label small fw-semibold text-muted mb-2 d-block">Remarks</label>
                            <textarea class="form-control form-control-sm" id="dayoffRemarks" rows="2" placeholder="Optional remarks..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-danger btn-sm me-auto" id="cancelDayoff" style="display: none;" onclick="cancelDayoff()">Cancel Dayoff</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveDayoff" style="display: none;" onclick="saveDayoff()">Save Dayoff</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewFixedRequest">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">View Request</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="view_attachments">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Attachments </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body w-100 h-100 d-flex align-items-center justify-content-center"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Approval Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        if (window.__leaveSchedulerLoaded) return;
        window.__leaveSchedulerLoaded = true;

        let leaveIdInput = null;
        let currentDate = new Date();
        let leaves = [];
        let groupCounter = 1;
        let lastCheckedIndex = null;
        let currentEditingLeaveData = null;
        let editedGroups = {};

        window.currentUserLocation = null;

        function getUserLocation(callback) {
            if (!navigator.geolocation) {
                callback({
                    error: 'Geolocation is not supported by your browser',
                    code: -1
                });
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = Math.round(position.coords.accuracy);

                    $.ajax({
                        url: `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`,
                        type: 'GET',
                        headers: {
                            'Accept-Language': 'en'
                        },
                        timeout: 10000,
                        success: function(data) {
                            let address = 'Unknown location';

                            if (data && data.address) {
                                const a = data.address;
                                const streetOrBarangay = a.road || a.suburb || a.neighbourhood || a.quarter || a.village || a.hamlet || '';
                                const cityOrMunicipality = a.city || a.town || a.municipality || a.city_district || '';
                                const province = a.state || a.province || a.region || '';
                                const country = a.country || '';

                                const parts = [];
                                if (streetOrBarangay) parts.push(streetOrBarangay);
                                if (cityOrMunicipality) parts.push(cityOrMunicipality);
                                if (province) parts.push(province);
                                if (country) parts.push(country);

                                address = parts.length > 0 ? parts.join(', ') : (data.display_name || address);
                            } else if (data && data.display_name) {
                                address = data.display_name;
                            }

                            callback({
                                lat: lat,
                                lng: lng,
                                accuracy: accuracy,
                                address: address,
                                coords: `${lat.toFixed(6)}, ${lng.toFixed(6)}`
                            });
                        },
                        error: function() {
                            callback({
                                lat: lat,
                                lng: lng,
                                accuracy: accuracy,
                                address: `${lat.toFixed(6)}, ${lng.toFixed(6)}`,
                                coords: `${lat.toFixed(6)}, ${lng.toFixed(6)}`
                            });
                        }
                    });
                },
                function(error) {
                    let errorMsg = 'Unknown error';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = 'User denied the request for Geolocation.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = 'Location information is unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMsg = 'The request to get user location timed out.';
                            break;
                        default:
                            errorMsg = 'An unknown error occurred while retrieving location.';
                            break;
                    }
                    // console.log('❌ Geolocation error:', errorMsg, 'Code:', error.code);
                    callback({
                        error: errorMsg,
                        code: error.code
                    });
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function refreshGeoLocationDisplay() {
            const hasLoc = window.currentUserLocation && !window.currentUserLocation.error;

            $('#geoTagLocation').val(
                hasLoc ? window.currentUserLocation.address : 'Tagbilaran, City, Bohol, Philippines'
            );
            $('#currentCoordinates').text(
                hasLoc ? (window.currentUserLocation.coords || 'Detecting...') : 'Detecting...'
            );

            const locText = hasLoc ? window.currentUserLocation.address : 'Tagbilaran, City, Bohol';

            $('.geo-time-summary .location-display').each(function() {
                $(this).html(`<i class="bi bi-geo-alt me-1"></i>${locText}`);
            });
        }

        function alertRequired(message, modal) {
            Swal.fire({
                title: 'Required!',
                text: message,
                icon: 'warning',
                iconColor: '#ffc107',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#0d6efd',
                focusConfirm: false,
                target: document.getElementById(modal),
            });
        }

        function errorAlert(message, modal) {
            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error',
                iconColor: '#dc3545',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#dc3545',
                focusConfirm: false,
                target: document.getElementById(modal),
            })
        }

        function alertInfo(title, message, modal) {
            Swal.fire({
                title: title,
                text: message,
                icon: 'info',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#0d6efd',
                focusConfirm: false,
                target: document.getElementById(modal),
            });
        }

        function buildFixedAttachmentViewer(attachments) {
            if (!attachments || !attachments.length) {
                return `<div class="attachment-preview"><div class="attachment-empty">No files uploaded</div></div>`;
            }

            const base = "<?= base_url('uploads/leaves/') ?>";

            const listHtml = attachments.map((att) => {
                const url = base + att.attachment;
                const name = att.attachment;
                let getType = name.split('.');
                return `
                    <div class="attachment-item" data-att-id="${att.tklraId}">
                        <i class="bi bi-paperclip"></i>
                        <span class="att-name" title="${name}">Attachment ${att.tklraId}</span>
                        <button type="button" class="btn btn-sm btn-outline-primary att-btn att-view-btn" title="View" onclick="viewAttachments('${url}','${getType[1]}')">View</button>
                        <button type="button" class="btn btn-sm btn-outline-danger att-btn att-del-btn" title="Delete" onclick="deleteFixedAttachment('${att.tklraId}', '${name}')">Delete</button>
                    </div>
                `;
            }).join('');

            return `<div class="attachment-preview">${listHtml}</div>`;
        }

        function deleteFixedAttachment(tklraId, fileName) {
            if (!confirm('Delete this attachment?')) return;

            $.ajax({
                url: "<?= site_url('delete_leave_attachment') ?>",
                type: 'POST',
                data: {
                    tklraId: tklraId,
                    fileName: fileName
                },
                dataType: 'json',
                success: function(res) {
                    if (res.message === 'success') {
                        alert('Attachment has been deleted.');
                        $('.attachment-item[data-att-id="' + tklraId + '"]').remove();
                        if ($('.attachment-item[data-att-id]').length === 0) {
                            $('.attachment-preview').html('<div class="attachment-empty">No files</div>');
                        }
                    } else {
                        alert(res.message || 'Delete failed.');
                    }
                },
                error: function() {
                    alert('Network error');
                }
            });
        }

        function buildApprovalDetailsHtml(leaveId, groupId) {

            $.ajax({
                url: "<?= site_url('get_approval_details') ?>",
                type: 'POST',
                data: {
                    leaveId: leaveId,
                    groupId: groupId
                },
                success: function(data) {
                    $('#approvalModal .modal-body').html(data);
                    $('#approvalModal').modal({
                        backdrop: 'static',
                        keyboard: false,
                    });
                    $('#approvalModal').modal('show');
                    $('#approvalModal').on('shown.bs.modal', function() {
                        $('#approvalModal').trigger('focus')
                    })
                }
            });
        }

        function buildLeaveDetailsHtml(leave, approvedBy) {
            let color, icon, label;

            if (leave.status == 'approved') {
                color = 'text-success';
                icon = '<i class="bi bi-check-circle-fill text-success"></i>';
                label = 'Approved';
            } else if (leave.status == 'disapproved') {
                color = 'text-danger';
                icon = '<i class="bi bi-x-circle-fill text-danger"></i>';
                label = 'Disapproved';
            } else {
                color = 'text-warning';
                icon = '⏳';
                label = 'Pending';
            }


            let hours = Number(leave.total_hours ?? leave.totalHours ?? 0);
            hours = hours.toFixed(2).replace(/\.?0+$/, '') || '0';
            let tt = hours / leave.total_days;
            hours = (typeof tt === 'string') ? tt : `${tt} hr${tt === 1 ? '' : 's'}`;

            let created = '';
            let location = '';
            if (leave.isFixed == '1') {
                created = `<div class="ld-item">
                            <span class="ld-label">Submitted:</span>
                            <span class="ld-value" style="color:#0f5132">${leave.created_at}</span>
                        </div>`;

                location = `<div class="ld-item">
                            <span class="ld-label">Saved Location:</span>
                            <span class="ld-value" style="color:#0f5132">${leave.geo_location}</span>
                        </div>`;
            }
            let appBy = '';
            let appDate = '';
            if (leave.status === "approved" || leave.status === "disapproved") {

                appBy = `<div class="ld-item">
                    <span class="ld-label">${leave.status.toUpperCase()} By:</span>
              
                    <span class="ld-value" style="color:#0f5132">${approvedBy}</span>
                </div>`;
                appDate = `<div class="ld-item">
                    <span class="ld-label">Approved Date:</span>
          
                    <span class="ld-value" style="color:#0f5132">${leave.approvedDate}</span>
                </div>`;
            }

            return `<div class="leave-details">
                    <div class="ld-item">
                        <span class="ld-label">Status:</span>
                        ${icon}
                        <span class="ld-value ${color}">${label}</span>
                    </div>
                    <div class="ld-item">
                        <span class="ld-label">Total Hours:</span>
                        <span class="ld-value" style="color:#ff6b4a;">${hours}</span>
                    </div>
                        ${created}${location}
                        ${appBy}${appDate}
                </div>`;

        }

        function getAttachmentsForRows(serverList, rowIds) {
            const base = "<?= base_url('uploads/leaves/') ?>";
            const wanted = (rowIds || []).map(String);
            return (serverList || [])
                .filter(a => wanted.includes(String(a.time_row)))
                .map(a => ({
                    tklraId: a.tklraId,
                    name: a.attachment,
                    type: '',
                    data: base + a.attachment,
                    groupId: a.groupId || '',
                    time_row: String(a.time_row || '')
                }));
        }

        function fileToDataUrl(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        /* Returns the cached md for $row, but only if $row is still part of
           the intact contiguous block that starts at its group's head row.
           Otherwise returns null so callers render it as fresh/empty. */
        function getRowMdIfGroupIntact($row, md) {
            if (!md) return null;
            const rowId = String($row.find('.tchk').attr('id') || '');
            const raw = md[rowId];
            if (!raw) return null;
            if (!raw.groupId) return raw;

            const $allRows = $('table tbody tr, table tr').has('.tchk');

            const groupIds = Object.keys(md)
                .filter(k => md[k] && md[k].groupId === raw.groupId)
                .map(Number)
                .sort((a, b) => a - b);
            const head = groupIds[0];

            let headIdx = -1,
                myIdx = -1;
            $allRows.each(function(idx) {
                const rr = String($(this).find('.tchk').attr('id') || '');
                if (Number(rr) === head) headIdx = idx;
                if (rr === rowId) myIdx = idx;
            });
            if (headIdx === -1 || myIdx === -1) return null;
            if (myIdx < headIdx) return null;

            let intactEnd = headIdx;
            for (let k = headIdx + 1; k < $allRows.length; k++) {
                const $r2 = $allRows.eq(k);
                const r2id = String($r2.find('.tchk').attr('id') || '');
                const r2md = md[r2id];
                if (!$r2.find('.tchk').is(':checked')) break;
                if (!r2md || r2md.groupId !== raw.groupId) break;
                intactEnd = k;
            }

            return (myIdx >= headIdx && myIdx <= intactEnd) ? raw : null;
        }

        function getRowAttachments(md) {
            if (!md) return [];
            if (Array.isArray(md.attachments)) return md.attachments.slice();
            if (md.attachment) {
                return [{
                    name: md.attachmentName || 'attachment',
                    type: md.attachmentType || '',
                    data: md.attachment
                }];
            }
            return [];
        }

        function buildAttachmentCellHtml(md, isLocked) {
            const list = getRowAttachments(md);
            const disabled = isLocked ? 'disabled' : '';

            let listHtml = '';
            if (list.length === 0) {
                listHtml = `<div class="attachment-empty">No files</div>`;
            } else {
                listHtml = list.map((att, idx) => `
                <div class="attachment-item" data-att-idx="${idx}" data-att-id="${att.tklraId || ''}">
                    <i class="bi bi-paperclip"></i>
                    <span class="att-name" title="${att.name}">Attachment ${att.tklraId}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary att-btn att-view-btn"
                        data-att-url="${att.data || ''}"
                        data-att-name="${att.name || ''}"
                        data-att-type="${att.type || ''}"
                        title="View">View</button>
                    ${!isLocked ? `<button type="button" class="btn btn-sm btn-outline-danger att-btn att-del-btn" title="Delete">Delete</button>` : ''}
                </div>
            `).join('');
            }

            const picker = !isLocked ? `
            <input type="file" class="form-control form-control-sm attachment-input mt-1" multiple accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted" style="font-size:0.65rem;">Multiple files allowed.</small>` : '';

            return `<div class="attachment-preview">${listHtml}${picker}</div>`;
        }

        /* =========================================================================
        DYNAMIC ROW — LEAVE TYPE CELL BUILDERS
        ========================================================================= */

        function buildAssistedSelectHtmlFixed(selected) {

            let relatives = [];
            $.ajax({
                url: "<?= site_url('get_relatives') ?>",
                type: 'POST',
                data: {},
                async: false,
                success: function(data) {
                    let res = JSON.parse(data);
                    relatives = res.rel;
                }
            });

            let opts = '<option value="">---</option>';
            Object.entries(relatives).forEach(([key, label]) => {
                if (key === 'MOTHER' || key === 'FATHER' || key === 'SPOUSE') {
                    opts += `<option value="${label}"${selected === label ? ' selected' : ''}>[${key}] ${label}</option>`;
                } else {
                    opts += `<option value="${label}"${selected === label ? ' selected' : ''}>[CHILD] ${label}</option>`;
                }
            });
            $('select#assistedPerson').html(opts);
        }

        function buildAssistedSelectHtml(selected, isLocked) {

            let relatives = [];
            $.ajax({
                url: "<?= site_url('get_relatives') ?>",
                type: 'POST',
                data: {},
                async: false,
                success: function(data) {
                    let res = JSON.parse(data);
                    relatives = res.rel;
                }
            });

            let opts = '<option value="">---</option>';
            Object.entries(relatives).forEach(([key, label]) => {
                if (key === 'MOTHER' || key === 'FATHER' || key === 'SPOUSE') {
                    opts += `<option value="${label}"${selected === label ? ' selected' : ''}>[${key}] ${label}</option>`;
                } else {
                    opts += `<option value="${label}"${selected === label ? ' selected' : ''}>[CHILD] ${label}</option>`;
                }
            });

            return `<div class="assistContainer"><label for="leaveType" class="form-label small fw-semibold text-muted mt-3 mb-0 d-block">Person Being Assisted</label><select class="form-select form-select-sm assisted-select mt-1"${isLocked ? ' disabled' : ''}>${opts}</select></div>`;

        }

        function buildLeaveTypeCellHtml(selectedValue, isLocked, selectedAssisted) {
            let leave_options = [];
            $.ajax({
                url: "<?= site_url('leave_options') ?>",
                type: 'POST',
                async: false,
                data: {
                    fixed_type: 0
                },
                success: function(data) {
                    let res = JSON.parse(data);
                    leave_options = res.options || [];
                }
            });

            const opts = leave_options.map(o => `<option value="${o.value}"${selectedValue === o.value ? ' selected' : ''}>${o.label}</option>`).join('');
            const assistedHtml = (selectedValue === 'medical') ? buildAssistedSelectHtml(selectedAssisted, isLocked) : '';
            return `<select class="form-select form-select-sm leave-select"${isLocked ? ' disabled' : ''}>${opts}</select>${assistedHtml}`;
        }

        function viewAttachment(dataUrl, name, type) {
            if (!dataUrl) return;

            if (!/^data:/i.test(dataUrl)) {
                const w = window.open(dataUrl, '_blank');
                if (!w) {
                    const a = document.createElement('a');
                    a.href = dataUrl;
                    a.target = '_blank';
                    a.download = name || 'attachment';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
                return;
            }

            try {
                const parts = dataUrl.split(',');
                const mime = (type || (parts[0].match(/:(.*?);/) || [])[1] || 'application/octet-stream');
                const bin = atob(parts[1]);
                const len = bin.length;
                const buf = new Uint8Array(len);
                for (let i = 0; i < len; i++) buf[i] = bin.charCodeAt(i);
                const blob = new Blob([buf], {
                    type: mime
                });
                const url = URL.createObjectURL(blob);
                const w = window.open(url, '_blank');
                if (!w) {
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = name || 'attachment';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
                setTimeout(() => URL.revokeObjectURL(url), 60000);
            } catch (e) {
                const a = document.createElement('a');
                a.href = dataUrl;
                a.target = '_blank';
                a.download = name || 'attachment';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        }

        window.changeLeave_ = function(leave) {
            const $form = $('#leaveForm');
            const isMatPat = (leave === 'maternity' || leave === 'paternity');
            if (isMatPat) {
                $('input#patStart').val('')
                $('input#patEnd').val('')
                $('input#matStart').val('')
                $('input#matEnd').val('')
                $form.addClass('mat-pat-active');

                if (leave === 'maternity') {
                    $('#maternityLeaveContainer').show();
                    $('#paternityLeaveContainer').hide();
                } else {
                    $('#paternityLeaveContainer').show();
                    $('#maternityLeaveContainer').hide();
                }
                return;
            }

            /* ---------- Normal mode ---------- */
            $form.removeClass('mat-pat-active');
            $('#maternityLeaveContainer, #paternityLeaveContainer').hide();

            if (leave !== 'vacation') {
                $('div#medicalAssistedPerson').hide();
                if (leave === 'medical') {
                    $('div#medicalAssistedPerson').show();
                }
                $('input#isTimeRange').prop('disabled', false);
                $('div#vacationPayment').hide();

                $('input#statusPaid').prop('disabled', true);
                $('input#statusUnpaid').prop('disabled', true);
            } else {
                $('div#vacationPayment').show();
                $('div#fixedAttachmentContainer').show();
                $('div#fixednotesContainer').show();
                $('div#fixedGeoContainer').show();
                $('input#statusPaid').prop('disabled', false);
                $('input#statusUnpaid').prop('disabled', false);
                $('input#isTimeRange').prop('disabled', true);
                $('input#isTimeRange').prop('checked', false);
                $('div#timeRangeContainer').hide();
                $('div#medicalAssistedPerson').hide();
                timeTable();
            }
        };

        window.matDelivery = function(val) {
            let m = getMatpatDays('maternity');
            window._matDays = m[val] || 0;
            computeMaternityDates();
        };

        function getMatpatDays(code) {
            let mat_pat_days = {};
            $.ajax({
                url: "<?= site_url('getMatpatDays') ?>",
                type: 'POST',
                async: false,
                data: {
                    code
                },
                success: function(data) {
                    let res = JSON.parse(data);
                    mat_pat_days = res.matpat;
                }
            });

            return mat_pat_days;
        }

        function computeMaternityDates() {
            const start = $('#matStart').val();
            const days = window._matDays || 0;

            if (!start || !days) {
                return;
            }

            const [y, m, d] = start.split('-').map(Number);
            const startDate = new Date(y, m - 1, d);

            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + days - 1);

            const backDate = new Date(endDate);
            backDate.setDate(backDate.getDate() + 1);

            const fmt = (dt) => {
                const yy = dt.getFullYear();
                const mm = String(dt.getMonth() + 1).padStart(2, '0');
                const dd = String(dt.getDate()).padStart(2, '0');
                return `${yy}-${mm}-${dd}`;
            };

            const startStr = fmt(startDate);
            const endStr = fmt(endDate);
            const backStr = fmt(backDate);
            console.log(startStr, endStr)

            const matEndEl = document.getElementById('matEnd');
            if (matEndEl && matEndEl._flatpickr) {
                matEndEl._flatpickr.set('minDate', startStr);
                matEndEl._flatpickr.setDate(endStr, false);
            } else {
                $('#matEnd').val(endStr);
            }

            $('#matDays').val(days);

            const matBackEl = document.getElementById('matBack');
            if (matBackEl && matBackEl._flatpickr) {
                matBackEl._flatpickr.set('minDate', backStr);
                matBackEl._flatpickr.setDate(backStr, false);
            } else {
                $('#matBack').val(backStr);
            }
        }

        const PATERNITY_DAYS = getMatpatDays('paternity');

        function computePaternityDates() {
            const patDate = $('#patDate').val();
            let patStart = $('#patStart').val();

            if (patDate && patStart && patStart < patDate) {
                patStart = '';
                const startEl = document.getElementById('patStart');
                if (startEl && startEl._flatpickr) startEl._flatpickr.clear();
                else $('#patStart').val('');

                const endEl = document.getElementById('patEnd');
                if (endEl && endEl._flatpickr) endEl._flatpickr.clear();
                else $('#patEnd').val('');
                $('#patDays').val('');
            }

            if (patDate) {
                const el = document.getElementById('patStart');
                if (el && el._flatpickr) el._flatpickr.set('minDate', patDate);
            }

            if (patStart) {
                const [y, m, d] = patStart.split('-').map(Number);
                const startDate = new Date(y, m - 1, d);

                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + PATERNITY_DAYS - 1);

                const fmt = (dt) => {
                    const yy = dt.getFullYear();
                    const mm = String(dt.getMonth() + 1).padStart(2, '0');
                    const dd = String(dt.getDate()).padStart(2, '0');
                    return `${yy}-${mm}-${dd}`;
                };

                const endStr = fmt(endDate);

                const patEndEl = document.getElementById('patEnd');
                if (patEndEl && patEndEl._flatpickr) {
                    patEndEl._flatpickr.set('minDate', patStart);
                    patEndEl._flatpickr.setDate(endStr, false);
                } else {
                    $('#patEnd').val(endStr);
                }

                $('#patDays').val(PATERNITY_DAYS);
            }
        }

        window.timeTable = function() {
            if (!$('input#isTimeRange').is(':checked')) {
                $('input#startDate').prop('disabled', false);
                $('input#endDate').prop('disabled', false);
                lastCheckedIndex = null;
                for (let i = 8; i <= 18; i++) {
                    $(`input.tchk#${i}`).prop('checked', false);
                    $(`input.tchk#${i}`).prop('disabled', false);
                    $(`input.tp${i}_1`).prop('disabled', true);
                    $(`input.tp${i}_2`).prop('disabled', true);
                    $(`input.tp${i}_1`).val(i + ':00');
                    $(`input.tp${i}_2`).val(i + ':59');
                    $(`#g_${i}`).val('');
                }
                $('tr[data-auto-split="1"]').remove();
                const $rows = $('table tbody tr, table tr').has('.tchk');
                $rows.each(function() {
                    const $cells = $(this).children('td');
                    for (let col = 3; col <= 7; col++) {
                        $cells.eq(col).empty().show().removeAttr('rowspan');
                    }
                });
                groupCounter = 1;
            } else {
                $('input#startDate').prop('disabled', true);
                $('input#endDate').prop('disabled', true);
                let s = $('input#startDate').val();
                $('input#endDate').val(s);
                for (let i = 8; i <= 18; i++) {
                    $(`input.tchk#${i}`).prop('checked', false);
                    activateTime(i);
                }
            }
            time_funcs();
        };

        window.activateTime = function(no) {
            const $chk = $(`#${no}`);
            const $row = $chk.closest('tr');
            if ($row.attr('data-conflict-row') === '1') {
                return;
            }
            const isChecked = $chk.is(':checked');
            const isRootRow = /^\d+$/.test(String(no));

            if (isRootRow && isChecked && !$chk.prop('disabled')) {
                const $allRows = $('table tbody tr, table tr').has('.tchk');
                const rootRows = [];
                $allRows.each(function() {
                    const id = String($(this).find('.tchk').attr('id') || '');
                    if (/^\d+$/.test(id)) rootRows.push($(this));
                });

                const currentIdx = rootRows.findIndex(r => String(r.find('.tchk').attr('id')) === String(no));

                if (currentIdx !== -1) {
                    let prevIdx = -1;
                    for (let i = currentIdx - 1; i >= 0; i--) {
                        const $c = rootRows[i].find('.tchk');
                        if ($c.is(':checked') && !$c.prop('disabled')) {
                            prevIdx = i;
                            break;
                        }
                    }

                    if (prevIdx !== -1) {
                        let blocked = false;
                        for (let i = prevIdx + 1; i < currentIdx; i++) {
                            const $midChk = rootRows[i].find('.tchk');
                            if ($midChk.is(':checked') && !$midChk.prop('disabled')) {
                                blocked = true;
                                break;
                            }
                        }

                        if (!blocked) {
                            for (let i = prevIdx + 1; i < currentIdx; i++) {
                                const $midChk = rootRows[i].find('.tchk');
                                if ($midChk.prop('disabled')) continue;
                                if (!$midChk.is(':checked')) {
                                    $midChk.prop('checked', true);

                                    const midId = String($midChk.attr('id'));
                                    $(`input.tp${midId}_1`).prop('disabled', false);
                                    $(`input.tp${midId}_2`).prop('disabled', false);
                                }
                            }
                        }
                    }
                }
            }

            if (isRootRow && !isChecked) {
                const root = String(no);

                const $splitRows = $(`tr[data-auto-split="1"][data-parent-row="${root}"]`);
                const anyChecked = $splitRows.find('.tchk:checked').length > 0;
                if (!anyChecked) {
                    $splitRows.each(function() {
                        const $splitRow = $(this);
                        $splitRow.find('.input_chk').each(function() {
                            if (this._flatpickr) this._flatpickr.destroy();
                        });

                        $splitRow.remove();
                    });
                }

                const remainingSplits = $(`tr[data-auto-split="1"][data-parent-row="${root}"]`).length;

                if (remainingSplits === 0) {
                    const startValue = `${root}:00`;
                    const $startInput = $(`#time${root}_1`);

                    if ($startInput.length) {
                        if ($startInput[0]._flatpickr) {
                            $startInput[0]._flatpickr.setDate(startValue, true, 'H:i');
                        } else {
                            $startInput.val(startValue);
                        }
                    }

                    const endValue = `${root}:59`;
                    const $endInput = $(`#time${root}_2`);

                    if ($endInput.length) {
                        if ($endInput[0]._flatpickr) {
                            $endInput[0]._flatpickr.setDate(endValue, true, 'H:i');
                        } else {
                            $endInput.val(endValue);
                        }
                    }
                }
            }

            if (isChecked) {
                $(`input.tp${no}_1`).prop('disabled', false);
                $(`input.tp${no}_2`).prop('disabled', false);

                $(`input#time${no}_1`).prop('disabled', false);
                $(`input#time${no}_2`).prop('disabled', false);
            } else {
                $(`input.tp${no}_1`).prop('disabled', true);
                $(`input.tp${no}_2`).prop('disabled', true);

                $(`input#time${no}_1`).prop('disabled', true);
                $(`input#time${no}_2`).prop('disabled', true);
            }

            if ($row.find('input.groups_row').val()) return;

            $(`.tp${no}_1, .tp${no}_2`).prop('disabled', !isChecked);

            (function wipeBrokenRows() {
                if (!currentEditingLeaveData || !currentEditingLeaveData.md) return;
                const $allRows = $('table tbody tr, table tr').has('.tchk');
                $allRows.each(function() {
                    const $r = $(this);
                    const $c = $r.find('.tchk');
                    if (!$c.is(':checked')) return;
                    if ($r.find('input.groups_row').val()) return;
                    const rid = String($c.attr('id') || '');
                    const raw = currentEditingLeaveData.md[rid];
                    if (!raw || !raw.groupId) return;

                    if (getRowMdIfGroupIntact($r, currentEditingLeaveData.md) === null) {
                        $r.children('td').slice(3, 8)
                            .empty().show().removeAttr('rowspan');
                    }
                });
            })();

            mergeLeaveColumns();
            updateDynamicInputs();
        };

        window.lockGroup = function(btn) {
            const $parentTd = $(btn).closest('td');
            const $firstRow = $(btn).closest('tr');
            const rowSpan = parseInt($parentTd.attr('rowspan')) || 1;

            const $attachInput = $firstRow.find('.attachment-input');
            const existingCount = $firstRow.find('.attachment-item').length;
            const hasNewFiles = $attachInput.length > 0 &&
                $attachInput[0].files &&
                $attachInput[0].files.length > 0;

            if (existingCount === 0 && !hasNewFiles) {
                alertRequired('Please attach at least one file before locking.', 'leaveModal')
                return;
            }

            const $remarks = $firstRow.find('.remarks-input');
            if (!$remarks.length || $remarks.val().trim() === '') {
                alertRequired('Please provide remarks before locking.', 'leaveModal')
                return;
            }

            const $rows = $('table tbody tr, table tr').has('.tchk');
            const startIndex = $rows.index($firstRow);

            const $leaveTd = $firstRow.children('td').eq(3);
            const $selectInput = $leaveTd.find('select.leave-select');
            if ($selectInput.val() == 'medical') {
                const $assist = $firstRow.children('td').eq(3);
                const $assistInput = $leaveTd.find('select.assisted-select');
                if ($assistInput.val() === '') {
                    alertRequired('Please select assisted person before locking.', 'leaveModal')
                    return;
                }
            }

            const $attachmentTd = $firstRow.children('td').eq(4);
            const $fileInput = $attachmentTd.find('input.attachment-input');
            const hasPendingFiles = $fileInput.length > 0 && $fileInput[0].files && $fileInput[0].files.length > 0;
            const rowId = $firstRow.find('.tchk').attr('id');

            let attachmentMd = null;
            if (currentEditingLeaveData && currentEditingLeaveData.md) {
                attachmentMd = currentEditingLeaveData.md[rowId] || null;
                if (!attachmentMd) {
                    const gid = $firstRow.find('input.groups_row').val();
                    if (gid) {
                        const mate = Object.values(currentEditingLeaveData.md)
                            .find(m => m.groupId === gid);
                        if (mate) attachmentMd = mate;
                    }
                }
            }

            if (!hasPendingFiles) {
                $attachmentTd.html(buildAttachmentCellHtml(attachmentMd, true));
            }

            let groupId = null;
            if (currentEditingLeaveData?.md?.[rowId]?.groupId !== undefined) {
                groupId = currentEditingLeaveData.md[rowId].groupId;
            }

            if (groupId) {
                const $rowsAll = $('table tbody tr, table tr').has('.tchk');
                let inUseElsewhere = false;
                $rowsAll.each(function(idx) {
                    if (idx >= startIndex && idx < startIndex + rowSpan) return;
                    if ($(this).find('input.groups_row').val() === groupId) {
                        inUseElsewhere = true;
                        return false;
                    }
                });
                if (inUseElsewhere) {
                    groupId = `group_${Date.now()}_${groupCounter++}`;
                }
            }

            if (!groupId) {
                groupId = `group_${Date.now()}_${groupCounter++}`;
            }

            for (let i = 0; i < rowSpan; i++) {

                const $row = $rows.eq(startIndex + i);

                $row.find('input.groups_row').val(groupId);

                $row.find('.tchk').prop('disabled', true);
                $row.find('.input_chk').prop('disabled', true);
            }

            for (let i = 0; i < rowSpan; i++) {
                const $row = $rows.eq(startIndex + i);
                const groupsRowValue = $row.find('input.groups_row').val();
                const currentRowId = String($row.find('.tchk').attr('id') || '');

                if (!editedGroups[groupsRowValue]) {
                    editedGroups[groupsRowValue] = [];
                }
                if (!editedGroups[groupsRowValue].includes(currentRowId)) {
                    editedGroups[groupsRowValue].push(currentRowId);
                }
            }

            updateDynamicInputs();
        };

        window.unlockGroup = function(btn) {

            const $rows = $('table tbody tr, table tr').has('.tchk');
            const $firstRow = $(btn).closest('tr');
            const startIdx = $rows.index($firstRow);

            const $chk = $firstRow.find('.tchk');
            const $attachmentTd = $firstRow.children('td').eq(4);
            const $actionTd = $firstRow.children('td').eq(7);
            const rowSpan = parseInt($actionTd.attr('rowspan')) || 1;

            const rowId = $chk.attr('id');

            let attachmentMd = null;
            if (currentEditingLeaveData && currentEditingLeaveData.md) {
                attachmentMd = currentEditingLeaveData.md[rowId] || null;
                if (!attachmentMd) {
                    const gid = $firstRow.find('input.groups_row').val();

                    if (gid) {
                        const mate = Object.values(currentEditingLeaveData.md)
                            .find(m => m.groupId === gid);
                        if (mate) attachmentMd = mate;
                    }
                }
            }

            $attachmentTd.html(buildAttachmentCellHtml(attachmentMd, false));

            const blockRows = [];
            for (let i = 0; i < rowSpan; i++) {
                blockRows.push($rows.eq(startIdx + i));
            }


            blockRows.forEach($row => {

                let gr = $row.find('input.groups_row').val();
                $row.find('input.groups_row').val('');

                $row.find('.tchk').prop('disabled', false);
                if ($row.find('.tchk').is(':checked')) {
                    $row.find('.input_chk').prop('disabled', false);
                }
            });

            mergeLeaveColumns();
            updateDynamicInputs();
        };

        window.deleteGroupRow = function(btn) {

            Swal.fire({
                title: 'Confirm!',
                text: 'Are you sure you want to remove this time range entry?',
                icon: 'question',
                iconColor: '#ffc107',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                allowOutsideClick: false,
                target: document.getElementById('leaveModal'),
                focusConfirm: false,
            }).then((result) => {
                const $rows = $('table tbody tr, table tr').has('.tchk');
                const $firstRow = $(btn).closest('tr');
                const startIdx = $rows.index($firstRow);

                const $actionTd = $firstRow.children('td').eq(7);
                const rowSpan = parseInt($actionTd.attr('rowspan')) || 1;

                for (let i = 0; i < rowSpan; i++) {
                    const $row = $rows.eq(startIdx + i);
                    const $chk = $row.find('.tchk');

                    $chk.prop('checked', false).prop('disabled', false);
                    $row.find('input.groups_row').val('');
                    $row.find('.input_chk').prop('disabled', true);
                    $row.find('input.attachment-input').prop('disabled', true);

                    const $cells = $row.children('td');
                    for (let col = 3; col <= 7; col++) {
                        $cells.eq(col).empty().show().removeAttr('rowspan');
                    }
                }

                mergeLeaveColumns();
                updateDynamicInputs();
            });
        };

        function convertTo24Hour(time12h) {
            if (!time12h) return '';
            if (!/[AP]M/i.test(time12h)) return time12h;
            const [time, modifier] = time12h.split(' ');
            let [hours, minutes] = time.split(':');
            if (hours === '12') hours = '00';
            if (modifier.toUpperCase() === 'PM') hours = parseInt(hours, 10) + 12;
            return `${hours.toString().padStart(2, '0')}:${minutes}`;
        }

        function formatTo12Hour(time24) {
            if (!time24 || time24 === '--:--') return '--:--';

            if (/am|pm/i.test(time24)) return time24.toUpperCase();

            const [hourStr, minuteStr] = time24.split(':');
            let hours = parseInt(hourStr, 10);
            const minutes = minuteStr;
            const modifier = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            if (hours === 0) hours = 12;
            const paddedHours = String(hours).padStart(2, '0');
            return `${paddedHours}:${minutes} ${modifier}`;
        }

        function addOneMinute(time24h) {
            if (!time24h) return '';

            let [hours, minutes] = time24h.split(':').map(Number);

            minutes += 1;

            if (minutes >= 60) {
                minutes = 0;
                hours += 1;
            }

            if (hours >= 24) {
                hours = 0;
            }

            const paddedHours = hours.toString().padStart(2, '0');
            const paddedMinutes = minutes.toString().padStart(2, '0');

            return `${paddedHours}:${paddedMinutes}`;
        }


        function timeRangeToMinutes(startTime, endTime) {
            if (!startTime || !endTime || startTime === '--:--' || endTime === '--:--') return 0;
            try {
                const timeStringToMinutes = (timeStr) => {
                    if (!timeStr) return 0;
                    let hours, minutes;
                    if (timeStr.includes('AM') || timeStr.includes('PM')) {
                        const [time, modifier] = timeStr.split(' ');
                        [hours, minutes] = time.split(':').map(Number);
                        if (modifier === 'PM' && hours < 12) hours += 12;
                        if (modifier === 'AM' && hours === 12) hours = 0;
                    } else {
                        [hours, minutes] = timeStr.split(':').map(Number);
                    }
                    return (hours * 60) + minutes;
                };
                const startM = timeStringToMinutes(startTime);
                const endM = timeStringToMinutes(endTime);
                let diff = endM - startM;
                if (diff < 0) diff += 24 * 60;
                return diff;
            } catch (e) {
                return 0;
            }
        }

        function formatMinutes(totalMinutes) {
            if (totalMinutes <= 0) return '0 hrs';
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            if (hours > 0 && minutes > 0) return `${hours} hr${hours > 1 ? 's' : ''} ${minutes} min${minutes > 1 ? 's' : ''}`;
            if (hours > 0) return `${hours} hr${hours > 1 ? 's' : ''}`;
            if (minutes > 0) return `${minutes} min${minutes > 1 ? 's' : ''}`;
            return '0 hrs';
        }

        function parseHoursToMinutes(text) {
            if (!text) return 0;
            const t = String(text).trim().toLowerCase();

            const hrMatch = t.match(/(\d+(?:\.\d+)?)\s*(?:hr|hour)/);
            const minMatch = t.match(/(\d+(?:\.\d+)?)\s*(?:min)/);
            if (hrMatch || minMatch) {
                const h = hrMatch ? parseFloat(hrMatch[1]) : 0;
                const m = minMatch ? parseFloat(minMatch[1]) : 0;
                return Math.round(h * 60 + m);
            }

            const clock = t.match(/^(\d{1,2}):(\d{2})$/);
            if (clock) return parseInt(clock[1], 10) * 60 + parseInt(clock[2], 10);

            const num = t.match(/-?\d+(?:\.\d+)?/);
            if (num) return Math.round(parseFloat(num[0]) * 60);

            return 0;
        }

        function formatMinutesShort(mins) {
            if (!mins) return '0h';
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            if (h && m) return `${h}h ${m}m`;
            if (h) return `${h}h`;
            return `${m}m`;
        }

        function updateWeekTotals() {
            const calendarGrid = document.getElementById('calendarGrid');
            if (!calendarGrid) return;

            const dayCells = Array.from(calendarGrid.querySelectorAll('.calendar-cell:not(.week-total-cell)'));
            const totalCells = calendarGrid.querySelectorAll('.week-total-cell');
            if (!totalCells.length) return;

            if (window._weekTotalsXhrs) {
                window._weekTotalsXhrs.forEach(x => {
                    try {
                        x.abort();
                    } catch (e) {}
                });
            }
            window._weekTotalsXhrs = [];

            totalCells.forEach((totalCell, weekIdx) => {
                const slice = dayCells.slice(weekIdx * 7, weekIdx * 7 + 7);
                if (!slice.length) return;

                const startDate = slice[0].dataset.date;
                const endDate = slice[slice.length - 1].dataset.date;

                const hoursDuty = totalCell.querySelector('.duty-hours');
                const hoursLeave = totalCell.querySelector('.leave-hours');
                const hoursEl = totalCell.querySelector('.wt-hours');
                const hoursDeficit = totalCell.querySelector('.deficit-hours');
                if (hoursDuty) hoursDuty.textContent = '…';
                if (hoursLeave) hoursLeave.textContent = '…';
                if (hoursEl) hoursEl.textContent = '…';
                if (hoursDeficit) hoursDeficit.textContent = '…';

                const xhr = $.ajax({
                    url: "<?= site_url('get_week_totals') ?>",
                    type: 'POST',
                    data: {
                        start: startDate,
                        end: endDate
                    },
                    success: function(res) {
                        let hours = JSON.parse(res);
                        if (hoursDuty) hoursDuty.textContent = hours.total_duty_hours + ' hrs';
                        if (hoursLeave) hoursLeave.textContent = hours.total_leave_hours + ' hrs';
                        if (hoursEl) hoursEl.textContent = hours.total_week_hours + ' hrs';
                        if (hoursDeficit) hoursDeficit.textContent = hours.total_deficit_hours + ' hrs';
                    },
                    error: function(jqXHR, textStatus) {
                        if (textStatus === 'abort') return;
                        console.error('get_week_totals failed:', startDate, endDate, textStatus);

                        let totalHours = 0;
                        slice.forEach(cell => {
                            cell.querySelectorAll('.total-hours').forEach(el => {
                                const h = parseFloat(String(el.textContent).replace(/[^\d.-]/g, ''));
                                if (!isNaN(h)) totalHours += h;
                            });
                        });
                        if (hoursEl) hoursEl.textContent = parseFloat(totalHours.toFixed(2)) + ' hrs';
                    }
                });

                window._weekTotalsXhrs.push(xhr);
            });
        }

        function buildGeoLocationHtml(rowIds, created, gloc) {
            if (!Array.isArray(rowIds)) rowIds = [rowIds];
            const todayDate = new Date().toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });

            rowIds.sort((a, b) => Number(a) - Number(b));
            const firstRowId = rowIds[0];
            const lastRowId = rowIds[rowIds.length - 1];
            const firstStart = formatTo12Hour($(`#time${firstRowId}_1`).val());
            const lastEnd = formatTo12Hour($(`#time${lastRowId}_2`).val());
            const rangeText = `${firstStart} to ${lastEnd}`;

            let end = formatTo12Hour($(`#time${lastRowId}_2`).val());
            const minEnd = parseInt(end.split(':')[1], 10);
            if (minEnd === 59) {
                const matches = end.match(/(\d{1,2}):(\d{2})/);
                let suffix = end.replace(matches[0], '').trim();
                if (!/^(AM|PM)$/i.test(suffix)) suffix = '';
                let h = parseInt(matches[1], 10);
                let m = parseInt(matches[2], 10) + 1;
                if (m === 60) {
                    m = 0;
                    if (suffix) {
                        if (h === 11) {
                            h = 12;
                            suffix = suffix.toUpperCase() === 'AM' ? 'PM' : 'AM';
                        } else if (h === 12) {
                            h = 1;
                        } else {
                            h += 1;
                        }
                    } else {
                        h = (h + 1) % 24;
                    }
                }

                end = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}${suffix ? ' ' + suffix : ''}`;
            }

            const time_range = `${firstStart} to ${end}`;

            let totalMinutes = 0;
            rowIds.forEach(rowId => {
                let s = $(`#time${rowId}_1`).val() || '--:--';
                let e = $(`#time${rowId}_2`).val() || '--:--';
                if (s !== '--:--' && e !== '--:--') {
                    const minS = parseInt(s.split(':')[1], 10);

                    if (minS === 0) {

                    } else {
                        const match = s.match(/(\d{1,2}):(\d{2})/);
                        let suffix = s.replace(match[0], '').trim();
                        if (!/^(AM|PM)$/i.test(suffix)) suffix = '';
                        let h = parseInt(match[1], 10);
                        let m = parseInt(match[2] - 1, 10);
                        if (m === -1) {
                            m = 59;
                            if (suffix) {
                                if (h === 12) {
                                    h = 11;
                                    suffix = suffix.toUpperCase() === 'AM' ? 'PM' : 'AM';
                                } else if (h === 1) {
                                    h = 12;
                                } else {
                                    h -= 1;
                                }
                            } else {
                                h = (h - 1 + 24) % 24;
                            }
                        }
                        s = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}${suffix ? ' ' + suffix : ''}`;

                    }

                    const minE = parseInt(e.split(':')[1], 10);
                    if (minE === 59) {
                        const match = e.match(/(\d{1,2}):(\d{2})/);
                        let suffix = e.replace(match[0], '').trim();
                        if (!/^(AM|PM)$/i.test(suffix)) suffix = '';
                        let h = parseInt(match[1], 10);
                        let m = parseInt(match[2], 10) + 1;
                        if (m === 60) {
                            m = 0;
                            if (suffix) {
                                if (h === 11) {
                                    h = 12;
                                    suffix = suffix.toUpperCase() === 'AM' ? 'PM' : 'AM';
                                } else if (h === 12) {
                                    h = 1;
                                } else {
                                    h += 1;
                                }
                            } else {
                                h = (h + 1) % 24;
                            }
                        }
                        e = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}${suffix ? ' ' + suffix : ''}`;

                    } else if (minE === 0) {

                    }

                    totalMinutes += timeRangeToMinutes(s, e);
                }
            });

            const timeText = `${Number((totalMinutes / 60).toFixed(2))} hr(s)`;

            let status_g = '';
            let checker_g = {};
            rowIds.forEach(rowId => {
                if (currentEditingLeaveData !== null) {
                    if (currentEditingLeaveData?.md?.[rowId]?.groupId !== undefined) {
                        if (!(currentEditingLeaveData.md[rowId].groupId in checker_g)) {
                            checker_g[currentEditingLeaveData.md[rowId].groupId] = true;
                            status_g = currentEditingLeaveData.md[rowId].g_status;
                        }
                    }
                }
            });

            let todaydateHtml = `<div class="geo-date-display" style="font-size: 0.65rem; font-weight: 600; color: #0d6efd; margin-bottom: 3px;">
                                <i class="bi bi-calendar3 me-1"></i><span class="geo-date-text">${todayDate}</span>
                            </div>`;


            let cur_loc = `${(window.currentUserLocation && !window.currentUserLocation.error) ? window.currentUserLocation.address: 'Tagbilaran, City, Bohol, Philippines'}`;
            let loc = `${(window.currentUserLocation && !window.currentUserLocation.error) ? window.currentUserLocation.address: 'Tagbilaran, City, Bohol, Philippines'}`;
            if (gloc != '') {
                loc = `${gloc}`;
            }

            const submittedHtml = created ? `<i class="bi bi-send me-1"></i>Submitted: <span class="geo-submitted-text">${created.split(" ")[0]} ${formatTo12Hour(created.split(" ")[1])}</span>` : '';

            let updated = '';
            if (currentEditingLeaveData?.details !== undefined) {
                const g_details = currentEditingLeaveData.details.find(d => rowIds.includes(String(d.time_row)));
                if (g_details?.updated_date !== undefined && g_details?.updated_location !== undefined) {
                    if (g_details.updated_date != '0000-00-00 00:00:00' && g_details.updated_location != '') {
                        updated = `<div class="geo-submitted-display text-muted" style="font-size: 10px; margin-top: 5px; padding-top: 4px; border-top: 1px dashed #dee2e6;">
                                    <i class="bi bi-send me-1"></i>Updated: <span class="geo-submitted-text">${g_details.updated_date.split(" ")[0]} ${formatTo12Hour(g_details.updated_date.split(" ")[1])}</span>
                                    <br><i class="bi bi-geo-alt me-1"></i><span class="geo-submitted-text">${g_details.updated_location}</span>
                                </div>`;
                    }
                }
            }

            let statusHtml = '';
            if (status_g) {

                if (status_g == 'approved') {
                    todaydateHtml = ``;
                    statusHtml = `<div class="location-displays small text-success fw-bold" style="font-size: 0.6rem; margin-bottom: 3px;">
                                <i class="bi bi-check-circle-fill me-1"></i> <span>${status_g.toUpperCase()}</span>
                            </div>`;
                } else if (status_g == 'disapproved') {
                    todaydateHtml = ``;
                    statusHtml = `<div class="location-displays small text-danger fw-bold" style="font-size: 0.6rem; margin-bottom: 3px;">
                                <i class="bi bi-x-circle-fill me-1"></i> <span>${status_g.toUpperCase()}</span>
                            </div>`;
                } else {
                    statusHtml = `<div class="location-displays small text-warning fw-bold" style="font-size: 0.6rem; margin-bottom: 3px;">
                                ⏳ <span class="ms-1">${status_g.toUpperCase()}</span>
                            </div>`;
                }
            }

            return `<div class="geo-time-summary text-nowrap">
                    <span class="geo-location-row" style="display: none;">${cur_loc}</span>
                    <span class="time_range" style="display: none;">${time_range}</span>
                    ${statusHtml}${todaydateHtml}
                    <div class="time-range-display fw-bold" style="font-size: 0.7rem; color: #333; margin-bottom: 3px;">
                        <i class="bi bi-clock me-1"></i><span class="time-range-text">${rangeText}</span>
                    </div>
                    <div class="hours-total-display fw-bold" style="font-size: 0.7rem; color: #333; margin-bottom: 3px;">
                        <i class="bi bi-hourglass-split me-1"></i><span class="hours-total-text">${timeText}</span>
                    </div>
                    <div class="location-displays small text-primary" style="font-size: 0.6rem; color: #0d6efd;">
                        ${submittedHtml}
                    </div>
                    <div class="location-displays small text-primary" style="font-size: 0.6rem; color: #0d6efd;">
                        <i class="bi bi-geo-alt me-1"></i>${loc}
                    </div>
                    ${updated}
                </div>`;
        }

        function updateGeoLocationForRow(rowId) {

            const $row = $(`#time${rowId}_1`).closest('tr');
            if (!$row.length) return;

            const $rows = $('table tbody tr, table tr').has('.tchk');
            const currentIdx = $rows.index($row);

            let $targetRow = null;
            let $geoLocationTd = null;

            for (let i = currentIdx; i >= 0; i--) {
                const $r = $rows.eq(i);
                const $cell = $r.children('td').eq(6);
                if ($cell.is(':visible') && $cell.find('.geo-time-summary').length > 0) {
                    $targetRow = $r;
                    $geoLocationTd = $cell;
                    break;
                }
            }

            if (!$geoLocationTd) return;

            const startIdx = $rows.index($targetRow);
            const rowSpan = parseInt($geoLocationTd.attr('rowspan')) || 1;

            const rowIds = [];
            for (let i = 0; i < rowSpan; i++) {
                const $r = $rows.eq(startIdx + i);
                const $chk = $r.find('.tchk');
                if ($chk.is(':checked')) rowIds.push($chk.attr('id'));
            }
            if (rowIds.length === 0) rowIds.push(rowId);

            let totalMinutes = 0;
            rowIds.forEach(rowId => {

                let s = $(`#time${rowId}_1`).val() || '--:--';
                let e = $(`#time${rowId}_2`).val() || '--:--';
                if (s !== '--:--' && e !== '--:--') {

                    const minS = parseInt(s.split(':')[1], 10);
                    if (minS === 0) {

                    } else {

                        const match = s.match(/(\d{1,2}):(\d{2})/);
                        let suffix = s.replace(match[0], '').trim();
                        if (!/^(AM|PM)$/i.test(suffix)) suffix = '';
                        let h = parseInt(match[1], 10);
                        let m = parseInt(match[2] - 1, 10);
                        if (m === -1) {
                            m = 59;
                            if (suffix) {
                                if (h === 12) {
                                    h = 11;
                                    suffix = suffix.toUpperCase() === 'AM' ? 'PM' : 'AM';
                                } else if (h === 1) {
                                    h = 12;
                                } else {
                                    h -= 1;
                                }
                            } else {
                                h = (h - 1 + 24) % 24;
                            }
                        }

                        s = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}${suffix ? ' ' + suffix : ''}`;

                    }

                    const minE = parseInt(e.split(':')[1], 10);
                    if (minE === 59) {
                        const match = e.match(/(\d{1,2}):(\d{2})/);
                        let suffix = e.replace(match[0], '').trim();
                        if (!/^(AM|PM)$/i.test(suffix)) suffix = '';
                        let h = parseInt(match[1], 10);
                        let m = parseInt(match[2], 10) + 1;

                        if (m === 60) {
                            m = 0;
                            if (suffix) {
                                if (h === 11) {
                                    h = 12;
                                    suffix = suffix.toUpperCase() === 'AM' ? 'PM' : 'AM';
                                } else if (h === 12) {
                                    h = 1;
                                } else {
                                    h += 1;
                                }
                            } else {
                                h = (h + 1) % 24;
                            }
                        }
                        e = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}${suffix ? ' ' + suffix : ''}`;

                    } else if (minE === 0) {

                    }

                    totalMinutes += timeRangeToMinutes(s, e);
                }
            });

            let hours = `${Number((totalMinutes / 60).toFixed(2))} hr(s)`;

            rowIds.sort((a, b) => Number(a) - Number(b));
            const firstRowId = rowIds[0];
            const lastRowId = rowIds[rowIds.length - 1];
            const firstStart = formatTo12Hour($(`#time${firstRowId}_1`).val());
            const lastEnd = formatTo12Hour($(`#time${lastRowId}_2`).val());
            const rangeText = `${firstStart} to ${lastEnd}`;

            let end = formatTo12Hour($(`#time${lastRowId}_2`).val());
            const minEnd = parseInt(end.split(':')[1], 10);
            if (minEnd === 59) {
                const matches = end.match(/(\d{1,2}):(\d{2})/);
                let suffix = end.replace(matches[0], '').trim();
                if (!/^(AM|PM)$/i.test(suffix)) suffix = '';
                let h = parseInt(matches[1], 10);
                let m = parseInt(matches[2], 10) + 1;
                if (m === 60) {
                    m = 0;
                    if (suffix) {
                        if (h === 11) {
                            h = 12;
                            suffix = suffix.toUpperCase() === 'AM' ? 'PM' : 'AM';
                        } else if (h === 12) {
                            h = 1;
                        } else {
                            h += 1;
                        }
                    } else {
                        h = (h + 1) % 24;
                    }
                }
                end = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}${suffix ? ' ' + suffix : ''}`;

            }
            const time_range = `${firstStart} - ${end}`;

            $geoLocationTd.find('.time_range').text(time_range);
            $geoLocationTd.find('.time-range-text').text(rangeText);
            $geoLocationTd.find('.hours-total-text').text(hours);
            let cur_loc = `${(window.currentUserLocation && !window.currentUserLocation.error) ? window.currentUserLocation.address: 'Tagbilaran, City, Bohol, Philippines'}`;
            $geoLocationTd.find('.geo-location-row').text(cur_loc);
        }

        function mergeLeaveColumns() {
            const $rows = $('table tbody tr, table tr').has('.tchk');
            $rows.each(function() {
                if ($(this).attr('data-conflict-row') === '1') return;
                if (!$(this).find('input.groups_row').val()) {
                    $(this).children('td').slice(3, 8).show().removeAttr('rowspan');
                }
            });

            const checkedRows = [];
            $rows.each(function() {
                if ($(this).attr('data-conflict-row') === '1') return;
                if ($(this).find('.tchk').is(':checked') || $(this).find('input.groups_row').val()) {
                    checkedRows.push($(this));
                }
            });

            if (checkedRows.length < 2) return;

            const groups = [];
            let currentGroup = [checkedRows[0]];

            for (let i = 1; i < checkedRows.length; i++) {
                const prevRow = checkedRows[i - 1];
                const currRow = checkedRows[i];

                if (currRow.attr('data-conflict-row') === '1' ||
                    prevRow.attr('data-conflict-row') === '1') {
                    groups.push(currentGroup);
                    currentGroup = [currRow];
                    continue;
                }

                const prevIdx = $rows.index(prevRow);
                const currIdx = $rows.index(currRow);

                const prevGroupAttr = prevRow.find('input.groups_row').val() || 'unlocked';
                const currGroupAttr = currRow.find('input.groups_row').val() || 'unlocked';

                if (currIdx === prevIdx + 1 && prevGroupAttr === currGroupAttr && prevGroupAttr === 'unlocked') {
                    currentGroup.push(currRow);
                } else {
                    groups.push(currentGroup);
                    currentGroup = [currRow];
                }
            }
            groups.push(currentGroup);

            groups.forEach(group => {
                if (group.length > 1) {
                    const firstRowCells = group[0].children('td');
                    const rowSpan = group.length;
                    for (let colIdx = 3; colIdx <= 7; colIdx++) {
                        firstRowCells.eq(colIdx).attr('rowspan', rowSpan);
                        for (let i = 1; i < group.length; i++) {
                            group[i].children('td').eq(colIdx).hide();
                        }
                    }
                }
            });
        }

        function updateDynamicInputs() {
            const $rows = $('table tbody tr, table tr').has('.tchk');

            $rows.each(function() {
                const $row = $(this);
                if ($row.attr('data-conflict-row') === '1') return;
                if ($row.hasClass('duty-row') || $row.attr('data-duty-type') !== undefined) {
                    return;
                }

                const gVal = $row.find('input.groups_row').val() || '';
                if (gVal.indexOf('duty_') === 0) {
                    $row.children('td').slice(3, 8).empty();
                }
            });

            $rows.each(function() {
                const $row = $(this);
                if ($row.attr('data-conflict-row') === '1') return;
                if ($row.hasClass('duty-row') || $row.attr('data-duty-type') !== undefined) {
                    return;
                }

                const $chk = $row.find('.tchk');
                const isChecked = $chk.is(':checked');
                const isLocked = Boolean($row.find('input.groups_row').val());
                const $cells = $row.children('td');

                const $leaveTypeTd = $cells.eq(3);
                const $attachmentTd = $cells.eq(4);
                const $remarksTd = $cells.eq(5);
                const $geoLocationTd = $cells.eq(6);
                const $actionTd = $cells.eq(7);

                if ((isChecked || isLocked) && $leaveTypeTd.is(':visible')) {

                    const rowSpan = parseInt($leaveTypeTd.attr('rowspan')) || 1;

                    let textRowSpan = rowSpan;
                    if (rowSpan === 1) {
                        textRowSpan = 2;
                    }

                    if ($leaveTypeTd.find('.leave-select').length === 0) {
                        let selectedValue = '';
                        let selectedAssisted = '';

                        const guardedMd = getRowMdIfGroupIntact($row, currentEditingLeaveData?.md);
                        if (guardedMd) {
                            selectedValue = guardedMd.leaveType || '';
                            selectedAssisted = guardedMd.assistedPerson || '';
                        }

                        if (selectedValue == '') {
                            selectedValue = $('#leaveType').val();
                        }

                        $leaveTypeTd.html(buildLeaveTypeCellHtml(selectedValue, isLocked, selectedAssisted));
                    } else {
                        $leaveTypeTd.find('.leave-select').prop('disabled', isLocked);
                        $leaveTypeTd.find('.assisted-select').prop('disabled', isLocked);
                    }

                    if ($attachmentTd.find('.attachment-preview').length === 0) {
                        const attachmentMd = getRowMdIfGroupIntact($row, currentEditingLeaveData?.md);
                        $attachmentTd.html(buildAttachmentCellHtml(attachmentMd, isLocked));
                    } else {
                        $attachmentTd.find('.attachment-input').prop('disabled', isLocked);
                    }

                    const $textarea = $remarksTd.find('textarea');
                    if ($textarea.length === 0) {
                        let remarksValue = '';
                        const guardedMd = getRowMdIfGroupIntact($row, currentEditingLeaveData?.md);
                        if (guardedMd) remarksValue = guardedMd.remarks || '';
                        $remarksTd.html(`<textarea class="form-control form-control-sm remarks-input" rows="${textRowSpan}" placeholder="Enter remarks..." ${isLocked ? 'disabled' : ''}>${remarksValue}</textarea>`);
                    } else {
                        $textarea.prop('disabled', isLocked);
                        $textarea.attr('rows', textRowSpan);
                    }

                    const geoRowId = $chk.attr('id');
                    if ($geoLocationTd.find('.geo-time-summary').length === 0) {
                        const $rowsAll = $('table tbody tr, table tr').has('.tchk');
                        const startIdx = $rowsAll.index($row);
                        const span = parseInt($geoLocationTd.attr('rowspan')) || 1;
                        const rowIds = [];
                        for (let i = 0; i < span; i++) {
                            const $r = $rowsAll.eq(startIdx + i);
                            const $c = $r.find('.tchk');
                            if ($c.is(':checked')) rowIds.push($c.attr('id'));
                        }
                        if (rowIds.length === 0) rowIds.push(geoRowId);
                        $geoLocationTd.html(buildGeoLocationHtml(rowIds, '', ''));
                    } else {
                        updateGeoLocationForRow(geoRowId);
                    }

                    if (isLocked) {
                        let rid = $chk.attr('id');
                        if (currentEditingLeaveData?.md?.[rid]?.g_status !== undefined) {
                            if (currentEditingLeaveData.md[rid].g_status == 'pending') {
                                $actionTd.html(`<button type="button" class="btn btn-sm btn-secondary action-btn" onclick="unlockGroup(this)"><i class="bi bi-unlock"></i></button>`);
                            } else if (currentEditingLeaveData.md[rid].g_status == 'approved') {
                                $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-success approval-details-btn" onclick="buildApprovalDetailsHtml('${currentEditingLeaveData.md[rid].leaveId}', '${currentEditingLeaveData.md[rid].groupId}')">Details</button>`);
                            } else {
                                $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-danger approval-details-btn" onclick="buildApprovalDetailsHtml('${currentEditingLeaveData.md[rid].leaveId}', '${currentEditingLeaveData.md[rid].groupId}')">Details</button>`);
                            }
                        } else {
                            $actionTd.html(`<button type="button" class="btn btn-sm btn-secondary action-btn" onclick="unlockGroup(this)"><i class="bi bi-unlock"></i></button>`);
                        }
                    } else {
                        let actionButtons = `<div class="d-flex gap-1 justify-content-center"><button type="button" class="btn btn-sm btn-primary action-btn" onclick="lockGroup(this)"><i class="bi bi-lock"></i></button>`;
                        if (leaveIdInput && leaveIdInput.value) {
                            actionButtons += `<button type="button" class="btn btn-sm btn-danger action-btn" onclick="deleteGroupRow(this)"><i class="bi bi-trash"></i></button>`;
                        }
                        actionButtons += `</div>`;
                        $actionTd.html(actionButtons);
                    }

                } else if (!isChecked && !isLocked && $leaveTypeTd.is(':visible') && $leaveTypeTd.attr('rowspan') === undefined) {
                    $leaveTypeTd.empty();
                    $attachmentTd.empty();
                    $remarksTd.empty();
                    $geoLocationTd.empty();
                    $actionTd.empty();
                }
            });
        }

        function time_funcs() {
            document.querySelectorAll('.input_chk').forEach(el => {
                if (el._flatpickr) el._flatpickr.destroy();
            });

            const getDefault = (selector, fallback) => {

                let val = $(selector).val();
                if (selector == '.tp18_2') {
                    val = '19:00 PM';
                }
                return (val && val.trim() !== '') ? val : fallback;
            };

            const flatpickrConfig = (min, max, defaultTime) => ({
                enableTime: true,
                noCalendar: true,
                dateFormat: "G:i K",
                minTime: min,
                maxTime: max,
                defaultDate: defaultTime,
                minuteIncrement: 1,
                static: true,
                disableMobile: true,
                appendTo: document.getElementById('leaveModal')
            });

            const addOneMinute = (timeStr) => {
                const [hours, minutes] = timeStr.split(':').map(Number);
                const date = new Date();
                date.setHours(hours, minutes + 1);
                return date.toTimeString().slice(0, 5);
            };

            const tp8_1 = flatpickr(".tp8_1", flatpickrConfig("08:00", "08:59", getDefault(".tp8_1", "08:00")));
            const tp8_2 = flatpickr(".tp8_2", flatpickrConfig("08:00", "08:59", getDefault(".tp8_2", "08:59")));
            const tp9_1 = flatpickr(".tp9_1", flatpickrConfig("09:00", "09:59", getDefault(".tp9_1", "09:00")));
            const tp9_2 = flatpickr(".tp9_2", flatpickrConfig("09:00", "09:59", getDefault(".tp9_2", "09:59")));
            const tp10_1 = flatpickr(".tp10_1", flatpickrConfig("10:00", "10:59", getDefault(".tp10_1", "10:00")));
            const tp10_2 = flatpickr(".tp10_2", flatpickrConfig("10:00", "10:59", getDefault(".tp10_2", "10:59")));
            const tp11_1 = flatpickr(".tp11_1", flatpickrConfig("11:00", "11:59", getDefault(".tp11_1", "11:00")));
            const tp11_2 = flatpickr(".tp11_2", flatpickrConfig("11:00", "11:59", getDefault(".tp11_2", "11:59")));
            const tp12_1 = flatpickr(".tp12_1", flatpickrConfig("12:00", "12:59", getDefault(".tp12_1", "12:00")));
            const tp12_2 = flatpickr(".tp12_2", flatpickrConfig("12:00", "12:59", getDefault(".tp12_2", "12:59")));
            const tp13_1 = flatpickr(".tp13_1", flatpickrConfig("13:00", "13:59", getDefault(".tp13_1", "13:00")));
            const tp13_2 = flatpickr(".tp13_2", flatpickrConfig("13:00", "13:59", getDefault(".tp13_2", "13:59")));
            const tp14_1 = flatpickr(".tp14_1", flatpickrConfig("14:00", "14:59", getDefault(".tp14_1", "14:00")));
            const tp14_2 = flatpickr(".tp14_2", flatpickrConfig("14:00", "14:59", getDefault(".tp14_2", "14:59")));
            const tp15_1 = flatpickr(".tp15_1", flatpickrConfig("15:00", "15:59", getDefault(".tp15_1", "15:00")));
            const tp15_2 = flatpickr(".tp15_2", flatpickrConfig("15:00", "15:59", getDefault(".tp15_2", "15:59")));
            const tp16_1 = flatpickr(".tp16_1", flatpickrConfig("16:00", "16:59", getDefault(".tp16_1", "16:00")));
            const tp16_2 = flatpickr(".tp16_2", flatpickrConfig("16:00", "16:59", getDefault(".tp16_2", "16:59")));
            const tp17_1 = flatpickr(".tp17_1", flatpickrConfig("17:00", "17:59", getDefault(".tp17_1", "17:00")));
            const tp17_2 = flatpickr(".tp17_2", flatpickrConfig("17:00", "17:59", getDefault(".tp17_2", "17:59")));
            const tp18_1 = flatpickr(".tp18_1", flatpickrConfig("18:00", "19:00", getDefault(".tp18_1", "18:00")));
            const tp18_2 = flatpickr(".tp18_2", flatpickrConfig("18:00", "19:00", getDefault(".tp18_2", "19:00")));

            tp8_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time8_2.value);
                if (dateStr > time8_2.value) t2 = t1;
                flatpickr(".tp8_2", flatpickrConfig(t1, "08:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp9_1", flatpickrConfig(t1, "09:59", next));
                updateGeoLocationForRow(8);
                time_funcs();
            });
            tp8_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp9_1", flatpickrConfig("09:00", "09:59", "09:00"));
                updateGeoLocationForRow(8);
                handleSplitRowChange($('#time8_2').closest('tr'));
            });
            tp9_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time9_2.value);
                if (dateStr > time9_2.value) t2 = t1;
                flatpickr(".tp9_2", flatpickrConfig(t1, "09:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp10_1", flatpickrConfig(t1, "10:59", next));
                updateGeoLocationForRow(9);
                time_funcs();
            });
            tp9_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp10_1", flatpickrConfig("10:00", "10:59", "10:00"));
                updateGeoLocationForRow(9);
                handleSplitRowChange($('#time9_2').closest('tr'));
            });
            tp10_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time10_2.value);
                if (dateStr > time10_2.value) t2 = t1;
                flatpickr(".tp10_2", flatpickrConfig(t1, "10:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp11_1", flatpickrConfig(t1, "11:59", next));
                updateGeoLocationForRow(10);
                time_funcs();
            });
            tp10_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp11_1", flatpickrConfig("11:00", "11:59", "11:00"));
                updateGeoLocationForRow(10);
                handleSplitRowChange($('#time10_2').closest('tr'));
            });
            tp11_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time11_2.value);
                if (dateStr > time11_2.value) t2 = t1;
                flatpickr(".tp11_2", flatpickrConfig(t1, "11:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp12_1", flatpickrConfig(t1, "12:59", next));
                updateGeoLocationForRow(11);
                time_funcs();
            });
            tp11_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp12_1", flatpickrConfig("12:00", "12:59", "12:00"));
                updateGeoLocationForRow(11);
                handleSplitRowChange($('#time11_2').closest('tr'));
            });
            tp12_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time12_2.value);
                if (dateStr > time12_2.value) t2 = t1;
                flatpickr(".tp12_2", flatpickrConfig(t1, "12:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp13_1", flatpickrConfig(t1, "13:59", next));
                updateGeoLocationForRow(12);
                time_funcs();
            });
            tp12_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp13_1", flatpickrConfig("13:00", "13:59", "13:00"));
                updateGeoLocationForRow(12);
                handleSplitRowChange($('#time12_2').closest('tr'));
            });
            tp13_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time13_2.value);
                if (dateStr > time13_2.value) t2 = t1;
                flatpickr(".tp13_2", flatpickrConfig(t1, "13:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp14_1", flatpickrConfig(t1, "14:59", next));
                updateGeoLocationForRow(13);
                time_funcs();
            });
            tp13_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp14_1", flatpickrConfig("14:00", "14:59", "14:00"));
                updateGeoLocationForRow(13);
                handleSplitRowChange($('#time13_2').closest('tr'));
            });
            tp14_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time14_2.value);
                if (dateStr > time14_2.value) t2 = t1;
                flatpickr(".tp14_2", flatpickrConfig(t1, "14:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp15_1", flatpickrConfig(t1, "15:59", next));
                updateGeoLocationForRow(14);
                time_funcs();
            });
            tp14_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp15_1", flatpickrConfig("15:00", "15:59", "15:00"));
                updateGeoLocationForRow(14);
                handleSplitRowChange($('#time14_2').closest('tr'));
            });
            tp15_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time15_2.value);
                if (dateStr > time15_2.value) t2 = t1;
                flatpickr(".tp15_2", flatpickrConfig(t1, "15:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp16_1", flatpickrConfig(t1, "16:59", next));
                updateGeoLocationForRow(15);
                time_funcs();
            });
            tp15_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp16_1", flatpickrConfig("16:00", "16:59", "16:00"));
                updateGeoLocationForRow(15);
                handleSplitRowChange($('#time15_2').closest('tr'));
            });
            tp16_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time16_2.value);
                if (dateStr > time16_2.value) t2 = t1;
                flatpickr(".tp16_2", flatpickrConfig(t1, "16:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp17_1", flatpickrConfig(t1, "17:59", next));
                updateGeoLocationForRow(16);
                time_funcs();
            });
            tp16_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp17_1", flatpickrConfig("17:00", "17:59", "17:00"));
                updateGeoLocationForRow(16);
                handleSplitRowChange($('#time16_2').closest('tr'));
            });
            tp17_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time17_2.value);
                if (dateStr > time17_2.value) t2 = t1;
                flatpickr(".tp17_2", flatpickrConfig(t1, "17:59", t2));
                // const next = addMinutesTo24(convertTo24Hour(t2), 1);
                // flatpickr(".tp18_1", flatpickrConfig(t1, "19:00", next));
                updateGeoLocationForRow(17);
                time_funcs();
            });
            tp17_2.set('onChange', function(selectedDates, dateStr) {
                // const next = addMinutesTo24(convertTo24Hour(dateStr), 1);
                flatpickr(".tp18_1", flatpickrConfig("18:00", "18:59", "18:00"));
                updateGeoLocationForRow(16);
                handleSplitRowChange($('#time17_2').closest('tr'));
            });
            tp18_1.set('onChange', function(selectedDates, dateStr) {
                let t1 = convertTo24Hour(dateStr);
                let t2 = convertTo24Hour(time18_2.value);
                if (dateStr > time18_2.value) t2 = t1;
                flatpickr(".tp18_2", flatpickrConfig(t1, "19:00", t2));
                updateGeoLocationForRow(18);
                time_funcs();
            });
            tp18_2.set('onChange', function(selectedDates, dateStr) {
                updateGeoLocationForRow(18);
                handleSplitRowChange($('#time18_2').closest('tr'));
            });
        }

        function addMinutesTo24(time24, minutes) {
            if (!time24) return time24;
            const [h, m] = time24.split(':').map(Number);
            const d = new Date(2000, 0, 1, h, m + minutes);
            return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        }

        let splitRowSeq = {};

        function getSplitRoot(rowId) {
            return String(rowId || '').replace(/s\d+$/, '');
        }

        function getHourMaxEnd24(rowId) {
            const n = parseInt(getSplitRoot(rowId), 10);
            if (isNaN(n)) return '';
            return (n >= 18) ? '19:00' : (String(n).padStart(2, '0') + ':59');
        }

        function removeSplitChainAfter($row) {
            let $next = $row.next('tr');
            while ($next.length && $next.attr('data-auto-split') === '1') {
                if ($next.attr('data-conflict-row') === '1') break;
                const $doomed = $next;
                $next = $next.next('tr');
                $doomed.remove();
            }
        }


        function handleSplitRowChange($row) {
            if (!$row || !$row.length) return;
            if ($row.attr('data-conflict-row') === '1') return;

            const $chk = $row.find('.tchk');
            if (!$chk.length) return;

            const rowId = $chk.attr('id');
            removeSplitChainAfter($row);

            setTimeout(() => {
                const isDutyRow = $row[0].classList.contains('duty-row');

                if (!isDutyRow) {
                    if (!$chk.is(':checked')) return;
                }

                const maxEnd = getHourMaxEnd24(rowId);
                if (!maxEnd) return;

                const end24 = convertTo24Hour($('#time' + rowId + '_2').val());
                if (!end24 || end24 >= maxEnd) return;

                const nextStart24 = addMinutesTo24(end24, 1);
                if (nextStart24 > maxEnd) return;

                const root = getSplitRoot(rowId);
                const isRootRow = String(rowId) === String(root);

                if (isRootRow) {
                    splitRowSeq[root] = 0;
                }



                if (!splitRowSeq[root]) splitRowSeq[root] = 0;
                splitRowSeq[root]++;

                const newId = root + 's' + splitRowSeq[root];

                const $newRow = $(`
                    <tr data-auto-split="1" data-parent-row="${root}" data-split-parent="${rowId}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input tchk" id="${newId}" onclick="activateTime(this.id)">
                            <input type="hidden" class="groups_row" id="g_${newId}" name="group_${newId}[]" value="">
                            <input type="hidden" class="parent_row" id="p_${newId}" value="${root}">
                        </td>
                        <td class="text-center"><input type="time" class="form-control form-control-sm input_chk" id="time${newId}_1" value="${nextStart24}" disabled></td>
                        <td class="text-center"><input type="time" class="form-control form-control-sm input_chk" id="time${newId}_2" value="${maxEnd}" disabled></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-center"></td>
                    </tr>
                `);

                $row.after($newRow);

                $newRow.find('input[type="time"]').each(function() {
                    this.type = 'text';
                });

                const startEl = document.getElementById('time' + newId + '_1');
                const endEl = document.getElementById('time' + newId + '_2');

                const cfg = (min, max, def) => ({
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "G:i K",
                    minTime: min,
                    maxTime: max,
                    defaultDate: def,
                    minuteIncrement: 1,
                    static: true,
                    disableMobile: true,
                    appendTo: document.getElementById('leaveModal')
                });

                const fpStart = flatpickr(startEl, cfg(nextStart24, maxEnd, nextStart24));
                const fpEnd = flatpickr(endEl, cfg(nextStart24, maxEnd, maxEnd));

                fpStart.set('onChange', function() {
                    updateGeoLocationForRow(newId);
                });

                fpEnd.set('onChange', function() {
                    updateGeoLocationForRow(newId);
                    handleSplitRowChange($newRow);
                });

                mergeLeaveColumns();
                updateDynamicInputs();
            }, 0);
        }

        function handleSplitRowDuty($row, t1, t2) {
            if (!$row || !$row.length) return;
            if ($row.attr('data-conflict-row') === '1') return;

            const $chk = $row.find('.tchk');
            if (!$chk.length) return;

            const rowId = $chk.attr('id');
            removeSplitChainAfter($row);

            setTimeout(() => {
                const isDutyRow = $row[0].classList.contains('duty-row');

                if (!isDutyRow) {
                    if (!$chk.is(':checked')) return;
                }

                let maxEnd = getHourMaxEnd24(rowId);
                if (!maxEnd) return;

                if (t1) {
                    const [h, m, s] = t1.split(":").map(Number);
                    const date = new Date();
                    date.setHours(h, m, 0, 0);

                    date.setMinutes(date.getMinutes() - 1);

                    maxEnd = [date.getHours(), date.getMinutes()]
                        .map(n => String(n).padStart(2, "0"))
                        .join(":");
                }

                if (t2) {
                    maxEnd = t2;
                }

                const end24 = convertTo24Hour($('#time' + rowId + '_2').val());
                if (!end24 || end24 >= maxEnd) return;

                const nextStart24 = addMinutesTo24(end24, 1);
                if (nextStart24 > maxEnd) return;

                const root = getSplitRoot(rowId);
                const isRootRow = String(rowId) === String(root);

                if (isRootRow) {
                    splitRowSeq[root] = 0;
                }

                if (!splitRowSeq[root]) splitRowSeq[root] = 0;
                splitRowSeq[root]++;

                const newId = root + 's' + splitRowSeq[root];

                const $newRow = $(`
                    <tr data-auto-split="1" data-parent-row="${root}" data-split-parent="${rowId}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input tchk" id="${newId}" onclick="activateTime(this.id)">
                            <input type="hidden" class="groups_row" id="g_${newId}" name="group_${newId}[]" value="">
                            <input type="hidden" class="parent_row" id="p_${newId}" value="${root}">
                        </td>
                        <td class="text-center"><input type="time" class="form-control form-control-sm input_chk" id="time${newId}_1" value="${nextStart24}" disabled></td>
                        <td class="text-center"><input type="time" class="form-control form-control-sm input_chk" id="time${newId}_2" value="${maxEnd}" disabled></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-center"></td>
                    </tr>
                `);

                $row.after($newRow);

                $newRow.find('input[type="time"]').each(function() {
                    this.type = 'text';
                });

                const startEl = document.getElementById('time' + newId + '_1');
                const endEl = document.getElementById('time' + newId + '_2');

                const cfg = (min, max, def) => ({
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "G:i K",
                    minTime: min,
                    maxTime: max,
                    defaultDate: def,
                    minuteIncrement: 1,
                    static: true,
                    disableMobile: true,
                    appendTo: document.getElementById('leaveModal')
                });

                const fpStart = flatpickr(startEl, cfg(nextStart24, maxEnd, nextStart24));
                const fpEnd = flatpickr(endEl, cfg(nextStart24, maxEnd, maxEnd));

                fpStart.set('onChange', function() {
                    updateGeoLocationForRow(newId);
                });

                if (t1) {
                    fpEnd.set('onChange', function() {
                        updateGeoLocationForRow(newId);
                        handleSplitRowDuty($newRow, '', maxEnd)
                    });
                }

                if (t2) {
                    fpEnd.set('onChange', function() {
                        updateGeoLocationForRow(newId);
                        handleSplitRowDuty($newRow, '', t2)
                    });
                }

                mergeLeaveColumns();
                updateDynamicInputs();
            }, 0);
        }

        function initFormRequestView() {
            const calendarGrid = document.getElementById('calendarGrid');
            if (!calendarGrid) return;

            const now = Date.now();
            if (calendarGrid._lastInit && (now - calendarGrid._lastInit) < 2000) {
                return;
            }
            calendarGrid._lastInit = now;

            if (calendarGrid.dataset.initialized === 'true' && calendarGrid.children.length > 0) {
                return;
            }
            calendarGrid.dataset.initialized = 'true';

            currentDate = new Date();
            window.leaves = leaves;
            groupCounter = 1;
            lastCheckedIndex = null;
            splitRowSeq = {};

            const monthYearDisplay = document.getElementById('monthYearDisplay');
            const prevMonthBtn = document.getElementById('prevMonthBtn');
            const nextMonthBtn = document.getElementById('nextMonthBtn');
            const todayBtn = document.getElementById('todayBtn');
            const scheduledLeavesList = document.getElementById('scheduledLeavesList');

            const leaveModalEl = document.getElementById('leaveModal');
            const leaveModal = leaveModalEl ? new bootstrap.Modal(leaveModalEl, {
                backdrop: 'static',
                keyboard: false
            }) : null;
            const leaveForm = document.getElementById('leaveForm');
            leaveIdInput = document.getElementById('leaveId');
            const leaveTypeInput = document.getElementById('leaveType');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const isTimeRangeCheckbox = document.getElementById('isTimeRange');
            const timeRangeContainer = document.getElementById('timeRangeContainer');
            const fixedAttachmentContainer = document.getElementById('fixedAttachmentContainer');
            const fixedGeoContainer = document.getElementById('fixedGeoContainer');
            const fixednotesContainer = document.getElementById('fixednotesContainer');
            const showLeavestatusContainer = document.getElementById('showLeavestatusContainer');

            const fixedgeoTagLocationInput = document.getElementById('geoTagLocation');

            const registered_loc = document.getElementById('registered_loc');

            const fixedmedicalAssistedPersonContainer = document.getElementById('medicalAssistedPerson');
            const assistedPersonInput = document.getElementById('assistedPerson');

            const vacationPayment = document.getElementById('vacationPayment');
            const statusPaid = document.getElementById('statusPaid');
            const statusUnpaid = document.getElementById('statusUnpaid');

            const timeInputs = {};
            const checkboxes = {};
            const groupInputs = {};
            for (let i = 8; i <= 18; i++) {
                checkboxes[i] = document.getElementById(i);
                groupInputs[i] = document.getElementById('g_' + i);
                timeInputs[i] = {
                    start: document.getElementById('time' + i + '_1'),
                    end: document.getElementById('time' + i + '_2')
                };
            }

            const fixedAttachmentInput = document.getElementById('leaveAttachment');
            const leaveNotesInput = document.getElementById('leaveNotes');
            const saveLeaveBtn = document.getElementById('saveLeaveBtn');
            const deleteLeaveBtn = document.getElementById('deleteLeaveBtn');
            const deleteDateBtn = document.getElementById('deleteDateBtn');
            const modalTitle = document.getElementById('modalTitle');
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            function formatDateToYMD(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function renderCalendar() {
                if (!calendarGrid) return;
                calendarGrid.innerHTML = '';
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();
                if (monthYearDisplay) monthYearDisplay.textContent = `${monthNames[month]} ${year}`;

                const firstDayIndex = new Date(year, month, 1).getDay();
                const totalDays = new Date(year, month + 1, 0).getDate();
                const prevMonthDays = new Date(year, month, 0).getDate();
                const todayStr = formatDateToYMD(new Date());

                let firstCellDate = null;
                let lastCellDate = null;
                let placedCount = 0;

                const placeCell = (dateStr, dayNum, isOtherMonth, isToday) => {
                    const cell = document.createElement('div');
                    cell.className = `calendar-cell ${isOtherMonth ? 'other-month' : ''} ${isToday ? 'today' : ''}`.trim();
                    cell.dataset.date = dateStr;
                    cell.innerHTML = `<div class="day-number">${dayNum}</div>`;
                    calendarGrid.appendChild(cell);

                    if (!firstCellDate) firstCellDate = dateStr;
                    lastCellDate = dateStr;

                    placedCount++;
                    if (placedCount % 7 === 0) {
                        const totalCell = document.createElement('div');
                        totalCell.className = 'calendar-cell week-total-cell';
                        totalCell.dataset.weekIndex = String((placedCount / 7) - 1);
                        totalCell.innerHTML =
                            `<span class="duty-label">Duty <span class="duty-hours">–</span></span>
                            <span class="leave-label">Leave <span class="leave-hours">–</span></span>
                            <span class="deficit-label">Deficit <span class="deficit-hours">–</span></span>`;

                        calendarGrid.appendChild(totalCell);
                    }
                };

                for (let i = firstDayIndex; i > 0; i--) {
                    const dayNum = prevMonthDays - i + 1;
                    const prevDateObj = new Date(year, month - 1, dayNum);
                    placeCell(formatDateToYMD(prevDateObj), dayNum, true, false);
                }

                for (let i = 1; i <= totalDays; i++) {
                    const currDateObj = new Date(year, month, i);
                    const dateStr = formatDateToYMD(currDateObj);
                    placeCell(dateStr, i, false, dateStr === todayStr);
                }

                const totalCellsSoFar = firstDayIndex + totalDays;
                const nextMonthCells = totalCellsSoFar % 7 === 0 ? 0 : 7 - (totalCellsSoFar % 7);
                for (let i = 1; i <= nextMonthCells; i++) {
                    const nextDateObj = new Date(year, month + 1, i);
                    placeCell(formatDateToYMD(nextDateObj), i, true, false);
                }

                if (!firstCellDate) return;

                if (window._cellBatchXhr) {
                    try {
                        window._cellBatchXhr.abort();
                    } catch (e) {}
                    window._cellBatchXhr = null;
                }

                window._cellBatchXhr = $.ajax({
                    url: "<?= site_url('getMonthCells') ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        start: firstCellDate,
                        end: lastCellDate
                    },
                    success: function(res) {
                        if (!res || !res.cells) return;
                        Object.keys(res.cells).forEach(function(date) {
                            const cellEl = calendarGrid.querySelector('.calendar-cell[data-date="' + date + '"]');
                            if (!cellEl) return;
                            cellEl.innerHTML = res.cells[date];
                        });

                        updateWeekTotals();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        if (textStatus === 'abort') return;
                        console.error('getMonthCells failed for ' + firstCellDate + '..' + lastCellDate, textStatus, errorThrown);
                    },
                    complete: function() {
                        window._cellBatchXhr = null;
                    }
                });
            }

            $(calendarGrid).off('click.cell', '.calendar-cell').on('click.cell', '.calendar-cell', function(e) {
                if ($(e.target).closest('.leave-badge').length) return;
                if ($(this).hasClass('week-total-cell')) return;
                openModalForDate(this.dataset.date);
            });

            $(calendarGrid).off('click.badge', '.leave-badge').on('click.badge', '.leave-badge', function(e) {
                e.stopPropagation();
                const cellDate = $(this).closest('.calendar-cell').data('date') || null;
                openModalForEdit(this.id, cellDate);
            });

            function openModalForDate(dateStr) {
                $('input#curDate').val(dateStr);
                $.ajax({
                    url: "<?= site_url('check_date_leave') ?>",
                    type: 'POST',
                    data: {
                        date: dateStr
                    },
                    success: function(data) {
                        let res = JSON.parse(data);
                        console.log(res);
                        if (res.day === 'holiday') {
                            alertInfo(res.holiday.holiday, 'HOLIDAY', '')
                            return;
                        }

                        if (res.day === 'duty') {
                            leaveForm.reset();
                            $('input#startDate').prop('disabled', true);
                            $('input#endDate').prop('disabled', true);

                            leaveTypeInput.disabled = true;

                            leaveIdInput.value = '';
                            startDateInput.value = dateStr;
                            endDateInput.value = dateStr;
                            deleteLeaveBtn.style.display = 'none';
                            deleteDateBtn.style.display = 'none';
                            modalTitle.textContent = 'Schedule Leave';
                            isTimeRangeCheckbox.disabled = true;
                            isTimeRangeCheckbox.checked = true;
                            timeRangeContainer.style.display = '';
                            leaveNotesInput.value = '';
                            leaveTypeInput.value = 'sick';

                            let vacationOption = leaveTypeInput.querySelector('option[value="vacation"]');
                            let paternityOption = leaveTypeInput.querySelector('option[value="paternity"]');
                            let maternityOption = leaveTypeInput.querySelector('option[value="maternity"]');
                            vacationOption.disabled = true;
                            paternityOption.disabled = true;
                            maternityOption.disabled = true;

                            window._matDays = 0;
                            $('#matEnd, #matDays, #matBack, #patEnd, #patDays').val('');
                            ['matEnd', 'matBack', 'patStart', 'patEnd'].forEach(id => {
                                const el = document.getElementById(id);
                                if (el && el._flatpickr) el._flatpickr.set('minDate', null);
                            });

                            vacationPayment.style.display = 'none';
                            statusPaid.disabled = true;
                            statusUnpaid.disabled = true;

                            fixedAttachmentContainer.style.display = 'none';
                            fixedAttachmentViewer.innerHTML = 'none';
                            fixedGeoContainer.style.display = 'none';
                            fixednotesContainer.style.display = 'none';
                            showLeavestatusContainer.style.display = 'none';
                            showLeavestatusContainer.innerHTML = '';

                            $('tr[data-auto-split="1"]').remove();
                            splitRowSeq = {};
                            editedGroups = {};

                            for (let i = 8; i <= 18; i++) {
                                if (checkboxes[i]) {
                                    checkboxes[i].checked = false;
                                    checkboxes[i].disabled = false;
                                }
                                if (timeInputs[i]) {
                                    timeInputs[i].start.disabled = true;
                                    timeInputs[i].end.disabled = true;
                                    timeInputs[i].start.value = i + ':00';
                                    timeInputs[i].end.value = i + ':59';
                                }
                                if (groupInputs[i]) groupInputs[i].value = '';
                            }

                            const $rows = $('table tbody tr, table tr').has('.tchk');
                            $rows.removeClass('duty-row').removeAttr('data-duty-type').removeAttr('data-duty-status');
                            $rows.each(function() {
                                const $cells = $(this).children('td');
                                for (let col = 3; col <= 7; col++) {
                                    $cells.eq(col).empty().show().removeAttr('rowspan');
                                }
                            });

                            const conflictHours = [];
                            res.time.forEach(a => {
                                res.time.forEach(b => {
                                    if (a === b) return;
                                    if (!a.start_time || !b.end_time) return;

                                    const startHour = Number(a.start_time.split(':')[0]);
                                    const endHour = Number(b.end_time.split(':')[0]);

                                    if (startHour === endHour && !conflictHours.includes(String(startHour))) {
                                        conflictHours.push(String(startHour));
                                    }
                                });
                            });

                            const conflictOffice = [];
                            res.time.forEach(f => {
                                if (conflictHours.includes(f.hour) && !conflictOffice.includes(f.hour) && f.row_key != '') {
                                    conflictOffice.push(f.hour);
                                }
                            });

                            groupCounter = 1;
                            time_funcs();
                            let rest = res.time.filter(item => item.hour !== item.row_key);
                            res.groups.forEach(g => {
                                const gId = String(g.id);
                                const gType = String(g.type);
                                const gStatus = String(g.status);
                                let badgeClass = '';
                                if (gStatus == 'pending') {
                                    badgeClass = 'badge bg-warning text-white py-1';
                                } else if (gStatus == 'approved') {
                                    badgeClass = 'badge bg-success py-1';
                                } else {
                                    badgeClass = 'badge bg-danger py-1';
                                }

                                res.time.forEach(t => {
                                    if (gId == t.group_id) {

                                        const rowId = String(t.row_key);
                                        const hourId = String(t.hour);
                                        const time1 = t.start_time;
                                        const time2 = t.end_time;
                                        if (gType == 'regular') {

                                            const hour2 = time2.split(':')[0];
                                            if (parseInt(hour2)) {
                                                conflictHours.forEach(function(c_hour, c_key) {
                                                    if (parseInt(t.hour) == parseInt(c_hour)) {
                                                        const $newRow = $(`
                                                                <tr data-auto-split="1" data-conflict-row="1">
                                                                    <td class="text-center">
                                                                        <input type="checkbox" class="form-check-input tchk" id="Duty_${c_key}" onclick="activateTime(this.id)">
                                                                        <input type="hidden" class="groups_row" id="g_Duty" name="group_Duty_${c_key}[]" value="">
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <input type="text" class="form-control form-control-sm input_chk"
                                                                            id="timeDuty_${c_key}_1" value="${time1}" disabled>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <input type="text" class="form-control form-control-sm input_chk"
                                                                            id="timeDuty_${c_key}_2" value="${conflictHours}:${59}:00" disabled>
                                                                    </td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td class="text-center"></td>
                                                                </tr>
                                                            `);

                                                        const $hourRow = $(`input#${hourId}`).closest('tr');
                                                        if ($hourRow.length) {
                                                            $hourRow.after($newRow);
                                                        } else {
                                                            $('table tbody tr, table tr').has('.tchk').last().after($newRow);
                                                        }

                                                        let startEl = document.getElementById(`timeDuty_${c_key}_1`);
                                                        let endEl = document.getElementById(`timeDuty_${c_key}_2`);

                                                        setTimeout(() => {
                                                            flatpickr(startEl, {
                                                                enableTime: true,
                                                                noCalendar: true,
                                                                dateFormat: "G:i K",
                                                                defaultDate: time1,
                                                                minuteIncrement: 1,
                                                                static: true,
                                                                disableMobile: true,
                                                                appendTo: document.getElementById('leaveModal')
                                                            });
                                                            flatpickr(endEl, {
                                                                enableTime: true,
                                                                noCalendar: true,
                                                                dateFormat: "G:i K",
                                                                defaultDate: `${conflictHours}:${59}:00`,
                                                                minuteIncrement: 1,
                                                                static: true,
                                                                disableMobile: true,
                                                                appendTo: document.getElementById('leaveModal')
                                                            });
                                                        }, 150);

                                                        const $tr = $(`input#Duty_${c_key}`).closest('tr');
                                                        if ($tr.length) {
                                                            $tr.attr('data-duty-type', gType);
                                                            $tr.attr('data-duty-status', gStatus);
                                                            $tr.addClass('duty-row');
                                                        }

                                                        $(`input#Duty_${c_key}`).prop('disabled', true);
                                                        $(`input#timeDuty_${c_key}_1`).prop('disabled', true);
                                                        $(`input#timeDuty_${c_key}_2`).prop('disabled', true);

                                                        const $rowCells = $tr.children('td');
                                                        if ($rowCells.length) {
                                                            let badgeText = '<span class="ms-3">HAS ENTRY</span>';
                                                            if (gStatus == 'pending') {
                                                                $rowCells.eq(3).html(`<span class="duty-badge pending-label">⏳ ${badgeText}</span>`);
                                                            } else if (gStatus == 'approved') {
                                                                $rowCells.eq(3).html(`<span class="duty-badge approved-label"><i class="bi bi-check-circle-fill text-success"></i>${badgeText}</span>`);
                                                            } else {
                                                                $rowCells.eq(3).html(`<span class="duty-badge disapproved-label"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                            }
                                                        }
                                                    }
                                                });


                                                if (!conflictHours.includes(String(hourId))) {
                                                    $('input#time' + hourId + '_1').val(time1);
                                                    if (t.hour >= 8) {
                                                        if ($('input#time' + hourId + '_1')[0]._flatpickr) {
                                                            $('input#time' + hourId + '_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                        }
                                                    } else {
                                                        if ($('input#time8_1')[0]._flatpickr) {
                                                            $('input#time8_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                        }
                                                    }
                                                }

                                                $('input#time' + parseInt(hour2) + '_2').val(time2);
                                                if ($('input#time' + parseInt(hour2) + '_2')[0]._flatpickr) {
                                                    $('input#time' + parseInt(hour2) + '_2')[0]._flatpickr.setDate(time2, false, 'H:i');
                                                }

                                                if (time2) {
                                                    var parts2 = time2.split(':');
                                                    var minutes2 = parseInt(parts2[1], 10);
                                                    if (minutes2 !== 59 && minutes2 !== 0) {

                                                        if (conflictHours.includes(String(t.hour)) && !conflictOffice.includes(String(t.hour))) {
                                                            handleSplitRowDuty($('#time' + t.hour + '_2').closest('tr'), t.start_time);
                                                        }

                                                        if (!conflictHours.includes(String(parseInt(hour2)))) {
                                                            handleSplitRowChange($('#time' + parseInt(hour2) + '_2').closest('tr'));
                                                        }
                                                    }

                                                }

                                                for (let i = parseInt(hourId); i <= parseInt(hour2); i++) {

                                                    const $tr = $('input#' + i).closest('tr');
                                                    if ($tr.length) {
                                                        $tr.attr('data-duty-type', gType);
                                                        $tr.attr('data-duty-status', gStatus);
                                                        $tr.addClass('duty-row');
                                                    }

                                                    $('input#' + i).prop('disabled', true);
                                                    $('input#time' + i + '_1').prop('disabled', true);
                                                    $('input#time' + i + '_2').prop('disabled', true);

                                                    const $rowCells = $tr.children('td');
                                                    if ($rowCells.length) {
                                                        let badgeText = '<span class="ms-3">HAS ENTRY</span>';
                                                        if (gStatus == 'pending') {
                                                            $rowCells.eq(3).html(`<span class="duty-badge pending-label">⏳ ${badgeText}</span>`);
                                                        } else if (gStatus == 'approved') {
                                                            $rowCells.eq(3).html(`<span class="duty-badge approved-label"><i class="bi bi-check-circle-fill text-success"></i>${badgeText}</span>`);
                                                        } else {
                                                            $rowCells.eq(3).html(`<span class="duty-badge disapproved-label"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                        }
                                                    }
                                                }


                                            } else {
                                                let newHourId = parseInt(hourId);
                                                let $tr = $('input#' + newHourId).closest('tr');
                                                if (parseInt(hourId) < 8) {
                                                    newHourId = 8;
                                                    $tr = $('input#8').closest('tr');
                                                }

                                                if ($tr.length) {
                                                    $tr.attr('data-duty-type', gType);
                                                    $tr.attr('data-duty-status', gStatus);
                                                    $tr.addClass('duty-row');
                                                }

                                                $('input#' + newHourId).prop('disabled', true);
                                                $('input#time' + newHourId + '_1').prop('disabled', true);
                                                $('input#time' + newHourId + '_2').prop('disabled', true);


                                                $('input#time' + newHourId + '_1').val(time1);
                                                if (t.hour >= 8) {
                                                    if ($('input#time' + newHourId + '_1')[0]._flatpickr) {
                                                        $('input#time' + newHourId + '_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                    }
                                                } else {
                                                    if ($('input#time8_1')[0]._flatpickr) {
                                                        $('input#time8_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                    }
                                                }

                                                const $rowCells = $tr.children('td');
                                                if ($rowCells.length) {
                                                    let badgeText = '<span class="ms-3">HAS ENTRY</span>';
                                                    if (gStatus == 'pending') {
                                                        $rowCells.eq(3).html(`<span class="duty-badge pending-label">⏳ ${badgeText}</span>`);
                                                    } else if (gStatus == 'approved') {
                                                        $rowCells.eq(3).html(`<span class="duty-badge approved-label"><i class="bi bi-check-circle-fill text-success"></i>${badgeText}</span>`);
                                                    } else {
                                                        $rowCells.eq(3).html(`<span class="duty-badge disapproved-label"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                    }
                                                }
                                            }
                                        } else {
                                            if (rowId !== hourId) {
                                                const $newRow = $(`
                                                    <tr data-auto-split="1" data-conflict-row="1">
                                                        <td class="text-center">
                                                            <input type="checkbox" class="form-check-input tchk" id="${rowId}" onclick="activateTime(this.id)">
                                                            <input type="hidden" class="groups_row" id="g_${rowId}" name="group_${rowId}[]" value="">
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm input_chk"
                                                                id="time${rowId}_1" value="${time1}" disabled>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm input_chk"
                                                                id="time${rowId}_2" value="${time2}" disabled>
                                                        </td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td class="text-center"></td>
                                                    </tr>
                                                `);

                                                const $hourRow = $('input#' + hourId).closest('tr');
                                                if ($hourRow.length) {
                                                    $hourRow.after($newRow);
                                                } else {
                                                    $('table tbody tr, table tr').has('.tchk').last().after($newRow);
                                                }

                                                let startEl = document.getElementById('time' + rowId + '_1');
                                                let endEl = document.getElementById('time' + rowId + '_2');

                                                setTimeout(() => {
                                                    flatpickr(startEl, {
                                                        enableTime: true,
                                                        noCalendar: true,
                                                        dateFormat: "G:i K",
                                                        defaultDate: time1,
                                                        minuteIncrement: 1,
                                                        static: true,
                                                        disableMobile: true,
                                                        appendTo: document.getElementById('leaveModal')
                                                    });
                                                    flatpickr(endEl, {
                                                        enableTime: true,
                                                        noCalendar: true,
                                                        dateFormat: "G:i K",
                                                        defaultDate: time2,
                                                        minuteIncrement: 1,
                                                        static: true,
                                                        disableMobile: true,
                                                        appendTo: document.getElementById('leaveModal')
                                                    });
                                                }, 150);

                                            }

                                            const $tr = $('input#' + rowId).closest('tr');
                                            if ($tr.length) {
                                                $tr.attr('data-duty-type', gType);
                                                $tr.attr('data-duty-status', gStatus);
                                                $tr.addClass('duty-row');
                                            }

                                            $('input#' + rowId).prop('disabled', true);
                                            $('input#time' + rowId + '_1').prop('disabled', true);
                                            $('input#time' + rowId + '_2').prop('disabled', true);

                                            $('input#time' + rowId + '_1').val(time1);
                                            $('input#time' + rowId + '_2').val(time2);

                                            if ($('input#time' + rowId + '_1')[0]._flatpickr) {
                                                $('input#time' + rowId + '_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                            }
                                            if ($('input#time' + rowId + '_2')[0]._flatpickr) {
                                                $('input#time' + rowId + '_2')[0]._flatpickr.setDate(time2, false, 'H:i');
                                            }

                                            if (time2) {
                                                var parts2 = time2.split(':');
                                                var minutes2 = parseInt(parts2[1], 10);
                                                if (minutes2 !== 59 && minutes2 !== 0) {

                                                    if (!conflictHours.includes(String(hourId).padStart(2, '0'))) {
                                                        handleSplitRowChange($('#time' + parseInt(hourId) + '_2').closest('tr'));
                                                    }
                                                }
                                            }

                                            if (conflictHours.length > 0) {
                                                conflictHours.forEach(c => {
                                                    if (c == t.hour) {
                                                        if (t.row_key != t.hour) {
                                                            handleSplitRowDuty($('#time' + t.hour + '_2').closest('tr'), t.start_time);
                                                        }
                                                    }
                                                });
                                            }

                                            const $rowCells = $tr.children('td');
                                            if ($rowCells.length) {
                                                const $badgeCell = $rowCells.eq(3);
                                                let badgeText = '';
                                                if (gType == 'inoffice') {
                                                    badgeText = '<span class="ms-3">IN OFFICE</span>';
                                                    if (gStatus == 'pending') {
                                                        $rowCells.eq(3).html(`<span class="duty-badge log-outoffice">⏳ ${badgeText}</span>`);
                                                    } else if (gStatus == 'approved') {
                                                        $rowCells.eq(3).html(`<span class="duty-badge log-outoffice"><i class="bi bi-check-circle-fill" style="color:#e65100 !important"></i>${badgeText}</span>`);
                                                    } else {
                                                        $rowCells.eq(3).html(`<span class="duty-badge log-outoffice"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                    }
                                                } else {
                                                    badgeText = '<span class="ms-3">OUT OF OFFICE</span>';
                                                    if (gStatus == 'pending') {
                                                        $rowCells.eq(3).html(`<span class="duty-badge log-inoffice">⏳ ${badgeText}</span>`);
                                                    } else if (gStatus == 'approved') {
                                                        $rowCells.eq(3).html(`<span class="duty-badge log-inoffice"><i class="bi bi-check-circle-fill text-success"></i>${badgeText}</span>`);
                                                    } else {
                                                        $rowCells.eq(3).html(`<span class="duty-badge log-inoffice"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            });

                            leaveModal.show();
                            return
                        } else if (res.day === 'duty and leave') {
                            openModalForEdit(res.id, dateStr);
                            return;
                        } else {

                            if (res.day === 'dayoff') {
                                dayoffModal(res.id, dateStr, res.dates);
                                return;
                            }

                            document.querySelectorAll('.input_chk').forEach(el => {
                                el.type = 'text';
                            });
                            if (!leaveForm || !leaveModal) return;

                            if (res.day === 'leave') {
                                openModalForEdit(res.id, dateStr);
                                return;
                            }

                            $('#currentCoordinates').text('Detecting...');
                            leaveTypeInput.disabled = false;
                            $('input#startDate').prop('disabled', false);
                            $('input#endDate').prop('disabled', false);

                            leaveForm.reset();
                            leaveIdInput.value = '';
                            startDateInput.value = dateStr;
                            endDateInput.value = dateStr;

                            registered_loc.textContent = res.employee.registered_loc || '';

                            window._pendingStartMinDate = dateStr;

                            deleteLeaveBtn.style.display = 'none';
                            deleteDateBtn.style.display = 'none';
                            modalTitle.textContent = 'Schedule Leave';
                            isTimeRangeCheckbox.disabled = false;
                            isTimeRangeCheckbox.checked = false;
                            timeRangeContainer.style.display = 'none';
                            leaveNotesInput.value = '';
                            leaveTypeInput.value = 'sick';
                            changeLeave_(leaveTypeInput.value);

                            let gender = res.employee.gender;
                            leaveTypeInput.querySelector('option[value="paternity"]').disabled = (gender === 'Female');
                            leaveTypeInput.querySelector('option[value="maternity"]').disabled = (gender === 'Male');
                            let vacationOption = leaveTypeInput.querySelector('option[value="vacation"]');
                            vacationOption.disabled = false;

                            window._matDays = 0;
                            $('#matEnd, #matDays, #matBack, #patEnd, #patDays').val('');
                            ['matEnd', 'matBack', 'patStart', 'patEnd'].forEach(id => {
                                const el = document.getElementById(id);
                                if (el && el._flatpickr) el._flatpickr.set('minDate', null);
                            });

                            buildAssistedSelectHtmlFixed('')

                            vacationPayment.style.display = 'none';
                            statusPaid.disabled = true;
                            statusUnpaid.disabled = true;

                            fixedAttachmentContainer.style.display = '';
                            fixedAttachmentInput.style.display = '';
                            fixedAttachmentViewer.innerHTML = '';
                            fixedGeoContainer.style.display = '';
                            fixednotesContainer.style.display = '';
                            showLeavestatusContainer.style.display = 'none';
                            showLeavestatusContainer.innerHTML = '';

                            $('tr[data-auto-split="1"]').remove();
                            splitRowSeq = {};
                            editedGroups = {};

                            for (let i = 8; i <= 18; i++) {
                                if (checkboxes[i]) {
                                    checkboxes[i].checked = false;
                                    checkboxes[i].disabled = false;
                                }
                                if (timeInputs[i]) {
                                    timeInputs[i].start.disabled = true;
                                    timeInputs[i].end.disabled = true;
                                    timeInputs[i].start.value = i + ':00';
                                    timeInputs[i].end.value = i + ':59';
                                }
                                if (groupInputs[i]) groupInputs[i].value = '';
                            }

                            const $rows = $('table tbody tr, table tr').has('.tchk');
                            $rows.removeClass('duty-row').removeAttr('data-duty-type').removeAttr('data-duty-status');
                            $rows.each(function() {
                                const $cells = $(this).children('td');
                                for (let col = 3; col <= 7; col++) {
                                    $cells.eq(col).empty().show().removeAttr('rowspan');
                                }
                            });

                            groupCounter = 1;
                            setTimeout(() => {
                                time_funcs();
                            }, 100);
                            leaveModal.show();
                        }
                    },
                    beforeSend: function() {

                    }
                })
            }

            function openModalForEdit(id, dateStr) {
                if (id == 'duty') {
                    openModalForDate(dateStr)
                    return;
                }
                $('input#curDate').val(dateStr);
                $.ajax({
                    url: "<?= site_url('edit_leave_form') ?>",
                    type: 'POST',
                    data: {
                        leaveId: id,
                        date: dateStr
                    },
                    success: function(data) {
                        let row = JSON.parse(data);
                        const leave = row.leave;
                        const statusLower = String(leave.status || '').toLowerCase();
                        const isApproved = statusLower === 'approved' || statusLower === 'disapproved';
                        const details = row.details || [];
                        const timetable = row.timeTable;
                        const leaveattachments = row.attachments || [];
                        const splitrows = row.splitrows || [];
                        const maternity = row.maternity || [];
                        const paternity = row.paternity || [];
                        const assistance = row.assistance || [];
                        const duty = row.duty || [];
                        const duty_groups = row.duty_groups || [];
                        const duty_time = row.duty_time || [];
                        console.log(row);

                        if (isApproved && leave.isFixed === '1') {
                            viewFixedRequest(id, dateStr);
                            return;
                        }

                        document.querySelectorAll('.input_chk').forEach(el => {
                            el.type = 'text';
                        });

                        if (!leaveModal) return;
                        leaveForm.reset();
                        resetAllTimeRows();
                        editedGroups = {};

                        let gender = row.employee.gender;
                        leaveTypeInput.querySelector('option[value="paternity"]').disabled = (gender === 'Female');
                        leaveTypeInput.querySelector('option[value="maternity"]').disabled = (gender === 'Male');

                        const baseAttUrl = "<?= base_url('uploads/leaves/') ?>";

                        leaveTypeInput.disabled = false;

                        const attachmentsByRow = {};
                        leaveattachments.forEach(a => {
                            const tr = String(a.time_row || '');
                            if (!tr) return;
                            if (!attachmentsByRow[tr]) attachmentsByRow[tr] = [];
                            attachmentsByRow[tr].push(a);
                        });

                        const assistanceByRow = {};
                        (Array.isArray(assistance) ? assistance : []).forEach(a => {
                            const tr = String(a.time_row || '');
                            if (!tr) return;
                            assistanceByRow[tr] = a.assisted_person || '';
                        });

                        const mapServerAttachment = (a) => ({
                            tklraId: a.tklraId,
                            name: a.attachment,
                            type: '',
                            data: baseAttUrl + a.attachment,
                            groupId: a.groupId || '',
                            time_row: String(a.time_row || '')
                        });

                        const collectAttachments = (rowIds) => {


                            const out = [];
                            (rowIds || []).forEach(r => {
                                (attachmentsByRow[String(r)] || []).forEach(a => out.push(mapServerAttachment(a)));
                            });
                            return out;
                        };

                        const md = {};

                        details.forEach((detail) => {
                            const rowId = String(detail.time_row);
                            const groupId = detail.groupId || '';

                            const groupRows = groupId ?
                                details.filter(d => (d.groupId || '') === groupId).map(d => String(d.time_row)) : [rowId];

                            const row_id = [rowId]
                            let assistedPerson =
                                assistanceByRow[rowId] ||
                                (groupRows.map(r => assistanceByRow[r]).find(v => v)) ||
                                detail.assistedPerson ||
                                'parent';

                            md[rowId] = {
                                leaveId: detail.leaveId || '',
                                groupId: groupId,
                                leaveType: detail.code || '',
                                remarks: detail.remarks || '',
                                attachments: collectAttachments(groupRows),
                                isLocked: Boolean(groupId),

                                gl: detail.location,
                                g_status: detail.status,
                                assistedPerson: assistedPerson
                            };
                        });

                        currentEditingLeaveData = {
                            leave,
                            details,
                            timetable,
                            attachments: leaveattachments,
                            md
                        };

                        leaveIdInput.value = leave.leaveId;
                        leaveTypeInput.value = leave.code || 'sick';
                        changeLeave_(leaveTypeInput.value);
                        startDateInput.value = leave.startDate;
                        endDateInput.value = leave.endDate;
                        window._pendingStartMinDate = leave.startDate;
                        isTimeRangeCheckbox.checked = leave.isFixed || false;
                        timeRangeContainer.style.display = leave.isFixed ? 'block' : 'none';
                        leaveNotesInput.value = leave.reason || '';
                        deleteLeaveBtn.style.display = 'block';
                        modalTitle.textContent = 'Edit Leave Schedule';
                        registered_loc.textContent = row.employee.registered_loc || '';

                        showLeavestatusContainer.innerHTML = buildLeaveDetailsHtml(leave, row.approvedBy);
                        showLeavestatusContainer.style.display = '';

                        fixedAttachmentInput.style.display = '';

                        if (leave.isFixed == '0') {
                            deleteDateBtn.style.display = 'none';
                            $('input#startDate').prop('disabled', true);
                            $('input#endDate').prop('disabled', true);
                        } else {
                            if (leave.total_days > 1) {
                                deleteDateBtn.style.display = '';
                            } else {
                                deleteDateBtn.style.display = 'none';
                            }
                            $(deleteDateBtn).html(`Delete [ ${dateStr} ]`);
                            $('input#startDate').prop('disabled', false);
                            $('input#endDate').prop('disabled', false);

                            $('#currentCoordinates').text('Detecting...');
                        }

                        if (leave.code === 'maternity') {
                            deleteDateBtn.style.display = 'none';
                            $('select#matDelivery').val(maternity.delivery_type);
                            $('input#matStart').val(maternity.startDate);
                            $('input#matEnd').val(maternity.endDate);
                            $('input#matDays').val(maternity.total_days);
                            $('input#matBack').val(maternity.backtowork);
                            $('textarea#matDetails').val(maternity.details);
                            let m = getMatpatDays('maternity');
                            window._matDays = m[maternity.delivery_type] || 0;
                        }

                        if (leave.code === 'paternity') {
                            deleteDateBtn.style.display = 'none';
                            $('input#patDate').val(paternity.delivery_date);
                            $('select#patApplication').val(paternity.application);
                            $('input#patStart').val(paternity.startDate);
                            $('input#patEnd').val(paternity.endDate);
                            $('input#patDays').val(paternity.total_days);
                            $('textarea#patDetails').val(paternity.details);
                            window._patDays = PATERNITY_DAYS;
                        }

                        if (leave.code === 'vacation') {
                            isTimeRangeCheckbox.disabled = true;
                            isTimeRangeCheckbox.checked = false;
                            timeRangeContainer.style.display = 'none';

                            vacationPayment.style.display = 'flex';
                            statusPaid.disabled = false;
                            statusUnpaid.disabled = false;

                            if (leave.payment === 'paid') {
                                statusPaid.checked = true;
                            } else {
                                statusUnpaid.checked = true;
                            }

                            fixedAttachmentContainer.style.display = '';
                            fixedAttachmentViewer.innerHTML = buildFixedAttachmentViewer(leaveattachments);
                            fixedGeoContainer.style.display = '';
                            fixednotesContainer.style.display = '';
                            fixedgeoTagLocationInput.value = leave.geo_location;
                            fixedmedicalAssistedPersonContainer.style.display = 'none';
                        } else if (leave.code !== 'vacation' && leave.isFixed == '1') {
                            isTimeRangeCheckbox.disabled = false;
                            isTimeRangeCheckbox.checked = false;
                            timeRangeContainer.style.display = 'none';

                            vacationPayment.style.display = 'none';
                            statusPaid.disabled = true;
                            statusUnpaid.disabled = true;

                            fixedAttachmentContainer.style.display = '';
                            fixedAttachmentViewer.innerHTML = buildFixedAttachmentViewer(leaveattachments);
                            fixedGeoContainer.style.display = '';
                            fixednotesContainer.style.display = '';
                            fixedgeoTagLocationInput.value = leave.geo_location;

                            if (leave.code === 'medical') {
                                fixedmedicalAssistedPersonContainer.style.display = '';
                                buildAssistedSelectHtmlFixed('')
                                const assistRecord = Array.isArray(assistance) ? assistance[0] : assistance;
                                assistedPersonInput.value =
                                    (assistRecord && assistRecord.assisted_person) ||
                                    (details[0] && details[0].assistedPerson) ||
                                    'parent';
                            }


                        } else {
                            isTimeRangeCheckbox.disabled = false;

                            vacationPayment.style.display = 'none';
                            statusPaid.disabled = true;
                            statusUnpaid.disabled = true;

                            fixedAttachmentContainer.style.display = 'none';
                            fixedGeoContainer.style.display = 'none';
                            fixednotesContainer.style.display = 'none';
                            fixedmedicalAssistedPersonContainer.style.display = 'none';

                            showLeavestatusContainer.innerHTML = '';
                            showLeavestatusContainer.style.display = 'none';
                        }

                        const lHours = [];
                        if (Array.isArray(splitrows) && splitrows.length) {
                            splitrows.forEach(function(sr) {
                                if (!lHours.includes(sr.parent)) {
                                    lHours.push(sr.parent);
                                }
                            });
                        }

                        const scHours = [];
                        const conflictHours = [];
                        if (leave.isFixed == '0') {

                            for (let i = 8; i <= 18; i++) {
                                if (checkboxes[i]) {

                                    let isChecked = false;
                                    details.forEach((detail) => {
                                        if (detail.time_row == i) {
                                            isChecked = true;
                                        }
                                    });

                                    checkboxes[i].checked = isChecked;
                                    if (timeInputs[i]) {

                                        if (timeInputs[i].start.type !== 'text') timeInputs[i].start.type = 'text';
                                        if (timeInputs[i].end.type !== 'text') timeInputs[i].end.type = 'text';
                                        let t1 = timetable['time_' + i + '_1'];
                                        let t2 = timetable['time_' + i + '_2'];
                                        timeInputs[i].start.value = t1 || (i + ':00');
                                        timeInputs[i].end.value = t2 || (i + ':59');
                                        timeInputs[i].start.disabled = !isChecked;
                                        timeInputs[i].end.disabled = !isChecked;
                                    }
                                }
                            }

                            if (Array.isArray(splitrows) && splitrows.length) {

                                const sortedSplits = [...splitrows].sort((a, b) => {
                                    const pa = parseInt(a.parent, 10) || 0;
                                    const pb = parseInt(b.parent, 10) || 0;
                                    if (pa !== pb) return pa - pb;

                                    const sa = parseInt(String(a.time_row).replace(/^\d+s/, ''), 10) || 0;
                                    const sb = parseInt(String(b.time_row).replace(/^\d+s/, ''), 10) || 0;
                                    return sa - sb;
                                });

                                sortedSplits.forEach(sr => {
                                    const rowId = String(sr.time_row);
                                    const root = String(sr.parent);
                                    const splitParent = String(sr.splitParent || sr.parent);
                                    const t1 = String(sr.t1 || '').substring(0, 5);
                                    const t2 = String(sr.t2 || '').substring(0, 5);
                                    const isChecked = String(sr.ischk) === '1';

                                    const $newRow = $(`
                                        <tr data-auto-split="1"
                                            data-parent-row="${root}"
                                            data-split-parent="${splitParent}"
                                            data-tksr-id="${sr.tksrId || ''}">
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input tchk" id="${rowId}" onclick="activateTime(this.id)">
                                                <input type="hidden" class="groups_row" id="g_${rowId}" name="group_${rowId}[]" value="">
                                                <input type="hidden" class="parent_row" id="p_${rowId}" value="${root}">
                                            </td>
                                            <td class="text-center">
                                                <input type="text" class="form-control form-control-sm input_chk"
                                                    id="time${rowId}_1" value="${t1}" disabled>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" class="form-control form-control-sm input_chk"
                                                    id="time${rowId}_2" value="${t2}" disabled>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-center"></td>
                                        </tr>
                                    `);

                                    $newRow.find('.tchk').prop('checked', isChecked);

                                    let $parentRow = $();
                                    $('table tbody tr, table tr').has('.tchk').each(function() {
                                        if (String($(this).find('.tchk').attr('id')) === splitParent) {
                                            $parentRow = $(this);
                                            return false;
                                        }
                                    });
                                    if ($parentRow.length) {
                                        $parentRow.after($newRow);
                                    } else {
                                        $('table tbody tr, table tr').has('.tchk').last().after($newRow);
                                    }

                                    const m = rowId.match(/^(\d+)s(\d+)$/);
                                    if (m) {
                                        const rootKey = m[1];
                                        const seq = parseInt(m[2], 10);
                                        if (!splitRowSeq[rootKey] || splitRowSeq[rootKey] < seq) {
                                            splitRowSeq[rootKey] = seq;
                                        }
                                    }
                                });
                            }

                            setTimeout(() => {
                                const $rows = $('table tbody tr, table tr').has('.tchk');
                                let groupStructure = [];
                                details.forEach((detail) => {
                                    if (detail.status != 'pending') {
                                        $('#deleteLeaveBtn').hide();
                                        leaveTypeInput.disabled = true;
                                        isTimeRangeCheckbox.disabled = true;
                                    }

                                    if (!detail.groupId) return;
                                    let group = groupStructure.find(g => g.groupId === detail.groupId);
                                    if (!group) {
                                        group = {
                                            leaveId: detail.leaveId,
                                            groupId: detail.groupId,
                                            rows: [],
                                            created: detail.created,
                                            gloc: detail.location,
                                            g_status: detail.status
                                        };
                                        groupStructure.push(group);
                                    }

                                    if (!group.rows.includes(String(detail.time_row))) {
                                        group.rows.push(String(detail.time_row));
                                    }
                                });

                                groupStructure = groupStructure.filter(g => g.rows.length > 1);

                                if (groupStructure && groupStructure.length > 0) {
                                    groupStructure.forEach(group => {
                                        const groupRows = [];
                                        group.rows.forEach(rowId => {
                                            $rows.each(function() {
                                                const $chk = $(this).find('.tchk');
                                                if ($chk.attr('id') === rowId) groupRows.push($(this));
                                            });
                                        });


                                        if (groupRows.length > 1) {

                                            const firstRowCells = groupRows[0].children('td');
                                            const rowSpan = groupRows.length;

                                            const groupId = group.groupId || `group_${Date.now()}_${groupCounter++}`;
                                            groupRows.forEach(row => {
                                                row.find('input.groups_row').val(groupId);
                                                row.find('.tchk').prop('disabled', true);
                                                row.find('.input_chk').prop('disabled', true);
                                            });

                                            for (let colIdx = 3; colIdx <= 7; colIdx++) {
                                                firstRowCells.eq(colIdx).attr('rowspan', rowSpan);
                                                for (let i = 1; i < groupRows.length; i++) {
                                                    groupRows[i].children('td').eq(colIdx).hide();
                                                }
                                            }

                                            const $cells = groupRows[0].children('td');
                                            const mergedData = md || {};
                                            let firstRowData = null;
                                            for (const rowId of group.rows) {
                                                const d = mergedData[rowId];
                                                if (d && (d.leaveType || d.remarks || getRowAttachments(d).length)) {
                                                    firstRowData = d;
                                                    break;
                                                }
                                            }
                                            if (!firstRowData) firstRowData = mergedData[group.rows[0]] || {};

                                            $cells.eq(3).html(buildLeaveTypeCellHtml(
                                                (firstRowData && firstRowData.leaveType) || '',
                                                true,
                                                (firstRowData && firstRowData.assistedPerson) || ''
                                            ));

                                            $cells.eq(4).html(buildAttachmentCellHtml(firstRowData, true));
                                            $cells.eq(5).html(`<textarea class="form-control form-control-sm remarks-input" rows="${rowSpan}" disabled placeholder="Enter remarks...">${firstRowData ? firstRowData.remarks : ''}</textarea>`);
                                            $cells.eq(6).html(buildGeoLocationHtml(group.rows, group.created, group.gloc));

                                            if (group.g_status == 'pending') {
                                                $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-secondary action-btn" onclick="unlockGroup(this)"><i class="bi bi-unlock"></i></button>`);
                                            } else if (group.g_status == 'approved') {
                                                $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-success approval-details-btn" onclick="buildApprovalDetailsHtml('${group.leaveId}', '${group.groupId}')">Details</button>`);
                                            } else {
                                                $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-danger approval-details-btn" onclick="buildApprovalDetailsHtml('${group.leaveId}', '${group.groupId}')">Details</button>`);
                                            }
                                        }
                                    });
                                }

                                $rows.each(function() {
                                    const $row = $(this);
                                    const $chk = $row.find('.tchk');
                                    const rowId = $chk.attr('id');
                                    const groupId = $row.find('input.groups_row').val();

                                    const mergedData = md || {};
                                    const rowData = mergedData[rowId] || {};
                                    const shouldBeLocked = rowData.isLocked === true;

                                    if (!groupId && $chk.is(':checked')) {
                                        let created = '';
                                        let gloc = '';
                                        details.forEach((detail) => {
                                            if (detail.time_row == rowId) {
                                                created = detail.created;
                                                gloc = detail.location;
                                            }
                                        });

                                        if (shouldBeLocked) {

                                            let singleGroupId = `group_${Date.now()}_${groupCounter++}`;
                                            if (mergedData[rowId].groupId) {
                                                singleGroupId = mergedData[rowId].groupId;
                                            }
                                            const $cells = $row.children('td');

                                            $row.find('input.groups_row').val(singleGroupId);
                                            $row.find('.tchk').prop('disabled', true);
                                            $row.find('.input_chk').prop('disabled', true);

                                            $cells.eq(3).html(buildLeaveTypeCellHtml(
                                                rowData.leaveType || '',
                                                true,
                                                rowData.assistedPerson || ''
                                            ));

                                            $cells.eq(4).html(buildAttachmentCellHtml(rowData, true));
                                            $cells.eq(5).html(`<textarea class="test form-control form-control-sm remarks-input" rows="2" disabled placeholder="Enter remarks...">${rowData.remarks || ''}</textarea>`);
                                            $cells.eq(6).html(buildGeoLocationHtml([rowId], created, gloc));
                                            if (rowData.g_status == 'pending') {
                                                $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-secondary action-btn" onclick="unlockGroup(this)"><i class="bi bi-unlock"></i></button>`);
                                            } else if (rowData.g_status == 'approved') {
                                                $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-success approval-details-btn" onclick="buildApprovalDetailsHtml('${rowData.leaveId}', '${rowData.groupId}')">Details</button>`);
                                            } else {
                                                $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-danger approval-details-btn" onclick="buildApprovalDetailsHtml('${rowData.leaveId}', '${rowData.groupId}')">Details</button>`);
                                            }

                                        } else {
                                            const $cells = $row.children('td');
                                            $cells.eq(3).html(buildLeaveTypeCellHtml(
                                                rowData.leaveType || '',
                                                false,
                                                rowData.assistedPerson || ''
                                            ));
                                            $cells.eq(4).html(buildAttachmentCellHtml(rowData, false, gloc));
                                            $cells.eq(5).html(`<textarea class="form-control form-control-sm remarks-input" rows="2" placeholder="Enter remarks...">${rowData.remarks || ''}</textarea>`);
                                            $cells.eq(6).html(buildGeoLocationHtml([rowId], created, gloc));
                                            $cells.eq(7).html(`<button type="button" class="btn btn-sm btn-primary action-btn" onclick="lockGroup(this)">Lock</button>`);
                                        }
                                    }
                                });

                                $rows.each(function() {
                                    const $row = $(this);
                                    const groupId = $row.find('input.groups_row').val();
                                    if (groupId) {
                                        $row.find('.tchk').prop('disabled', true);
                                        $row.find('.input_chk').prop('disabled', true);
                                        $row.find('select.leave-select').prop('disabled', true);
                                        $row.find('input.attachment-input').prop('disabled', true);
                                        $row.find('textarea.remarks-input').prop('disabled', true);
                                    }
                                });

                                time_funcs();
                            }, 100);

                            setTimeout(() => {
                                if (duty) {
                                    let rest = row.duty_time.filter(item => item.hour !== item.row_key);
                                    row.duty_time.forEach(a => {
                                        row.duty_time.forEach(b => {
                                            if (a === b) return;
                                            if (!a.start_time || !b.end_time) return;

                                            const startHour = Number(a.start_time.split(':')[0]);
                                            const endHour = Number(b.end_time.split(':')[0]);

                                            if (startHour === endHour && !conflictHours.includes(String(startHour))) {
                                                conflictHours.push(String(startHour));
                                                if (Array.isArray(splitrows) && splitrows.length) {
                                                    splitrows.forEach(function(sr) {
                                                        if (!conflictHours.includes(sr.parent)) {
                                                            conflictHours.push(String(sr.parent));
                                                        }
                                                        if (!scHours.includes(sr.time_row)) {
                                                            scHours.push(sr.time_row);
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    });

                                    console.log(conflictHours);
                                    console.log(scHours);

                                    leaveTypeInput.disabled = true;
                                    isTimeRangeCheckbox.disabled = true;
                                    row.duty_groups.forEach(g => {
                                        const gId = String(g.id);
                                        const gType = String(g.type);
                                        const gStatus = String(g.status);
                                        let badgeClass = '';
                                        if (gStatus == 'pending') {
                                            badgeClass = 'badge bg-warning text-white py-1';
                                        } else if (gStatus == 'approved') {
                                            badgeClass = 'badge bg-success py-1';
                                        } else {
                                            badgeClass = 'badge bg-danger py-1';
                                        }

                                        row.duty_time.forEach(t => {
                                            if (gId == t.group_id) {
                                                const rowId = String(t.row_key);
                                                const hourId = String(t.hour);

                                                const time1 = t.start_time;
                                                const time2 = t.end_time;

                                                if (gType == 'regular') {
                                                    const hour2 = time2.split(':')[0];
                                                    if (parseInt(hour2)) {
                                                        conflictHours.forEach(function(c_hour, c_key) {
                                                            if (parseInt(t.hour) == parseInt(c_hour)) {
                                                                const $newRow = $(`
                                                                    <tr data-auto-split="1" data-conflict-row="1">
                                                                        <td class="text-center">
                                                                            <input type="checkbox" class="form-check-input tchk" id="Duty_${c_key}" onclick="activateTime(this.id)">
                                                                            <input type="hidden" class="groups_row" id="g_Duty" name="group_Duty_${c_key}[]" value="">
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <input type="text" class="form-control form-control-sm input_chk"
                                                                                id="timeDuty_${c_key}_1" value="${time1}" disabled>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <input type="text" class="form-control form-control-sm input_chk"
                                                                                id="timeDuty_${c_key}_2" value="${conflictHours}:${59}:00" disabled>
                                                                        </td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td class="text-center"></td>
                                                                    </tr>
                                                                `);

                                                                const $hourRowS = $('input#' + hourId + 's1').closest('tr');
                                                                const $hourRow = $('input#' + hourId).closest('tr');
                                                                if ($hourRowS.length) {
                                                                    $hourRowS.after($newRow);
                                                                } else if ($hourRow.length) {
                                                                    $hourRow.after($newRow);
                                                                } else {
                                                                    $('table tbody tr, table tr').has('.tchk').last().after($newRow);
                                                                }

                                                                let startEl = document.getElementById(`timeDuty_${c_key}_1`);
                                                                let endEl = document.getElementById(`timeDuty_${c_key}_2`);

                                                                setTimeout(() => {
                                                                    flatpickr(startEl, {
                                                                        enableTime: true,
                                                                        noCalendar: true,
                                                                        dateFormat: "G:i K",
                                                                        defaultDate: time1,
                                                                        minuteIncrement: 1,
                                                                        static: true,
                                                                        disableMobile: true,
                                                                        appendTo: document.getElementById('leaveModal')
                                                                    });
                                                                    flatpickr(endEl, {
                                                                        enableTime: true,
                                                                        noCalendar: true,
                                                                        dateFormat: "G:i K",
                                                                        defaultDate: `${conflictHours}:${59}:00`,
                                                                        minuteIncrement: 1,
                                                                        static: true,
                                                                        disableMobile: true,
                                                                        appendTo: document.getElementById('leaveModal')
                                                                    });
                                                                }, 150);

                                                                const $tr = $(`input#Duty_${c_key}`).closest('tr');
                                                                if ($tr.length) {
                                                                    $tr.attr('data-duty-type', gType);
                                                                    $tr.attr('data-duty-status', gStatus);
                                                                    $tr.addClass('duty-row');
                                                                }

                                                                $(`input#Duty_${c_key}`).prop('disabled', true);
                                                                $(`input#timeDuty_${c_key}_1`).prop('disabled', true);
                                                                $(`input#timeDuty_${c_key}_2`).prop('disabled', true);

                                                                const $rowCells = $tr.children('td');
                                                                if ($rowCells.length) {
                                                                    let badgeText = '<span class="ms-3">HAS ENTRY</span>';
                                                                    if (gStatus == 'pending') {
                                                                        $rowCells.eq(3).html(`<span class="duty-badge pending-label">⏳ ${badgeText}</span>`);
                                                                    } else if (gStatus == 'approved') {
                                                                        $rowCells.eq(3).html(`<span class="duty-badge approved-label"><i class="bi bi-check-circle-fill text-success"></i>${badgeText}</span>`);
                                                                    } else {
                                                                        $rowCells.eq(3).html(`<span class="duty-badge disapproved-label"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                                    }
                                                                }
                                                            }
                                                        });

                                                        if (!conflictHours.includes(String(hourId))) {
                                                            $('input#time' + hourId + '_1').val(time1);
                                                            if (t.hour >= 8) {
                                                                if ($('input#time' + hourId + '_1')[0]._flatpickr) {
                                                                    $('input#time' + hourId + '_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                                }
                                                            } else {
                                                                if ($('input#time8_1')[0]._flatpickr) {
                                                                    $('input#time8_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                                }
                                                            }
                                                        }

                                                        $('input#time' + parseInt(hour2) + '_2').val(time2);
                                                        if ($('input#time' + parseInt(hour2) + '_2')[0]._flatpickr) {
                                                            $('input#time' + parseInt(hour2) + '_2')[0]._flatpickr.setDate(time2, false, 'H:i');
                                                        }

                                                        if (time2) {
                                                            var parts2 = time2.split(':');
                                                            var minutes2 = parseInt(parts2[1], 10);
                                                            if (minutes2 !== 59 && minutes2 !== 0) {

                                                                if (conflictHours.includes(String(t.hour)) && !lHours.includes(String(t.hour))) {
                                                                    handleSplitRowDuty($('#time' + t.hour + '_2').closest('tr'), t.start_time);
                                                                }

                                                                if (!conflictHours.includes(String(parseInt(hour2))) && !lHours.includes(String(parseInt(hour2)))) {
                                                                    handleSplitRowChange($('#time' + parseInt(hour2) + '_2').closest('tr'));
                                                                }
                                                            }

                                                        }

                                                        for (let i = parseInt(hourId); i <= parseInt(hour2); i++) {

                                                            const $tr = $('input#' + i).closest('tr');
                                                            if ($tr.length) {
                                                                $tr.attr('data-duty-type', gType);
                                                                $tr.attr('data-duty-status', gStatus);
                                                                $tr.addClass('duty-row');
                                                            }

                                                            $('input#' + i).prop('disabled', true);
                                                            $('input#time' + i + '_1').prop('disabled', true);
                                                            $('input#time' + i + '_2').prop('disabled', true);

                                                            const $rowCells = $tr.children('td');
                                                            if ($rowCells.length) {
                                                                let badgeText = '<span class="ms-3">HAS ENTRY</span>';
                                                                if (gStatus == 'pending') {
                                                                    $rowCells.eq(3).html(`<span class="duty-badge pending-label">⏳ ${badgeText}</span>`);
                                                                } else if (gStatus == 'approved') {
                                                                    $rowCells.eq(3).html(`<span class="duty-badge approved-label"><i class="bi bi-check-circle-fill text-success"></i>${badgeText}</span>`);
                                                                } else {
                                                                    $rowCells.eq(3).html(`<span class="duty-badge disapproved-label"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                                }
                                                            }
                                                        }

                                                    } else {
                                                        let newHourId = parseInt(hourId);
                                                        let $tr = $('input#' + newHourId).closest('tr');
                                                        if (parseInt(hourId) < 8) {
                                                            newHourId = 8;
                                                            $tr = $('input#8').closest('tr');
                                                        }

                                                        if ($tr.length) {
                                                            $tr.attr('data-duty-type', gType);
                                                            $tr.attr('data-duty-status', gStatus);
                                                            $tr.addClass('duty-row');
                                                        }

                                                        $('input#' + newHourId).prop('disabled', true);
                                                        $('input#time' + newHourId + '_1').prop('disabled', true);
                                                        $('input#time' + newHourId + '_2').prop('disabled', true);


                                                        $('input#time' + newHourId + '_1').val(time1);
                                                        if (t.hour >= 8) {
                                                            if ($('input#time' + newHourId + '_1')[0]._flatpickr) {
                                                                $('input#time' + newHourId + '_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                            }
                                                        } else {
                                                            if ($('input#time8_1')[0]._flatpickr) {
                                                                $('input#time8_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                            }
                                                        }

                                                        const $rowCells = $tr.children('td');
                                                        if ($rowCells.length) {
                                                            let badgeText = '<span class="ms-3">HAS ENTRY</span>';
                                                            if (gStatus == 'pending') {
                                                                $rowCells.eq(3).html(`<span class="duty-badge pending-label">⏳ ${badgeText}</span>`);
                                                            } else if (gStatus == 'approved') {
                                                                $rowCells.eq(3).html(`<span class="duty-badge approved-label"><i class="bi bi-check-circle-fill text-success"></i>${badgeText}</span>`);
                                                            } else {
                                                                $rowCells.eq(3).html(`<span class="duty-badge disapproved-label"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    if (rowId !== hourId) {
                                                        const $newRow = $(`
                                                            <tr data-auto-split="1" data-conflict-row="1">
                                                                <td class="text-center">
                                                                    <input type="checkbox" class="form-check-input tchk" id="${rowId}" onclick="activateTime(this.id)">
                                                                    <input type="hidden" class="groups_row" id="g_${rowId}" name="group_${rowId}[]" value="">
                                                                </td>
                                                                <td class="text-center">
                                                                    <input type="text" class="form-control form-control-sm input_chk"
                                                                        id="time${rowId}_1" value="${time1}" disabled>
                                                                </td>
                                                                <td class="text-center">
                                                                    <input type="text" class="form-control form-control-sm input_chk"
                                                                        id="time${rowId}_2" value="${time2}" disabled>
                                                                </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="text-center"></td>
                                                            </tr>
                                                        `);
                                                        const $hourRowS = $('input#' + hourId + 's1').closest('tr');
                                                        const $hourRow = $('input#' + hourId).closest('tr');
                                                        if ($hourRowS.length) {
                                                            $hourRowS.after($newRow);
                                                        } else if ($hourRow.length) {
                                                            $hourRow.after($newRow);
                                                        } else {
                                                            $('table tbody tr, table tr').has('.tchk').last().after($newRow);
                                                        }

                                                        let startEl = document.getElementById('time' + rowId + '_1');
                                                        let endEl = document.getElementById('time' + rowId + '_2');

                                                        setTimeout(() => {
                                                            flatpickr(startEl, {
                                                                enableTime: true,
                                                                noCalendar: true,
                                                                dateFormat: "G:i K",
                                                                defaultDate: time1,
                                                                minuteIncrement: 1,
                                                                static: true,
                                                                disableMobile: true,
                                                                appendTo: document.getElementById('leaveModal')
                                                            });
                                                            flatpickr(endEl, {
                                                                enableTime: true,
                                                                noCalendar: true,
                                                                dateFormat: "G:i K",
                                                                defaultDate: time2,
                                                                minuteIncrement: 1,
                                                                static: true,
                                                                disableMobile: true,
                                                                appendTo: document.getElementById('leaveModal')
                                                            });
                                                        }, 150);

                                                    }

                                                    const $tr = $('input#' + rowId).closest('tr');
                                                    if ($tr.length) {
                                                        $tr.attr('data-duty-type', gType);
                                                        $tr.attr('data-duty-status', gStatus);
                                                        $tr.addClass('duty-row');
                                                    }

                                                    $('input#' + rowId).prop('disabled', true);
                                                    $('input#time' + rowId + '_1').prop('disabled', true);
                                                    $('input#time' + rowId + '_2').prop('disabled', true);

                                                    $('input#time' + rowId + '_1').val(time1);
                                                    $('input#time' + rowId + '_2').val(time2);

                                                    if ($('input#time' + rowId + '_1')[0]._flatpickr) {
                                                        $('input#time' + rowId + '_1')[0]._flatpickr.setDate(time1, false, 'H:i');
                                                    }
                                                    if ($('input#time' + rowId + '_2')[0]._flatpickr) {
                                                        $('input#time' + rowId + '_2')[0]._flatpickr.setDate(time2, false, 'H:i');
                                                    }




                                                    if (time2) {
                                                        var parts2 = time2.split(':');
                                                        var minutes2 = parseInt(parts2[1], 10);
                                                        if (minutes2 !== 59 && minutes2 !== 0) {
                                                            // if (!conflictHours.includes(String(hourId))) {
                                                            //     handleSplitRowChange($('#time' + parseInt(hourId) + '_2').closest('tr'));
                                                            // }
                                                            if (!conflictHours.includes(String(parseInt(hourId))) && !lHours.includes(String(parseInt(hourId)))) {
                                                                handleSplitRowChange($('#time' + parseInt(hourId) + '_2').closest('tr'));
                                                            }

                                                        }
                                                    }

                                                    if (conflictHours.length > 0) {
                                                        conflictHours.forEach(c => {
                                                            if (c == t.hour) {
                                                                if (t.row_key != t.hour) {
                                                                    // if (!lHours.includes(t.hour)) {
                                                                    //     handleSplitRowDuty($('#time' + t.hour + '_2').closest('tr'), t.start_time);
                                                                    // }

                                                                    if (conflictHours.includes(String(t.hour)) && !lHours.includes(String(t.hour))) {
                                                                        handleSplitRowDuty($('#time' + t.hour + '_2').closest('tr'), t.start_time);
                                                                    }
                                                                }
                                                            }
                                                        });
                                                    }

                                                    const $rowCells = $tr.children('td');
                                                    if ($rowCells.length) {
                                                        const $badgeCell = $rowCells.eq(3);
                                                        let badgeText = '';
                                                        if (gType == 'inoffice') {
                                                            badgeText = '<span class="ms-3">IN OFFICE</span>';
                                                            if (gStatus == 'pending') {
                                                                $rowCells.eq(3).html(`<span class="duty-badge log-outoffice">⏳ ${badgeText}</span>`);
                                                            } else if (gStatus == 'approved') {
                                                                $rowCells.eq(3).html(`<span class="duty-badge log-outoffice"><i class="bi bi-check-circle-fill" style="color:#e65100 !important"></i>${badgeText}</span>`);
                                                            } else {
                                                                $rowCells.eq(3).html(`<span class="duty-badge log-outoffice"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                            }
                                                        } else {
                                                            badgeText = '<span class="ms-3">OUT OF OFFICE</span>';
                                                            if (gStatus == 'pending') {
                                                                $rowCells.eq(3).html(`<span class="duty-badge log-inoffice">⏳ ${badgeText}</span>`);
                                                            } else if (gStatus == 'approved') {
                                                                $rowCells.eq(3).html(`<span class="duty-badge log-inoffice"><i class="bi bi-check-circle-fill text-success"></i>${badgeText}</span>`);
                                                            } else {
                                                                $rowCells.eq(3).html(`<span class="duty-badge log-inoffice"><i class="bi bi-x-circle-fill text-danger"></i>${badgeText}</span>`);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    });
                                }
                            }, 250);
                        }

                        setTimeout(() => {
                            time_funcs();
                        }, 150);

                        setTimeout(() => {

                            if (!Array.isArray(splitrows) || !splitrows.length) return;

                            const cfg = (min, max, def) => ({
                                enableTime: true,
                                noCalendar: true,
                                dateFormat: "G:i K",
                                minTime: min,
                                maxTime: max,
                                defaultDate: def,
                                minuteIncrement: 1,
                                static: true,
                                disableMobile: true,
                                appendTo: document.getElementById('leaveModal')
                            });

                            splitrows.forEach(sr => {
                                const rowId = String(sr.time_row);
                                const t1 = String(sr.t1 || '').substring(0, 5);
                                const t2 = String(sr.t2 || '').substring(0, 5);

                                const startEl = document.getElementById('time' + rowId + '_1');
                                const endEl = document.getElementById('time' + rowId + '_2');

                                if (startEl) {
                                    if (startEl._flatpickr) startEl._flatpickr.destroy();

                                    const fpStart = flatpickr(startEl, cfg(t1 || '00:00', t2 || '23:59', t1 || '00:00'));

                                    fpStart.set('onChange', function() {
                                        updateGeoLocationForRow(rowId);
                                    });

                                    startEl.disabled = true;
                                }

                                if (endEl) {

                                    if (endEl._flatpickr) endEl._flatpickr.destroy();

                                    const fpEnd = flatpickr(endEl, cfg(t1 || '00:00', t2 || '23:59', t2 || '00:00'));

                                    fpEnd.set('onChange', function() {
                                        updateGeoLocationForRow(rowId);
                                        if (scHours.includes(rowId)) {
                                            handleSplitRowDuty($(endEl).closest('tr'), '', t2);
                                        } else {
                                            handleSplitRowChange($(endEl).closest('tr'));
                                        }
                                    });

                                    endEl.disabled = true;
                                }
                            });
                        }, 250);

                        $('#leaveModal').off('shown.bs.modal.approved');

                        if (isApproved) {
                            if (leave.isFixed == '1') {
                                deleteDateBtn.style.display = 'none';
                                $('#leaveModal .modal-title').html('View Request');
                                $('#deleteLeaveBtn, #saveLeaveBtn').hide();
                                $('#leaveModal').on('shown.bs.modal.approved', function() {
                                    $('#leaveForm').find('input, select, textarea, button').prop('disabled', true);
                                    $('#leaveForm').find('.approval-details-btn,.att-view-btn').prop('disabled', false);
                                    fixedAttachmentInput.style.display = 'none';
                                    fixedGeoContainer.style.display = 'none';
                                });
                            } else {
                                $('#deleteLeaveBtn').hide();
                                $('#leaveForm').find('.approval-details-btn,.att-view-btn').prop('disabled', false);
                            }
                        }

                        if (isApproved) {
                            if (leave.isFixed == '1') {
                                deleteDateBtn.style.display = 'none';
                                $('#leaveModal .modal-title').html('View Request');
                                $('#deleteLeaveBtn, #saveLeaveBtn').hide();
                                $('#leaveForm').find('input, select, textarea, button').each(function() {
                                    $(this).data('approvedPrevDisabled', this.disabled === true);
                                    this.disabled = true;
                                });
                                fixedAttachmentInput.style.display = 'none';
                                fixedGeoContainer.style.display = 'none';
                                $('#leaveForm').find('.approval-details-btn,.att-view-btn').prop('disabled', false);
                            } else {
                                $('#deleteLeaveBtn').hide();
                                $('#leaveForm').find('.approval-details-btn,.att-view-btn').prop('disabled', false);
                            }
                        }

                        leaveModal.show();
                    }
                });
            }

            function dayoffModal(id, date, dates) {
                $('input#old_do').prop('disabled', true);
                $('select#new_do').prop('disabled', false).html(dates);
                $('input#dayoffId').val('');
                $('textarea#dayoffRemarks').val('');
                let olddo = flatpickr("#old_do");
                olddo.setDate(date, true);

                $('select#new_do').val('');
                $('button#saveDayoff').show();
                $('button#cancelDayoff').hide();
                if (id) {

                    $('input#dayoffId').val(id.tkcdoId);
                    $('textarea#dayoffRemarks').val(id.remarks);
                    $('button#cancelDayoff').show();
                    $('select#new_do').val(id.new_dayoff);
                    if (id.status == 'approved') {
                        $('button#saveDayoff').hide();
                        $('button#deleteDayoff').hide();
                        olddo.setDate(id.old_dayoff, true);
                        $('select#new_do').prop('disabled', true);
                    }
                }

                $('#dayoffModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $('#dayoffModal').modal('show');
            }

            function viewFixedRequest(leaveId, date) {
                $('#viewFixedRequest').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $('#viewFixedRequest').modal('show');

                $.ajax({
                    url: "<?= site_url('viewFixedRequest') ?>",
                    type: 'POST',
                    data: {
                        leaveId: leaveId,
                        date: date
                    },
                    success: function(data) {
                        let res = JSON.parse(data);
                        $('#viewFixedRequest .modal-body').html(res.form);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX Error:", textStatus, errorThrown);
                        alert(errorThrown || 'Network error');
                    }
                });
            }

            window.viewAttachments = function(link, type) {
                if (type === 'pdf') {
                    // window.open(link, '_blank');
                    $('#view_attachments').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#view_attachments').modal('show');
                    $("#view_attachments .modal-body").html('<iframe src="' + link + '" style="width:100%; height:75vh; border:0;"></iframe>');
                } else {
                    $('#view_attachments').modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                    $('#view_attachments').modal('show');
                    $("#view_attachments .modal-body").html('<img src="' + link + '" class="d-block w-100" />');
                }
            }

            window.saveDayoff = function() {

                let old_do = document.getElementById('old_do').value;
                let new_do = document.getElementById('new_do').value;
                let remarks = document.getElementById('dayoffRemarks').value;
                if (!new_do) {
                    $("select#new_do").css("border-color", "#dd4b39");
                    $("select#new_do").on("focus", function() {
                        $(this).css("border-color", "");
                    });

                    alertRequired('Please fillup the required fields.', 'dayoffModal');
                    return;
                }

                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to change day off?',
                    icon: 'question',
                    iconColor: '#ffc107',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    target: document.getElementById('dayoffModal'),
                    focusConfirm: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Please wait!',
                            text: 'Saving change day off request...',
                            allowOutsideClick: false,
                            target: document.getElementById('dayoffModal'),
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('save_dayoff') ?>",
                                type: 'POST',
                                data: {
                                    old_do: old_do,
                                    new_do: new_do,
                                    remarks: remarks
                                },
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data);
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Change day off request has been saved.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonColor: '#3085d6',
                                                confirmButtonText: 'Ok',
                                                allowOutsideClick: false,
                                                target: document.getElementById('dayoffModal'),
                                                focusConfirm: false,
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    renderCalendar();
                                                    $('#dayoffModal').modal('hide');
                                                }
                                            });
                                        } else if (response.message === 'updated') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Change dayoff request has been updated.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonColor: '#3085d6',
                                                confirmButtonText: 'Ok',
                                                allowOutsideClick: false,
                                                target: document.getElementById('dayoffModal'),
                                                focusConfirm: false,
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    renderCalendar();
                                                    $('#dayoffModal').modal('hide');
                                                }
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message, 'dayoffModal')
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        errorAlert(response.message, 'dayoffModal')
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    console.error("AJAX Error:", textStatus, errorThrown);
                                    errorAlert(errorThrown, 'dayoffModal')
                                }
                            });
                        }, 2000);
                    }
                })
            }

            window.cancelDayoff = function() {
                let dayoffId = document.getElementById('dayoffId').value;
                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to cancel this change day off request?',
                    icon: 'question',
                    iconColor: '#ffc107',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    target: document.getElementById('dayoffModal'),
                    focusConfirm: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Please wait!',
                            text: 'Canceling change day off request...',
                            allowOutsideClick: false,
                            target: document.getElementById('dayoffModal'),
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('cancel_dayoff') ?>",
                                type: 'POST',
                                data: {
                                    dayoffId: dayoffId,
                                },
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data);
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Change dayoff request has been cancelled.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonColor: '#3085d6',
                                                confirmButtonText: 'Ok',
                                                allowOutsideClick: false,
                                                target: document.getElementById('dayoffModal'),
                                                focusConfirm: false,
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    renderCalendar();
                                                    $('#dayoffModal').modal('hide');
                                                }
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message, 'dayoffModal')
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        errorAlert(response.message, 'dayoffModal')
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    console.error("AJAX Error:", textStatus, errorThrown);
                                    errorAlert(errorThrown, 'dayoffModal')
                                }
                            });
                        }, 2000);
                    }
                })
            }

            function resetAllTimeRows() {
                $('tr[data-auto-split="1"]').remove();

                splitRowSeq = {};
                editedGroups = {};

                for (let i = 8; i <= 18; i++) {
                    if (checkboxes[i]) {
                        checkboxes[i].checked = false;
                        checkboxes[i].disabled = false;
                    }
                    if (timeInputs[i]) {
                        timeInputs[i].start.disabled = true;
                        timeInputs[i].end.disabled = true;
                        timeInputs[i].start.value = i + ':00';
                        timeInputs[i].end.value = i + ':59';
                    }
                    if (groupInputs[i]) groupInputs[i].value = '';
                }
                const $rows = $('table tbody tr, table tr').has('.tchk');
                $rows.removeClass('duty-row').removeAttr('data-duty-type').removeAttr('data-duty-status');
                $rows.each(function() {
                    const $cells = $(this).children('td');
                    for (let col = 3; col <= 7; col++) {
                        $cells.eq(col).empty().show().removeAttr('rowspan');
                    }
                });
                groupCounter = 1;
            }

            function validateTimeRangeSelection() {
                if (!isTimeRangeCheckbox.checked) return true;

                let hasCheckedRow = false;
                let hasLockedRow = false;
                let hasUnlockedCheckedRow = false;

                const $rows = $('table tbody tr, table tr').has('.tchk');

                $rows.each(function() {
                    const $row = $(this);
                    const $chk = $row.find('.tchk');
                    const isChecked = $chk.is(':checked');
                    const isLocked = Boolean($row.find('input.groups_row').val());

                    if (isChecked) {
                        hasCheckedRow = true;
                        if (isLocked) {
                            hasLockedRow = true;
                        } else {
                            hasUnlockedCheckedRow = true;
                        }
                    }
                });

                if (!hasCheckedRow) {
                    alertRequired('Please select at least one time slot by checking a checkbox.', 'leaveModal')
                    return false;
                }

                if (hasUnlockedCheckedRow) {
                    alertRequired('Please click the "Lock" button before saving.', 'leaveModal')
                    return false;
                }

                if (!hasLockedRow) {
                    alertRequired('Please lock at least one checked time slot by clicking the "Lock" button.', 'leaveModal')
                    return false;
                }

                return true;
            }

            async function saveLeave() {
                const isMatPat = (leaveTypeInput.value === 'maternity' || leaveTypeInput.value === 'paternity');

                const geoPayload = (() => {
                    if (window.currentUserLocation && !window.currentUserLocation.error) {
                        return {
                            location: window.currentUserLocation.address || '',
                            latitude: window.currentUserLocation.lat || '',
                            longitude: window.currentUserLocation.lng || '',
                            accuracy: window.currentUserLocation.accuracy || 0
                        };
                    }
                    return {
                        location: 'Tagbilaran, City, Bohol, Philippines',
                        latitude: '',
                        longitude: '',
                        accuracy: 0
                    };
                })();

                if (isMatPat) {
                    let matPatData = null;

                    if (leaveTypeInput.value === 'maternity') {
                        const delivery = $('#matDelivery').val();
                        const mStart = $('#matStart').val();
                        const mEnd = $('#matEnd').val();
                        const mBack = $('#matBack').val();

                        if (!delivery) {
                            alert('Please select a delivery type.');
                            return;
                        }
                        if (!mStart || !mEnd) {
                            alert('Please select maternity start and end dates.');
                            return;
                        }
                        if (mStart > mEnd) {
                            alert('Maternity start date cannot be after end date.');
                            return;
                        }
                        if (!mBack) {
                            alert('Please select the date back to work.');
                            return;
                        }

                        matPatData = {
                            deliveryType: delivery,
                            dateStart: mStart,
                            dateEnd: mEnd,
                            days: $('#matDays').val() || '',
                            dateBackToWork: mBack,
                            details: $('#matDetails').val() || ''
                        };
                    } else {
                        const patDate = $('#patDate').val();
                        const patApp = $('#patApplication').val();
                        const pStart = $('#patStart').val();
                        const pEnd = $('#patEnd').val();

                        if (!patDate) {
                            alert('Please select the delivery on/before date.');
                            return;
                        }
                        if (!patApp) {
                            alert('Please select application.');
                            return;
                        }
                        if (!pStart || !pEnd) {
                            alert('Please select paternity start and end dates.');
                            return;
                        }
                        if (pStart > pEnd) {
                            alert('Paternity start date cannot be after end date.');
                            return;
                        }

                        matPatData = {
                            deliveryOnOrBefore: patDate,
                            application: patApp,
                            dateStart: pStart,
                            dateEnd: pEnd,
                            days: $('#patDays').val() || '',
                            details: $('#patDetails').val() || ''
                        };
                    }

                    const leaveObj = {
                        id: leaveIdInput.value,
                        type: leaveTypeInput.value,
                        fixedgeoTagLocation: fixedgeoTagLocationInput.value,
                        location: geoPayload.location,
                        latitude: geoPayload.latitude,
                        longitude: geoPayload.longitude,
                        accuracy: geoPayload.accuracy,
                        matPatData: matPatData
                    };

                    return saveLeaveAjax(leaveObj, {});
                }

                if (!validateTimeRangeSelection()) return;
                if (!startDateInput.value || !endDateInput.value) {
                    errorAlert('Please select valid start and end dates.', 'leaveModal');
                    return;
                }

                if (startDateInput.value > endDateInput.value) {
                    $('input#endDate').css('border-color', '#dd4b39').on('focus', function() {
                        $(this).css('border-color', '');
                    });
                    errorAlert('Start date cannot be after end date.', 'leaveModal');
                    return;
                }

                if (!isTimeRangeCheckbox.checked) {
                    const hasNewAttachment = fixedAttachmentInput.files &&
                        fixedAttachmentInput.files.length > 0;
                    const hasExistingAttachment =
                        $('#fixedAttachmentViewer .attachment-item').length > 0;

                    const hasAttachment = hasNewAttachment || hasExistingAttachment;
                    const hasNotes = leaveNotesInput.value.trim() !== '';

                    const fields = [{
                            valid: hasAttachment,
                            selector: 'input#leaveAttachment'
                        },
                        {
                            valid: hasNotes,
                            selector: 'textarea#leaveNotes'
                        }
                    ];

                    const invalid = fields.filter(f => !f.valid);

                    if (invalid.length) {
                        invalid.forEach(({
                            selector
                        }) => {
                            $(selector).css('border-color', '#dd4b39').on('focus', function() {
                                $(this).css('border-color', '');
                            });
                        });

                        alertRequired('Please fillup the required fields.', 'leaveModal');
                        return;
                    }
                }

                const payment = $('input[name=payment]:checked').val();
                const id = leaveIdInput.value;

                const leaveObj = {
                    id,
                    type: leaveTypeInput.value,
                    startDate: startDateInput.value,
                    endDate: endDateInput.value,
                    isTimeRange: isTimeRangeCheckbox.checked,
                    notes: leaveNotesInput.value.trim(),
                    // fixedgeoTagLocation: fixedgeoTagLocationInput.value,
                    fixedgeoTagLocation: geoPayload.location,
                    location: geoPayload.location,
                    latitude: geoPayload.latitude,
                    longitude: geoPayload.longitude,
                    accuracy: geoPayload.accuracy
                };
                if (leaveTypeInput.value === 'vacation') {
                    leaveObj.vpayment = payment;
                }

                if (leaveTypeInput.value === 'medical') {
                    leaveObj.assistedPerson = assistedPersonInput.value;
                }


                if (isTimeRangeCheckbox.checked) {
                    for (let i = 8; i <= 18; i++) {
                        const isChecked = checkboxes[i] ? checkboxes[i].checked : false;
                        leaveObj['ischk' + i] = isChecked;
                        leaveObj['t_' + i + '_1'] = timeInputs[i] ? timeInputs[i].start.value : '';
                        leaveObj['t_' + i + '_2'] = timeInputs[i] ? timeInputs[i].end.value : '';
                    }
                } else {
                    for (let i = 8; i <= 18; i++) {
                        leaveObj['ischk' + i] = false;
                        leaveObj['t_' + i + '_1'] = '';
                        leaveObj['t_' + i + '_2'] = '';
                    }
                }

                const $rows = $('table tbody tr, table tr').has('.tchk');
                const splitRowsPayload = [];

                $rows.each(function() {
                    const $row = $(this);
                    const $chk = $row.find('.tchk');
                    const rowId = String($chk.attr('id') || '');

                    if (/^\d+$/.test(rowId)) return;
                    if ($row.attr('data-auto-split') !== '1') return;

                    if ($row.attr('data-duty-type')) return;

                    const parentRoot = $row.attr('data-parent-row') || getSplitRoot(rowId);
                    const splitParent = $row.attr('data-split-parent') || '';
                    const t1 = $('#time' + rowId + '_1').val() || '';
                    const t2 = $('#time' + rowId + '_2').val() || '';
                    const isChecked = $chk.is(':checked');

                    if (isTimeRangeCheckbox.checked) {
                        leaveObj['ischk' + rowId] = isChecked;
                        leaveObj['t_' + rowId + '_1'] = t1;
                        leaveObj['t_' + rowId + '_2'] = t2;
                        leaveObj['parent_' + rowId] = parentRoot;
                    }

                    splitRowsPayload.push({
                        id: rowId,
                        parent: parentRoot,
                        splitParent: splitParent,
                        ischk: isChecked,
                        t1: t1,
                        t2: t2
                    });
                });
                leaveObj.splitRows = splitRowsPayload;

                const mergedData = {};
                const mergedFiles = {};

                for (let ri = 0; ri < $rows.length; ri++) {
                    const $row = $rows.eq(ri);
                    const $chk = $row.find('.tchk');
                    const rowId = String($chk.attr('id') || '');

                    const $leaveTypeCell = $row.children('td').eq(3);
                    if (!$leaveTypeCell.is(':visible')) continue;

                    const $leaveSelect = $row.find('.leave-select');
                    const $assistSelect = $row.find('.assisted-select');
                    const $remarksInput = $row.find('.remarks-input');
                    const $attachmentInput = $row.find('.attachment-input');
                    const $geoLocRow = $row.find('.geo-location-row');
                    const $timeRange = $row.find('.time_range');

                    const hasVisibleInputs = $leaveSelect.length > 0 || $remarksInput.length > 0;
                    if (!hasVisibleInputs) continue;

                    const isLocked = Boolean($row.find('input.groups_row').val());
                    const leaveType = $leaveSelect.length ? $leaveSelect.val() : '';
                    const remarks = $remarksInput.length ? $remarksInput.val() : '';

                    const assistedPerson = $assistSelect.length ? $assistSelect.val() : '';

                    const existingRow = leaveIdInput.value ?
                        (leaves.find(l => l.id === leaveIdInput.value)?.mergedData?.[rowId] || {}) : {};
                    const existingNames = getRowAttachments(existingRow).map(a => a.name);

                    const files = $attachmentInput.length ? $attachmentInput[0].files : null;
                    if (files && files.length) {
                        for (let fi = 0; fi < files.length; fi++) {
                            const f = files[fi];
                            // if (f.size > 2 * 1024 * 1024) {
                            //     alert(`"${f.name}" is too large. Max 2MB per file.`);
                            //     return;
                            // }
                            if (!mergedFiles[rowId]) mergedFiles[rowId] = [];
                            mergedFiles[rowId].push(f);
                        }
                    }

                    mergedData[rowId] = {
                        leaveType: leaveType,
                        remarks: remarks,
                        assistedPerson: (leaveType === 'medical') ? assistedPerson : '',
                        // geoLocRow: $geoLocRow.text(),
                        geoLocRow: geoPayload.location,
                        timeRange: $timeRange.text(),
                        existingAttachments: existingNames,
                        isLocked: isLocked,
                        groupId: $row.find('input.groups_row').val() || '',
                    };
                }
                leaveObj.mergedData = mergedData;

                const groupStructure = [];
                const processedGroups = new Set();
                $rows.each(function() {
                    const $row = $(this);
                    const groupId = $row.find('input.groups_row').val();
                    if (groupId && !processedGroups.has(groupId)) {
                        processedGroups.add(groupId);
                        const groupRows = [];
                        $rows.each(function() {
                            if ($(this).find('input.groups_row').val() === groupId) {
                                groupRows.push($(this).find('.tchk').attr('id'));
                            }
                        });
                        if (groupRows.length > 1) {
                            groupStructure.push({
                                groupId: groupId,
                                rows: groupRows
                            });
                        }
                    }
                });
                leaveObj.groupStructure = groupStructure;
                leaveObj.editedGroups = editedGroups;
                return saveLeaveAjax(leaveObj, mergedFiles);
            }

            function saveLeaveAjax(leaveObj, mergedFiles) {
                const formData = new FormData();
                formData.append('payload', JSON.stringify(leaveObj));

                const fixedFiles = fixedAttachmentInput.files;
                if (fixedFiles && fixedFiles.length) {
                    for (let i = 0; i < fixedFiles.length; i++) {
                        formData.append('fixedattachment[]', fixedFiles[i]);
                    }
                }

                Object.keys(mergedFiles || {}).forEach(rowId => {
                    for (let i = 0; i < mergedFiles[rowId].length; i++) {
                        formData.append(`merged[${rowId}][]`, mergedFiles[rowId][i]);
                    }
                });

                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to save leave request?',
                    icon: 'question',
                    iconColor: '#ffc107',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    target: document.getElementById('leaveModal'),
                    focusConfirm: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Please wait!',
                            text: 'Saving leave request...',
                            allowOutsideClick: false,
                            target: document.getElementById('leaveModal'),
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('save_leave') ?>",
                                type: 'POST',
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data);
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Leave request has been saved.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonColor: '#3085d6',
                                                confirmButtonText: 'Ok',
                                                allowOutsideClick: false,
                                                target: document.getElementById('leaveModal'),
                                                focusConfirm: false,
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    renderCalendar();
                                                    if (leaveModal) leaveModal.hide();
                                                }
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message, 'leaveModal');
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        errorAlert(response.message, 'leaveModal');
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    errorAlert(errorThrown, 'leaveModal')
                                },
                                complete: function() {}
                            });
                        }, 2000);
                    }
                })
            }

            function deleteLeave() {
                const id = leaveIdInput.value;
                let curdate = $('input#curDate').val();

                if (!id) {
                    errorAlert('Leave ID is blank!', 'leaveModal');
                    return;
                }

                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to delete this leave request?',
                    icon: 'question',
                    iconColor: '#ffc107',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    target: document.getElementById('leaveModal'),
                    focusConfirm: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Please wait!',
                            text: 'Deleting leave request...',
                            allowOutsideClick: false,
                            target: document.getElementById('leaveModal'),
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('delete_leave') ?>",
                                type: 'POST',
                                data: {
                                    leaveId: id,
                                    date: curdate
                                },
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data);
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Leave request has been deleted.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonColor: '#3085d6',
                                                confirmButtonText: 'Ok',
                                                allowOutsideClick: false,
                                                target: document.getElementById('leaveModal'),
                                                focusConfirm: false,
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    renderCalendar();
                                                    if (leaveModal) leaveModal.hide();
                                                }
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message), 'leaveModal';
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        errorAlert(data, 'leaveModal')
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    errorAlert(errorThrown, 'leaveModal')
                                }
                            });
                        }, 2000);
                    }
                })
            }

            function deleteDateLeave() {
                const id = leaveIdInput.value;
                let curdate = $('input#curDate').val();

                if (!id) {
                    errorAlert('Leave ID is blank!', 'leaveModal');
                    return;
                }

                if (curdate === '') {
                    errorAlert('Date is blank!', 'leaveModal');
                    return;
                }

                Swal.fire({
                    title: 'Confirm!',
                    text: 'Are you sure you want to delete [' + curdate + '] of this leave request?',
                    icon: 'question',
                    iconColor: '#ffc107',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    target: document.getElementById('leaveModal'),
                    focusConfirm: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Please wait!',
                            text: 'Deleting [' + curdate + '] of this leave request...',
                            allowOutsideClick: false,
                            target: document.getElementById('leaveModal'),
                            focusConfirm: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        setTimeout(() => {
                            $.ajax({
                                url: "<?= site_url('delete_date_leave') ?>",
                                type: 'POST',
                                data: {
                                    leaveId: id,
                                    date: curdate
                                },
                                success: function(data) {
                                    try {
                                        let response = JSON.parse(data);
                                        if (response.message === 'success') {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: '[' + curdate + '] of this leave request has been deleted.',
                                                icon: 'success',
                                                iconColor: '#15ca20',
                                                confirmButtonColor: '#3085d6',
                                                confirmButtonText: 'Ok',
                                                allowOutsideClick: false,
                                                target: document.getElementById('leaveModal'),
                                                focusConfirm: false,
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    renderCalendar();
                                                    if (leaveModal) leaveModal.hide();
                                                }
                                            });
                                        } else {
                                            Swal.close();
                                            errorAlert(response.message, 'leaveModal');
                                        }
                                    } catch (e) {
                                        Swal.close();
                                        errorAlert(data, 'leaveModal')
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Swal.close();
                                    errorAlert(errorThrown, 'leaveModal')
                                }
                            });
                        }, 2000);
                    }
                })
            }

            if (prevMonthBtn) prevMonthBtn.onclick = () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            };
            if (nextMonthBtn) nextMonthBtn.onclick = () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            };
            if (todayBtn) todayBtn.onclick = () => {
                currentDate = new Date();
                renderCalendar();
            };
            if (isTimeRangeCheckbox) {
                isTimeRangeCheckbox.onchange = (e) => {
                    if (timeRangeContainer) timeRangeContainer.style.display = e.target.checked ? 'flex' : 'none';
                    if (fixedAttachmentContainer) fixedAttachmentContainer.style.display = e.target.checked ? 'none' : '';
                    if (fixedGeoContainer) fixedGeoContainer.style.display = e.target.checked ? 'none' : '';
                    if (fixednotesContainer) fixednotesContainer.style.display = e.target.checked ? 'none' : '';
                    if (fixedmedicalAssistedPersonContainer) fixedmedicalAssistedPersonContainer.style.display = e.target.checked ? 'none' : (leaveType.value === 'medical' ? '' : 'none');
                };
            }

            if (saveLeaveBtn) saveLeaveBtn.onclick = saveLeave;
            if (deleteLeaveBtn) deleteLeaveBtn.onclick = deleteLeave;
            if (deleteDateBtn) deleteDateBtn.onclick = deleteDateLeave;

            $('#dayoffModal').off('shown.bs.modal.cal').on('shown.bs.modal.cal', function() {

                flatpickr("#old_do", {
                    dateFormat: "Y-m-d",
                    clickOpens: false,
                    allowInput: false,
                    disableMobile: true,
                    appendTo: document.getElementById('dayoffModal')
                });
            });

            $('#leaveModal').off('shown.bs.modal.cal').on('shown.bs.modal.cal', function() {
                flatpickr(".date1", {
                    dateFormat: "Y-m-d",
                    disableMobile: true,
                    appendTo: document.getElementById('leaveModal')
                });
                flatpickr(".date2", {
                    dateFormat: "Y-m-d",
                    disableMobile: true,
                    appendTo: document.getElementById('leaveModal')
                });

                const sd = document.getElementById('startDate');
                const ed = document.getElementById('endDate');

                if (sd && sd._flatpickr) {
                    if (window._pendingStartMinDate) {
                        sd._flatpickr.set('minDate', window._pendingStartMinDate);
                        sd._flatpickr.setDate(window._pendingStartMinDate, false);
                        if (ed && ed._flatpickr) {
                            ed._flatpickr.set('minDate', window._pendingStartMinDate);
                            if (!ed.value || ed.value < window._pendingStartMinDate) {
                                ed._flatpickr.setDate(window._pendingStartMinDate, false);
                            }
                        }

                        window._pendingStartMinDate = null;
                    } else {
                        sd._flatpickr.set('minDate', null);
                        if (ed && ed._flatpickr) ed._flatpickr.set('minDate', null);
                    }
                }

                const patDateEl = document.getElementById('patDate');
                const patStartEl = document.getElementById('patStart');
                const patEndEl = document.getElementById('patEnd');
                const todayStr = formatDateToYMD(new Date());

                if (patDateEl && patDateEl._flatpickr) {
                    const existing = patDateEl.value;
                    const minPatDate = (existing && existing < todayStr) ? existing : todayStr;

                    patDateEl._flatpickr.set('onChange', function() {
                        computePaternityDates();
                    });
                }

                if (patStartEl && patStartEl._flatpickr) {
                    const patDateVal = patDateEl ? patDateEl.value : '';
                    if (patDateVal) patStartEl._flatpickr.set('minDate', patDateVal);

                    patStartEl._flatpickr.set('onChange', function() {
                        computePaternityDates();
                    });
                }

                if (patEndEl && patEndEl._flatpickr) {
                    const patStartVal = patStartEl ? patStartEl.value : '';
                    if (patStartVal) patEndEl._flatpickr.set('minDate', patStartVal);
                }

                getUserLocation(function(location) {
                    if (location && !location.error) {
                        window.currentUserLocation = location;
                    } else {
                        window.currentUserLocation = {
                            address: 'Tagbilaran, City, Bohol, Philippines',
                            coords: 'Unavailable',
                            accuracy: 0,
                            error: location ? location.error : 'Unknown'
                        };
                    }
                    refreshGeoLocationDisplay();
                });
            });

            $('#leaveModal').off('hidden.bs.modal.ced').on('hidden.bs.modal.ced', function() {
                currentEditingLeaveData = null;
                editedGroups = {};
                window._pendingStartMinDate = null;

                $('#leaveModal').off('shown.bs.modal.approved');
                $('#leaveForm').find('input, select, textarea, button').each(function() {
                    const prev = $(this).data('approvedPrevDisabled');
                    if (prev === undefined) return;
                    this.disabled = prev === true;
                    $(this).removeData('approvedPrevDisabled');
                });

                $('#saveLeaveBtn, #deleteLeaveBtn').show();
            });

            renderCalendar();
        }

        $(document).off('click.attView', '.att-view-btn').on('click.att-view', '.att-view-btn', function(e) {
            e.stopPropagation();

            if ($(this).attr('onclick')) return;

            const url = $(this).attr('data-att-url');
            const name = $(this).attr('data-att-name');
            const type = $(this).attr('data-att-type');

            let getType = name.split('.');
            viewAttachments(url, getType[1]);
        });

        $(document).off('click.attDel', '.att-del-btn').on('click.att-del', '.att-del-btn', function(e) {
            e.stopPropagation();
            if ($(this).attr('onclick')) return;
            if (!confirm('Remove this attachment?')) return;

            const $item = $(this).closest('.attachment-item');
            const $cell = $(this).closest('td');
            const tklraId = $item.attr('data-att-id');
            const fileName = $item.find('.att-name').text();

            const removeFromDom = () => {
                $item.remove();
                if (currentEditingLeaveData && currentEditingLeaveData.md && tklraId) {
                    Object.values(currentEditingLeaveData.md).forEach(md => {
                        if (md.attachments) {
                            md.attachments = md.attachments.filter(a => String(a.tklraId) !== String(tklraId));
                        }
                    });
                }

                if ($cell.find('.attachment-item').length === 0) {
                    $cell.find('.attachment-preview').prepend('<div class="attachment-empty">No files</div>');
                }
                $cell.find('.attachment-item').each(function(i) {
                    $(this).attr('data-att-idx', i);
                });
            };

            if (!tklraId) {
                removeFromDom();
                return;
            }

            $.ajax({
                url: "<?= site_url('delete_leave_attachment') ?>",
                type: 'POST',
                data: {
                    tklraId: tklraId,
                    fileName: fileName
                },
                dataType: 'json',
                success: function(res) {
                    if (res && res.message === 'success') {
                        removeFromDom();
                        alert('Attachment has been deleted.');
                    } else {
                        alert((res && res.message) || 'Delete failed.');
                    }
                },
                error: function() {
                    alert('Network error');
                }
            });
        });

        $(document).off('change.geoUpdate', 'table input[type="time"]').on('change.geoUpdate', 'table input[type="time"]', function() {
            const id = $(this).attr('id');
            if (!id) return;
            const match = id.match(/time(\d+)_(\d+)/);
            if (!match) return;
            updateGeoLocationForRow(match[1]);
        });

        $(document).off('input.geoUpdate', 'table input[type="time"]').on('input.geoUpdate', 'table input[type="time"]', function() {
            const id = $(this).attr('id');
            if (!id) return;
            const match = id.match(/time(\d+)_(\d+)/);
            if (!match) return;
            updateGeoLocationForRow(match[1]);
        });

        $(document).off('change.tpCascade', '[class*="tp"][class$="_2"]').on('change.tpCascade', '[class*="tp"][class$="_2"]', function() {
            let t1 = $(this).val();
            let currentClass = $(this).attr('class').match(/tp(\d+)_2/);
            if (currentClass) {
                let currentHour = parseInt(currentClass[1]);
                let nextHour = currentHour + 1;
                $(`.tp${nextHour}_1`).val(t1);
            }
        });

        $(document).off('change.matStart', '#matStart')
            .on('change.matStart', '#matStart', computeMaternityDates);

        $(document).off('change.matDelivery', '#matDelivery')
            .on('change.matDelivery', '#matDelivery', function() {
                window.matDelivery(this.value);
            });

        $(document).off('change.leaveAssist', '.leave-select').on('change.leaveAssist', '.leave-select', function() {
            const $td = $(this).closest('td');
            const val = $(this).val();
            const isLocked = $(this).prop('disabled');
            const $assist = $td.find('.assisted-select');
            const $assistContainer = $td.find('.assistContainer');

            if (val === 'medical') {

                if ($assist.length === 0) {
                    $td.append(buildAssistedSelectHtml('', isLocked));
                } else {
                    $assist.prop('disabled', isLocked);
                }
            } else {
                $assistContainer.remove();
            }
        });

        $(document).ready(function() {
            initFormRequestView();
        });

        window.addEventListener('hashchange', function() {
            if (location.hash.indexOf('/form-request') !== -1) {
                setTimeout(initFormRequestView, 50);
            }
        });



        (function() {
            if (typeof MutationObserver === 'undefined') {
                setInterval(function() {
                    if (location.hash.indexOf('/form-request') !== -1) {
                        const grid = document.getElementById('calendarGrid');
                        if (grid && grid.children.length === 0) {
                            grid.dataset.initialized = '';
                            initFormRequestView();
                        }
                    }
                }, 400);
                return;
            }

            const observer = new MutationObserver(function() {
                if (location.hash.indexOf('/form-request') === -1) return;
                const grid = document.getElementById('calendarGrid');
                if (grid && grid.children.length === 0) {
                    grid.dataset.initialized = '';
                    initFormRequestView();
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });

            if (document.readyState !== 'loading') {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
        })();

        window.buildApprovalDetailsHtml = buildApprovalDetailsHtml;
        window.deleteFixedAttachment = deleteFixedAttachment;
    })();
</script>