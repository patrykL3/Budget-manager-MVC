<?php

namespace App;

/**
 * Date
 *
 * PHP version 7.0
 */
class Date
{
    public static function getCurrentDate()
    {
        date_default_timezone_set('Europe/Warsaw');
        return date('Y/m/d');
    }

    /**
     * Checked date
     *
     * @param string $date  The date to checked
     *
     * @return void
     */
    public static function isRealDate($date)
    {
        if (false === strtotime($date)) {
            return false;
        }
        list($year, $month, $day) = explode('-', $date);
        return checkdate($month, $day, $year);
    }
}
