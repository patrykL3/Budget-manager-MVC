<?php

namespace App\Controllers;

use \Core\View;

class Menu extends Authentication_login
{
    public function openAction()
    {
        View::renderTemplate('Menu/open.html');
    }
}
