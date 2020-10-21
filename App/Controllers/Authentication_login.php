<?php

namespace App\Controllers;

abstract class Authentication_login extends \Core\Controller
{
    protected function before()
    {
        $this->requireLogin();
    }
}
