<?php

namespace App\Config;

define('DB_SERVER', 'localhost:3306');
//define('DB_USERNAME', 'prueba_salav2');
define('DB_USERNAME', 'edi_db_user');
//define('DB_PASSWORD', '147258369');
define('DB_PASSWORD', 'test_pass12');
define('DB_NAME', 'salav_test');
//require_once("GeneralLogger.php");
use App\Config\GeneralLogger;
use mysqli;
use Exception;
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
class DbConfig {

    private $_log;
    
    public function __construct(string $process = "general"){
        $this->_log = new GeneralLogger(\get_class($this),$process);
    }
    
    /* Attempt to connect to MySQL database */
    public function openConnect() : mixed{
        $link = null;
        try {
            //code...
            $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME)or throw new Exception("Connect failed: %s\n". $link -> error);
            if($link !== false)
                $this->_log->outMessage("Connected to: " . DB_SERVER);
            
        } catch (Exception $e) {
            //throw $th;
            $this->_log->outErrorMessage("Error al conectar con: '".DB_SERVER."' error: \n ".$e->getMessage());
        } finally {
            
            return $link;
        }
    
    }
    
    public function closeConnect(mysqli $link){
        if($link->close())
            $this->_log->outMessage("Close connection to: " . DB_SERVER);
        else
            $this->_log->outErrorMessage("Error al desconectar: '".DB_SERVER."' error: \n ".mysqli_error($link));
        
    }

}
?>