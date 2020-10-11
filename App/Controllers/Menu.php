<?php

namespace App\Controllers;

use \Core\View;

//use \App\Authentication;

/**
 * Registration controller
 *
 * PHP version 7.0
 */
class Menu extends Authentication_login
{
    /**
     * Show the Registration page
     *
     * @return void
     */
    public function openAction()
    {
        View::renderTemplate('menu/open.html');
    }
}
