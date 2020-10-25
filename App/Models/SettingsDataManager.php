<?php

namespace App\Models;

use PDO;
use \App\Authentication;
use \App\Models\IncomeDataManager;
use \App\Models\ExpenseDataManager;
use \App\Models\User;

class SettingsDataManager extends \Core\Model
{
    private $incomeDataManager;
    private $expenseDataManager;
    private $user;

    public $errors = [];

    public $userIncomeCategories = [];
    public $userExpenseCategories = [];
    public $userPaymentCategories = [];
    //public $userData = [];


    public function __construct($data = [])
    {
        $this->incomeDataManager = new IncomeDataManager();
        $this->expenseDataManager = new ExpenseDataManager();
        $this->user = new User();

        $this->userIncomeCategories = $this->incomeDataManager->userIncomeCategories;
        $this->userExpenseCategories = $this->expenseDataManager->userExpenseCategories;
        $this->userPaymentCategories = $this->expenseDataManager->userPaymentCategories;
    }


    public static function getExpenseCategoryData($expenseCategoryId)
    {
        return ExpenseDataManager::getExpenseCategoryData($expenseCategoryId);
    }


    public static function getUserData()
    {
        return Authentication::getLoggedUser();
    }

    public static function updateUserData($data = [])
    {
        User::updateUserData($data);
    }

    public static function updateUserPassword($data = [])
    {
        return User::updateUserPassword($data);
    }








    public static function validateIncomeEditData($data = [])
    {
        IncomeDataManager::validateIncomeEditData($data);
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
