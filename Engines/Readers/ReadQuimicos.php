<?php

require_once('ReaderImplement.php');
require_once('../Capture/InscribeCatalogoQuimicos.php');

class ReadQuimicos extends ReaderImplement
{
    private InscribeCatalogoQuimicos $quimicosValidate;
    protected string $bitacoraBasePath = "../logs/BD_QUIMICOS/bitacoraquimicos";
    protected string $bitacoraPath = "../logs/BD_QUIMICOS/bitacoraquimicos";
    protected array $processActualSequence = [0 => "part_number", 1 => "descripcion", 2 => "division", 3 => "marca", 4 => "aplicacion"];
    protected array $processTransformation = [0, 1, 2, 3, 4];
    protected array $processRequired = [0];
    protected array $processToupper = [0, 1, 2, 3, 4];

    //agregar total de hojas antes de highestRow
    public function readDataQuimicos(string $fileName, mysqli $link, array $dataToProcess, int $totalDeHojas, array $highestRow): void
    {
        $this->quimicosValidate = new InscribeCatalogoQuimicos($link, PROCESS_NAME);

        $this->outMessage("Inicia la captura de datos desde archivo QUIMICOS. Registro de logs independiente... ");

        $countOk = 0;
        $countNotExists = 0;
        $countRepeats = 0;

        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Se detecto el siguente provedor: QUIMICOS', '', '', '', '0', '0');


        foreach ($dataToProcess as $rowKey => $rowValue) {
            //$this->writeBitacora("Datos recuperados var_export. " . var_export($dataToProcess, true), $fileName);
            //$this->writeBitacora("Datos recuperados var_export. " . var_export($rowKey, true), $fileName);
            //$this->writeBitacora("Datos recuperados var_export. " . var_export($rowValue, true), $fileName);
            # code...
            $readMoment = \time();
            if ((!is_array($rowValue) || gettype($rowValue) !== 'array')) {
                $typeOfRow = gettype($rowValue);
                $this->writeBitacora("time:{$readMoment}|row:{$rowKey}|status:'ERROR'|conflict:'No se puede procesar la informacion en fila'|row_type:{$typeOfRow}", $fileName);
            } else if ($rowKey > 0) {

                //$this->writeBitacora("Datos recuperados var_export. " . var_export($rowKey, true), $fileName);

                if ($dataToRetrieve = $this->retrieveDataStructure($fileName, $rowKey, $rowValue)) {

                    $this->writeBitacora("Datos recuperados, continua proceso. ", $fileName);
                    //$this->writeBitacora("Datos recuperados var_export. " . var_export($dataToRetrieve, true), $fileName);
                    //Validar en catalogo_producto por part_number
                    if ($idCatalogoProducto = $this->validateCatalogoProductos($fileName, $dataToRetrieve['part_number'], $link)) {

                        $this->writeBitacora("Se encontro con el Id catalogo_quimicos: {$idCatalogoProducto}", $fileName);
                        //Aqui data harcode
                        if ($this->addCatalogoQuimicos(
                            $fileName,
                            $link,
                            $dataToRetrieve["part_number"],
                            $dataToRetrieve["descripcion"],
                            $dataToRetrieve["division"],
                            $dataToRetrieve["marca"],
                            $dataToRetrieve["aplicacion"],
                            $idCatalogoProducto
                        )) {
                            $this->writeBitacora("Se completa la captura de la fila en QuimicosSalav: {$rowKey}", $fileName);
                            $countOk += 1;
                        } else {
                            $this->writeBitacora("Se completa, no se captura fila: {$rowKey}", $fileName);
                            $countRepeats += 1;
                        }
                    } else {
                        $this->writeBitacora("No se encontro el numero de parte en catalogo_quimicos", $fileName);
                        $countNotExists += 1;
                    }
                }
            }
        }

        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Termina el proceso de lectura', '', $countNotExists, $countRepeats, '', $countOk);
    }

    protected function validateCatalogoProductos(string $fileName, string $part_number, mysqli $link): mixed
    {
        $this->writeBitacora("Se consulta Catalogo de Quimicos el No. De Parte: {$part_number} ", $fileName);
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

    public function addCatalogoQuimicos(string $fileName, mysqli $link, mixed ...$data): mixed
    {
        $QuimicosSalav = $this->quimicosValidate->executeQuery("select", ...$data);
        $result = true;
        if ($QuimicosSalav === 0) {
            //echo "<br /> No existe, se debe guardar";
            $dataToString = json_encode($data);

            $this->writeBitacora("Iniciara insercion en QuimicosSalav : {$dataToString} ", $fileName);
            $insertData = $this->quimicosValidate->executeQuery("insert", ...$data);
            if (is_bool($insertData))
                $result = $insertData;
            else if (is_int($insertData)) {
                $this->writeBitacora("Se ha insertado correctamente QuimicosSalav : {$insertData} ", $fileName);
                $result = $insertData;
            } else
                $result = false;
        } else {

            $this->writeBitacora("Ya existe en QuimicosSalav, no se guardara. Id: {$QuimicosSalav[0]['id']} ", $fileName);
            $result = false;
        }

        return $result;
    }

    protected function transformDataIfItsNecesary(mixed $value, int $key, array $dataStructure, string $fileName = "no_filename"): mixed
    {

        $value = $this->validateParticularData($key, $value);

        if ((in_array($key, $this->processTransformation) || in_array($key, $this->processToupper)) && !is_bool($value)) {

            if (!in_array($key, $this->processToupper)) {
                $value = strtoupper($value);
            }
            $value = utf8_decode($value);
        }
       
        $dataStructure[$this->processActualSequence[$key]] = $value;

        return $dataStructure;
    }

    public function validateParticularData(int $key, string $value): mixed
    {
        if (in_array($key, $this->processRequired) && empty($value))
            $value = false;
        return $value;
    }
}
