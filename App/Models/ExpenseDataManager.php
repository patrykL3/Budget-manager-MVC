<?php

namespace App\Models;

use PDO;
use \App\Authentication;
use \App\Date;
use \App\AuxiliaryFunctions;

class ExpenseDataManager extends \Core\Model
{
    public $errors = [];
    public $userExpenseCategories;
    public $userPaymentCategories;
    private $loggedUser;


    public function __construct($data = [])
    {
        $this->loggedUser = Authentication::getLoggedUser();
        $this->userExpenseCategories = $this->getUserExpenseCategories($this->loggedUser->user_id);
        $this->userPaymentCategories = $this->getUserPaymentCategories($this->loggedUser->user_id);

        foreach ($data as $key => $value) {
            $value = filter_input(INPUT_POST, $key);
            $this->$key = $value;
        };
    }


    public function addExpense()
    {
        $this->validateExpenseData();

        if (empty($this->errors)) {
            $this->saveExpenseToExpensesTabel();
            $this->assignExpenseToUser();
            return true;
        }
        return false;
    }


    private function validateExpenseData()
    {
        // Amount
        $this->amount = str_replace(',', '.', $this->amount);
        $this->amount = filter_var($this->amount, FILTER_VALIDATE_FLOAT);
        if (empty($this->amount)) {
            $this->errors['amountRequired'] = "Wprowadź poprawną kwotę!";
        }

        // Date
        if (!Date::isRealDate($this->date)) {
            $this->errors['dateRequired'] = 'Wprowadź poprawną datę!';
        }

        // Expense category
        if ($this->expense_category == 'Wybierz kategorię' || empty($this->expense_category)) {
            $this->errors['expenseCategoryRequired'] = 'Wprowadź kategorię wydatku!';
        }

        // Payment category
        if ($this->payment_category == 'Wybierz kategorię' || empty($this->payment_category)) {
            $this->errors['paymentCategoryRequired'] = 'Wprowadź metodę płatności!';
        }
    }


    private function getSelectedExpenseCategoryId()
    {
        foreach ($this->userExpenseCategories as $onceOfCategories) {
            if ($onceOfCategories['category_type'] === $this->expense_category) {
                return $onceOfCategories['expense_category_id'];
            }
        }
    }

    private function getSelectedPaymentCategoryId()
    {
        foreach ($this->userPaymentCategories as $onceOfCategories) {
            if ($onceOfCategories['payment_category_type'] === $this->payment_category) {
                return $onceOfCategories['payment_category_id'];
            }
        }
    }

    private function saveExpenseToExpensesTabel()
    {
        $selectedExpenseCategoryId = $this->getSelectedExpenseCategoryId();
        $selectedPaymentCategoryId = $this->getSelectedPaymentCategoryId();

        $database = static::getDB();

        $addExpense = $database->prepare('INSERT INTO expenses VALUES (NULL, :expense_category_id, :payment_category_id, :amount, :date_of_expense, :expense_comment)');
        $addExpense->bindValue(':expense_category_id', $selectedExpenseCategoryId, PDO::PARAM_INT);
        $addExpense->bindValue(':payment_category_id', $selectedPaymentCategoryId, PDO::PARAM_INT);
        $addExpense->bindValue(':amount', $this->amount, PDO::PARAM_STR);
        $addExpense->bindValue(':date_of_expense', $this->date, PDO::PARAM_STR);
        $addExpense->bindValue(':expense_comment', $this->comment, PDO::PARAM_STR);
        $addExpense->execute();
    }

    private function getNewExpenseId()
    {
        $database = static::getDB();

        $newExpenseIdQuery = $database->prepare('SELECT MAX(expense_id) FROM expenses');
        $newExpenseIdQuery->execute();
        $newExpenseId = $newExpenseIdQuery->fetch();

        return $newExpenseId[0];
    }

