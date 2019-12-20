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

            $conn = Connection::get();
            $sql = "INSERT INTO users(email, password, first_name, last_name, last_login, date_created)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_DATE)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getByEmail($email) {
        try {
            $conn = Connection::get();
            $sql = "SELECT id, password, CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$email]);
            if ($stmt->rowCount() == 1) {
                return $stmt->fetch(\PDO::FETCH_OBJ);
            }
            return false;
        } catch (\PDOException $exception) {
            return false;
        }
    }
}