<?php
include_once 'classes/init.php';
class User {
    public function __construct() {
        global $loggedOutOk;
        $this->_user = Sql::queryRow("SELECT * FROM users WHERE code = " . Sql::val($_COOKIE['invite']));
        if(empty($this->_user) && !(isset($loggedOutOk) && $loggedOutOk)) {
            header("Location: /login.php");
            exit;
        }
    }
    public function login($code){
        $this->_user = Sql::queryRow("SELECT * FROM users WHERE code = " . Sql::val($code));
        if(empty($this->_user)) {
            return false;
        }
        setcookie("invite", $code, time() + (86400 * 90), "/");
        header("Location: /record.php");
        die();
    }
    public function isAdmin() {
        return $this->_user['isAdmin'];
    }
}