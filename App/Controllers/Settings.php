<?php

namespace App\Controllers;

use \Core\View;

use \App\Models\SettingsDataManager;

//use \App\Flash;

class Settings extends Authentication_login
{
    //private $incomeDataManager;

    public function __construct()
    {
        //$this->settingsDataManager = new settingsDataManager($_POST);
    }


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

    public function getDataToEditUserDataAction()
    {
        $userData = SettingsDataManager::getUserData();

        echo json_encode($userData);
    }

    public function updateUserDataAction()
    {
        SettingsDataManager::updateUserData($_POST);
    }

    public function updateUserPasswordAction()
    {
        $error = false;

        if (!SettingsDataManager::updateUserPassword($_POST)) {
            $error = true;
        }
        echo json_encode($error);
    }












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
