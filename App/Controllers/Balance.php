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
        $incomeToEdit = PeriodDataManager::getIncomeData($incomeIdToEdit);

        echo json_encode($incomeToEdit);
    }


    public function updateIncomeAction()
    {
        PeriodDataManager::updateIncome($_POST);
    }


    public function deleteIncomeAction()
    {
        $incomeId = $_POST['incomeId'];

        PeriodDataManager::deleteIncome($incomeId);
    }


    public function getDataToEditExpenseAction()
    {
        $expenseIdToEdit = $_POST['expense_id'];
        $expenseToEdit = PeriodDataManager::getExpenseData($expenseIdToEdit);

        echo json_encode($expenseToEdit);
    }

    public function updateExpenseAction()
    {
        PeriodDataManager::updateExpense($_POST);
    }


    public function deleteExpenseAction()
    {
        $expenseId = $_POST['expenseId'];

        PeriodDataManager::deleteExpense($expenseId);
    }
}
