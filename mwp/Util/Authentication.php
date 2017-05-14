<?php

namespace Util;

class Authentication {

    private $authenticated = false;
    private $id;
    private $email;
    private $joined;
    private $role;

    public function authenticate($email, $secret) {
        $result = \MVC\DB::instance()->fUserAuthenticate($email, $secret);
        if(!$result->columnCount()) {
            $this->authenticated = false;
            return false;
        }
        list($id) = $result->fetch(\PDO::FETCH_NUM);
        $result->closeCursor();
        if (!empty($id)) {
            $userStmt = \MVC\DB::instance()->pUserSelectById($id);
            $user = $userStmt->fetch(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
            $this->id = $user->id;
            $this->email = $user->email;
            $this->joined = $user->joined;
            $this->role = $user->role;
            $this->authenticated = true;
            $userStmt->closeCursor();
            return true;
        } else {
            $this->authenticated = false;
            return false;
        }
    }

    public function clearAuthentication() {
        $this->authenticated = false;
        unset($this->email, $this->id, $this->joined, $this->rank);
    }

    public function isAuthenticated() {
        return $this->authenticated;
    }

    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getJoined() {
        return $this->joined;
    }

    public function getRole() {
        return $this->role;
    }

}

?>