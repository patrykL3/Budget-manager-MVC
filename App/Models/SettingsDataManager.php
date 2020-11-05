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

    public $userIncomeCategories = [];
    public $userExpenseCategories = [];
    public $userPaymentCategories = [];


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

    public static function updateUserExpenseCategory($data = [])
    {
        return ExpenseDataManager::updateUserExpenseCategory($data);
    }

    public static function deleteUserExpenseCategory($expenseCategoryIdToDelete)
    {
        ExpenseDataManager::deleteUserExpenseCategory($expenseCategoryIdToDelete);
    }

    public static function whetherExpenseCategoryIsUsedByUser($expenseCategoryId)
    {
        $usedUserExpenseCategories = ExpenseDataManager::getIdUsedUserExpenseCategories();

        foreach ($usedUserExpenseCategories as $onceOfUserCategories) {
            if ($onceOfUserCategories['expense_category_id'] === $expenseCategoryId) {
                return true;
            }
        }

        return false;
    }

    public static function deleteUserExpensesInSelectedCategory($expenseCategoryId)
    {
        ExpenseDataManager::deleteUserExpensesInSelectedCategory($expenseCategoryId);
    }

    public static function moveUserExpensesFromSelectedCategory($expenseSelectedCategoryId, $categoryToCarryOverExpenses)
    {
        ExpenseDataManager::moveUserExpensesFromSelectedCategory($expenseSelectedCategoryId, $categoryToCarryOverExpenses);
    }



    public static function validateIncomeEditData($data = [])
    {
        IncomeDataManager::validateIncomeEditData($data);
    }


    public static function getExpenseData($expenseId)
    {
        return ExpenseDataManager::getExpenseData($expenseId);
    }

    public static function deleteExpense($expenseIdToDelete)
    {
        ExpenseDataManager::deleteExpense($expenseIdToDelete);
    }

    public static function addNewExpenseCategory($newExpenseCategory)
    {
        ExpenseDataManager::addExpenseCategory($newExpenseCategory, 0, 0);
    }

    public static function isCategoryAssignedToUser($expenseCategory)
    {
        return ExpenseDataManager::isCategoryAssignedToUser($expenseCategory);
    }

    public static function getExpenseCategoryId($expenseCategory)
    {
        return ExpenseDataManager::getExpenseCategoryId($expenseCategory);
    }
/*
    public static function validateExpenseCategoryChangeType($expenseCategoryId, $expenseCategoryType)
    {
        return ExpenseDataManager::validateExpenseCategoryChangeType($expenseCategoryId, $expenseCategoryType);
    }
    */
}