    private function assignExpenseToUser()
    {
        $newExpenseId = $this->getNewExpenseId();

        $database = static::getDB();

        $assignIncomeToUserQuery = $database->prepare('INSERT INTO users_expenses VALUES (:user_id, :expense_id)');
        $assignIncomeToUserQuery->bindValue(':user_id', $this->loggedUser->user_id, PDO::PARAM_INT);
        $assignIncomeToUserQuery->bindValue(':expense_id', $newExpenseId, PDO::PARAM_INT);
        $assignIncomeToUserQuery->execute();
    }

    public static function getUserExpenseCategories($userId)
    {
        $database = static::getDB();

        $userExpenseQuery = $database->prepare(
            'SELECT *
            FROM users_categories_expenses AS uce
            INNER JOIN expenses_categories AS ec
            ON uce.expense_category_id = ec.expense_category_id
            WHERE
            uce.user_id= :user_id'
        );
        $userExpenseQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $userExpenseQuery->execute();

        return $userExpenseQuery->fetchAll();
    }

    public static function getUserPaymentCategories($userId)
    {
        $database = static::getDB();

        $userPaymentQuery = $database->prepare(
            'SELECT payment_category_type, ucp.payment_category_id
            FROM users_categories_payments AS ucp
            INNER JOIN payments_categories AS pc
            ON ucp.payment_category_id = pc.payment_category_id
            WHERE
            ucp.user_id= :user_id'
        );
        $userPaymentQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $userPaymentQuery->execute();

        return $userPaymentQuery->fetchAll();
    }

    public function getUserExpensesFromPeriod($period, $balanceStartDate, $balanceEndDate)
    {
        $database = static::getDB();
        $mainPartGettingExpensesQuery =
            'SELECT e.expense_id, pc.payment_category_type, ec.category_type, e.amount, e.date_of_expense, e.expense_comment
            FROM expenses AS e
            INNER JOIN expenses_categories AS ec
            ON e.expense_category_id = ec.expense_category_id
            INNER JOIN payments_categories AS pc
            ON e.payment_category_id = pc.payment_category_id
            INNER JOIN users_expenses AS ue
            ON e.expense_id = ue.expense_id
            WHERE
            ue.user_id = :user_id
            ';

        $expenseTimePartOfTheQuery = $this->getExpenseTimePartOfTheQuery($period);

        $userExpensesFromPeriodQuery = $database->prepare($mainPartGettingExpensesQuery.$expenseTimePartOfTheQuery.' ORDER BY e.date_of_expense');
        $userExpensesFromPeriodQuery->bindValue(':user_id', $this->loggedUser->user_id, PDO::PARAM_INT);
        if ($period != 'custom') {
            $userExpensesFromPeriodQuery->bindValue(':currentDate', Date::getCurrentDate(), PDO::PARAM_STR);
        } else {
            $userExpensesFromPeriodQuery->bindValue(':balanceStartDate', $balanceStartDate, PDO::PARAM_STR);
            $userExpensesFromPeriodQuery->bindValue(':balanceEndDate', $balanceEndDate, PDO::PARAM_STR);
        }
        $userExpensesFromPeriodQuery->execute();
        return $userExpensesFromPeriodQuery->fetchAll();
    }

    private function getExpenseTimePartOfTheQuery($period)
    {
        if ($period == 'currentMonth') {
            $expenseTimePartOfTheQuery = 'AND MONTH(e.date_of_expense) = MONTH(:currentDate)';
        } elseif ($period == 'previousMonth') {
            $expenseTimePartOfTheQuery = 'AND MONTH(e.date_of_expense) = MONTH(:currentDate)-1';
        } elseif ($period == 'currentYear') {
            $expenseTimePartOfTheQuery = 'AND YEAR(e.date_of_expense) = YEAR(:currentDate)';
        } elseif ($period == 'custom') {
            $expenseTimePartOfTheQuery = 'AND e.date_of_expense BETWEEN :balanceStartDate AND :balanceEndDate';
        }

        return $expenseTimePartOfTheQuery;
    }


