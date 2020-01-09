<?php


namespace model\users;


class User implements \JsonSerializable {
    private $id;
    private $email;
    private $password;
    private $first_name;
    private $last_name;
    private $avatar_url;
    private $last_login;

    public function __construct($email, $password, $first_name, $last_name, $avatar_url) {
        $this->email = $email;
        $this->password = $password;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->avatar_url = $avatar_url;
    }

    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getFirstName() {
        return $this->first_name;
    }

    public function getLastName() {
        return $this->last_name;
    }

    public function getAvatarUrl() {
        return $this->avatar_url;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setAvatarUrl($avatar_url) {
        $this->avatar_url = $avatar_url;
    }

    public function jsonSerialize() {
       return [
           'id' => $this->getId(),
           'email' => $this->getEmail(),
           'first_name' => $this->getFirstName(),
           'last_name' => $this->getLastName(),
           'avatar_url' => $this->getAvatarUrl()
       ];
    }
}