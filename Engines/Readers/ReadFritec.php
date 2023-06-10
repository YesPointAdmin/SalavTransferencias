<?php

require_once('ReaderImplement.php');

class ReadFritec extends ReaderImplement
{

    protected string $bitacoraBasePath = "../logs/BD_FRITEC/bitacorafritec";
    protected string $bitacoraPath = "../logs/BD_FRITEC/bitacorafritec";
    protected array $processActualSequence = [1 => "marca", 2 => "year", 3 => "submodelo", 4 => "modelo", 5 => "motor", 12 => "part_type", 13 => "position", 16 => "part_number"];
    protected array $processTransformation = [1, 4, 5, 16];
    protected array $processRequired = [1, 4, 16];
    protected array $processTrim = [2, 3, 4, 12, 13];
    public string $fileName;

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
            } else if ($rowKey > 0) {
                if ($dataToRetrieve = $this->retrieveDataStructure($fileName, $rowKey, $rowValue)) {

                    $this->writeBitacora("Datos recuperados, continua proceso. ", $fileName);
                    //Validar en catalogo_producto por part_number
                    if ($idCatalogoProducto = $this->validateCatalogoProductos($fileName, $dataToRetrieve['part_number'], $link)) {

                        $this->writeBitacora("Se encontro con el Id catalogo_producto: {$idCatalogoProducto}", $fileName);
                        //Aqui data harcode
                        $dataToRetrieve["cil"] = "0";
                        if ($this->addCatalogoProductos(
                            $fileName,
                            $link,
                            $dataToRetrieve["marca"],
                            $dataToRetrieve["modelo"],
                            $dataToRetrieve["anio_inicio"],
                            $dataToRetrieve["anio_fin"],
                            $dataToRetrieve["motor"],
                            $dataToRetrieve["cil"],
                            $dataToRetrieve["part_number"],
                            $dataToRetrieve["position"],
                            $dataToRetrieve["part_type"],
                            $idCatalogoProducto
                        )) {
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
        $resultData = ProductosSingleton::getInstance($link)->getRowFromCatalogoProductosByPartNumber($fileName, $part_number);

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
        $productoSalv = ProductosSingleton::getInstance($link)->getRowFromProductosSalavByData($fileName, ...$data);
       
        $result = true;
        if ($productoSalv === 0) {

            $dataToString = json_encode($data);

            $this->writeBitacora("Iniciara insercion en ProductoSalav : {$dataToString} ", $fileName);
            $insertData = ProductosSingleton::getInstance($link)->addRowToProductosSalav($fileName, ...$data);
            if (is_bool($insertData))
                $result = $insertData;
            else if (is_int($insertData)) {
                $this->writeBitacora("Se ha insertado correctamente ProductoSalav : {$insertData} ", $fileName);
                $result = $insertData;
            } else
                $result = false;
        } else {
            
            $this->writeBitacora("Ya existe en ProductosSalav, no se guardara. Id: {$productoSalv[0]['id']} ", $fileName);
            $result = false;
        }

        return $result;
    }

    protected function transformDataIfItsNecesary(mixed $value, int $key, array $dataStructure, string $fileName = "no_filename"): mixed
    {
        $value = $this->validateParticularData($key, $value);
        $this->writeBitacora("Value key: {$key} Value export: " . var_export($value, true), $fileName);

        if ((in_array($key, $this->processTransformation) || in_array($key, $this->processTrim)) && !is_bool($value)) {

            if (!in_array($key, $this->processTrim)) {

                $value = str_replace("'", "", $value);
                $value = str_replace('"', "", $value);
                $value = utf8_decode($value);
            }
            $value = str_replace('"', "", $value);
            $value = trim($value);
            $value = ltrim($value);
            $value = rtrim($value);
            $this->writeBitacora("Value transformed key: {$key} Value transformed export: " . var_export($value, true), $fileName);
        }

        switch ($key) {
            case 2:
                # code...
                $dataStructure["anio_inicio"] = $value;
                $dataStructure["anio_fin"] = $value;
                break;

            case 5:
                # code...
                if ($value === "SIN MOTOR") {
                    $dataStructure[$this->processActualSequence[$key]] = $value;
                    $dataStructure["cil"]  = $this->validateParticularData($key + 1, "");
                } else {
                    $motorAndCil = explode(" ", $value);
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
        else if (in_array($key, $this->processRequired) && empty($value))
            $value = false;
        return $value;
    }

    protected function retrieveDataStructure(string $fileName, int $rowKey, array $rowValue): mixed
    {
        $dataRow = "|";
        $dataStrig = $rowValue[0] . ',' . $rowValue[1] . ',"' . $rowValue[2] . '","' . $rowValue[3];
       


        $dataStrig = str_replace(",", $dataRow, $dataStrig);
        $explodeByData = explode($dataRow, $dataStrig);
        return parent::retrieveDataStructure($fileName, $rowKey, $explodeByData);
 
    }
}
