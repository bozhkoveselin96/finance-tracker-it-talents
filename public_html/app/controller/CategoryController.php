<?php


namespace controller;


use model\categories\Category;
use model\categories\CategoryDAO;

class CategoryController {
    public function add(){
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly or you are not logged in.';
        if (isset($_POST["add_category"]) && isset($_SESSION["logged_user"])) {
            $name = $_POST["name"];
            $type = $_POST["type"];
            $icon = $_POST["icon"];
            $category = new Category($name, $type, $icon, $_SESSION['logged_user']);

            if (Validator::validateName($category->getName()) && Validator::validateCategoryType($type) &&
                CategoryDAO::createCategory($category)) {
                $response["target"] = "category";
                $status = STATUS_CREATED;
            }
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No categories available or you are not logged in.';
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION["logged_user"]) &&
            Validator::validateCategoryType($_GET["category_type"])) {
            $type = $_GET["category_type"];
            $categories = CategoryDAO::getAll($_SESSION['logged_user'], $type);
            if ($categories !== false) {
                $status = STATUS_OK;
                $response["data"] = $categories;
            }
        }
        header($status);
        return $response;
    }

    public function edit() {
        $status = STATUS_FORBIDDEN . 'Something is not filled correctly or you are not logged in.';
        if (isset($_POST["edit"])) {
            $category_id = $_POST["category_id"];
            $owner_id = $_SESSION["logged_user"];
            $category = CategoryDAO::getCategoryById($category_id, $owner_id);
            $name = $_POST["name"];
            if ($category && Validator::validateName($name)) {
                $icon_url = $_POST["icon"];
                $editedCategory = new Category($name, $category->type ,$icon_url, $owner_id);
                $editedCategory->setId($category_id);
                if ($editedCategory->getOwnerId() == $category->owner_id &&
                    CategoryDAO::editCategory($editedCategory)) {
                    $status = STATUS_OK;
                }
            }
        }
        return header($status);
    }
}