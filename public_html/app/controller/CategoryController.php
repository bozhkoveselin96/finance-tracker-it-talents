<?php


namespace controller;


use model\categories\Category;
use model\categories\CategoryDAO;

class CategoryController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly';
        if (isset($_POST["add_category"])) {
            $name = $_POST["name"];
            $type = $_POST["type"];
            $icon = $_POST["icon"];
            $owner_id = $_SESSION["logged_user"];

            $category = new Category($name, $type, $icon, $owner_id);
            $categoryDAO = new CategoryDAO();

            if (Validator::validateName($category->getName()) && Validator::validateCategoryType($category->getType())) {
                try {
                    $categoryDAO->createCategory($category);
                    $response["target"] = "addCategory";
                    $status = STATUS_CREATED;
                } catch (\Exception $exception) {
                    $status = STATUS_ACCEPTED . 'Not created. Please try again';
                }

            }
            $response["target"] = "category";
            $status = STATUS_CREATED;
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No categories available';
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $user_id = $_SESSION["logged_user"];
                $type = $_GET["category_type"];
                $categoriesDAO = new CategoryDAO();
                $categories = $categoriesDAO->getAll($user_id, $type);
                $status = STATUS_OK;
                /** @var Category $category */
                foreach ($categories as $category) {
                    $response["data"][]["id"] = $category->getId();
                    $response["data"][]["name"] = $category->getName();
                    $response["data"][]["icon"] = $category->getIcon();
                    $response["data"][]["type"] = $category->getType();
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again.';
            }
        }
        header($status);
        return $response;
    }

    public function edit() {
        $status = STATUS_FORBIDDEN . 'Something is not filled correctly';
        if (isset($_POST["edit"])) {
            try {
                $category_id = $_POST["category_id"];
                $owner_id = $_SESSION["logged_user"];
                $name = $_POST["name"];
                $icon_url = $_POST["icon"];

                $categoryDAO = new CategoryDAO();
                //** @Category $category */
                $category = $categoryDAO->getCategoryById($category_id, $owner_id);
                $editedCategory = new Category($name, $category->getType(), $icon_url, $owner_id);
                $editedCategory->setId($category->getId());

                if ($editedCategory->getOwnerId() == $category->getId()) {
                    $categoryDAO->editCategory($category);
                    $status = STATUS_OK;
                }
            } catch (\PDOException $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again.';
            }
            return header($status);
        }
    }
}