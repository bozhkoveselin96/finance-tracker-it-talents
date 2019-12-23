<?php


namespace controller;


use model\categories\Category;
use model\categories\CategoryDAO;

class CategoryController
{
    public function add()
    {
        $response = [];
        $response["status"] = false;
        if (isset($_POST["add_category"]) && isset($_SESSION["logged_user"])) {
            $name = $_POST["name"];
            $type = $_POST["type"];
            $icon_url = $_POST["icon_url"];
            $owner_id = $_SESSION["logged_user"];
            $category = new Category($name, $type, $icon_url, $owner_id);

            if (mb_strlen($category->getName()) >= MIN_LENGTH_NAME) {
                if (CategoryDAO::createCategory($category)) {
                    $response["status"] = true;
                    $response["target"] = "addCategory";
                }
            }
        }
        return $response;
    }

    public function getAll()
    {
        $response = [];
        $response["status"] = false;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION["logged_user"]) && isset($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            $owner_id = $_SESSION["logged_user"];
            $type = $_GET["category_type"];
            if ($_SESSION["logged_user"] == $user_id) {
                $categories = CategoryDAO::getAll($owner_id, $type);
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

            if ($category) {
                $name = $_POST["name"];
                $icon_url = $_POST["icon_url"];
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