    public static function getExpenseData($expenseId)
    {
        $database = static::getDB();

        $userExpenseToEditQuery = $database->prepare(
            "SELECT ec.category_type, pc.payment_category_type, e.amount, e.date_of_expense, e.expense_comment
            FROM expenses AS e
            INNER JOIN expenses_categories AS ec
            ON e.expense_category_id = ec.expense_category_id
            INNER JOIN payments_categories AS pc
            ON pc.payment_category_id = e.payment_category_id
            WHERE
            e.expense_id = :expense_id_to_edit;"
        );

        $userExpenseToEditQuery->bindValue(':expense_id_to_edit', $expenseId, PDO::PARAM_INT);
        $userExpenseToEditQuery->execute();
        return $userExpenseToEditQuery->fetch();
    }

    public static function updateExpense($data = [])
    {
        if (ExpenseDataManager::validateExpenseEditData($data)) {
            $database = static::getDB();
            $selectedCategoryId = ExpenseDataManager::getSelectedCategoryId($data['category']);
            $selectedPaymentCategoryId = ExpenseDataManager::getSelectedPaymentCategoryIdToEdit($data['payment_category']);

            $editExpense = $database->prepare(
                'UPDATE expenses
                SET expense_category_id = :expense_category_id, payment_category_id = :payment_category_id, amount = :amount, date_of_expense = :date_of_expense, expense_comment = :expense_comment
                WHERE expense_id = :expense_id;
                '
            );
            $editExpense->bindValue(':expense_id', $data['expense_id'], PDO::PARAM_INT);
            $editExpense->bindValue(':expense_category_id', $selectedCategoryId, PDO::PARAM_INT);
            $editExpense->bindValue(':payment_category_id', $selectedPaymentCategoryId, PDO::PARAM_INT);
            $editExpense->bindValue(':amount', $data['amount'], PDO::PARAM_STR);
            $editExpense->bindValue(':date_of_expense', $data['date'], PDO::PARAM_STR);
            $editExpense->bindValue(':expense_comment', $data['comment'], PDO::PARAM_STR);
            $editExpense->execute();
        }
    }

    public static function getSelectedCategoryId($selectedCategory)
    {
        $LoggedUserId = Authentication::getLoggedUser()->user_id;
        $userExpenseCategories = ExpenseDataManager::getUserExpenseCategories($LoggedUserId);

        foreach ($userExpenseCategories as $onceOfCategories) {
            if ($onceOfCategories['category_type'] === $selectedCategory) {
                return $onceOfCategories['expense_category_id'];
            }
        }
    }

    public static function getSelectedPaymentCategoryIdToEdit($selectedPaymentCategory)
    {
        $LoggedUserId = Authentication::getLoggedUser()->user_id;
        $userPaymentCategories = ExpenseDataManager::getUserPaymentCategories($LoggedUserId);

        foreach ($userPaymentCategories as $onceOfCategories) {
            if ($onceOfCategories['payment_category_type'] === $selectedPaymentCategory) {
                return $onceOfCategories['payment_category_id'];
            }
        }
    }


    private static function validateExpenseEditData($data = [])
    {
        $data['expense_id'] = filter_input(INPUT_POST, 'expense_id');
        $data['expense_id'] = filter_var($data['expense_id'], FILTER_VALIDATE_INT);
        if (empty($data['expense_id'])) {
            return false;
        }

        // Amount
        $data['amount'] = filter_input(INPUT_POST, 'amount');
        $data['amount'] = str_replace(',', '.', $data['amount']);
        $data['amount'] = filter_var($data['amount'], FILTER_VALIDATE_FLOAT);
        if (empty($data['amount'])) {
            return false;
        }

        // Date
        $data['date'] = filter_input(INPUT_POST, 'date');
        if (!Date::isRealDate($data['date'])) {
            return false;
        }

        // Category
        $data['category'] = filter_input(INPUT_POST, 'category');
        if (empty($data['category'])) {
            return false;
        }

        // Payment category
        $data['payment_category'] = filter_input(INPUT_POST, 'payment_category');
        if (empty($data['payment_category'])) {
            return false;
        }

        return true;
    }


