<?php


namespace controller;


use model\categories\Category;
use model\categories\CategoryDAO;

class CategoryController {
    public function add(){
        $response = [];
        $response["status"] = false;
        if (isset($_POST["add_category"]) && isset($_SESSION["logged_user"])) {
            $name = $_POST["name"];
            $type = $_POST["type"];
            $icon = $_POST["icon"];
            $owner_id = $_SESSION["logged_user"];
            $category = new Category($name, $type, $icon, $owner_id);

            if (Validator::validateName($category->getName())) {
                if (CategoryDAO::createCategory($category)) {
                    $response["status"] = true;
                    $response["target"] = "category";
                }
            }
        }
        return $response;
    }

    public function getAll() {
        $response = [];
        $response["status"] = false;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION["logged_user"]) && isset($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            $type = $_GET["category_type"];
            if (Validator::validateLoggedUser($user_id)) {
                $categories = CategoryDAO::getAll($user_id, $type);
                if ($categories) {
                    $response["status"] = true;
                    $response["data"] = $categories;
                }
            }
        }
        return $response;
    }

    public function edit() {
        $response["status"] = false;
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
                    $response["status"] = true;
                }
            }
        }
        return $response;
    }
}