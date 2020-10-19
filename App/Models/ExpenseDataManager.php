<?php

namespace App\Models;

use PDO;
use \App\Authentication;
use \App\Date;

/**
 * Expense data manager
 *
 * PHP version 7.0
 */
class ExpenseDataManager extends \Core\Model
{
    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];
    public $userExpenseCategories;
    public $userPaymentCategories;
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
        $this->userExpenseCategories = $this->getUserExpenseCategories($this->loggedUser->user_id);
        $this->userPaymentCategories = $this->getUserPaymentCategories($this->loggedUser->user_id);

        foreach ($data as $key => $value) {
            $value = filter_input(INPUT_POST, $key);
            $this->$key = $value;
        };
    }

    /**
     * Save the expense with the current property values
     *
     * @return void
     */
    public function addExpense()
    {
        $this->validateExpenseData();

        if (empty($this->errors)) {
            $this->saveExpenseToExpensesTabel();
            $this->assignExpenseToUser();
            return true;
        }
        return false;
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    private function validateExpenseData()
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

        // Expense category
        if ($this->expense_category == 'Wybierz kategorię' || empty($this->expense_category)) {
            $this->errors['expenseCategoryRequired'] = 'Wprowadź kategorię wydatku!';
        }

        // Payment category
        if ($this->payment_category == 'Wybierz kategorię' || empty($this->payment_category)) {
            $this->errors['paymentCategoryRequired'] = 'Wprowadź metodę płatności!';
        }
    }


    private function getSelectedExpenseCategoryId()
    {
        foreach ($this->userExpenseCategories as $onceOfCategories) {
            if ($onceOfCategories['category_type'] === $this->expense_category) {
                return $onceOfCategories['expense_category_id'];
            }
        }
    }

    private function getSelectedPaymentCategoryId()
    {
        foreach ($this->userPaymentCategories as $onceOfCategories) {
            if ($onceOfCategories['payment_category_type'] === $this->payment_category) {
                return $onceOfCategories['payment_category_id'];
            }
        }
    }

    private function saveExpenseToExpensesTabel()
    {
        $selectedExpenseCategoryId = $this->getSelectedExpenseCategoryId();
        $selectedPaymentCategoryId = $this->getSelectedPaymentCategoryId();

        $database = static::getDB();

        $addExpense = $database->prepare('INSERT INTO expenses VALUES (NULL, :expense_category_id, :payment_category_id, :amount, :date_of_expense, :expense_comment)');
        $addExpense->bindValue(':expense_category_id', $selectedExpenseCategoryId, PDO::PARAM_INT);
        $addExpense->bindValue(':payment_category_id', $selectedPaymentCategoryId, PDO::PARAM_INT);
        $addExpense->bindValue(':amount', $this->amount, PDO::PARAM_STR);
        $addExpense->bindValue(':date_of_expense', $this->date, PDO::PARAM_STR);
        $addExpense->bindValue(':expense_comment', $this->comment, PDO::PARAM_STR);
        $addExpense->execute();
    }

    private function getNewExpenseId()
    {
        $database = static::getDB();

        $newExpenseIdQuery = $database->prepare('SELECT MAX(expense_id) FROM expenses');
        $newExpenseIdQuery->execute();
        $newExpenseId = $newExpenseIdQuery->fetch();

        return $newExpenseId[0];
    }

    private function assignExpenseToUser()
    {
        $newExpenseId = $this->getNewExpenseId();

        $database = static::getDB();

        $assignIncomeToUserQuery = $database->prepare('INSERT INTO users_expenses VALUES (:user_id, :expense_id)');
        $assignIncomeToUserQuery->bindValue(':user_id', $this->loggedUser->user_id, PDO::PARAM_INT);
        $assignIncomeToUserQuery->bindValue(':expense_id', $newExpenseId, PDO::PARAM_INT);
        $assignIncomeToUserQuery->execute();
    }

    private function getUserExpenseCategories($userId)
    {
        $database = static::getDB();

        $userExpenseQuery = $database->prepare(
            'SELECT category_type, uce.expense_category_id
            FROM users_categories_expenses AS uce
            INNER JOIN expenses_categories AS ec
            ON uce.expense_category_id = ec.expense_category_id
            WHERE
            uce.user_id= :user_id'
        );
        $userExpenseQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $userExpenseQuery->execute();

        return $userExpenseQuery->fetchAll();
    }

    private function getUserPaymentCategories($userId)
    {
        $database = static::getDB();

        $userPaymentQuery = $database->prepare(
            'SELECT payment_category_type, ucp.payment_category_id
            FROM users_categories_payments AS ucp
            INNER JOIN payments_categories AS pc
            ON ucp.payment_category_id = pc.payment_category_id
            WHERE
            ucp.user_id= :user_id'
        );
        $userPaymentQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $userPaymentQuery->execute();

        return $userPaymentQuery->fetchAll();
    }

    public function getUserExpensesFromPeriod($period, $balanceStartDate, $balanceEndDate)
    {
        $database = static::getDB();
        $mainPartGettingExpensesQuery =
            'SELECT e.expense_id, pc.payment_category_type, ec.category_type, e.amount, e.date_of_expense, e.expense_comment
            FROM expenses AS e
            INNER JOIN expenses_categories AS ec
            ON e.expense_category_id = ec.expense_category_id
            INNER JOIN payments_categories AS pc
            ON e.payment_category_id = pc.payment_category_id
            INNER JOIN users_expenses AS ue
            ON e.expense_id = ue.expense_id
            WHERE
            ue.user_id = :user_id
            ';

        $expenseTimePartOfTheQuery = $this->getExpenseTimePartOfTheQuery($period);

        $userExpensesFromPeriodQuery = $database->prepare($mainPartGettingExpensesQuery.$expenseTimePartOfTheQuery.' ORDER BY e.date_of_expense');
        $userExpensesFromPeriodQuery->bindValue(':user_id', $this->loggedUser->user_id, PDO::PARAM_INT);
        if ($period != 'custom') {
            $userExpensesFromPeriodQuery->bindValue(':currentDate', Date::getCurrentDate(), PDO::PARAM_STR);
        } else {
            $userExpensesFromPeriodQuery->bindValue(':balanceStartDate', $balanceStartDate, PDO::PARAM_STR);
            $userExpensesFromPeriodQuery->bindValue(':balanceEndDate', $balanceEndDate, PDO::PARAM_STR);
        }
        $userExpensesFromPeriodQuery->execute();
        return $userExpensesFromPeriodQuery->fetchAll();
    }

    private function getExpenseTimePartOfTheQuery($period)
    {
        if ($period == 'currentMonth') {
            $expenseTimePartOfTheQuery = 'AND MONTH(e.date_of_expense) = MONTH(:currentDate)';
        } elseif ($period == 'previousMonth') {
            $expenseTimePartOfTheQuery = 'AND MONTH(e.date_of_expense) = MONTH(:currentDate)-1';
        } elseif ($period == 'currentYear') {
            $expenseTimePartOfTheQuery = 'AND YEAR(e.date_of_expense) = YEAR(:currentDate)';
        } elseif ($period == 'custom') {
            $expenseTimePartOfTheQuery = 'AND e.date_of_expense BETWEEN :balanceStartDate AND :balanceEndDate';
        }

        return $expenseTimePartOfTheQuery;
    }


}