    public static function deleteExpense($expenseIdToDelete)
    {
        $expenseIdToDelete = filter_var($expenseIdToDelete, FILTER_VALIDATE_INT);
        $database = static::getDB();

        try {
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec("DELETE FROM expenses WHERE expense_id=$expenseIdToDelete");
            $database->exec("DELETE FROM users_expenses WHERE expense_id=$expenseIdToDelete");
        } catch (PDOException $e) {
            echo "<br>".$e->getMessage();
        };
    }

    public static function deleteUserExpensesInSelectedCategory($expenseCategoryId)
    {
        $expenseCategoryId = filter_var($expenseCategoryId, FILTER_VALIDATE_INT);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        try {
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec(
                "DELETE FROM expenses, users_expenses
                USING expenses
                INNER JOIN users_expenses
                ON expenses.expense_id = users_expenses.expense_id
                WHERE expense_category_id=$expenseCategoryId AND user_id=$loggedUser->user_id"
            );
        } catch (PDOException $e) {
            echo "<br>".$e->getMessage();
        };
    }

    public static function deleteUserExpensesWithPaymentCategory($paymentCategoryId)
    {
        $paymentCategoryId = filter_var($paymentCategoryId, FILTER_VALIDATE_INT);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        try {
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec(
                "DELETE FROM expenses, users_expenses
                USING expenses
                INNER JOIN users_expenses
                ON expenses.expense_id = users_expenses.expense_id
                WHERE payment_category_id=$paymentCategoryId AND user_id=$loggedUser->user_id"
            );
        } catch (PDOException $e) {
            echo "<br>".$e->getMessage();
        };
    }



    public static function moveUserExpensesFromCategory($oldCategoryId, $categoryToCarryOverExpenses)
    {
        $oldCategoryId = filter_var($oldCategoryId, FILTER_VALIDATE_INT);
        $categoryIdToCarryOverExpenses = ExpenseDataManager::getSelectedCategoryId($categoryToCarryOverExpenses);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $updateCategoriesExpenses = $database->prepare(
            'UPDATE expenses AS e
            INNER JOIN users_expenses AS ue
            ON e.expense_id = ue.expense_id
            SET e.expense_category_id = :new_expense_category_id
            WHERE e.expense_category_id = :previous_expense_category_id AND ue.user_id = :user_id;
            '
        );
        $updateCategoriesExpenses->bindValue(':new_expense_category_id', $categoryIdToCarryOverExpenses, PDO::PARAM_INT);
        $updateCategoriesExpenses->bindValue(':previous_expense_category_id', $oldCategoryId, PDO::PARAM_INT);
        $updateCategoriesExpenses->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $updateCategoriesExpenses->execute();
    }

    public static function movePaymentsToAnotherCategory($oldPaymentCategoryId, $categoryToCarryOverPayments)
    {
        $oldPaymentCategoryId = filter_var($oldPaymentCategoryId, FILTER_VALIDATE_INT);
        $categoryIdToCarryOverPayments = ExpenseDataManager::getPaymentCategoryId($categoryToCarryOverPayments);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $updateExpenses = $database->prepare(
            'UPDATE expenses AS e
            INNER JOIN users_expenses AS ue
            ON e.expense_id = ue.expense_id
            SET e.payment_category_id = :new_payment_category_id
            WHERE e.payment_category_id = :previous_payment_category_id AND ue.user_id = :user_id;
            '
        );
        $updateExpenses->bindValue(':new_payment_category_id', $categoryIdToCarryOverPayments, PDO::PARAM_INT);
        $updateExpenses->bindValue(':previous_payment_category_id', $oldPaymentCategoryId, PDO::PARAM_INT);
        $updateExpenses->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $updateExpenses->execute();
    }


