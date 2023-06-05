<?php

require ("../Config/DbConfig.php");
require('../Engines/Singleton/BitacoraSingleton.php');

class TestSigleton {
    public function testRunAddRowToBitacora(mysqli $link, $fileName = "test.test") : void {
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName,'Se detecto el siguente provedor: BOSH','','','','0','0');
        //this->executeQuery();
    }
}


$dbConfig = new DbConfig();
$link = $dbConfig->openConnect();

/* $testPrepareStatements = new PrepareStatements($link,"transferencia");
echo "<br /> Init test at PrepareStatements <br />";
$testPrepareStatements->testRunPreparedStatement();
$testPrepareStatements = new ProductosSalavTest($link,"transferencia");
echo "<br /> Init test at ProductosSalavTest <br />"; */
$testPrepareStatements = new TestSigleton();
echo "<br /> Init test at TestSigleton testRunAddRowToBitacora <br />";
$testPrepareStatements->testRunAddRowToBitacora($link);
echo "<br /> End test testRunAddRowToBitacora <br />";
$dbConfig->closeConnect($link);
?>