<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\PeriodDataManager;

/**
 * Balance controller
 *
 * PHP version 7.0
 */
class Balance extends Authentication_login
{
    public function openAction()
    {
        $period = $_GET['period'];
        $periodDataManager = new PeriodDataManager($period, $_POST);

        if ($periodDataManager->isCorrectRangePeriod()) {
            $periodDataManager->createData();
        }

        View::renderTemplate('balance/open.html', ['data' => $periodDataManager]);
    }


    public function getDataToEditIncomeAction()
    {
        $incomeIdToEdit = $_POST['income_id'];
        $IncomeToEdit = PeriodDataManager::getIncomeData($incomeIdToEdit);

        echo json_encode($IncomeToEdit);
    }


    public function updateIncomeAction()
    {
        $incomeId = $_GET['incomeId'];

        PeriodDataManager::updateIncome($incomeId, $_POST);
    }

    public function deleteIncomeAction()
    {
        $incomeId = $_POST['incomeId'];

        PeriodDataManager::deleteIncome($incomeId);
    }
}