    public static function getExpenseCategoryData($expenseCategoryId)
    {
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $userExpenseCategoryToEditQuery = $database->prepare(
            "SELECT ec.category_type, uce.killer_feature, uce.killer_feature_value
            FROM users_categories_expenses AS uce
            INNER JOIN expenses_categories AS ec
            ON uce.expense_category_id = ec.expense_category_id
            WHERE uce.user_id = :user_id AND ec.expense_category_id = :expense_category_id_to_edit;"
        );

        $userExpenseCategoryToEditQuery->bindValue(':expense_category_id_to_edit', $expenseCategoryId, PDO::PARAM_INT);
        $userExpenseCategoryToEditQuery->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $userExpenseCategoryToEditQuery->execute();
        return $userExpenseCategoryToEditQuery->fetch();
    }

    public static function updateUserExpenseCategory($data = [])
    {
        if (ExpenseDataManager::validateExpenseCategoryData($data)) {
            ($data['newKillerFeature'] === "true") ? $data['newKillerFeature'] = 1 : $data['newKillerFeature'] = 0;
            ExpenseDataManager::deleteUserExpenseCategory($data['expenseCategoryId']);
            ExpenseDataManager::addExpenseCategory($data['newCategoryType'], $data['newKillerFeature'], $data['newKillerFeatureValue']);
            ExpenseDataManager::moveUserExpensesFromCategory($data['expenseCategoryId'], $data['newCategoryType']);

            echo ExpenseDataManager::getExpenseCategoryId($data['newCategoryType']);
        }
    }

    public static function updateUserPaymentCategory($data = [])
    {
        if (ExpenseDataManager::validatePaymentCategoryData($data)) {
            ExpenseDataManager::deleteUserPaymentCategory($data['paymentCategoryId']);
            ExpenseDataManager::addPaymentCategory($data['newCategoryType']);
            ExpenseDataManager::movePaymentsToAnotherCategory($data['paymentCategoryId'], $data['newCategoryType']);

            echo ExpenseDataManager::getPaymentCategoryId($data['newCategoryType']);
        }
    }

    private static function validatePaymentCategoryData($data = [])
    {
        $loggedUser = Authentication::getLoggedUser();
        $userCurrentPaymentCategories = ExpenseDataManager::getUserPaymentCategories($loggedUser->user_id);

        // categoryId
        $data['paymentCategoryId'] = filter_input(INPUT_POST, 'paymentCategoryId');
        $data['paymentCategoryId'] = filter_var($data['paymentCategoryId'], FILTER_VALIDATE_INT);
        if (empty($data['paymentCategoryId'])) {
            return false;
        }

        // Category;
        $data['newCategoryType'] = filter_input(INPUT_POST, 'newCategoryType');
        if (empty($data['newCategoryType'])) {
            echo "empty";
            return false;
        }
        foreach ($userCurrentPaymentCategories as $onceOfCurrentCategories) {
            if ($onceOfCurrentCategories['payment_category_type'] === $data['newCategoryType'] && $onceOfCurrentCategories['payment_category_id'] != $data['paymentCategoryId']) {
                return false;
            }
        }
        return true;
    }


    private static function validateExpenseCategoryData($data = [])
    {
        $loggedUser = Authentication::getLoggedUser();
        $userCurrentExpenseCategories = ExpenseDataManager::getUserExpenseCategories($loggedUser->user_id);

        // categoryId
        $data['expenseCategoryId'] = filter_input(INPUT_POST, 'expenseCategoryId');
        $data['expenseCategoryId'] = filter_var($data['expenseCategoryId'], FILTER_VALIDATE_INT);
        if (empty($data['expenseCategoryId'])) {
            return false;
        }

        // Category;
        $data['newCategoryType'] = filter_input(INPUT_POST, 'newCategoryType');
        if (empty($data['newCategoryType'])) {
            return false;
        }
        foreach ($userCurrentExpenseCategories as $onceOfCurrentCategories) {
            if ($onceOfCurrentCategories['category_type'] === $data['newCategoryType'] && $onceOfCurrentCategories['expense_category_id'] != $data['expenseCategoryId']) {
                return false;
            }
        }

        // killerFeature
        $data['newKillerFeatureValue'] = filter_input(INPUT_POST, 'newKillerFeatureValue');
        $data['newKillerFeatureValue'] = str_replace(',', '.', $data['newKillerFeatureValue']);
        $data['newKillerFeatureValue'] = filter_var($data['newKillerFeatureValue'], FILTER_VALIDATE_FLOAT);
        if (empty($data['newKillerFeatureValue']) && $data['newKillerFeature'] != 'false') {
            return false;
        }

        return true;
    }


