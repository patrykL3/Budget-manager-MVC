<?php

namespace App\Models;

use PDO;
use \App\Authentication;
use \App\Models\IncomeDataManager;
use \App\Models\ExpenseDataManager;
use \App\Date;

/**
 * Income data manager
 *
 * PHP version 7.0
 */
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
    public $userExpenceCategories = [];
    public $userPaymentCategories = [];

    public $balanceValuel;
    public $expensesSumToPie;



    /**
     * Class constructor
     *
     * @param array $data  Initial property values
     *
     * @return void
     */
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
        //$this->calculateBalance();
        //$this->createExpensesDataToPie();
    }


    public static function getIncomeData($incomeId)
    {
        return IncomeDataManager::getIncomeData($incomeId);
    }


    public static function validateIncomeEditData($data = [])
    {
        IncomeDataManager::validateIncomeEditData($data);
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
}
