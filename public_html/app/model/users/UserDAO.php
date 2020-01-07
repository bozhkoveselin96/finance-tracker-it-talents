<?php


namespace model\users;

use model\Connection;

class UserDAO {
    public static function register(User $user){
        try {
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
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getUser($email_or_id) {
        try {
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
                return $stmt->fetch(\PDO::FETCH_OBJ);
            }
            return false;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function edit(User $user) {
        try {
            $data = [];
            $data[] = $user->getPassword();
            $data[] = $user->getFirstName();
            $data[] = $user->getLastName();
            $data[] = $user->getAvatarUrl();
            $data[] = $user->getId();

            $instance = Connection::getInstance();
            $conn = $instance->getConn();
            $sql = "UPDATE users SET password = ?, first_name = ?, last_name = ?, avatar_url = ? WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function updateLastLogin($user_id) {
        try {
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
            $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP 
                    WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);

            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function addToken($token, $user_id) {
        try {
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
            $sql = "INSERT INTO reset_password(token) VALUE ?
                    WHERE user_id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$token, $user_id]);

            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function tokenExists($token) {
        try {
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
            $sql = "SELECT user_id FROM reset_password
                    WHERE token = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$token]);
            if ($stmt->rowCount() == 1) {
                return true;
            }
            return false;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function changeForgottenPassword($newPassword, $user_id) {
        try {
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
            $sql = "UPDATE users SET password = ? WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$newPassword, $user_id]);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }
}