<?php

namespace App;

use App\Models\User;
/**
 * Authentication
 *
 * PHP version 7.0
 */
class Authentication
{
    /**
     * Login the user
     *
     * @param User $user The user model
     *
     * @return void
     */
    public static function login($user)
    {
        session_regenerate_id(true);

        $_SESSION['logged_user_id'] = $user->user_id;
    }

    /**
     * Logout the user
     *
     * @return void
     */
    public static function logout()
    {
      // Unset all of the session variables
      $_SESSION = [];

      // Delete the session cookie
      if (ini_get('session.use_cookies')) {
          $params = session_get_cookie_params();

          setcookie(
              session_name(),
              '',
              time() - 42000,
              $params['path'],
              $params['domain'],
              $params['secure'],
              $params['httponly']
          );
      }

      // Finally destroy the session
      session_destroy();
    }

    /**
     * Get the current logged-in user, from the session or the remember-me cookie
     *
     * @return mixed The user model or null if not logged in
     */
    public static function getLoggedUser()
    {
        if (isset($_SESSION['logged_user_id'])) {
            return User::findByID($_SESSION['logged_user_id']);
        }
    }



}
