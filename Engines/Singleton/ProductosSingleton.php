<?php

require_once("../Capture/InscribeCatalogoProductos.php");
require_once("../Capture/inscribeProductosSalav.php");

class ProductosSingleton
{
    private static $instance;
    private InscribeCatalogoProductos $productosQueryng;
    private InscribeProductosSalav $productosValidate;

    private function __construct(mysqli $link) {
        //echo 'Contruyendo objeto..'.PHP_EOL;
        $this->productosQueryng = new InscribeCatalogoProductos($link, "transferencia");

        $this->productosValidate = new InscribeProductosSalav($link, "transferencia");

    }

    public static function getInstance(mysqli $link)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($link);
        }

        return self::$instance;
    }

    public function getRowFromCatalogoProductosByPartNumber(string $part_number) : mixed
    {
        return $this->productosQueryng->executeQuery("select",$part_number);
    }
    public function addRowToProductosSalav(string $marca, string $modelo, string $anio_inicio, string $anio_fin, string $motor, string $cil, string $part_number, string $position, string $part_type, string $id_catprod) : mixed {

        return $this->productosValidate->executeQuery("insert", $marca, $modelo, $anio_inicio, $anio_fin, $motor, $cil, $part_number, $position, $part_type, $id_catprod);
   }
    public function getRowFromProductosSalavByData(string $marca, string $modelo, string $anio_inicio, string $anio_fin, string $motor, string $cil, string $part_number, string $position, string $part_type, string $id_catprod) : mixed
    {
        return $this->productosValidate->executeQuery("select", $marca, $modelo, $anio_inicio, $anio_fin, $motor, $cil, $part_number, $position, $part_type, $id_catprod);
    }

    

    public function getBitacoraWritter()
    {
        return $this->productosQueryng;
    }

    public function cleanResult($result)
    {
        $this->productosQueryng->cleanMemoryAfterQuery($result);;
    }
}

?>