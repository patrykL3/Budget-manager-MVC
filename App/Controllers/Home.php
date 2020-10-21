<?php

namespace App\Controllers;

use \Core\View;

class Home extends Authentication_logout
{
    public function indexAction()
    {
        View::renderTemplate('Home/index.html');
    }
}
