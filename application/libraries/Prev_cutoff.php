<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prev_cutoff {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Generate cutoffs for a given year using the employee's schedule.
     *
     * @param array $schedule  ['startFC'=>'24','endFC'=>'08','startSC'=>'09','endSC'=>'23']
     * @param int   $year
     * @return array
     */
    public function generate_yearly_monthly_cutoffs($schedule, $year)
    {
        if (!is_array($schedule) ||
            !isset($schedule['startFC']) || !isset($schedule['endFC']) ||
            !isset($schedule['startSC']) || !isset($schedule['endSC'])) {
            return array('year' => $year, 'cutoffs' => array(), 'total_cutoffs' => 0);
        }

        $year = ($year !== null) ? (int) $year : (int) date('Y');

        $cut1_start = trim($schedule['startFC']);   // e.g. "24"
        $cut1_end   = trim($schedule['endFC']);     // e.g. "08"
        $cut2_start = trim($schedule['startSC']);   // e.g. "09"
        $cut2_end   = trim($schedule['endSC']);     // e.g. "23"

        $cutoffs = array();
        $cutoff_number = 1;

        // ------------------------------------------------------------
        // 1. Cutoff from previous December that spans into January
        // ------------------------------------------------------------
        $prev_year = $year - 1;
        $dec_last_day = 31;

        $cut1_start_day_dec = $this->parseDayNumber($cut1_start, $dec_last_day);
        $cut1_end_day_dec   = $this->parseDayNumber($cut1_end, $dec_last_day);
        $cut1_ends_next_month_dec = ($cut1_end !== 'END' && $cut1_end_day_dec < $cut1_start_day_dec);

        if ($cut1_ends_next_month_dec) {
            $jan_last_day = 31;
            $cut1_end_day_jan = min($this->parseDayNumber($cut1_end, $jan_last_day), $jan_last_day);

            $cutoffs[$cutoff_number] = array(
                'number'       => $cutoff_number,
                'month'        => 12,
                'month_name'   => 'December',
                'type'         => 'first_cutoff_prev_year',
                'start'        => sprintf('%04d-%02d-%02d', $prev_year, 12, $cut1_start_day_dec),
                'end'          => sprintf('%04d-%02d-%02d', $year, 1, $cut1_end_day_jan),
                'start_year'   => $prev_year,
                'end_year'     => $year,
                'start_month'  => 12,
                'end_month'    => 1,
                'spans_years'  => true,
                'display'      => $this->formatCutoffDisplayCrossYear(12, $cut1_start, 1, $cut1_end, $prev_year, $year),
                'display_full' => $this->formatCutoffDisplayFullCrossYear(12, $cut1_start, 1, $cut1_end, $prev_year, $year),
                'days_count'   => $this->calculateDaysCount($prev_year, 12, $cut1_start_day_dec, $year, 1, $cut1_end_day_jan),
                'is_cross_year'=> true
            );
            $cutoff_number++;
        }

        // ------------------------------------------------------------
        // 2. Loop every month
        // ------------------------------------------------------------
        for ($month = 1; $month <= 12; $month++) {
            $month_name = date('F', mktime(0, 0, 0, $month, 1, $year));
            $last_day   = (int) date('t', mktime(0, 0, 0, $month, 1, $year));

            $cut1_start_day = $this->parseDayNumber($cut1_start, $last_day);
            $cut1_end_day   = $this->parseDayNumber($cut1_end, $last_day);
            $cut2_start_day = $this->parseDayNumber($cut2_start, $last_day);
            $cut2_end_day   = $this->parseDayNumber($cut2_end, $last_day);

            // Cutoff 1 — does it span to next month?
            $cut1_ends_next_month = ($cut1_end !== 'END' && $cut1_end_day < $cut1_start_day);
            $cut1_end_month = $cut1_ends_next_month ? ($month == 12 ? 1 : $month + 1) : $month;
            $cut1_end_year  = $cut1_ends_next_month ? ($month == 12 ? $year + 1 : $year) : $year;

            if ($cut1_end === 'END') {
                $cut1_end_day        = $last_day;
                $cut1_ends_next_month = false;
                $cut1_end_month      = $month;
                $cut1_end_year       = $year;
            }

            // Cutoff 2 — does it span to next month?
            $cut2_ends_next_month = ($cut2_end !== 'END' && $cut2_end_day < $cut2_start_day);
            $cut2_end_month = $cut2_ends_next_month ? ($month == 12 ? 1 : $month + 1) : $month;
            $cut2_end_year  = $cut2_ends_next_month ? ($month == 12 ? $year + 1 : $year) : $year;

            if ($cut2_end === 'END') {
                $cut2_end_day        = $last_day;
                $cut2_ends_next_month = false;
                $cut2_end_month      = $month;
                $cut2_end_year       = $year;
            }

            // February clamp
            if ($month == 2) {
                $feb_last_day = date('L', mktime(0, 0, 0, 1, 1, $year)) ? 29 : 28;
                if ($cut1_start_day > $feb_last_day && $cut1_start !== 'END') $cut1_start_day = $feb_last_day;
                if ($cut1_end_day   > $feb_last_day && $cut1_end   !== 'END') $cut1_end_day   = $feb_last_day;
                if ($cut2_start_day > $feb_last_day && $cut2_start !== 'END') $cut2_start_day = $feb_last_day;
                if ($cut2_end_day   > $feb_last_day && $cut2_end   !== 'END') $cut2_end_day   = $feb_last_day;
            }

            // Skip cutoff 1 in January if it duplicates the December cross-year one
            $should_create_cut1 = true;
            if ($month == 1 && $cut1_ends_next_month_dec) {
                $jan_last_day = 31;
                $dec_cutoff_end_day = min($this->parseDayNumber($cut1_end, $jan_last_day), $jan_last_day);
                if ($cut1_start_day == $dec_cutoff_end_day + 1) {
                    $should_create_cut1 = false;
                }
            }
            if ($month > 1 && $cut1_ends_next_month) {
                $prev_month    = $month - 1;
                $prev_last_day = (int) date('t', mktime(0, 0, 0, $prev_month, 1, $year));
                $prev_cut2_end_day = $this->parseDayNumber($cut2_end, $prev_last_day);
                $prev_cut2_ends_next_month = ($cut2_end !== 'END' && $prev_cut2_end_day < $this->parseDayNumber($cut2_start, $prev_last_day));
                if ($prev_cut2_ends_next_month && $cut1_start_day == $prev_cut2_end_day + 1) {
                    $should_create_cut1 = false;
                }
            }

            // ---- First cutoff of month ----
            if ($should_create_cut1) {
                $cutoffs[$cutoff_number] = array(
                    'number'       => $cutoff_number,
                    'month'        => $month,
                    'month_name'   => $month_name,
                    'type'         => 'first_cutoff',
                    'start'        => sprintf('%04d-%02d-%02d', $year, $month, $cut1_start_day),
                    'end'          => sprintf('%04d-%02d-%02d', $cut1_end_year, $cut1_end_month, $cut1_end_day),
                    'start_year'   => $year,
                    'end_year'     => $cut1_end_year,
                    'start_month'  => $month,
                    'end_month'    => $cut1_end_month,
                    'spans_months' => $cut1_ends_next_month,
                    'spans_years'  => ($cut1_end_year != $year),
                    'display'      => $this->formatCutoffDisplay($month, $cut1_start_day, $cut1_end_month, $cut1_end_day, $year, $cut1_end_year, true),
                    'display_full' => $this->formatCutoffDisplayFull($month, $cut1_start_day, $cut1_end_month, $cut1_end_day, $year, $cut1_end_year, true),
                    'days_count'   => $this->calculateDaysCount($year, $month, $cut1_start_day, $cut1_end_year, $cut1_end_month, $cut1_end_day),
                    'is_cross_year'=> ($cut1_end_year != $year)
                );
                $cutoff_number++;
            }

            // ---- Second cutoff of month ----
            if (!($month == 12 && $cut2_ends_next_month)) {
                $cutoffs[$cutoff_number] = array(
                    'number'       => $cutoff_number,
                    'month'        => $month,
                    'month_name'   => $month_name,
                    'type'         => 'second_cutoff',
                    'start'        => sprintf('%04d-%02d-%02d', $year, $month, $cut2_start_day),
                    'end'          => sprintf('%04d-%02d-%02d', $cut2_end_year, $cut2_end_month, $cut2_end_day),
                    'start_year'   => $year,
                    'end_year'     => $cut2_end_year,
                    'start_month'  => $month,
                    'end_month'    => $cut2_end_month,
                    'spans_months' => $cut2_ends_next_month,
                    'spans_years'  => ($cut2_end_year != $year),
                    'display'      => $this->formatCutoffDisplay($month, $cut2_start_day, $cut2_end_month, $cut2_end_day, $year, $cut2_end_year, true),
                    'display_full' => $this->formatCutoffDisplayFull($month, $cut2_start_day, $cut2_end_month, $cut2_end_day, $year, $cut2_end_year, true),
                    'days_count'   => $this->calculateDaysCount($year, $month, $cut2_start_day, $cut2_end_year, $cut2_end_month, $cut2_end_day),
                    'is_cross_year'=> ($cut2_end_year != $year)
                );
                $cutoff_number++;
            }
        }

        // ------------------------------------------------------------
        // 3. December cutoff 2 that spans into next January
        // ------------------------------------------------------------
        $cut2_start_day_dec = $this->parseDayNumber($cut2_start, $dec_last_day);
        $cut2_end_day_dec   = $this->parseDayNumber($cut2_end, $dec_last_day);
        $cut2_ends_next_month_dec = ($cut2_end !== 'END' && $cut2_end_day_dec < $cut2_start_day_dec);

        if ($cut2_ends_next_month_dec) {
            $jan_next_year    = $year + 1;
            $jan_last_day     = 31;
            $cut2_end_day_jan = min($this->parseDayNumber($cut2_end, $jan_last_day), $jan_last_day);

            $cutoffs[$cutoff_number] = array(
                'number'       => $cutoff_number,
                'month'        => 12,
                'month_name'   => 'December',
                'type'         => 'second_cutoff_cross_year',
                'start'        => sprintf('%04d-%02d-%02d', $year, 12, $cut2_start_day_dec),
                'end'          => sprintf('%04d-%02d-%02d', $jan_next_year, 1, $cut2_end_day_jan),
                'start_year'   => $year,
                'end_year'     => $jan_next_year,
                'start_month'  => 12,
                'end_month'    => 1,
                'spans_years'  => true,
                'display'      => $this->formatCutoffDisplayCrossYear(12, $cut2_start, 1, $cut2_end, $year, $jan_next_year),
                'display_full' => $this->formatCutoffDisplayFullCrossYear(12, $cut2_start, 1, $cut2_end, $year, $jan_next_year),
                'days_count'   => $this->calculateDaysCount($year, 12, $cut2_start_day_dec, $jan_next_year, 1, $cut2_end_day_jan),
                'is_cross_year'=> true
            );
        }

        // Sort & renumber
        usort($cutoffs, function ($a, $b) {
            return strcmp($a['start'], $b['start']);
        });
        foreach ($cutoffs as $index => &$cutoff) {
            $cutoff['number'] = $index + 1;
        }
        unset($cutoff);

        // Prev/next links
        $total = count($cutoffs);
        for ($i = 0; $i < $total; $i++) {
            if ($i > 0) {
                $cutoffs[$i]['previous'] = array(
                    'start'   => $cutoffs[$i-1]['start'],
                    'end'     => $cutoffs[$i-1]['end'],
                    'display' => $cutoffs[$i-1]['display'],
                );
            }
            if ($i < $total - 1) {
                $cutoffs[$i]['next'] = array(
                    'start'   => $cutoffs[$i+1]['start'],
                    'end'     => $cutoffs[$i+1]['end'],
                    'display' => $cutoffs[$i+1]['display'],
                );
            }
        }

        return array(
            'year'          => $year,
            'config'        => $schedule,
            'total_cutoffs' => $total,
            'cutoffs'       => $cutoffs,
        );
    }

    // ---------------- helpers (unchanged) ----------------
    private function formatCutoffDisplay($start_month, $start_day, $end_month, $end_day, $start_year, $end_year, $show_year = false) {
        $sm = date('M', mktime(0, 0, 0, $start_month, 1, $start_year));
        $em = date('M', mktime(0, 0, 0, $end_month,   1, $end_year));
        if ($start_year == $end_year && $start_month == $end_month) return "$sm $start_day to $end_day";
        if ($start_year == $end_year)                               return "$sm $start_day to $em $end_day";
        return "$sm $start_day, $start_year to $em $end_day, $end_year";
    }

    private function formatCutoffDisplayCrossYear($start_month, $start_day_str, $end_month, $end_day_str, $start_year, $end_year) {
        $sd = is_numeric($start_day_str) ? (int)$start_day_str : $start_day_str;
        $ed = is_numeric($end_day_str)   ? (int)$end_day_str   : $end_day_str;
        $sm = date('M', mktime(0, 0, 0, $start_month, 1, $start_year));
        $em = date('M', mktime(0, 0, 0, $end_month,   1, $end_year));
        return "$sm $sd, $start_year to $em $ed, $end_year";
    }

    private function formatCutoffDisplayFullCrossYear($start_month, $start_day_str, $end_month, $end_day_str, $start_year, $end_year) {
        $sd = is_numeric($start_day_str) ? (int)$start_day_str : $start_day_str;
        $ed = is_numeric($end_day_str)   ? (int)$end_day_str   : $end_day_str;
        $sm = date('F', mktime(0, 0, 0, $start_month, 1, $start_year));
        $em = date('F', mktime(0, 0, 0, $end_month,   1, $end_year));
        return "$sm $sd, $start_year to $em $ed, $end_year";
    }

    private function parseDayNumber($day_str, $last_day) {
        if ($day_str === 'END') return $last_day;
        return (int) $day_str;
    }

    private function formatCutoffDisplayFull($start_month, $start_day, $end_month, $end_day, $start_year, $end_year) {
        $sm = date('F', mktime(0, 0, 0, $start_month, 1, $start_year));
        $em = date('F', mktime(0, 0, 0, $end_month,   1, $end_year));
        return "$sm $start_day, $start_year to $em $end_day, $end_year";
    }

    private function calculateDaysCount($start_year, $start_month, $start_day, $end_year, $end_month, $end_day) {
        $start = new DateTime("$start_year-$start_month-$start_day");
        $end   = new DateTime("$end_year-$end_month-$end_day");
        $end->modify('+1 day');
        $interval = $start->diff($end);
        return $interval->days;
    }
}