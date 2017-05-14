<?php


namespace MVC;

class DB {

    private $dbh;
    private $s = array();
    private $lastStmt;

    public function getDbh() {
        return $this->dbh;
    }

    public function getLastStmt() {
        return $this->lastStmt;
    }

    public function __call($name, $parameters) {

        global $qc;
        
        // gyors�t�t�r vizsg�lata, ha van, akkor
        if (isset($this->s[$name])) {
            // ment�s
            $this->lastStmt = $this->s[$name];
            // v�grehajt�s
            $this->s[$name]->execute($parameters);
            // eredm�yn visszaad�sa
            return $this->s[$name];
        }

        // param�terlista hely�re megfelel� sz�m� '?'
        $c = count($parameters);
        if ($c)
            $call = '(' . str_repeat('?, ', $c - 1) . ' ?)';
        else
            $call = '()';

        // mysql t�rolt elj�r�s nev�nek lev�laszt�sa
        $n = substr($name, 1);

        // f�ggv�ny vagy elj�r�s?
        switch ($name[0]) {
            case 'p':
                $this->s[$name] = $statement = $this->dbh->prepare("call $n $call;");
                break;
            case 'f':

                $this->s[$name] = $statement = $this->dbh->prepare("select $n $call;");
                break;
            default:
                throw new \Exception('�rv�nytelen f�ggv�nyn�v: ' . $name);
        }
        // ment�s
        $this->lastStmt = $statement;

        // v�grehajt�s
        $statement->execute($parameters);
        $qc++;
        
        // eredm�ny visszaad�sa
        return $statement;
    }

    /* singleton minta */

    private static $instance;
    private static $c = __CLASS__;

    private function __construct() {
        global $config;

        $dbh = new \PDO($config['PDO_DSN'], $config['PDO_USERNAME'], $config['PDO_PASSWORD']);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
        $dbh->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
        $dbh->setAttribute(\PDO::ATTR_TIMEOUT, 5);
        $dbh->query("SET NAMES 'utf8'")->closeCursor();
        $this->dbh = $dbh;
    }

    public static function instance() {
        if (!(self::$instance instanceof self::$c)) {
            self::$instance = new self::$c();
        }
        return self::$instance;
    }

    /* singleton minta v�ge */
}

?>
