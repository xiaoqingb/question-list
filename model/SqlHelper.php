<?php
class SqlHelper extends mysqli{

    private $host = "";
    private $account = "";
    private $password = "";
    private $db = "";
    private $port = "";
    
    public $isConnectError = false;

    public function __construct(){
        $this->host=App::$dbHost;
        $this->account=App::$dbAccount;
        $this->password=App::$dbPassword;
        $this->db=App::$dbName;
        $this->port=App::$dbPort;
        parent::__construct($this->host,$this->account,$this->password,$this->db,$this->port);

        if($this->connect_error){
            $this->isConnectError = true;
        }
        $this->set_charset("utf8");
    }
}