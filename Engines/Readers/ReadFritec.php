<?php

require_once('ReaderImplement.php');

class ReadFritec extends ReaderImplement
{

    protected string $bitacoraBasePath = "../logs/BD_FRITEC/bitacorafritec";
    protected string $bitacoraPath = "../logs/BD_FRITEC/bitacorafritec";
    protected array $processActualSequence = [1 => "marca", 2 => "year", 3 => "submodelo",4 => "modelo", 5 => "motor", 12 => "part_type", 13 => "position", 16 => "part_number"];
    protected array $processTransformation = [1, 3, 5, 16];
    protected array $processRequired = [1, 2, 3, 5, 12, 16];
    protected array $processTrim = [];

    public function readData(string $fileName, mysqli $link, array $dataToProcess, array $highestRow): void
    {

        $this->outMessage("Inicia la captura de datos desde archivo FRITEC. Registro de logs independiente... ");

        //$this->bitacoraResgistartion = new InscribeBitacora($link, "transferencia");
        $countOk = 0;
        $countNotExists = 0;
        $countRepeats = 0;
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Se detecto el siguente provedor: FRITEC', '', '', '', '0', '0');
        foreach ($dataToProcess as $rowKey => $rowValue) {
            # code...
            $readMoment = \time();
            if ((!is_array($rowValue) || gettype($rowValue) !== 'array')) {
                $typeOfRow = gettype($rowValue);
                $this->writeBitacora("time:{$readMoment}|row:{$rowKey}|status:'ERROR'|conflict:'No se puede procesar la informacion en fila'|row_type:{$typeOfRow}", $fileName);
            } else if($rowKey > 0) {
                if ($dataToRetrieve = $this->retrieveDataStructure($fileName, $rowKey, $rowValue)) {

                    $this->writeBitacora("Datos recuperados, continua proceso. ", $fileName);
                    //Validar en catalogo_producto por part_number
                    if ($idCatalogoProducto = $this->validateCatalogoProductos($fileName, $dataToRetrieve['part_number'], $link)) {

                        $this->writeBitacora("Se encontro con el Id catalogo_producto: {$idCatalogoProducto}", $fileName);
                        //Aqui data harcode
                        $dataToRetrieve["cil"] = "0";
                        if($this->addCatalogoProductos($fileName, $link, $dataToRetrieve["marca"], $dataToRetrieve["modelo"], $dataToRetrieve["anio_inicio"], 
                            $dataToRetrieve["anio_fin"], $dataToRetrieve["motor"], $dataToRetrieve["cil"], $dataToRetrieve["part_number"], $dataToRetrieve["position"], $dataToRetrieve["part_type"], $idCatalogoProducto)){                            
                            $this->writeBitacora("Se completa la captura de la fila en ProductosSalav: {$rowKey}", $fileName);
                            $countOk += 1;
                        } else {
                            $this->writeBitacora("Se completa, no se captura fila: {$rowKey}", $fileName);
                            $countRepeats += 1;
                        }
                    } else {
                        $this->writeBitacora("No se encontro el numero de parte en catalogo_producto", $fileName);
                        $countNotExists += 1;
                    }


                }
            }
        }
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Termina el proceso de lectura', '', $countNotExists, $countRepeats, '', $countOk);

    }

    protected function validateCatalogoProductos(string $fileName, string $part_number, mysqli $link): mixed
    {
        $this->writeBitacora("Se consulta Catalogo de Producto el No. De Parte: {$part_number} ", $fileName);
        $resultData = ProductosSingleton::getInstance($link)->getRowFromCatalogoProductosByPartNumber($part_number);
        //$resultData = false;

        if (is_bool($resultData))
            return false;


        if (is_array($resultData)) {

            if (count($resultData) === 0)
                return false;

            $id = $resultData[0]["id"];


            $this->outDebugMessage("Dato de array (id) =>  " . $id);

            if (!empty($id))
                return $id;
        }
        return false;
    }

    public function addCatalogoProductos(string $fileName, mysqli $link, mixed ...$data): mixed
    {
        $productoSalv = ProductosSingleton::getInstance($link)->getRowFromProductosSalavByData(...$data);
        //$productoSalv = ProductosSingleton::getInstance($link)->getRowFromProductosSalavByData("Porsche","Cayenne","2003","2003","4.5L","V8","0986MF4220","","Air Filter",0);
        $result = true;
        if($productoSalv === 0){
            //echo "<br /> No existe, se debe guardar";
            $dataToString =json_encode($data);
    
            $this->writeBitacora("Iniciara insercion en ProductoSalav : {$dataToString} ", $fileName);
            $insertData = ProductosSingleton::getInstance($link)->addRowToProductosSalav(...$data);
            if (is_bool($insertData))
                $result = $insertData;
            else if(is_int($insertData)){
                $this->writeBitacora("Se ha insertado correctamente ProductoSalav : {$insertData} ", $fileName);
                $result = $insertData;
            }   else
                $result = false;
            //$insertProductos = ProductosSingleton::getInstance($link)->addRowToProductosSalav("Porsche","Cayenne","2003","2003","4.5L","V8","0986MF4220","","Air Filter",0);
        }   else{
            //var_dump($productoSalv);
            $this->writeBitacora("Ya existe en ProductosSalav, no se guardara. Id: {$productoSalv[0]['id']} ", $fileName);
            $result = false;
        }

        return $result;
    }