    public static function deleteUserExpenseCategory($expenseCategoryIdToDelete)
    {
        $expenseCategoryIdToDelete = filter_var($expenseCategoryIdToDelete, FILTER_VALIDATE_INT);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        try {
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec("DELETE FROM users_categories_expenses WHERE user_id=$loggedUser->user_id AND expense_category_id=$expenseCategoryIdToDelete");
        } catch (PDOException $e) {
            echo "<br>".$e->getMessage();
        };
    }

    public static function deleteUserPaymentCategory($paymentCategoryIdToDelete)
    {
        $paymentCategoryIdToDelete = filter_var($paymentCategoryIdToDelete, FILTER_VALIDATE_INT);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        try {
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec("DELETE FROM users_categories_payments WHERE user_id=$loggedUser->user_id AND payment_category_id=$paymentCategoryIdToDelete");
        } catch (PDOException $e) {
            echo "<br>".$e->getMessage();
        };
    }

    public static function getIdUsedUserExpenseCategories()
    {
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $idUsedUserExpenseCategoriesQuery = $database->prepare(
            "SELECT DISTINCT e.expense_category_id
            FROM expenses AS e
            INNER JOIN users_expenses AS ue
            ON e.expense_id = ue.expense_id
            WHERE ue.user_id = :user_id;"
        );
        $idUsedUserExpenseCategoriesQuery->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $idUsedUserExpenseCategoriesQuery->execute();
        return $idUsedUserExpenseCategoriesQuery->fetchAll();
    }

    public static function getIdUsedUserPaymentsCategories()
    {
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $idUsedUserPaymentCategoriesQuery = $database->prepare(
            "SELECT DISTINCT e.payment_category_id
            FROM expenses AS e
            INNER JOIN users_expenses AS ue
            ON e.expense_id = ue.expense_id
            WHERE ue.user_id = :user_id;"
        );
        $idUsedUserPaymentCategoriesQuery->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $idUsedUserPaymentCategoriesQuery->execute();
        return $idUsedUserPaymentCategoriesQuery->fetchAll();
    }


    public static function addExpenseCategory($newExpenseCategory, $killerFeature, $killerFeatureValue)
    {
        $newExpenseCategory = mb_strtolower($newExpenseCategory, 'UTF-8');
        $newExpenseCategory = AuxiliaryFunctions::ucfirstUtf8($newExpenseCategory);

        if (!ExpenseDataManager::isExpenseCategoryInTable($newExpenseCategory)) {
            ExpenseDataManager::saveExpenseCategoryToExpensesCategoriesTabel($newExpenseCategory);
        }
        ExpenseDataManager::assignExpenseCategoryToUser($newExpenseCategory, $killerFeature, $killerFeatureValue);
    }

    public static function addPaymentCategory($newPaymentCategory)
    {
        $newPaymentCategory = mb_strtolower($newPaymentCategory, 'UTF-8');
        $newPaymentCategory = AuxiliaryFunctions::ucfirstUtf8($newPaymentCategory);

        if (!ExpenseDataManager::isPaymentCategoryInTable($newPaymentCategory)) {
            ExpenseDataManager::savePaymentCategoryToPaymentsCategoriesTabel($newPaymentCategory);
        }
        ExpenseDataManager::assignPaymentCategoryToUser($newPaymentCategory);
    }


