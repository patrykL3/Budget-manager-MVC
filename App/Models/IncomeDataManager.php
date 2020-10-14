<?php

namespace App\Models;

use PDO;
use \App\Authentication;
use \App\Date;

/**
 * Income data manager
 *
 * PHP version 7.0
 */
class IncomeDataManager extends \Core\Model
{
    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];
    public $userIncomeCategories;
    private $loggedUser;


    /**
     * Class constructor
     *
     * @param array $data  Initial property values
     *
     * @return void
     */
    public function __construct($data = [])
    {
        $this->loggedUser = Authentication::getLoggedUser();
        $this->userIncomeCategories = $this->getUserIncomeCategories($this->loggedUser->user_id);

        foreach ($data as $key => $value) {
            $value = filter_input(INPUT_POST, $key);
            $this->$key = $value;
        };
    }

    /**
     * Save the income with the current property values
     *
     * @return void
     */
    public function addIncome()
    {
        $this->validateIncomeData();

        if (empty($this->errors)) {
            $this->saveIncomeToIncomesTabel();
            $this->assignIncomeToUser();
            return true;
        }
        return false;
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validateIncomeData()
    {
        // Amount
        $this->amount = str_replace(',', '.', $this->amount);
        $this->amount = filter_var($this->amount, FILTER_VALIDATE_FLOAT);
        if (empty($this->amount)) {
            $this->errors['amountRequired'] = "Wprowadź poprawną kwotę!";
        }

        // Date
        if (!Date::isRealDate($this->date)) {
            $this->errors['dateRequired'] = 'Wprowadź poprawną datę!';
        }

        // Category
        if ($this->category == 'Wybierz kategorię' || empty($this->category)) {
            $this->errors['categoryRequired'] = 'Wprowadź kategorię!';
        }
    }


    private function getSelectedCategoryId()
    {
        foreach ($this->userIncomeCategories as $onceOfCategories) {
            if ($onceOfCategories['category_type'] === $this->category) {
                return $onceOfCategories['income_category_id'];
            }
        }
    }

    private function saveIncomeToIncomesTabel()
    {
        $selectedCategoryId = $this->getSelectedCategoryId();

        $database = static::getDB();

        $addIncome = $database->prepare('INSERT INTO incomes VALUES (NULL, :income_category_id, :amount, :date_of_income, :income_comment)');
        $addIncome->bindValue(':income_category_id', $selectedCategoryId, PDO::PARAM_INT);
        $addIncome->bindValue(':amount', $this->amount, PDO::PARAM_STR);
        $addIncome->bindValue(':date_of_income', $this->date, PDO::PARAM_STR);
        $addIncome->bindValue(':income_comment', $this->comment, PDO::PARAM_STR);
        $addIncome->execute();
    }

    private function getNewIncomeId()
    {
        $database = static::getDB();

        $newIncomeIdQuery = $database->prepare('SELECT MAX(income_id) FROM incomes');
        $newIncomeIdQuery->execute();
        $newIncomeId = $newIncomeIdQuery->fetch();

        return $newIncomeId[0];
    }

    private function assignIncomeToUser()
    {
        $newIncomeId = $this->getNewIncomeId();

        $database = static::getDB();

        $assignIncomeToUserQuery = $database->prepare('INSERT INTO users_incomes VALUES (:user_id, :income_id)');
        $assignIncomeToUserQuery->bindValue(':user_id', $this->loggedUser->user_id, PDO::PARAM_INT);
        $assignIncomeToUserQuery->bindValue(':income_id', $newIncomeId, PDO::PARAM_INT);
        $assignIncomeToUserQuery->execute();
    }

    public function getUserIncomeCategories($userId)
    {
        $database = static::getDB();

        $userIncomesQuery = $database->prepare(
            'SELECT category_type, uci.income_category_id
            FROM users_categories_incomes AS uci
            INNER JOIN incomes_categories AS ic
            ON uci.income_category_id = ic.income_category_id
            WHERE
            uci.user_id= :user_id'
        );
        $userIncomesQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $userIncomesQuery->execute();

        return $userIncomesQuery->fetchAll();
    }

    public function getUserIncomesFromPeriod($period, $balanceStartDate, $balanceEndDate)
    {
        $database = static::getDB();
        $mainPartGettingIncomesQuery =
                'SELECT i.income_id, ic.category_type, i.amount, i.date_of_income, i.income_comment
                FROM incomes AS i
                INNER JOIN incomes_categories AS ic
                ON i.income_category_id = ic.income_category_id
                INNER JOIN users_incomes AS ui
                ON i.income_id = ui.income_id
                WHERE
                ui.user_id = :user_id
                ';
        $incomeTimePartOfTheQuery = $this->getIncomeTimePartOfTheQuery($period);

        $userIncomesFromPeriodQuery = $database->prepare($mainPartGettingIncomesQuery.$incomeTimePartOfTheQuery.' ORDER BY i.date_of_income');
        $userIncomesFromPeriodQuery->bindValue(':user_id', $this->loggedUser->user_id, PDO::PARAM_INT);
        if($period != 'custom') {
            $userIncomesFromPeriodQuery->bindValue(':currentDate', Date::getCurrentDate(), PDO::PARAM_STR);
        } else {
            $userIncomesFromPeriodQuery->bindValue(':balanceStartDate', $balanceStartDate, PDO::PARAM_STR);
            $userIncomesFromPeriodQuery->bindValue(':balanceEndDate', $balanceEndDate, PDO::PARAM_STR);
        }
        $userIncomesFromPeriodQuery->execute();
        return $userIncomesFromPeriodQuery->fetchAll();
    }

    private function getIncomeTimePartOfTheQuery($period)
    {
        if ($period == 'currentMonth') {
            $incomeTimePartOfTheQuery = 'AND MONTH(i.date_of_income) = MONTH(:currentDate)';
        } elseif ($period == 'previousMonth') {
            $incomeTimePartOfTheQuery = 'AND MONTH(i.date_of_income) = MONTH(:currentDate)-1';
        } elseif ($period == 'currentYear') {
            $incomeTimePartOfTheQuery = 'AND YEAR(i.date_of_income) = YEAR(:currentDate)';
        } elseif ($period == 'custom') {
            $incomeTimePartOfTheQuery = 'AND i.date_of_income BETWEEN :balanceStartDate AND :balanceEndDate';
        }

        return $incomeTimePartOfTheQuery;
    }
}
