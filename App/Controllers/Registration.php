<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

/**
 * Registration controller
 *
 * PHP version 7.0
 */
class Registration extends Authentication_logout
{

    /**
     * Show the Registration page
     *
     * @return void
     */
    public function openAction()
    {
        View::renderTemplate('Registration/open.html');
    }

    /**
 * Registration a new user
 *
 * @return void
 */
    public function createAction()
    {
        $user = new User($_POST);

        if ($user->save()) {
            $this->redirect('/login');
            exit;
        } else {
            View::renderTemplate('Registration/open.html', ['user' => $user]);
        }
    }
}
