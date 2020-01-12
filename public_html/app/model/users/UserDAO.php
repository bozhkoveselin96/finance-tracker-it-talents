<?php


namespace model\users;

use model\Connection;
use mysql_xdevapi\Exception;

class UserDAO {
    public function register(User $user) {
        $data = [];
        $data[] = $user->getEmail();
        $data[] = $user->getPassword();
        $data[] = $user->getFirstName();
        $data[] = $user->getLastName();
        $data[] = $user->getAvatarUrl();

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "INSERT INTO users(email, password, first_name, last_name, avatar_url, last_login, date_created)
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_DATE);";
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        return $conn->lastInsertId();
    }

    public function getUser($email_or_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT id, email, password, first_name, last_name, avatar_url FROM users ";
        if (is_int($email_or_id)) {
            $sql .= "WHERE id = ?;";
        } else {
            $sql .= "WHERE email = ?;";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email_or_id]);

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(\PDO::FETCH_OBJ);
            $user = new User($row->email, $row->password, $row->first_name, $row->last_name, $row->avatar_url);
            $user->setId($row->id);
            return $user;
        }
        return false;
    }

    public function edit(User $user) {
        $parameters = [];
        $parameters[] = $user->getPassword();
        $parameters[] = $user->getFirstName();
        $parameters[] = $user->getLastName();
        $parameters[] = $user->getAvatarUrl();
        $parameters[] = $user->getId();

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "UPDATE users SET password = ?, first_name = ?, last_name = ?, avatar_url = ? WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
    }

    public function deleteProfile($user_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "DELETE FROM users WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
    }

    public function updateLastLogin($user_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP 
                WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
    }

    public function addToken($token, $user_id) {

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "INSERT INTO reset_password(token, owner_id, expiration_time) VALUES (?, ?, NOW() + INTERVAL ? MINUTE );";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$token, $user_id, TOKEN_EXPIRATION_MINUTES])) {
            return true;
        }
    }

    public function tokenExists($userIdOrToken, $byToken = false) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT token, owner_id FROM reset_password ";

        if (!$byToken) {
            $sql .= "WHERE owner_id = ? ";
        } else {
            $sql .= "WHERE token = ? ";
        }

        $sql .= "AND expiration_time > CURRENT_TIMESTAMP ORDER BY expiration_time DESC LIMIT 1;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userIdOrToken]);
        if ($stmt->rowCount() == 1) {
            return $stmt->fetch(\PDO::FETCH_OBJ);
        }
        return false;
    }

    public function changeForgottenPassword(User $user) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();

            $sql1 = "UPDATE users SET password = ? WHERE id = ?;";
            $stmt = $conn->prepare($sql1);
            $stmt->execute([$user->getPassword(), $user->getId()]);

            $sql2 = "DELETE FROM reset_password WHERE owner_id = ?;";
            $stmt = $conn->prepare($sql2);
            $stmt->execute([$user->getId()]);

            $conn->commit();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    public function getLastTransaction() {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT DISTINCT u.email
                FROM transactions AS t
                JOIN accounts AS a ON(a.id = t.account_id)
                JOIN users AS u ON(u.id = a.owner_id)
                WHERE DATE (NOW()) -  DATE(time_event) < 7;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT id FROM users;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return false;
    }
}