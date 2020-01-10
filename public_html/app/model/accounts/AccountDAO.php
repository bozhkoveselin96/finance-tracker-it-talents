<?php


namespace model\accounts;


use model\Connection;

class AccountDAO {
    public function createAccount(Account $account) {
        $parameters = [];
        $parameters[] = $account->getName();
        $parameters[] = $account->getCurrentAmount();
        $parameters[] = $account->getCurrency();
        $parameters[] = $account->getOwnerId();

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "INSERT INTO accounts(name, current_amount, currency, owner_id, date_created)
                VALUES (?, ?, ?, ?, CURRENT_DATE);";
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        return $conn->lastInsertId();
    }

    public function getMyAccounts(int $user_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT id, name, current_amount, currency, owner_id FROM accounts WHERE owner_id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $accounts = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $value) {
            $account = new Account($value->name, $value->current_amount, $value->currency, $value->owner_id);
            $account->setId($value->id);
            $accounts[] = $account;
        }
        return $accounts;
    }

    public function getAccountById(int $account_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT id, name, current_amount, currency, owner_id FROM accounts WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$account_id]);
        if ($stmt->rowCount() == 1) {
            $response = $stmt->fetch(\PDO::FETCH_OBJ);
            $account = new Account($response->name, $response->current_amount, $response->currency, $response->owner_id);
            $account->setId($response->id);
            return $account;
        }
        return false;
    }

    public function deleteAccount(int $account_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "DELETE FROM accounts WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$account_id]);
    }

    public function editAccount(Account $account) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "UPDATE accounts SET name = ? WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$account->getName(), $account->getId()]);
    }
}