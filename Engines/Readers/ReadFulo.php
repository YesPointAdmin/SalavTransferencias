<?php

namespace App\Engines\Readers;
use App\Engines\Singleton\BitacoraSingleton;
use App\Engines\Singleton\ProductosSingleton;
use mysqli;
class ReadFulo extends ReaderImplement
{

    protected string $bitacoraBasePath = "../logs/BD_FULO/bitacorafulo";
    protected string $bitacoraPath = "../logs/BD_FULO/bitacorafulo";
    protected array $processActualSequence = [1 => "marca", 5 => "year", 2 => "modelo", 4 => "motor", 6 => "part_number", 7 => "part_number", 8 => "part_number", 9 => "part_number"];
    protected array $processTransformation = [1, 2, 4];
    protected array $processRequired = [1, 5, 2, 4, 6];
    protected array $processTrim = [6];

    public function readData(string $fileName, mysqli $link, array $dataToProcess, array $highestRow): void
    {

        $this->outMessage("Inicia la captura de datos desde archivo FULO. Registro de logs independiente... ");

        //$this->bitacoraResgistartion = new InscribeBitacora($link, "transferencia");
        $countOk = 0;
        $countNotExists = 0;
        $countRepeats = 0;
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Se detecto el siguente provedor: FULO', '', '', '', '0', '0');
        foreach ($dataToProcess as $rowKey => $rowValue) {
            # code...
            $readMoment = \time();
            if ((!is_array($rowValue) || gettype($rowValue) !== 'array')) {
                $typeOfRow = gettype($rowValue);
                $this->writeBitacora("time:{$readMoment}|row:{$rowKey}|status:'ERROR'|conflict:'No se puede procesar la informacion en fila'|row_type:{$typeOfRow}", $fileName);
            } else if ($rowKey > 8) {
                if ($dataToRetrieve = $this->retrieveDataStructure($fileName, $rowKey, $rowValue)) {

                    $this->writeBitacora("Datos recuperados, continua proceso. ", $fileName);
                    $this->writeBitacora("Datos recuperados var_export. " . var_export($dataToRetrieve, true), $fileName);
                    //Validar en catalogo_producto por part_number
                    foreach($dataToRetrieve["part_number"] as $partKey => $part_number){
                        if ($idCatalogoProducto = $this->validateCatalogoProductos($fileName, $part_number, $link)) {
    
                            $this->writeBitacora("Se encontro con el Id catalogo_producto: {$idCatalogoProducto}", $fileName);
                            //Aqui data harcode
                            $dataToRetrieve["cil"] = "0";
                            $dataToRetrieve["position"] = "central";
                            $dataToRetrieve["part_type"] = "Refaccion";
                            if ($this->addCatalogoProductos(
                                $fileName,
                                $link,
                                $dataToRetrieve["marca"],
                                $dataToRetrieve["modelo"],
                                $dataToRetrieve["anio_inicio"],
                                $dataToRetrieve["anio_fin"],
                                $dataToRetrieve["motor"],
                                $dataToRetrieve["cil"],
                                $part_number,
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
        }
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Termina el proceso de lectura', '', $countNotExists, $countRepeats, '', $countOk);
    }

    protected function validateCatalogoProductos(string $fileName, string $part_number, mysqli $link): mixed
    {
        $this->writeBitacora("Se consulta Catalogo de Producto el No. De Parte: {$part_number} ", $fileName);
        $resultData = ProductosSingleton::getInstance($link)->getRowFromCatalogoProductosByPartNumber($fileName, $part_number);
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
        $productoSalv = ProductosSingleton::getInstance($link)->getRowFromProductosSalavByData($fileName, ...$data);
        //$productoSalv = ProductosSingleton::getInstance($link)->getRowFromProductosSalavByData("Porsche","Cayenne","2003","2003","4.5L","V8","0986MF4220","","Air Filter",0);
        $result = true;
        if ($productoSalv === 0) {
            //echo "<br /> No existe, se debe guardar";
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
            //$insertProductos = ProductosSingleton::getInstance($link)->addRowToProductosSalav("Porsche","Cayenne","2003","2003","4.5L","V8","0986MF4220","","Air Filter",0);
        } else {
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
        // /^$/
        $regex = "[ ]";
        switch ($key) {
            case 5:
                //separar anios
                $separadaAnio = explode("-", $value);
                switch (count($separadaAnio)) {
                    case 1:
                        $dataStructure["anio_inicio"] = $separadaAnio[0];
                        $dataStructure["anio_fin"] = $separadaAnio[0];
                        break;
                    case 2:
                        $dataStructure["anio_inicio"] = $separadaAnio[0];
                        $dataStructure["anio_fin"] = $separadaAnio[1];
                        break;

                    default:
                        $dataStructure["anio_inicio"] = 0000;
                        $dataStructure["anio_fin"] = 0000;
                        break;
                }

                break;

                // "/[^-|null]/"
            case 6:
            case 7:
            case 8:
            case 9:
                //$this->writeBitacora("Tipo value: ", gettype($value));
                //$value = string;
                if(!empty($value))
                    if($value !== "-")
                        $dataStructure[$this->processActualSequence[$key]][] = $value;
                
               /*  if ($value !== $regex)
                    $dataStructure[$this->processActualSequence[$key]] = $value; */


                break;

            default:
                # code...
                $value = (!$value) ? $value : preg_replace($this->patron, "", strtoupper($value));
                $dataStructure[$this->processActualSequence[$key]] = $value;
                break;
        }

        //Decode/Encode error

        return $dataStructure;
    }

    public function validateParticularData(int $key, string $value): mixed
    {
        if ($key === 2 && empty($value))
            $value = "SIN MOTOR";
        // else if ($key === 10 && empty($value))
        //   $value = "SIN POSICION";/* falta agregar variable estatica con el valor de central y posicion refaccion */
        else if (in_array($key, $this->processRequired) && empty($value))
            $value = false;
        return $value;
    }
}
