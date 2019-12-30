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

            $conn = Connection::get();
            $sql = "INSERT INTO users(email, password, first_name, last_name, avatar_url, last_login, date_created)
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_DATE)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getUser($email_or_id) {
        try {
            $conn = Connection::get();
            $sql = "SELECT id, password, first_name, last_name, avatar_url FROM users ";
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

            $conn = Connection::get();
            $sql = "UPDATE users SET password = ?, first_name = ?, last_name = ?, avatar_url = ? WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }
}