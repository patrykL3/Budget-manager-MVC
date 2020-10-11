<?php

namespace App\Controllers;

use \Core\View;

//use \App\Models\ExpenseDataManager;
use \App\Flash;
/**
 * Income controller
 *
 * PHP version 7.0
 */
class Expense extends Authentication_login
{
    //private $expenseDataManager;

    public function __construct()
    {
        //$this->expenseDataManager = new ExpenseDataManager($_POST);
    }


    /**
     * Show the Expense page
     *
     * @return void
     */
    public function openAction()
    {
        View::renderTemplate('expense/open.html');//, ['data' => $this->expenseDataManager]);
    }

    /**
 * Create a new expense
 *
 * @return void
 */
    public function createAction()
    {/*
        if ($this->expenseDataManager->addIncome()) {
            Flash::addMessage('Dodano nowy wydatek');
            $this->redirect('/expense');
            exit;
        } else {
            View::renderTemplate('Expense/open.html', ['data' => $this->incomeDataManager]);
        }*/
    }
}
