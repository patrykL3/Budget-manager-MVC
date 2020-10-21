<?php

namespace App\Controllers;

use \Core\View;

use \App\Models\IncomeDataManager;
use \App\Flash;

class Income extends Authentication_login
{
    private $incomeDataManager;

    public function __construct()
    {
        $this->incomeDataManager = new IncomeDataManager($_POST);
    }


    public function openAction()
    {
        View::renderTemplate('income/open.html', ['data' => $this->incomeDataManager]);
    }


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
}
