<?php

require_once("../Capture/InscribeCatalogoProductos.php");

class ProductosSingleton
{
    private static $instance;
    private InscribeCatalogoProductos $productosQueryng;

    private function __construct(mysqli $link) {
        //echo 'Contruyendo objeto..'.PHP_EOL;
        $this->productosQueryng = new InscribeCatalogoProductos($link, "transferencia");
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