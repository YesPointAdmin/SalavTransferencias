<?php

require ("../Config/DbConfig.php");
require('../Engines/Singleton/BitacoraSingleton.php');
require('../Engines/Singleton/ProductosSingleton.php');

class TestSigleton {
    public function testRunAddRowToBitacora(mysqli $link, $fileName = "test.test") : void {
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName,'Se detecto el siguente provedor: BOSH','','','','0','0');
        //this->executeQuery();
    }

    public function testProductoSalav(mysqli $link) {

        $productoSalv = ProductosSingleton::getInstance($link)->getRowFromProductosSalavByData("Porsche","Cayenne","2003","2003","4.5L","V8","0986MF4220","","Air Filter",0);
        echo"singleton test";
        var_dump($productoSalv);

        if($productoSalv === 0){
            echo "<br /> No existe, se debe guardar";
            $insertProductos = ProductosSingleton::getInstance($link)->addRowToProductosSalav("Porsche","Cayenne","2003","2003","4.5L","V8","0986MF4220","","Air Filter",0);
        }
            
        
        else
            echo "No se guarda porque ya existe";
        //$marca, $modelo, $anio_inicio, $anio_fin, $motor, $cil, $part_number, $position, $part_type, $id_catprod
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
echo "<br /> Init test at TestSigleton testProductoSalav <br />";
$testPrepareStatements->testProductoSalav($link);
echo "<br /> End test testProductoSalav <br />";
$dbConfig->closeConnect($link);
?>