<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Authentication;

/**
 * Registration controller
 *
 * PHP version 7.0
 */
class Logout extends \Core\Controller
{
    /**
     * Log out a user
     *
     * @return void
     */
    public function destroyAction()
    {
        Authentication::logout();

        $this->redirect('/');
    }
}
