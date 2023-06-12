<?php

namespace App\Engines\Singleton;
use App\Capture\InscribeBitacora;
use mysqli;

class BitacoraSingleton
{
    private static $instance;
    private InscribeBitacora $bitacoraWritter;

    private function __construct(mysqli $link) {
        //echo 'Contruyendo objeto..'.PHP_EOL;
        $this->bitacoraWritter = new InscribeBitacora($link, "transferencia");
    }

    public static function getInstance(mysqli $link)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($link);
        }

        return self::$instance;
    }

    public function addRowToBitacora($fileName,mixed ...$values)
    {
        $result = $this->bitacoraWritter->executeQuery("insert", $fileName, $fileName,...$values);
        $this->bitacoraWritter->cleanMemoryAfterQuery($result);
    }

    public function getBitacoraWritter()
    {
        return $this->bitacoraWritter;
    }
}

?>