    protected function transformDataIfItsNecesary(mixed $value, int $key, array $dataStructure): mixed
    { 
        $value = $this->validateParticularData($key, $value);

        if ((array_key_exists($key, $this->processTransformation) || array_key_exists($key, $this->processTrim)) && !is_bool($value)) {

            if (!array_key_exists($key, $this->processTrim)) {

                $value = str_replace('"', "", $value);
                $value = str_replace("'", " ", $value);
                $value = utf8_decode($value);
            }
            $value = trim($value);
            $value = ltrim($value);
            $value = rtrim($value);
        }

        switch ($key) {
            case 2:
                # code...
                $dataStructure["anio_inicio"] = $value;
                $dataStructure["anio_fin"] = $value;
                break;

            case 5:
                # code...
                if($value === "SIN MOTOR"){
                    $dataStructure[$this->processActualSequence[$key]] =$value ;
                    $dataStructure["cil"]  = $this->validateParticularData($key + 1, "");
                    
                } else {
                    $motorAndCil = explode(" ",$value);
                    $dataStructure[$this->processActualSequence[$key]] =  $motorAndCil[0];
                    $valueCil = $this->validateParticularData($key + 1, $motorAndCil[1]);
                    $dataStructure["cil"] = $valueCil;

                }
                break;
            
            default:
                # code...
                $value = (!$value) ? $value : preg_replace($this->patron, "", strtoupper($value));
                $dataStructure[$this->processActualSequence[$key]] = $value;
                break;
        }
/*         if ($key === 2) {
            $value = $this->validateParticularData($key, $value);
            $dataStructure["anio_inicio"] = $value;
            $dataStructure["anio_fin"] = $value;
        } if ($key === 5) {
            $motorAndCil = explode(" ",$value);
            $valueMotor = $this->validateParticularData($key, $motorAndCil[0]);
            $dataStructure[$this->processActualSequence[$key]] =$valueMotor ;
            $valueCil = $this->validateParticularData($key + 1, $motorAndCil[1]);
            $dataStructure["cil"] = $valueCil;
        }   else {
            
            $value = $this->validateParticularData($key, $value);
            $dataStructure[$this->processActualSequence[$key]] = $value;
        } */

        //Decode/Encode error

        return $dataStructure;
    }

    public function validateParticularData(int $key, string $value): mixed
    {
        if ($key === 5 && empty($value))
            $value = "SIN MOTOR";
        else if ($key === 6 && empty($value))
            $value = "0";
        else if ($key === 13 && empty($value))
            $value = "SIN POSICION";        
        else if ($key === 2 && empty($value))
            $value = "0000";
        else if (array_key_exists($key, $this->processRequired) && empty($value))
            $value = false;
        return $value;
    }

    protected function retrieveDataStructure(string $fileName, int $rowKey, array $rowValue) : mixed {
        $dataRow = "|";
        $dataStrig = $rowValue[0] . ',' . $rowValue[1] . ',"' . $rowValue[2] . '","' . $rowValue[3];
/*         foreach($rowValue as $columnKey => $columnValue)
            switch ($columnKey) {
                case 0:
                    # code...                
                    $dataStrig .= $columnValue;
                    break; 

                case 2:
                    # code...
                    $dataStrig .= ',"'.$columnValue;
                case 3:
                    # code...
                    $dataStrig .= '","'.$columnValue;
                
                case 1:
                default:
                    # code...
                    $dataStrig .= ','.$columnValue;
                    break;
            }               
         */
        
        
        $dataStrig = str_replace(",", $dataRow, $dataStrig);
        $explodeByData = explode($dataRow, $dataStrig);
        return parent::retrieveDataStructure( $fileName, $rowKey, $explodeByData);
/*         if(array_key_exists($columnKey,$this->processActualSequence)){
                
            $columnValue = utf8_decode($columnValue);
            $dataRow .= "{$this->processActualSequence[$columnKey]}:{$columnValue}|";
            $dataStructure=$this->transformDataIfItsNecesary($columnValue,$columnKey, $dataStructure);
        } 
        if($dataRow!=="|"&&count($dataStructure) > 0){
            $this->writeBitacora("Fila Actual: {$rowKey} => {$dataRow}. ",$fileName);
            if(in_array(false,$dataStructure)){
                $this->writeBitacora("No se procesara puesto que almenos uno de los datos requeridos esta vacio: {$rowKey} => N/A. ",$fileName);
                $dataStructure=false;
            }
                

        } else {
            $this->writeBitacora("Sin datos que procesar en la fila: {$rowKey} => N/A. ",$fileName);
            $dataStructure=false;

        }
        return $dataStructure;*/
    }
}
