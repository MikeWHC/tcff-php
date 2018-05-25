<?php
class Database{
 
    // specify your own database credentials
    private $host = "localhost";
    private $db_name = "tcff";
    private $username = "MikeWu";
    private $password = "987654321";
    public $mysqli;
 
    // get the database connection
    public function getConnection(){
 
        $this->mysqli = null;
 
        
        $this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        $this->mysqli->query("SET NAMES utf8");

        if ($this->mysqli->connect_errno) {
            die('Connect Error: ' . $mysqli->connect_errno);
        }
 
        return $this->mysqli;
    }
}
?>