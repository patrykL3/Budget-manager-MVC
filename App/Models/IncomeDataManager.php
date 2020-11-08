<?php

namespace App\Models;

use PDO;
use \App\Authentication;
use \App\Date;
use \App\AuxiliaryFunctions;

class IncomeDataManager extends \Core\Model
{
    public $errors = [];
    public $userIncomeCategories;
    private $loggedUser;


    public function __construct($data = [])
    {
        $this->loggedUser = Authentication::getLoggedUser();
        $this->userIncomeCategories = $this->getUserIncomeCategories($this->loggedUser->user_id);

        foreach ($data as $key => $value) {
            $value = filter_input(INPUT_POST, $key);
            $this->$key = $value;
        };
    }


    public function addIncome()
    {
        $this->validateIncomeData();

        if (empty($this->errors)) {
            $this->saveIncomeToIncomesTabel();
            $this->assignIncomeToUser();
            return true;
        }
        return false;
    }


    public function validateIncomeData()
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

        // Category
        if ($this->category == 'Wybierz kategorię' || empty($this->category)) {
            $this->errors['categoryRequired'] = 'Wprowadź kategorię!';
        }
    }


    private static function getSelectedCategoryId($category)
    {
        $LoggedUserId = Authentication::getLoggedUser()->user_id;
        $userIncomeCategories = IncomeDataManager::getUserIncomeCategories($LoggedUserId);

        foreach ($userIncomeCategories as $onceOfCategories) {
            if ($onceOfCategories['category_type'] === $category) {
                return $onceOfCategories['income_category_id'];
            }
        }
    }

    private function saveIncomeToIncomesTabel()
    {
        $selectedCategoryId = $this->getSelectedCategoryId($this->category);

        $database = static::getDB();

        $addIncome = $database->prepare('INSERT INTO incomes VALUES (NULL, :income_category_id, :amount, :date_of_income, :income_comment)');
        $addIncome->bindValue(':income_category_id', $selectedCategoryId, PDO::PARAM_INT);
        $addIncome->bindValue(':amount', $this->amount, PDO::PARAM_STR);
        $addIncome->bindValue(':date_of_income', $this->date, PDO::PARAM_STR);
        $addIncome->bindValue(':income_comment', $this->comment, PDO::PARAM_STR);
        $addIncome->execute();
    }

    private function getNewIncomeId()
    {
        $database = static::getDB();

        $newIncomeIdQuery = $database->prepare('SELECT MAX(income_id) FROM incomes');
        $newIncomeIdQuery->execute();
        $newIncomeId = $newIncomeIdQuery->fetch();

        return $newIncomeId[0];
    }

    private function assignIncomeToUser()
    {
        $newIncomeId = $this->getNewIncomeId();

        $database = static::getDB();

        $assignIncomeToUserQuery = $database->prepare('INSERT INTO users_incomes VALUES (:user_id, :income_id)');
        $assignIncomeToUserQuery->bindValue(':user_id', $this->loggedUser->user_id, PDO::PARAM_INT);
        $assignIncomeToUserQuery->bindValue(':income_id', $newIncomeId, PDO::PARAM_INT);
        $assignIncomeToUserQuery->execute();
    }

    public static function getUserIncomeCategories($userId)
    {
        $database = static::getDB();

        $userIncomesQuery = $database->prepare(
            'SELECT category_type, uci.income_category_id
            FROM users_categories_incomes AS uci
            INNER JOIN incomes_categories AS ic
            ON uci.income_category_id = ic.income_category_id
            WHERE
            uci.user_id= :user_id'
        );
        $userIncomesQuery->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $userIncomesQuery->execute();

        return $userIncomesQuery->fetchAll();
    }

    public function getUserIncomesFromPeriod($period, $balanceStartDate, $balanceEndDate)
    {
        $database = static::getDB();
        $mainPartGettingIncomesQuery =
                'SELECT i.income_id, ic.category_type, i.amount, i.date_of_income, i.income_comment
                FROM incomes AS i
                INNER JOIN incomes_categories AS ic
                ON i.income_category_id = ic.income_category_id
                INNER JOIN users_incomes AS ui
                ON i.income_id = ui.income_id
                WHERE
                ui.user_id = :user_id
                ';
        $incomeTimePartOfTheQuery = $this->getIncomeTimePartOfTheQuery($period);

        $userIncomesFromPeriodQuery = $database->prepare($mainPartGettingIncomesQuery.$incomeTimePartOfTheQuery.' ORDER BY i.date_of_income');
        $userIncomesFromPeriodQuery->bindValue(':user_id', $this->loggedUser->user_id, PDO::PARAM_INT);
        if ($period != 'custom') {
            $userIncomesFromPeriodQuery->bindValue(':currentDate', Date::getCurrentDate(), PDO::PARAM_STR);
        } else {
            $userIncomesFromPeriodQuery->bindValue(':balanceStartDate', $balanceStartDate, PDO::PARAM_STR);
            $userIncomesFromPeriodQuery->bindValue(':balanceEndDate', $balanceEndDate, PDO::PARAM_STR);
        }
        $userIncomesFromPeriodQuery->execute();
        return $userIncomesFromPeriodQuery->fetchAll();
    }

    private function getIncomeTimePartOfTheQuery($period)
    {
        if ($period == 'currentMonth') {
            $incomeTimePartOfTheQuery = 'AND MONTH(i.date_of_income) = MONTH(:currentDate)';
        } elseif ($period == 'previousMonth') {
            $incomeTimePartOfTheQuery = 'AND MONTH(i.date_of_income) = MONTH(:currentDate)-1';
        } elseif ($period == 'currentYear') {
            $incomeTimePartOfTheQuery = 'AND YEAR(i.date_of_income) = YEAR(:currentDate)';
        } elseif ($period == 'custom') {
            $incomeTimePartOfTheQuery = 'AND i.date_of_income BETWEEN :balanceStartDate AND :balanceEndDate';
        }

        return $incomeTimePartOfTheQuery;
    }


    public static function getIncomeData($incomeId)
    {
        $database = static::getDB();

        $userIncomeToEditQuery = $database->prepare(
            "SELECT ic.category_type, i.amount, i.date_of_income, i.income_comment
            FROM incomes AS i
            INNER JOIN incomes_categories AS ic
            ON i.income_category_id = ic.income_category_id
            WHERE
            i.income_id = :income_id;"
        );
        $userIncomeToEditQuery->bindValue(':income_id', $incomeId, PDO::PARAM_INT);
        $userIncomeToEditQuery->execute();
        return $userIncomeToEditQuery->fetch();
    }


    public static function updateIncome($data = [])
    {
        if (IncomeDataManager::validateIncomeEditData($data)) {
            $database = static::getDB();
            $selectedCategoryId = IncomeDataManager::getSelectedCategoryIdToEdit($data['category']);

            $editIncome = $database->prepare(
                'UPDATE incomes
            SET income_category_id = :income_category_id, amount = :amount, date_of_income = :date_of_income, income_comment = :income_comment
            WHERE income_id = :income_id;
            '
            );
            $editIncome->bindValue(':income_id', $data['income_id'], PDO::PARAM_INT);
            $editIncome->bindValue(':income_category_id', $selectedCategoryId, PDO::PARAM_INT);
            $editIncome->bindValue(':amount', $data['amount'], PDO::PARAM_STR);
            $editIncome->bindValue(':date_of_income', $data['date'], PDO::PARAM_STR);
            $editIncome->bindValue(':income_comment', $data['comment'], PDO::PARAM_STR);
            $editIncome->execute();
        }
    }


    public static function getSelectedCategoryIdToEdit($selectedCategory)
    {
        $LoggedUserId = Authentication::getLoggedUser()->user_id;
        $userIncomeCategories = IncomeDataManager::getUserIncomeCategories($LoggedUserId);

        foreach ($userIncomeCategories as $onceOfCategories) {
            if ($onceOfCategories['category_type'] === $selectedCategory) {
                return $onceOfCategories['income_category_id'];
            }
        }
    }


    private static function validateIncomeEditData($data = [])
    {
        $data['income_id'] = filter_input(INPUT_POST, 'income_id');
        $data['income_id'] = filter_var($data['income_id'], FILTER_VALIDATE_INT);
        if (empty($data['income_id'])) {
            return false;
        }

        // Amount
        $data['amount'] = filter_input(INPUT_POST, 'amount');
        $data['amount'] = str_replace(',', '.', $data['amount']);
        $data['amount'] = filter_var($data['amount'], FILTER_VALIDATE_FLOAT);
        if (empty($data['amount'])) {
            echo "blad1";
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

        return true;
    }


    public static function deleteIncome($incomeIdToDelete)
    {
        $incomeIdToDelete = filter_var($incomeIdToDelete, FILTER_VALIDATE_INT);
        $database = static::getDB();

        try {
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec("DELETE FROM incomes WHERE income_id=$incomeIdToDelete");
            $database->exec("DELETE FROM users_incomes WHERE income_id=$incomeIdToDelete");
        } catch (PDOException $e) {
            echo "<br>".$e->getMessage();
        };
    }

    public static function addIncomeCategory($newIncomeCategory)
    {
        $newIncomeCategory = mb_strtolower($newIncomeCategory, 'UTF-8');
        $newIncomeCategory = AuxiliaryFunctions::ucfirstUtf8($newIncomeCategory);

        if (!IncomeDataManager::isIncomeCategoryInTable($newIncomeCategory)) {
            IncomeDataManager::saveIncomeCategoryToIncomesCategoriesTabel($newIncomeCategory);
        }
        IncomeDataManager::assignIncomeCategoryToUser($newIncomeCategory);
    }

    public static function isIncomeCategoryInTable($incomeCategory)
    {
        $database = static::getDB();

        $isIncomeCategoryInTableQuery = $database->prepare('SELECT distinct 1 category_type FROM incomes_categories WHERE category_type = :category_type');
        $isIncomeCategoryInTableQuery->bindValue(':category_type', $incomeCategory, PDO::PARAM_STR);
        $isIncomeCategoryInTableQuery->execute();
        $isIncomeCategoryInTable = $isIncomeCategoryInTableQuery->fetch();

        return $isIncomeCategoryInTable;
    }

    public static function saveIncomeCategoryToIncomesCategoriesTabel($newIncomeCategory)
    {
        $database = static::getDB();

        $assignIncomeToUserQuery = $database->prepare('INSERT INTO incomes_categories (category_type, default_type) VALUES (:category_type, :default_type)');
        $assignIncomeToUserQuery->bindValue(':category_type', $newIncomeCategory, PDO::PARAM_STR);
        $assignIncomeToUserQuery->bindValue(':default_type', 0, PDO::PARAM_INT);
        $assignIncomeToUserQuery->execute();
    }

    public static function assignIncomeCategoryToUser($newIncomeCategory)
    {
        $newIncomeCategoryId = IncomeDataManager::getIncomeCategoryId($newIncomeCategory);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $assignIncomeCategoryToUserQuery = $database->prepare(
            'INSERT INTO users_categories_incomes (user_id, income_category_id)
            VALUES (:user_id, :income_category_id)'
        );
        $assignIncomeCategoryToUserQuery->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $assignIncomeCategoryToUserQuery->bindValue(':income_category_id', $newIncomeCategoryId, PDO::PARAM_INT);
        $assignIncomeCategoryToUserQuery->execute();
    }

    public static function getIncomeCategoryId($incomeCategory)
    {
        $database = static::getDB();

        $incomeCategoryIdQuery = $database->prepare('SELECT income_category_id FROM incomes_categories WHERE category_type = :category_type');
        $incomeCategoryIdQuery->bindValue(':category_type', $incomeCategory, PDO::PARAM_STR);
        $incomeCategoryIdQuery->execute();
        $incomeCategoryId = $incomeCategoryIdQuery->fetch();

        return $incomeCategoryId[0];
    }

    public static function isCategoryAssignedToUser($incomeCategory)
    {
        $loggedUser = Authentication::getLoggedUser();
        $userIncomeCategories = IncomeDataManager::getUserIncomeCategories($loggedUser->user_id);

        $incomeCategory = mb_strtolower($incomeCategory, 'UTF-8');
        $incomeCategory = AuxiliaryFunctions::ucfirstUtf8($incomeCategory);

        foreach ($userIncomeCategories as $onceOfCategories) {
            if ($onceOfCategories['category_type'] === $incomeCategory) {
                return true;
            }
        }
        return false;
    }

    public static function getIdUsedUserIncomeCategories()
    {
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $idUsedUserIncomeCategoriesQuery = $database->prepare(
            "SELECT DISTINCT i.income_category_id
            FROM incomes AS i
            INNER JOIN users_incomes AS ui
            ON i.income_id = ui.income_id
            WHERE ui.user_id = :user_id;"
        );
        $idUsedUserIncomeCategoriesQuery->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $idUsedUserIncomeCategoriesQuery->execute();
        return $idUsedUserIncomeCategoriesQuery->fetchAll();
    }

    public static function deleteUserIncomeCategory($incomeCategoryIdToDelete)
    {
        $incomeCategoryIdToDelete = filter_var($incomeCategoryIdToDelete, FILTER_VALIDATE_INT);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        try {
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec("DELETE FROM users_categories_incomes WHERE user_id=$loggedUser->user_id AND income_category_id=$incomeCategoryIdToDelete");
        } catch (PDOException $e) {
            echo "<br>".$e->getMessage();
        };
    }

    public static function deleteUserIncomesInSelectedCategory($incomeCategoryId)
    {
        $incomeCategoryId = filter_var($incomeCategoryId, FILTER_VALIDATE_INT);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        try {
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec(
                "DELETE FROM incomes, users_incomes
                USING incomes
                INNER JOIN users_incomes
                ON incomes.income_id = users_incomes.income_id
                WHERE income_category_id=$incomeCategoryId AND user_id=$loggedUser->user_id"
            );
        } catch (PDOException $e) {
            echo "<br>".$e->getMessage();
        };
    }

    public static function moveUserIncomesFromCategory($oldCategoryId, $categoryToCarryOverIncomes)
    {
        $oldCategoryId = filter_var($oldCategoryId, FILTER_VALIDATE_INT);
        $categoryIdToCarryOverIncomes = IncomeDataManager::getSelectedCategoryId($categoryToCarryOverIncomes);
        $loggedUser = Authentication::getLoggedUser();
        $database = static::getDB();

        $updateCategoriesIncomes = $database->prepare(
            'UPDATE incomes AS i
            INNER JOIN users_incomes AS ui
            ON i.income_id = ui.income_id
            SET i.income_category_id = :new_income_category_id
            WHERE i.income_category_id = :previous_income_category_id AND ui.user_id = :user_id;
            '
        );
        $updateCategoriesIncomes->bindValue(':new_income_category_id', $categoryIdToCarryOverIncomes, PDO::PARAM_INT);
        $updateCategoriesIncomes->bindValue(':previous_income_category_id', $oldCategoryId, PDO::PARAM_INT);
        $updateCategoriesIncomes->bindValue(':user_id', $loggedUser->user_id, PDO::PARAM_INT);
        $updateCategoriesIncomes->execute();
    }

    public static function updateUserIncomeCategory($data = [])
    {
        if (IncomeDataManager::validateIncomeCategoryData($data)) {
            IncomeDataManager::deleteUserIncomeCategory($data['incomeCategoryId']);
            IncomeDataManager::addIncomeCategory($data['newCategoryType']);
            IncomeDataManager::moveUserIncomesFromCategory($data['incomeCategoryId'], $data['newCategoryType']);

            echo IncomeDataManager::getIncomeCategoryId($data['newCategoryType']);
        }
    }

    private static function validateIncomeCategoryData($data = [])
    {
        $loggedUser = Authentication::getLoggedUser();
        $userCurrentIncomeCategories = IncomeDataManager::getUserIncomeCategories($loggedUser->user_id);

        // categoryId
        $data['incomeCategoryId'] = filter_input(INPUT_POST, 'incomeCategoryId');
        $data['incomeCategoryId'] = filter_var($data['incomeCategoryId'], FILTER_VALIDATE_INT);
        if (empty($data['incomeCategoryId'])) {
            return false;
        }

        // Category;
        $data['newCategoryType'] = filter_input(INPUT_POST, 'newCategoryType');
        if (empty($data['newCategoryType'])) {
            echo "empty";
            return false;
        }
        foreach ($userCurrentIncomeCategories as $onceOfCurrentCategories) {
            if ($onceOfCurrentCategories['category_type'] === $data['newCategoryType'] && $onceOfCurrentCategories['income_category_id'] != $data['incomeCategoryId']) {
                return false;
            }
        }

        return true;
    }
}
