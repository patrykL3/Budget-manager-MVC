<?php

namespace App\Controllers;

abstract class Authentication_logout extends \Core\Controller
{
    protected function before()
    {
        $this->requireLogout();
    }
}
