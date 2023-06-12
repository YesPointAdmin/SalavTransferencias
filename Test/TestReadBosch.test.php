<?php
use App\Config\DbConfig;
use App\Engines\Readers\ReadBosch;

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
        $testMock = $this->getMock2();
        $this->addCatalogoProductos($fileName,$link, $testMock["marca"], $testMock["modelo"], $testMock["anio_inicio"], $testMock["anio_fin"], $testMock["motor"], $testMock["cil"], $testMock["part_number"], $testMock["position"], $testMock["part_type"], $testMock["id_catprod"]);
        
    }
    public function getMock1() : array {
        return [
            "marca"=>"Porsche",
            "modelo"=>"Cayenne",
            "anio_inicio"=>"2003",
            "anio_fin"=>"2003", 
            "motor"=>"4.5L", 
            "cil"=>"V8",
            "part_number"=>"0986MF4220",
            "position"=>"",
            "part_type"=>"Air Filter",
            "id_catprod"=>0
        ];
    }
    //motor:V6 181cid 3.0L
    public function getMock2() : array {
        return [
            "marca"=>"Jaguar",
            "modelo"=>"S-Type",
            "anio_inicio"=>"2001",
            "anio_fin"=>"2001", 
            "motor"=>"Oil Filter", 
            "cil"=>"0",
            "part_number"=>"0986MF0044",
            "position"=>"",
            "part_type"=>"Engine Oil Filter",
            "id_catprod"=>8064
        ];
    }
}
$dbConfig = new DbConfig();
$link = $dbConfig->openConnect();

echo "Se probara el test de ReadBosch...";
$testReadBosch = new TestReadBosch();
$testReadBosch->testAddProductoSalav($link);

$dbConfig->closeConnect($link);
?>