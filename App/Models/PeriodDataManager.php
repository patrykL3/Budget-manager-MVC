<?php

namespace App\Models;

use PDO;
use \App\Authentication;
use \App\Models\IncomeDataManager;
use \App\Models\ExpenseDataManager;
use \App\Date;

class PeriodDataManager extends \Core\Model
{
    private $incomeDataManager;
    private $expenseDataManager;

    public $period;
    public $periodTitle;
    public $rangePeriod = [];
    public $errors = [];

    public $incomesFromPeriod;
    public $expensesFromPeriod;
    public $userIncomeCategories = [];
    public $userExpenseCategories = [];
    public $userPaymentCategories = [];

    public $balanceValue;
    public $expensesSumsToPie= [];


    public function __construct($period, $rangePeriod = [])
    {
        $this->incomeDataManager = new IncomeDataManager();
        $this->expenseDataManager = new ExpenseDataManager();

        $this->period = $period;
        $this->setPeriodTitle($period);
        $this->userIncomeCategories = $this->incomeDataManager->userIncomeCategories;
        $this->userExpenseCategories = $this->expenseDataManager->userExpenseCategories;
        $this->userPaymentCategories = $this->expenseDataManager->userPaymentCategories;

        foreach ($rangePeriod as $key => $value) {
            $value = filter_input(INPUT_POST, $key);
            $this->$key = $value;
        };
    }

    private function setPeriodTitle($period)
    {
        if ($period == 'currentMonth') {
            $this->periodTitle = "OBECNY MIESIĄC";
        } elseif ($period == 'previousMonth') {
            $this->periodTitle = "POPRZEDNI MIESIĄC";
        } elseif ($period == 'currentYear') {
            $this->periodTitle = "OBECNY ROK";
        } elseif ($period == 'custom') {
            $this->periodTitle = "OKRES NIESTANDARDOWY";
        }
    }

    public function isCorrectRangePeriod()
    {
        if ($this->period == 'custom') {
            if (isset($this->balance_start_date)) {
                return $this->checkReceivedDates();
            }
            return false;
        }
        return true;
    }

    private function checkReceivedDates()
    {
        if (!Date::isRealDate($this->balance_start_date)) {
            $this->errors['correctStartDateRequired'] = 'Niepoprawna data';
            return false;
        } elseif (!Date::isRealDate($this->balance_end_date)) {
            $this->errors['correctEndDateRequired'] = 'Niepoprawna data';
            return false;
        }
        return true;
    }


    public function createData()
    {
        if ($this->period != 'custom') {
            $this->balance_start_date = '';
            $this->balance_end_date = '';
        }
        $this->incomesFromPeriod = $this->incomeDataManager->getUserIncomesFromPeriod($this->period, $this->balance_start_date, $this->balance_end_date);
        $this->expensesFromPeriod = $this->expenseDataManager->getUserExpensesFromPeriod($this->period, $this->balance_start_date, $this->balance_end_date);
        $this->calculateBalance();
        $this->createExpensesDataToPie();
    }

    private function calculateBalance()
    {
        $incomesSum = 0;
        $expensesSum = 0;

        foreach ($this->incomesFromPeriod as $onceIncome) {
            $incomesSum += $onceIncome['amount'];
        }
        foreach ($this->expensesFromPeriod as $onceExpense) {
            $expensesSum += $onceExpense['amount'];
        }

        $this->balanceValue= $incomesSum - $expensesSum;
    }


    private function createExpensesDataToPie()
    {
        $incomesSum = 0;
        $expensesSum = 0;

        $this->expensesSumsToPie= array();
        foreach ($this->expensesFromPeriod as $onceExpense) {
            $categoryName = $onceExpense['category_type'];
            $this->expensesSumsToPie[$categoryName] = 0;
        }
        foreach ($this->expensesFromPeriod as $onceExpense) {
            $categoryName = $onceExpense['category_type'];
            $this->expensesSumsToPie[$categoryName] += $onceExpense['amount'];
        }
    }



    public static function getIncomeData($incomeId)
    {
        return IncomeDataManager::getIncomeData($incomeId);
    }


    public static function updateIncome($data = [])
    {
        IncomeDataManager::updateIncome($data);
    }


    public static function deleteIncome($incomeIdToDelete)
    {
        IncomeDataManager::deleteIncome($incomeIdToDelete);
    }

    public static function getExpenseData($expenseId)
    {
        return ExpenseDataManager::getExpenseData($expenseId);
    }

    public static function updateExpense($data = [])
    {
        ExpenseDataManager::updateExpense($data);
    }

    public static function deleteExpense($expenseIdToDelete)
    {
        ExpenseDataManager::deleteExpense($expenseIdToDelete);
    }
}
