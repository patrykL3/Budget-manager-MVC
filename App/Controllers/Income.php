<?php

namespace App\Controllers;

use \Core\View;

use \App\Models\IncomeDataManager;
use \App\Flash;
/**
 * Income controller
 *
 * PHP version 7.0
 */
class Income extends Authentication_login
{
    private $incomeDataManager;

    public function __construct()
    {
        $this->incomeDataManager = new IncomeDataManager($_POST);
    }


    /**
     * Show the Income page
     *
     * @return void
     */
    public function openAction()
    {
        View::renderTemplate('income/open.html', ['data' => $this->incomeDataManager]);
    }

    /**
 * Create a new income
 *
 * @return void
 */
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
