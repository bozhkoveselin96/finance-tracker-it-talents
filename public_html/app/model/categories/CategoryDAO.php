<?php


namespace model\categories;


use model\Connection;

class CategoryDAO {
    public static function createCategory(Category $category) {
        try {
            $data = [];
            $data[] = $category->getName();
            $data[] = $category->getType();
            $data[] = $category->getIcon();
            $data[] = $category->getOwnerId();

            $conn = Connection::get();
            $sql = "INSERT INTO transaction_categories(name, type, icon, owner_id)
                    VALUES (?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getAll($owner_id, $type) {
        try {
            $conn = Connection::get();
            $sql = "SELECT * FROM transaction_categories 
                    WHERE (owner_id is NULL OR owner_id = ?) AND type = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$owner_id, $type]);

            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function editCategory(Category $category) {
        try {
            $conn = Connection::get();
            $sql = "UPDATE transaction_categories SET name = ?, icon = ? 
                    WHERE id = ? AND owner_id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $category->getName(),
                $category->getIcon(),
                $category->getId(),
                $category->getOwnerId()
                ]);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getCategoryById($category_id, $owner_id) {
        try {
            $conn = Connection::get();
            $sql = "SELECT id, name, type, icon, owner_id 
                    FROM transaction_categories WHERE id = ? AND (owner_id = ? OR owner_id IS NULL);";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$category_id, $owner_id]);
            if ($stmt->rowCount() == 1) {
                return $stmt->fetch(\PDO::FETCH_OBJ);
            }
            return false;
        } catch (\PDOException $exception) {
            return false;
        }
    }
}