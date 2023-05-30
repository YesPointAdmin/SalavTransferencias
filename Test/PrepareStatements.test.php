<?php

require ("../Capture/InscribeBitacora.php");
require ("../Capture/InscribeProductosSalav.php");
require ("../Capture/InscribeCatalogoProductos.php");
require ("../Config/DbConfig.php");

class PrepareStatements extends InscribeBitacora{
    public function testRunPreparedStatement() : void {
        $this->executeQuery();
    }
}
class ProductosSalavTest extends InscribeProductosSalav{
    public function testRunPreparedStatement() : void {
        $this->executeQuery();
    }
}
class CatalogoProductosTest extends InscribeCatalogoProductos{
    public function testRunPreparedStatement() : void {
        echo "<br /> Running test...";
        $testVar = "SPC-8655-Z";
        $this->executeQuery("select", $testVar);
    }
}


$dbConfig = new DbConfig();
$link = $dbConfig->openConnect();

/* $testPrepareStatements = new PrepareStatements($link,"transferencia");
echo "<br /> Init test at PrepareStatements <br />";
$testPrepareStatements->testRunPreparedStatement();
$testPrepareStatements = new ProductosSalavTest($link,"transferencia");
echo "<br /> Init test at ProductosSalavTest <br />"; */
$testPrepareStatements = new CatalogoProductosTest($link,"transferencia");
echo "<br /> Init test at CatalogoProductosTest <br />";
$testPrepareStatements->testRunPreparedStatement();
$dbConfig->closeConnect($link);
?>