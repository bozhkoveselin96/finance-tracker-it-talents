<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use interfaces\Deletable;
use interfaces\Editable;
use exceptions\MethodNotAllowedException;
use model\categories\Category;
use model\categories\CategoryDAO;

class CategoryController implements Editable, Deletable {
    public function add() {
        if (isset($_POST["add_category"])) {
            if (!isset($_POST['name']) || !Validator::validateName($_POST['name'])) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            }elseif (!isset($_POST['type']) || !Validator::validateCategoryType($_POST['type'])) {
                throw new BadRequestException("Type must be have income or outcome!");
            }

            $icon = isset($_POST["icon"]) ? $_POST["icon"] : '';

            $category = new Category($_POST["name"], $_POST["type"], $icon, $_SESSION["logged_user"]);
            $categoryDAO = new CategoryDAO();
            $categoryDAO->createCategory($category);
            return new ResponseBody("Category added successfully.", $category);

        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function getAll() {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $categoriesDAO = new CategoryDAO();
            $categories = $categoriesDAO->getAll($_SESSION["logged_user"]);

            if (isset($_GET["category_type"]) && Validator::validateCategoryType($_GET["category_type"])) {
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
        throw new MethodNotAllowedException("Method not allowed!");
    }


    public function edit() {
        if (isset($_POST["edit"])) {
            if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
                throw new BadRequestException("Category is required!");
            } elseif(!isset($_POST['name']) || !Validator::validateName($_POST['name'])) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            }

            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($_POST["category_id"], $_SESSION["logged_user"]);

            if (!$category || $category->getOwnerId() != $_SESSION["logged_user"]) {
                throw new ForbiddenException("This category is not yours");
            }

            if (isset($_POST['icon']) && !empty($_POST['icon'])) {
                $category->setIcon($_POST["icon"]);
            }

            $category->setName($_POST["name"]);
            $categoryDAO->editCategory($category);
            return new ResponseBody("Category edited successfully", $category);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function delete() {
        if (isset($_POST["delete"])) {
            if (!isset($_POST["category_id"]) || empty($_POST["category_id"])) {
                throw new BadRequestException("Category is required!");
            }

            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($_POST["category_id"], $_SESSION["logged_user"]);

            if (!$category || $category->getOwnerId() != $_SESSION['logged_user']) {
                throw new ForbiddenException("This category is not yours. Predefined categories are not deletable.");
            }
            $categoryDAO->deleteCategory($category);
            return new ResponseBody("Category deleted successfully.", $category);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }
}