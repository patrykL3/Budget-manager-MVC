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
class Login extends Authentication_logout
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

    /**
     * Log in a user
     *
     * @return void
     */
    public function createAction()
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);

        $remember_me = isset($_POST['remember_me']);

        if ($user) {
            Authentication::login($user, $remember_me);

            $this->redirect('/menu');

        } else {
            View::renderTemplate('Login/open.html', [
                'email' => $_POST['email'],
                'remember_me' => $remember_me,
                'dataIncorrect'=> 'Nieporpawne dane logowania.'
            ]);
        }
    }
}
