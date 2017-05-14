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
        
        // gyorsítótár vizsgálata, ha van, akkor
        if (isset($this->s[$name])) {
            // mentés
            $this->lastStmt = $this->s[$name];
            // végrehajtás
            $this->s[$name]->execute($parameters);
            // eredméyn visszaadása
            return $this->s[$name];
        }

        // paraméterlista helyére megfelelõ számú '?'
        $c = count($parameters);
        if ($c)
            $call = '(' . str_repeat('?, ', $c - 1) . ' ?)';
        else
            $call = '()';

        // mysql tárolt eljárás nevének leválasztása
        $n = substr($name, 1);

        // függvény vagy eljárás?
        switch ($name[0]) {
            case 'p':
                $this->s[$name] = $statement = $this->dbh->prepare("call $n $call;");
                break;
            case 'f':

                $this->s[$name] = $statement = $this->dbh->prepare("select $n $call;");
                break;
            default:
                throw new \Exception('Érvénytelen függvénynév: ' . $name);
        }
        // mentés
        $this->lastStmt = $statement;

        // végrehajtás
        $statement->execute($parameters);
        $qc++;
        
        // eredmény visszaadása
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

    /* singleton minta vége */
}

?>
