<?php

require_once('ReaderImplement.php');

class ReadLubricantes extends ReaderImplement{
    
    protected string $bitacoraBasePath = "../logs/BD_LUBRICANTES/bitacoralubricantes";
    protected string $bitacoraPath = "../logs/BD_LUBRICANTES/bitacoralubricantes";
    protected array $processActualSequence = [1 => "marca", 2 => "modelo", 3 => "year", 4 => "motor", 5 => "viscosidad", 6 => "servicio", 7 => "homologacion"];
    protected array $lubricantesSequence = [8=>"0_60k",9=>"0_60k",10=>"61k_100k",11=>"61k_100k",12=>"101k_150k",13=>"101k_150k",14=>"151k_200k",15=>"151k_200k",16=>"200k_o_mas",17=>"200k_o_mas"];
    protected array $frenosInyeccionAndRefrigeranteSequence = [28=>"fluido_de_frenos",30=>"fluido_de_frenos",31=>"0_200k_refrigerante",32=>"0_200k_refrigerante",33=>"200k_o_mas_refrigerante",34=>"200k_o_mas_refrigerante",36=>"aditivo_sistema_inyeccion",37=>"aditivo_sistema_inyeccion"];
    protected array $gasolinaAndGrasasSequence = [38 => "aditivo_gasolina", 39 => "grasa_chasi", 40 => "grasa_juntas", 41 => "grasa_baleros"];
    protected array $processTransformation = [1, 3, 5];
    protected array $processRequired = [1, 2, 3, 5, 9, 13];
    protected array $processTrim = [13];
    
    public function readData(string $fileName, mysqli $link, array $dataToProcess, array $highestRow): void
    {

        $this->outMessage("Inicia la captura de datos desde archivo Lubricantes. Registro de logs independiente... ");

        //$this->bitacoraResgistartion = new InscribeBitacora($link, "transferencia");
        $countOk = 0;
        $countNotExists = 0;
        $countRepeats = 0;
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Se detecto el siguente provedor: Lubricantes', '', '', '', '0', '0');
        foreach ($dataToProcess as $rowKey => $rowValue) {
            # code...
            $readMoment = \time();
            if ((!is_array($rowValue) || gettype($rowValue) !== 'array')) {
                $typeOfRow = gettype($rowValue);
                $this->writeBitacora("time:{$readMoment}|row:{$rowKey}|status:'ERROR'|conflict:'No se puede procesar la informacion en fila'|row_type:{$typeOfRow}", $fileName);
            } else if($rowKey > 2) {
                
                //$this->writeBitacora("Test lubricantes values export at row: {$rowKey}. ".var_export($rowValue,true), $fileName);
                $dataStructure = $this->retrieveDataStructure($fileName, $rowKey, $rowValue);
                $this->writeBitacora("Test lubricantes dataStructure export at row: {$rowKey}. ".var_export($dataStructure,true), $fileName);
/*                 if ($dataToRetrieve = $this->retrieveDataStructure($fileName, $rowKey, $rowValue)) {

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


                } */
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

    protected function transformDataIfItsNecesary(mixed $value, int $key, array $dataStructure, string $fileName = "no_filename"): mixed
    {

        $value = $this->validateParticularData($key, $value);

        if ((in_array($key, $this->processTransformation) || in_array($key, $this->processTrim)) && !is_bool($value)) {

            if (!in_array($key, $this->processTrim)) {
                $value = str_replace(".", "", $value);
                $value = str_replace(",", "", $value);
                $value = str_replace("/", "", $value);
                $value = str_replace("'", "", $value);
                $value = str_replace('"', "", $value);
            }
            $value = trim($value);
            $value = ltrim($value);
            $value = rtrim($value);
        }

        if ($key === 2) {
            $dataStructure["anio_inicio"] = $value;
            $dataStructure["anio_fin"] = $value;
        } else {
            $value = (!$value) ? $value : preg_replace($this->patron, "", strtoupper($value));
            $dataStructure[$this->processActualSequence[$key]] = $value;
        }

        //Decode/Encode error

        return $dataStructure;
    }

    public function validateParticularData(int $key, string $value): mixed
    {
        if ($key === 5 && empty($value))
            $value = "SIN MOTOR";
        else if ($key === 10 && empty($value))
            $value = "SIN POSICION";
        else if (in_array($key, $this->processRequired) && empty($value))
            $value = false;
        return $value;
    }

    protected function retrieveDataStructure(string $fileName, int $rowKey, array $rowValue) : mixed {
        $dataRow = "|";
        $dataStructure = [];
        foreach($rowValue as $columnKey => $columnValue){
            
            if(array_key_exists($columnKey,$this->processActualSequence)){
                $this->writeBitacora("Test lubricantes General Column: {$columnKey}. Data: ".var_export($columnValue,true), $fileName);
                $dataStructure[$this->processActualSequence[$columnKey]] = $columnValue;
                
            }else if(array_key_exists($columnKey,$this->lubricantesSequence)){
                $this->writeBitacora("Test lubricantes Lubs Column: {$columnKey}. Data: ".var_export($columnValue,true), $fileName);
                $dataStructure[$this->lubricantesSequence[$columnKey]][] = $columnValue;

            }else if(array_key_exists($columnKey,$this->frenosInyeccionAndRefrigeranteSequence)){
                $this->writeBitacora("Test Frenos, Inyeccion o Refrigerantes Lubs Column: {$columnKey}. Data: ".var_export($columnValue,true), $fileName);
                $dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$columnKey]][] = $columnValue;

            } else if(array_key_exists($columnKey,$this->gasolinaAndGrasasSequence)){
                $this->writeBitacora("Test aditivo and grasas Column: {$columnKey}. Data: ".var_export($columnValue,true), $fileName);
                $dataStructure[$this->gasolinaAndGrasasSequence[$columnKey]] = $columnValue;
            }
        }

        return $dataStructure;
    }
}

?>