<?php


namespace model\categories;


class Category implements \JsonSerializable {
    private $id;
    private $name;
    private $type;
    private $icon;
    private $owner_id;

    public function __construct($name, $type, $icon, $owner_id) {
        $this->name = $name;
        $this->type = $type;
        $this->icon = $icon;
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

    public function getIcon() {
        return $this->icon;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
    }

    public function setName($name){
        $this->name = $name;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    public function jsonSerialize() {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'icon'=>$this->icon,
            'type'=>$this->type,
            'owner'=>$this->owner_id
        ];
    }

}