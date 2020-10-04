<?php

namespace App\Controllers;

/**
 * Authenticated base controller
 *
 * PHP version 7.0
 */
abstract class Authentication_logout extends \Core\Controller
{
    /**
     * Require the user to not be logged before giving access to all methods in the controller
     *
     * @return void
     */
    protected function before()
    {
        $this->requireLogout();
    }
}
