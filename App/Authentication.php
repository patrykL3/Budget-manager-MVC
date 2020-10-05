<?php

namespace App;

use App\Models\User;
use App\Models\RememberedLogin;

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
    public static function login($user, $remember_me)
    {
        session_regenerate_id(true);

        $_SESSION['logged_user_id'] = $user->user_id;

        if ($remember_me) {
            if ($user->rememberLogin()) {
                setcookie('remember_me', $user->remember_token, $user->expiry_timestamp, '/');
            }
        }
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

        static::forgetLogin();
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
        } else {
            return static::loginFromRememberCookie();
        }
    }


    /**
     * Login the user from a remembered login cookie
     *
     * @return mixed The user model if login cookie found; null otherwise
     */
    protected static function loginFromRememberCookie()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie) {
            $remembered_login = RememberedLogin::findByToken($cookie);

            if ($remembered_login && ! $remembered_login->hasExpired()) {
                $user = $remembered_login->getUser();

                static::login($user, false);

                return $user;
            }
        }
    }


    /**
     * Forget the remembered login, if present
     *
     * @return void
     */
    protected static function forgetLogin()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie) {
            $remembered_login = RememberedLogin::findByToken($cookie);

            if ($remembered_login) {
                $remembered_login->delete();
            }

            setcookie('remember_me', '', time() - 3600);  // set to expire in the past
        }
    }
}
