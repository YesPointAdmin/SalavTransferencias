<?php
require_once('../Engines/Readers/ReadBosch.php');
require ("../Config/DbConfig.php");
require ("../Engines/Singleton/ProductosSingleton.php");

class TestReadBosch extends ReadBosch{

    public function testValidateCatalogoProductos(mysqli $link) {
        echo "<br /> Running test...";
        //string $fileName, string $part_number, mysqli $link
        $fileName = "Bosch_excel.xlsx";
        $part_number = "0986MF4220";
        $this->validateCatalogoProductos($fileName, $part_number, $link);
      

    }

    public function testAddProductoSalav(mysqli $link) {
        echo "<br /> Running test...";
        $fileName ="testAdd.xlsx";
        $marca="Porsche";
        $modelo="Cayenne";
        $anio_inicio="2003";
        $anio_fin="2003"; 
        $motor="4.5L"; 
        $cil="V8";
        $part_number="0986MF4220";
        $position="";
        $part_type="Air Filter";
        $id_catprod=0;
        $this->addCatalogoProductos($fileName, $link, $marca, $modelo, $anio_inicio, $anio_fin, $motor, $cil, $part_number, $position, $part_type, $id_catprod);
        
    }

}
$dbConfig = new DbConfig();
$link = $dbConfig->openConnect();

echo "Se probara el test de ReadBosch...";
$testReadBosch = new TestReadBosch();
$testReadBosch->testValidateCatalogoProductos($link);

$dbConfig->closeConnect($link);
?>