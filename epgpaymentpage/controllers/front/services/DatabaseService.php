<?php

class DatabaseService extends mysqli
{
    private $host = _DB_SERVER_;
    private $account = _DB_USER_;
    private $password = _DB_PASSWD_;
    private $database = _DB_NAME_;

    private static $instance;

    public function __construct(){
        try {
            parent::__construct($this->host, $this->account, $this->password, $this->database);
            parent::set_charset('utf8');
        } catch (mysqli_sql_exception $e) {
            die('DB Connection error');
        }
    }

    static function instance() {

        if (!self::$instance)
            self::$instance = new DatabaseService;

        return self::$instance;
    }

}
