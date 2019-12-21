<?php


namespace model\users;


class User {
    private $id;
    private $email;
    private $password;
    private $first_name;
    private $last_name;
    private $last_login;

    public function __construct($email, $password, $first_name, $last_name) {
        $this->email = $email;
        $this->password = $password;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
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

    public function setPassword($password){
        $this->password = $password;
    }
}