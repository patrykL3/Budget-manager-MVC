<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Authentication;

class Login extends Authentication_logout
{
    public function openAction()
    {
        View::renderTemplate('Login/open.html');
    }


    public function createAction()
    {
        $user = User::authenticate($_POST);
        $remember_me = isset($_POST['remember_me']);

        if ($user) {
            Authentication::login($user, $remember_me);

            $this->redirect('/menu');
        } else {
            View::renderTemplate('Login/open.html', [
                'email_or_login' => $_POST['email_or_login'],
                'remember_me' => $remember_me,
                'dataIncorrect'=> 'Nieporpawne dane logowania.'
            ]);
        }
    }
}