    public static function saveExpenseCategoryToExpensesCategoriesTabel($newExpenseCategory)
    {
        $database = static::getDB();

        $assignExpenseToUserQuery = $database->prepare('INSERT INTO expenses_categories (category_type, default_type) VALUES (:category_type, :default_type)');
        $assignExpenseToUserQuery->bindValue(':category_type', $newExpenseCategory, PDO::PARAM_STR);
        $assignExpenseToUserQuery->bindValue(':default_type', 0, PDO::PARAM_INT);
        $assignExpenseToUserQuery->execute();
    }

    public static function savePaymentCategoryToPaymentsCategoriesTabel($newPaymentCategory)
    {
        $database = static::getDB();

        $assignPaymentToUserQuery = $database->prepare('INSERT INTO payments_categories (payment_category_type, default_type) VALUES (:category_type, :default_type)');
        $assignPaymentToUserQuery->bindValue(':category_type', $newPaymentCategory, PDO::PARAM_STR);
        $assignPaymentToUserQuery->bindValue(':default_type', 0, PDO::PARAM_INT);
        $assignPaymentToUserQuery->execute();
    }

    public static function assignExpenseCategoryToUser($newExpenseCategory, $killerFeature, $killerFeatureValue)
    {
        $newExpenseCategoryId = ExpenseDataManager::getExpenseCategoryId($newExpenseCategory);
        $loggedUser = Authentication::getLoggedUser();

        $database = static::getDB();

        $assignExpenseCategoryToUserQuery = $database->prepare(
            'INSERT INTO users_categories_expenses (user_id, expense_category_id, killer_feature, killer_feature_value)
            VALUES (:user_id, :expense_category_id, :killer_feature, :killer_feature_value)'
        );

        $assignExpenseCategoryToUserQuery->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $assignExpenseCategoryToUserQuery->bindValue(':expense_category_id', $newExpenseCategoryId, PDO::PARAM_INT);
        $assignExpenseCategoryToUserQuery->bindValue(':killer_feature', $killerFeature, PDO::PARAM_INT);
        $assignExpenseCategoryToUserQuery->bindValue(':killer_feature_value', $killerFeatureValue, PDO::PARAM_INT);
        $assignExpenseCategoryToUserQuery->execute();
    }

    public static function assignPaymentCategoryToUser($newPaymentCategory)
    {
        $newPaymentCategoryId = ExpenseDataManager::getPaymentCategoryId($newPaymentCategory);
        $loggedUser = Authentication::getLoggedUser();

        $database = static::getDB();

        $assignPaymentCategoryToUserQuery = $database->prepare(
            'INSERT INTO users_categories_payments (user_id, payment_category_id)
            VALUES (:user_id, :payment_category_id)'
        );

        $assignPaymentCategoryToUserQuery->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $assignPaymentCategoryToUserQuery->bindValue(':payment_category_id', $newPaymentCategoryId, PDO::PARAM_INT);
        $assignPaymentCategoryToUserQuery->execute();
    }


    public static function getExpenseCategoryId($expenseCategory)
    {
        $database = static::getDB();

        $expenseCategoryIdQuery = $database->prepare('SELECT expense_category_id FROM expenses_categories WHERE category_type = :category_type');
        $expenseCategoryIdQuery->bindValue(':category_type', $expenseCategory, PDO::PARAM_STR);
        $expenseCategoryIdQuery->execute();
        $expenseCategoryId = $expenseCategoryIdQuery->fetch();

        return $expenseCategoryId[0];
    }

    public static function getPaymentCategoryId($paymentCategory)
    {
        $database = static::getDB();

        $paymentCategoryIdQuery = $database->prepare('SELECT payment_category_id FROM payments_categories WHERE payment_category_type = :category_type');
        $paymentCategoryIdQuery->bindValue(':category_type', $paymentCategory, PDO::PARAM_STR);
        $paymentCategoryIdQuery->execute();
        $paymentCategoryId = $paymentCategoryIdQuery->fetch();

        return $paymentCategoryId[0];
    }

