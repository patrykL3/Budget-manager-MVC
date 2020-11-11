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

    public static function updateUserPaymentCategory($data = [])
    {
        return ExpenseDataManager::updateUserPaymentCategory($data);
    }

    public static function updateUserIncomeCategory($data = [])
    {
        return IncomeDataManager::updateUserIncomeCategory($data);
    }

    public static function deleteUserExpenseCategory($expenseCategoryIdToDelete)
    {
        ExpenseDataManager::deleteUserExpenseCategory($expenseCategoryIdToDelete);
    }

    public static function deleteUserPaymentCategory($paymentCategoryIdToDelete)
    {
        ExpenseDataManager::deleteUserPaymentCategory($paymentCategoryIdToDelete);
    }

    public static function deleteUserIncomeCategory($incomeCategoryIdToDelete)
    {
        IncomeDataManager::deleteUserIncomeCategory($incomeCategoryIdToDelete);
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

    public static function whetherPaymentCategoryIsUsedByUser($paymentCategoryId)
    {
        $usedUserPaymentCategories = ExpenseDataManager::getIdUsedUserPaymentsCategories();

        foreach ($usedUserPaymentCategories as $onceOfUserCategories) {
            if ($onceOfUserCategories['payment_category_id'] === $paymentCategoryId) {
                return true;
            }
        }

        return false;
    }

    public static function whetherIncomeCategoryIsUsedByUser($incomeCategoryId)
    {
        $usedUserIncomeCategories = IncomeDataManager::getIdUsedUserIncomeCategories();

        foreach ($usedUserIncomeCategories as $onceOfUserCategories) {
            if ($onceOfUserCategories['income_category_id'] === $incomeCategoryId) {
                return true;
            }
        }

        return false;
    }

    public static function deleteUserExpensesInSelectedCategory($expenseCategoryId)
    {
        ExpenseDataManager::deleteUserExpensesInSelectedCategory($expenseCategoryId);
    }

    public static function deleteUserExpensesWithPaymentCategory($paymentCategoryId)
    {
        ExpenseDataManager::deleteUserExpensesWithPaymentCategory($paymentCategoryId);
    }

    public static function deleteUserIncomesInSelectedCategory($incomeCategoryId)
    {
        IncomeDataManager::deleteUserIncomesInSelectedCategory($incomeCategoryId);
    }

    public static function moveUserExpensesFromCategory($oldCategoryId, $categoryToCarryOverExpenses)
    {
        ExpenseDataManager::moveUserExpensesFromCategory($oldCategoryId, $categoryToCarryOverExpenses);
    }

    public static function movePaymentsToAnotherCategory($oldPaymentCategoryId, $categoryToCarryOverPayments)
    {
        ExpenseDataManager::movePaymentsToAnotherCategory($oldPaymentCategoryId, $categoryToCarryOverPayments);
    }

    public static function moveUserIncomesFromCategory($oldCategoryId, $categoryToCarryOverIncomes)
    {
        IncomeDataManager::moveUserIncomesFromCategory($oldCategoryId, $categoryToCarryOverIncomes);
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

    public static function addNewPaymentCategory($newPaymentCategory)
    {
        ExpenseDataManager::addPaymentCategory($newPaymentCategory);
    }

    public static function addNewIncomeCategory($newIncomeCategory)
    {
        IncomeDataManager::addIncomeCategory($newIncomeCategory);
    }

    public static function isExpenseCategoryAssignedToUser($expenseCategory)
    {
        return ExpenseDataManager::isCategoryAssignedToUser($expenseCategory);
    }

    public static function isPaymentCategoryAssignedToUser($paymentCategory)
    {
        return ExpenseDataManager::isPaymentCategoryAssignedToUser($paymentCategory);
    }

    public static function isIncomeCategoryAssignedToUser($incomeCategory)
    {
        return IncomeDataManager::isCategoryAssignedToUser($incomeCategory);
    }

    public static function getExpenseCategoryId($expenseCategory)
    {
        return ExpenseDataManager::getExpenseCategoryId($expenseCategory);
    }

    public static function getPaymentCategoryId($paymentCategory)
    {
        return ExpenseDataManager::getPaymentCategoryId($paymentCategory);
    }

    public static function getIncomeCategoryId($incomeCategory)
    {
        return IncomeDataManager::getIncomeCategoryId($incomeCategory);
    }


    public static function getUserIncomeCategories()
    {
        $LoggedUserId = Authentication::getLoggedUser()->user_id;
        return IncomeDataManager::getUserIncomeCategories($LoggedUserId);
    }

    public static function getUserExpenseCategories()
    {
        $LoggedUserId = Authentication::getLoggedUser()->user_id;
        return ExpenseDataManager::getUserExpenseCategories($LoggedUserId);
    }

    public static function getUserPaymentCategories()
    {
        $LoggedUserId = Authentication::getLoggedUser()->user_id;
        return ExpenseDataManager::getUserPaymentCategories($LoggedUserId);
    }


}
