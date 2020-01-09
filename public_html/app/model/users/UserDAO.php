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

    public function tokenExists($user_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT token FROM reset_password
                WHERE owner_id = ? AND expiration_time > CURRENT_TIMESTAMP ORDER BY expiration_time DESC;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetch(\PDO::FETCH_OBJ);

    }

    public function changeForgottenPassword($new_password, $user_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();

            $sql1 = "UPDATE users SET password = ? WHERE id = ?;";
            $stmt = $conn->prepare($sql1);
            $stmt->execute([$new_password, $user_id]);

            $sql2 = "DELETE FROM password_reset WHERE user_id = ?;";
            $stmt = $conn->prepare($sql2);
            $stmt->execute([$user_id]);

            $conn->commit();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new Exception($exception->getMessage());
        }
    }
}