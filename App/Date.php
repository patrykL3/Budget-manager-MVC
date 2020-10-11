<?php

namespace App;

/**
 * Date
 *
 * PHP version 7.0
 */
class Date
{
    /**
     * Add a message
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
