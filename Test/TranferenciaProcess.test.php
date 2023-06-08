<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('file_uploads', 1);
define('PROCESS_NAME', 'transferencia');
error_reporting(E_ERROR);
$errorsPath = "../logs/errors/php-error-".date("Ymd").".log";
$errorsDir = dirname($errorsPath);
if (!is_dir($errorsDir))
    mkdir($errorsDir, 0755, true);

ini_set("error_log", $errorsPath);
require ('../vendor/autoload.php');
require_once('../Engines/TransferenciaProcess.php');
require ("../Config/DbConfig.php");

class TestTransferenciaProcess extends TransferenciaProcess{
    public string $pathToTestFile = "./files/fritec/Dacomsa_Fritec_FULL_20230121.xlsx";
    public function readFileByTypeTest(mysqli $link){
        $fileName = \basename($this->pathToTestFile);
        $this->readFileByType($this->pathToTestFile, $fileName, $link);
    }
}

$dbConfig = new DbConfig();
$link = $dbConfig->openConnect();

echo "Se probara el test de TestTransferenciaProcess... <br />";
$testReadBosch = new TestTransferenciaProcess();
$testReadBosch->readFileByTypeTest($link);

$dbConfig->closeConnect($link);
?>