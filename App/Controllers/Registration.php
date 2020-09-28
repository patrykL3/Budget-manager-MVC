<?php

namespace App\Controllers;

use \Core\View;

/**
 * Registration controller
 *
 * PHP version 7.0
 */
class Registration extends \Core\Controller
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
}
