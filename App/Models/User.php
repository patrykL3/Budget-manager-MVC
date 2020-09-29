<?php

namespace App\Models;

use PDO;

/**
 * Example user model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
{

    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];

    /**
     * Class constructor
     *
     * @param array $data  Initial property values
     *
     * @return void
     */
    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    /**
     * Save the user model with the current property values
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        if (empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'INSERT INTO users (login, name, surname, email, password)
                VALUES (:login, :name, :surname, :email, :password_hash)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':login', $this->login, PDO::PARAM_STR);
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':surname', $this->surname, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validate()
    {
        // Name
        if ($this->name == '') {
            $this->errors['nameRequired'] = 'Wprowadź imię!';
        }

        // Surname
        if ($this->surname == '') {
            $this->errors['surnameRequired'] = 'Wprowadź nazwisko!';
        }

        // Login
        if ($this->login == '') {
            $this->errors['loginRequired'] = 'Wprowadź login!';
        }
        if ($this->loginExists($this->login)) {
            $this->errors['loginTaken'] = 'Login jest zajęty';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors['invalidEmail'] = 'Niepoprawny adres email';
        }
        if ($this->emailExists($this->email)) {
            $this->errors['emailTaken'] = 'Adres email jest zajęty';
        }

        // Password
        if ($this->password != $this->password_confirmation) {
            $this->errors['passwordConfirmation'] = 'Hasła nie są identyczne';
        }
        if (strlen($this->password) < 6 || preg_match('/.*[a-z]+.*/i', $this->password) == 0 || preg_match('/.*\d+.*/i', $this->password) == 0) {
            $this->errors['password'] = 'Hasło musi zawierać przynajmniej 6 znaków, jedną cyfrę i literę';
        }
    }


    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     *
     * @return boolean  True if a record already exists with the specified email, false otherwise
     */
    protected function emailExists($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    /**
     * See if a user record already exists with the specified login
     *
     * @param string $login login address to search for
     *
     * @return boolean  True if a record already exists with the specified login, false otherwise
     */
    protected function loginExists($login)
    {
        $sql = 'SELECT * FROM users WHERE login = :login';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch() !== false;
    }
}
