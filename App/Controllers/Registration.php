<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

class Registration extends Authentication_logout
{
    public function openAction()
    {
        View::renderTemplate('Registration/open.html');
    }


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
