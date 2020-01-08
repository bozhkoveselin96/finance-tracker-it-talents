<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use model\categories\Category;
use model\categories\CategoryDAO;

class CategoryController {
    public function add() {
        $response = [];
        if (isset($_POST["add_category"])) {
            $name = $_POST["name"];
            $type = $_POST["type"];
            $icon = $_POST["icon"];
            $owner_id = $_SESSION["logged_user"];

            $category = new Category($name, $type, $icon, $owner_id);
            $categoryDAO = new CategoryDAO();

            if (!Validator::validateName($category->getName())) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            } elseif (!Validator::validateCategoryType($category->getType())) {
                throw new BadRequestException("Type must be have income or outcome");
            } else {
                $categoryDAO->createCategory($category);
                $response["target"] = "category";
            }
        }
        return $response;
    }

    public function getAll() {
        $response = [];
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $user_id = $_SESSION["logged_user"];
            $type = $_GET["category_type"];
            $categoriesDAO = new CategoryDAO();
            $categories = $categoriesDAO->getAll($user_id, $type);
            $response['data'] = $categories;
        }
        return $response;
    }


    public function edit() {
        if (isset($_POST["edit"])) {
            $category_id = $_POST["category_id"];
            $owner_id = $_SESSION["logged_user"];
            $name = $_POST["name"];
            $icon_url = $_POST["icon"];
            if (!Validator::validateName($name)) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            }
            $categoryDAO = new CategoryDAO();

            $category = $categoryDAO->getCategoryById($category_id, $owner_id);
            $editedCategory = new Category($name, $category->getType(), $icon_url, $owner_id);
            $editedCategory->setId($category->getId());

            if ($editedCategory->getOwnerId() == $category->getId()) {
                $categoryDAO->editCategory($category);
            } else {
                throw new ForbiddenException("This category is not yours");
            }
        }
    }
}