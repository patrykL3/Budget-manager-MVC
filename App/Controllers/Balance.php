<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\PeriodDataManager;

/**
 * Income controller
 *
 * PHP version 7.0
 */
class Balance extends Authentication_login
{

    /**
     * Show the Balance page
     *
     * @return void
     */
    public function openAction()
    {
        $period = $_GET['period'];
        $periodDataManager = new PeriodDataManager($period, $_POST);

        if($periodDataManager->isCorrectRangePeriod()) {
            $periodDataManager->createData();
        }

        View::renderTemplate('balance/open.html', ['data' => $periodDataManager]);
    }
}
