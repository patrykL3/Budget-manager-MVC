<?php

namespace App\Controllers;

use \Core\View;

use \App\Models\ExpenseDataManager;
use \App\Flash;

class Expense extends Authentication_login
{
    private $expenseDataManager;

    public function __construct()
    {
        $this->expenseDataManager = new ExpenseDataManager($_POST);
    }


    public function openAction()
    {
        View::renderTemplate('expense/open.html', ['data' => $this->expenseDataManager]);
    }


    public function createAction()
    {
        if ($this->expenseDataManager->addExpense()) {
            Flash::addMessage('Dodano nowy wydatek');
            $this->redirect('/expense');
            exit;
        } else {
            View::renderTemplate('Expense/open.html', ['data' => $this->expenseDataManager]);
        }
    }
}
