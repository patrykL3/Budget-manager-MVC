<?php

namespace App\Controllers;

use \Core\View;

use \App\Models\SettingsDataManager;

//use \App\Flash;

class Settings extends Authentication_login
{
    public function openAction()
    {
        $settingsDataManager = new SettingsDataManager($_POST);
        View::renderTemplate('Settings/open.html', ['data' => $settingsDataManager]);
    }


    public function getDataToEditExpenseCategoryAction()
    {
        $expenseCategoryIdToEdit = $_POST['expenseCategoryId'];
        $expenseCategoryToEdit = SettingsDataManager::getExpenseCategoryData($expenseCategoryIdToEdit);

        echo json_encode($expenseCategoryToEdit);
    }

    public function updateExpenseCategory()
    {
        SettingsDataManager::updateUserExpenseCategory($_POST);
    }

    public function tryDeleteUserExpenseCategoryAction()
    {
        $expenseCategoryIdToDelete = $_POST['expenseCategoryId'];
        $whetherExpenseCategoryIsUsedByUser = SettingsDataManager::whetherExpenseCategoryIsUsedByUser($expenseCategoryIdToDelete);

        if (!$whetherExpenseCategoryIsUsedByUser) {
            SettingsDataManager::deleteUserExpenseCategory($expenseCategoryIdToDelete);
        }

        echo json_encode($whetherExpenseCategoryIsUsedByUser);
    }

    public function deleteUserExpenseCategoryWithExpensesAction()
    {
        $expenseCategoryIdToDelete = $_POST['expenseCategoryId'];

        SettingsDataManager::deleteUserExpensesInSelectedCategory($expenseCategoryIdToDelete);
        SettingsDataManager::deleteUserExpenseCategory($expenseCategoryIdToDelete);
    }

    public function deleteUserExpenseCategoryWithMoveExpensesToAnotherCategoryAction()
    {
        $expenseCategoryIdToDelete = $_POST['expenseCategoryId'];
        $categoryToCarryOverExpenses = $_POST['categoryToCarryOverExpenses'];

        SettingsDataManager::moveUserExpensesFromCategory($expenseCategoryIdToDelete, $categoryToCarryOverExpenses);
        SettingsDataManager::deleteUserExpenseCategory($expenseCategoryIdToDelete);
    }

    public function addExpenseCategoryAction()
    {
        $newExpenseCategory = filter_input(INPUT_POST, 'newExpenseCategory');
        $newExpenseCategoryId="";

        if (!SettingsDataManager::isExpenseCategoryAssignedToUser($newExpenseCategory) && $newExpenseCategory !="") {
            SettingsDataManager::addNewExpenseCategory($newExpenseCategory);
            $newExpenseCategoryId = SettingsDataManager::getExpenseCategoryId($newExpenseCategory);
        }
        echo json_encode($newExpenseCategoryId);
    }


    public function getDataToEditUserDataAction()
    {
        $userData = SettingsDataManager::getUserData();

        echo json_encode($userData);
    }

    public function updateUserDataAction()
    {
         SettingsDataManager::updateUserData($_POST);
        $categoryIdAfterUpdate = SettingsDataManager::updateUserData($_POST);
        echo json_encode($categoryIdAfterUpdate);
    }

    public function updateUserPasswordAction()
    {
        $error = false; //????????????

        if (!SettingsDataManager::updateUserPassword($_POST)) {
            $error = true;
        }
        echo json_encode($error);
    }
/*
    public function validateExpenseCategoryChangeType()
    {
        $expenseCategoryId = $_GET['expenseCategoryId'];
        $expenseCategoryType = $_GET['expenseCategoryType'];

        $isValid =  SettingsDataManager::validateExpenseCategoryChangeType($expenseCategoryId, $expenseCategoryType);

        echo json_encode($isValid);
    }
    */

    public function addIncomeCategoryAction()
    {
        $newIncomeCategory = filter_input(INPUT_POST, 'newIncomeCategory');
        $newIncomeCategoryId="";

        if (!SettingsDataManager::isIncomeCategoryAssignedToUser($newIncomeCategory) && $newIncomeCategory !="") {
            SettingsDataManager::addNewIncomeCategory($newIncomeCategory);
            $newIncomeCategoryId = SettingsDataManager::getIncomeCategoryId($newIncomeCategory);
        }
        echo json_encode($newIncomeCategoryId);
    }

    public function tryDeleteUserIncomeCategoryAction()
    {
        $incomeCategoryIdToDelete = $_POST['incomeCategoryId'];
        $whetherIncomeCategoryIsUsedByUser = SettingsDataManager::whetherIncomeCategoryIsUsedByUser($incomeCategoryIdToDelete);

        if (!$whetherIncomeCategoryIsUsedByUser) {
            SettingsDataManager::deleteUserIncomeCategory($incomeCategoryIdToDelete);
        }

        echo json_encode($whetherIncomeCategoryIsUsedByUser);
    }

    public function deleteUserIncomeCategoryWithIncomesAction()
    {
        $incomeCategoryIdToDelete = $_POST['incomeCategoryId'];

        SettingsDataManager::deleteUserIncomesInSelectedCategory($incomeCategoryIdToDelete);
        SettingsDataManager::deleteUserIncomeCategory($incomeCategoryIdToDelete);
    }

    public function deleteUserIncomeCategoryWithMoveIncomesToAnotherCategoryAction()
    {
        $incomeCategoryIdToDelete = $_POST['incomeCategoryId'];
        $categoryToCarryOverIncomes = $_POST['categoryToCarryOverIncomes'];

        SettingsDataManager::moveUserIncomesFromCategory($incomeCategoryIdToDelete, $categoryToCarryOverIncomes);
        SettingsDataManager::deleteUserIncomeCategory($incomeCategoryIdToDelete);
    }


    public function getUserIncomeCategoriesAction()
    {
        $userIncomeCategories = SettingsDataManager::getUserIncomeCategories();

        echo json_encode($userIncomeCategories);
    }

    public function getUserExpenseCategoriesAction()
    {
        $userExpenseCategories = SettingsDataManager::getUserExpenseCategories();

        echo json_encode($userExpenseCategories);
    }

    public function updateIncomeCategory()
    {
        SettingsDataManager::updateUserIncomeCategory($_POST);
    }



}
