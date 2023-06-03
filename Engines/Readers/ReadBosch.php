<?php

require_once('ReaderImplement.php');

class ReadBosch extends ReaderImplement{
    
    protected string $bitacoraBasePath = "../logs/BD_BOSCH/bitacorabosh";
    protected string $bitacoraPath = "../logs/BD_BOSCH/bitacorabosh";
    protected array $processActualSequence = [1=>"marca",2=>"year",3=>"modelo",5=>"motor",9=>"part_type",10=>"position",13=>"part_number"];
    protected array $processTransformation = [1,3,5];
    protected array $processRequired = [1,2,3,5,9,13];
    protected array $processTrim = [13];

    public function readData(string $fileName, mysqli $link, array $dataToProcess, array $highestRow) : void {

        $this->outMessage("Inicia la captura de datos desde archivo BOSCH. Registro de logs independiente... ");
        
        //$this->bitacoraResgistartion = new InscribeBitacora($link, "transferencia");
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName,'Se detecto el siguente provedor: BOSH','','','','0','0');
        foreach ($dataToProcess as $rowKey => $rowValue) {
            # code...
            $readMoment = \time();
            if((!is_array($rowValue) || gettype($rowValue) !== 'array')){
                $typeOfRow = gettype($rowValue);
                $this->writeBitacora("time:{$readMoment}|row:{$rowKey}|status:'ERROR'|conflict:'No se puede procesar la informacion en fila'|row_type:{$typeOfRow}",$fileName);
            } else {
                if($dataToRetrieve = $this->retrieveDataStructure( $fileName, $rowKey, $rowValue)){
                    
                    $this->writeBitacora("Datos recuperados, continua proceso. ",$fileName);
                    //Validar en catalogo_producto por part_number
                    $this->validateCatalogoProductos($fileName, $dataToRetrieve['part_number'], $link);

                    //validar en ProductosSalav

                    //Si no existe en ProductosSalav y Existe en catalogo_producto se inserta
                }
            }
        }

    }

    protected function validateCatalogoProductos(string $fileName, string $part_number, mysqli $link) : mixed {
        $this->writeBitacora("Se consulta Catalogo de Producto el No. De Parte: {$part_number} ",$fileName);
        $resultData = ProductosSingleton::getInstance($link)->getRowFromCatalogoProductosByPartNumber($fileName,$part_number);
        //$resultData = false;

        if(is_bool($resultData))
            return false;

/*         if(\get_class($resultData) === 'mysqli_result'){
            $this->writeBitacora("Existen datos, inicia la recuperacion... ",$fileName);
            while ($rowResult = mysqli_fetch_array($resultData))
            {
                $this->writeBitacora("Type of existing Data: ".gettype($rowResult),$fileName);
                $rowMessage = "|";
                foreach ($rowResult as $keyRowResult => $valueRowResult)
                {
                    $this->writeBitacora("Type of valueRowResult: ".gettype($valueRowResult),$fileName);
                    $rowMessage .= " keyRowResult:{$keyRowResult}<=>valueRowResult:{$valueRowResult} |";
                }
                $this->writeBitacora("Datos recuperados, resultado de consultar Catalogo de producto: \n\t ",$fileName);
            }
        }
        ProductosSingleton::getInstance($link)->cleanResult($resultData); */
        return false;
    }

    protected function transformDataIfItsNecesary(mixed $value, int $key, array $dataStructure) : mixed {
        
        $value = $this->validateParticularData( $key, $value);

        if((array_key_exists($key,$this->processTransformation)||array_key_exists($key,$this->processTrim))&&!is_bool($value)){
            
            if(!array_key_exists($key,$this->processTrim)){
                $value = str_replace(".", "", $value);
                $value = str_replace(",", "", $value);
                $value = str_replace("/", "", $value);
                $value = str_replace("'", "", $value);
                $value = str_replace('"', "", $value);

            }
            $value = trim ( $value);
            $value = ltrim ( $value);
            $value = rtrim ( $value);
        }
        
        if($key === 2){
            $dataStructure["anio_inicio"] = $value;
            $dataStructure["anio_fin"] = $value;
        } else {
            $value = (!$value)?$value:preg_replace($this->patron, "", strtoupper($value));
            $dataStructure[$this->processActualSequence[$key]] = $value;

        }

        //Decode/Encode error

        return $dataStructure;
    }

    public function validateParticularData(int $key, string $value ) : mixed{
        if($key === 5 && empty($value))
            $value = "SIN MOTOR";
        else if($key === 10 && empty($value))            
            $value = "SIN POSICION";
        else if(array_key_exists($key,$this->processRequired) && empty($value))
            $value = false;
        return $value;
    }
}

?>