<?php

namespace App\Models;

use PDO;
use \App\Token;

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
    public $expiry_timestamp;

    //private static $database;

    /**
     * Class constructor
     *
     * @param array $data  Initial property values
     *
     * @return void
     */
    public function __construct($data = [])
    {
        //self::$database = static::getDB();
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
        $this->validateUserData();

        if (empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $this->addUser($password_hash);
            $idAddedUser = $this->getUserId($this->login);
            $idDefaultPaymentMethods = $this->getIdDefaultPaymentMethods();
            $idDefaultExpenses = $this->getIdDefaultExpenses();
            $idDefaultIncomes =  $this->getIdDefaultIncomes();

            foreach ($idDefaultPaymentMethods as $onceOfId) {
                $this->addPaymentMethod($onceOfId['payment_category_id'], $idAddedUser['user_id']);
            }
            foreach ($idDefaultExpenses as $onceOfId) {
                $this->addExpenseCategory($onceOfId['expense_category_id'], $idAddedUser['user_id']);
            }
            foreach ($idDefaultIncomes as $onceOfId) {
                $this->addIncomeCategory($onceOfId['income_category_id'], $idAddedUser['user_id']);
            }
            return true;
        }
        return false;
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validateUserData()
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
        if (static::loginExists($this->login)) {
            $this->errors['loginTaken'] = 'Login jest zajęty';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors['invalidEmail'] = 'Niepoprawny adres email';
        }
        if (static::emailExists($this->email)) {
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

    protected function addUser($password_hash)
    {
        $sql = 'INSERT INTO users (login, name, surname, email, password)
                VALUES (:login, :name, :surname, :email, :password_hash)';

        $database = static::getDB();
        $stmt = $database->prepare($sql);
        $stmt->bindValue(':login', $this->login, PDO::PARAM_STR);
        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindValue(':surname', $this->surname, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
        $stmt->execute();
    }

    private function getUserId($login)
    {
        $database = static::getDB();
        $userIdQuery = $database->prepare('SELECT user_id FROM users WHERE login = :login');
        $userIdQuery->bindValue(':login', $login, PDO::PARAM_STR);
        $userIdQuery->execute();
        return $userIdQuery->fetch();
    }

    private function getIdDefaultPaymentMethods()
    {
        $database = static::getDB();
        $idNumbersDefaultPaymentMethodsQuery = $database->prepare('SELECT payment_category_id FROM payments_categories WHERE default_type = 1');
        $idNumbersDefaultPaymentMethodsQuery->execute();
        return $idNumbersDefaultPaymentMethodsQuery->fetchAll();
    }

    private function getIdDefaultExpenses()
    {
        $database = static::getDB();
        $idNumbersDefaultExpensesQuery = $database->prepare('SELECT expense_category_id FROM expenses_categories WHERE default_type = 1');
        $idNumbersDefaultExpensesQuery->execute();
        return $idNumbersDefaultExpensesQuery->fetchAll();
    }

    private function getIdDefaultIncomes()
    {
        $database = static::getDB();
        $idNumbersDefaultIncomesQuery = $database->prepare('SELECT income_category_id FROM incomes_categories WHERE default_type = 1');
        $idNumbersDefaultIncomesQuery->execute();
        return $idNumbersDefaultIncomesQuery->fetchAll();
    }

    private function addPaymentMethod($paymentMethodId, $userId)
    {
        $database = static::getDB();
        $addPaymentMethodQuery = $database->prepare('INSERT INTO users_categories_payments VALUES (:user_id, :payment_category_id)');
        $addPaymentMethodQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $addPaymentMethodQuery->bindValue(':payment_category_id', $paymentMethodId, PDO::PARAM_INT);
        $addPaymentMethodQuery->execute();
    }

    private function addExpenseCategory($expenseCategoryId, $userId)
    {
        $database = static::getDB();
        $addExpenseQuery = $database->prepare('INSERT INTO users_categories_expenses VALUES (:user_id, :expense_category_id)');
        $addExpenseQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $addExpenseQuery->bindValue(':expense_category_id', $expenseCategoryId, PDO::PARAM_INT);
        $addExpenseQuery->execute();
    }

    private function addIncomeCategory($incomeCategoryId, $userId)
    {
        $database = static::getDB();
        $addIncomeQuery = $database->prepare('INSERT INTO users_categories_incomes VALUES (:user_id, :income_category_id)');
        $addIncomeQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $addIncomeQuery->bindValue(':income_category_id', $incomeCategoryId, PDO::PARAM_INT);
        $addIncomeQuery->execute();
    }

    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     *
     * @return boolean  True if a record already exists with the specified email, false otherwise
     */
    public static function emailExists($email)
    {
        return static::findByEmail($email) !== false;
    }

    /**
     * See if a user record already exists with the specified login
     *
     * @param string $login login address to search for
     *
     * @return boolean  True if a record already exists with the specified login, false otherwise
     */
    public static function loginExists($login)
    {
        $sql = 'SELECT * FROM users WHERE login = :login';

        $database = static::getDB();
        $stmt = $database->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    /**
     * Find a user model by email address
     *
     * @param string $email email address to search for
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $database = static::getDB();
        $stmt = $database->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
 * Authenticate a user by email and password.
 *
 * @param string $email email address
 * @param string $password password
 *
 * @return mixed  The user object or false if authentication fails
 */
    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }

        $_SESSION['dataIncorrect'] = 'Nieporpawne dane.';
        return false;
    }

    /**
     * Find a user model by ID
     *
     * @param string $id The user ID
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE user_id = :id';
        $database = static::getDB();

        $stmt = $database->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Remember the login by inserting a new unique token into the remembered_logins table
     * for this user record
     *
     * @return boolean  True if the login was remembered successfully, false otherwise
     */
    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                 VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }
}
