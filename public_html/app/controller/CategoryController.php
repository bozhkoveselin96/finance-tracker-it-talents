<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use exceptions\MethodNotAllowedException;
use Interfaces\Deletable;
use Interfaces\Editable;
use model\categories\Category;
use model\categories\CategoryDAO;

class CategoryController implements Editable, Deletable {
    public function add() {
        if (isset($_POST["add_category"])) {
            $name = $_POST["name"];
            $type = $_POST["type"];
            $icon = $_POST["icon"];
            $owner_id = $_SESSION["logged_user"];

            if (!Validator::validateName($name)) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            } elseif (!Validator::validateCategoryType($type)) {
                throw new BadRequestException("Type must be have income or outcome!");
            } else {
                $category = new Category($name, $type, $icon, $owner_id);
                $categoryDAO = new CategoryDAO();
                $categoryDAO->createCategory($category);
            }
        } else {
            throw new MethodNotAllowedException("Method not allowed!");
        }
        return new ResponseBody("Category added successfully.", $category);
    }

    public function getAll() {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $user_id = $_SESSION["logged_user"];
            $categoriesDAO = new CategoryDAO();
            $categories = $categoriesDAO->getAll($user_id);
            if (isset($_GET["category_type"])) {
                if(!Validator::validateCategoryType($_GET["category_type"])) {
                    throw new BadRequestException("Type must be have income or outcome!");
                }
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
            $category_id = $_POST["category_id"];
            $owner_id = $_SESSION["logged_user"];
            $name = $_POST["name"];
            $icon_url = $_POST["icon"];
            if (!Validator::validateName($name)) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            }

            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($category_id, $owner_id);
            if (!empty($icon_url)) {
                $category->setIcon($icon_url);
            }
            $category->setName($name);

            if ($category->getOwnerId() == $_SESSION["logged_user"]) {
                $categoryDAO->editCategory($category);
                return new ResponseBody("Category edited successfully", $category);
            } else {
                throw new ForbiddenException("This category is not yours");
            }
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function delete() {
        if (isset($_POST["delete"])) {
            $category_id = $_POST["category_id"];
            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($category_id, $_SESSION["logged_user"]);

            if ($category && $category->getOwnerId() == $_SESSION['logged_user']) {
                $categoryDAO->deleteCategory($category->getId());
                return new ResponseBody("Category deleted successfully.", $category);
            } else {
                throw new ForbiddenException("This category is not yours. Predefined categories are not deletable.");
            }
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }
}