    public static function isCategoryAssignedToUser($expenseCategory)
    {
        $loggedUser = Authentication::getLoggedUser();
        $userExpenseCategories = ExpenseDataManager::getUserExpenseCategories($loggedUser->user_id);

        $expenseCategory = mb_strtolower($expenseCategory, 'UTF-8');
        $expenseCategory = AuxiliaryFunctions::ucfirstUtf8($expenseCategory);

        foreach ($userExpenseCategories as $onceOfCategories) {
            if ($onceOfCategories['category_type'] === $expenseCategory) {
                return true;
            }
        }
        return false;
    }

    public static function isPaymentCategoryAssignedToUser($paymentCategory)
    {
        $loggedUser = Authentication::getLoggedUser();
        $userPaymentCategories = ExpenseDataManager::getUserPaymentCategories($loggedUser->user_id);

        $paymentCategory = mb_strtolower($paymentCategory, 'UTF-8');
        $paymentCategory = AuxiliaryFunctions::ucfirstUtf8($paymentCategory);

        foreach ($userPaymentCategories as $onceOfCategories) {
            if ($onceOfCategories['payment_category_type'] === $paymentCategory) {
                return true;
            }
        }
        return false;
    }

    public static function isExpenseCategoryInTable($expenseCategory)
    {
        $database = static::getDB();

        $isExpenseCategoryInTableQuery = $database->prepare('SELECT distinct 1 category_type FROM expenses_categories WHERE category_type = :category_type');
        $isExpenseCategoryInTableQuery->bindValue(':category_type', $expenseCategory, PDO::PARAM_STR);
        $isExpenseCategoryInTableQuery->execute();
        $isExpenseCategoryInTable = $isExpenseCategoryInTableQuery->fetch();

        return $isExpenseCategoryInTable;
    }

    public static function isPaymentCategoryInTable($paymentCategory)
    {
        $database = static::getDB();

        $isPaymentCategoryInTableQuery = $database->prepare('SELECT distinct 1 payment_category_type FROM payments_categories WHERE payment_category_type = :category_type');
        $isPaymentCategoryInTableQuery->bindValue(':category_type', $paymentCategory, PDO::PARAM_STR);
        $isPaymentCategoryInTableQuery->execute();
        $isPaymentCategoryInTable = $isPaymentCategoryInTableQuery->fetch();

        return $isPaymentCategoryInTable;
    }


    public static function getKillerData()
    {
        $loggedUser = Authentication::getLoggedUser();

        $killerData = ExpenseDataManager::getUserExpenseCategories($loggedUser->user_id);

        foreach ($killerData as &$categoryKillerData) {
            if ($categoryKillerData['killer_feature'] == 1) {
                $currentMonthExpense = ExpenseDataManager::getCurrentMonthExpense($categoryKillerData['expense_category_id']);
                if(!$currentMonthExpense) {
                    $currentMonthExpense = 0;
                }
                $categoryKillerData[ 'current_month_expense'] = $currentMonthExpense;
            }
        };
        return $killerData;
    }

    public static function getCurrentMonthExpense($expenseCategoryId)
    {
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $currentMonthExpenseQuery = $database->prepare('SELECT SUM(e.amount)
        FROM expenses AS e
        INNER JOIN users_expenses AS ue
        ON e.expense_id = ue.expense_id
        WHERE ue.user_id = :user_id AND e.expense_category_id = :expense_category_id AND MONTH(e.date_of_expense) = MONTH(:currentDate)');
        $currentMonthExpenseQuery->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $currentMonthExpenseQuery->bindValue(':expense_category_id', $expenseCategoryId, PDO::PARAM_INT);
        $currentMonthExpenseQuery->bindValue(':currentDate', Date::getCurrentDate(), PDO::PARAM_STR);
        $currentMonthExpenseQuery->execute();
        $currentMonthExpense = $currentMonthExpenseQuery->fetch();

        return $currentMonthExpense[0];
    }
}
