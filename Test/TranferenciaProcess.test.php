<?php
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