<?php


namespace model\accounts;


use model\Connection;

class AccountDAO {
    public static function createAccount(Account $account) {
        try {
            $data = [];
            $data[] = $account->getName();
            $data[] = $account->getCurrentAmount();
            $data[] = $account->getOwnerId();

            $conn = Connection::get();
            $sql = "INSERT INTO accounts(name, current_amount, owner_id, date_created)
                VALUES (?, ?, ?, CURRENT_DATE);";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getMyAccounts(int $user_id) {
        try {
            $conn = Connection::get();
            $sql = "SELECT id, name, current_amount FROM accounts WHERE owner_id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll(\PDO::FETCH_OBJ);
            }
            return false;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getAccountById(int $account_id) {
        try {
            $conn = Connection::get();
            $sql = "SELECT id, name, current_amount, owner_id FROM accounts WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$account_id]);
            if ($stmt->rowCount() == 1) {
                return $stmt->fetch(\PDO::FETCH_OBJ);
            }
            return false;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function deleteAccount(int $account_id) {
        try {
            $conn = Connection::get();
            $sql = "DELETE FROM accounts WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$account_id]);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function editAccount(Account $account) {
        try {
            $conn = Connection::get();
            $sql = "UPDATE accounts SET name = ? WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$account->getName(), $account->getId()]);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }
}