<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use model\categories\Category;
use model\categories\CategoryDAO;

class CategoryController {
    public function add() {
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
                $id = $categoryDAO->createCategory($category);
                $category->setId($id);
            }
        } else {
            throw new BadRequestException("Bad request");
        }
        return new ResponseBody("Category added successfully.", $category);
    }

    public function getAll() {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $user_id = $_SESSION["logged_user"];
            $categoriesDAO = new CategoryDAO();
            $categories = $categoriesDAO->getAll($user_id);
            if (isset($_GET["category_type"])) {
                $type = $_GET["category_type"];
                /** @var Category $category */
                $categoriesByType = [];
                foreach ($categories as $category) {
                    if ($type == $category->getType()) {
                        $categoriesByType[] = $category;
                    }
                }
                $response = $categoriesByType;
            } else {
                $response = $categories;
            }
            return new ResponseBody(null, $response);
        }
        throw new BadRequestException("Bad request");
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
            $category->setIcon($icon_url);
            $category->setName($name);

            if ($category->getOwnerId() == $_SESSION["logged_user"]) {
                $categoryDAO->editCategory($category);
                return new ResponseBody("Category edited successfully", $category);
            } else {
                throw new ForbiddenException("This category is not yours");
            }
        }
        throw new BadRequestException("Bad request");
    }

    public function delete() {
        if (isset($_POST["delete"])) {
            $category_id = $_POST["category_id"];
            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($category_id, $_SESSION["logged_user"]);

            if ($category && $category->getOwnerId() == $_SESSION['logged_user']) {
                $categoryDAO->deleteCategory($category->getId(), $_SESSION["logged_user"]);
                return new ResponseBody("Category deleted successfully.", $category);
            } else {
                throw new ForbiddenException("This account is not yours");
            }
        }
        throw new BadRequestException("Bad request");
    }
}