<?php

namespace App\Controllers;

use \App\Models\User;

class Account extends \Core\Controller
{
    public function validateEmailAction()
    {
        $is_valid = ! User::emailExists($_GET['email']);

        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }

    public function validateLoginAction()
    {
        $is_valid = ! User::loginExists($_GET['login']);

        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }

    public function validateEditedEmailAction()
    {
        $is_valid = ! (User::emailExists($_GET['email']) && !User::isEmailCurrentUser($_GET['email']));

        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }
}
