<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

/**
 * Registration controller
 *
 * PHP version 7.0
 */
class Login extends \Core\Controller
{
    /**
     * Show the Registration page
     *
     * @return void
     */
    public function openAction()
    {
        View::renderTemplate('Login/open.html');
    }

}
