<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Authentication;

class Logout extends \Core\Controller
{
    public function destroyAction()
    {
        Authentication::logout();

        $this->redirect('/');
    }
}
