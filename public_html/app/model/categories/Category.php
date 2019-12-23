<?php


namespace model\accounts;


class Category {
    private $id;
    private $name;
    private $type;
    private $icon_url;
    private $owner_id;

    public function __construct($name, $type, $icon_url, $owner_id) {
        $this->name = $name;
        $this->type = $type;
        $this->icon_url = $icon_url;
        $this->owner_id = $owner_id;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getOwnerId() {
        return $this->owner_id;
    }

    public function getIconUrl() {
        return $this->icon_url;
    }

    public function setId($id) {
        $this->id = $id;
    }

}