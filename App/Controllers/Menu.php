<?php

namespace App\Controllers;

use \Core\View;

class Menu extends Authentication_login
{
    public function openAction()
    {
        View::renderTemplate('menu/open.html');
    }
}
