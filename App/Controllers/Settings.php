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
        $updateSuccess = SettingsDataManager::updateUserExpenseCategory($_POST);
        //$updateSuccess = "a";
        //echo json_encode($updateSuccess);
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
        ////SettingsDataManager::deleteUserExpenseCategory($expenseCategoryIdToDelete);
    }

    public function deleteUserExpenseCategoryWithMoveExpensesToAnotherCategoryAction()
    {
        $expenseCategoryIdToDelete = $_POST['expenseCategoryId'];
        $categoryToCarryOverExpenses = $_POST['categoryToCarryOverExpenses'];

        SettingsDataManager::moveUserExpensesFromSelectedCategory($expenseCategoryIdToDelete, $categoryToCarryOverExpenses);
        ////SettingsDataManager::deleteUserExpenseCategory($expenseCategoryIdToDelete);
    }

    public function addExpenseCategoryAction()
    {
        $newExpenseCategory = filter_input(INPUT_POST, 'newExpenseCategory');
        $newExpenseCategoryId="";

        if (!SettingsDataManager::isCategoryAssignedToUser($newExpenseCategory) && $newExpenseCategory !="") {
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
         //SettingsDataManager::updateUserData($_POST);
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












    /*
        public function createAction()
        {
            if ($this->incomeDataManager->addIncome()) {
                Flash::addMessage('Dodano nowy przychód');
                $this->redirect('/income');
                exit;
            } else {
                View::renderTemplate('Income/open.html', ['data' => $this->incomeDataManager]);
            }
        }
        */
}
