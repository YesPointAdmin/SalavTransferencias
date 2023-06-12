<?php
//use App\Capture;
namespace App\Engines\Readers;
use App\Config\GeneralLogger;
use mysqli;
//require_once("../Capture/InscribeBitacora.php");

class ReaderImplement extends GeneralLogger{

    protected string $bitacoraBasePath = "../logs/BD_GENERAL/bitacorageneral";
    protected string $bitacoraPath = "../logs/BD_GENERAL/bitacorageneral";
    protected array $processActualSequence = [1=>"marca",2=>"year",3=>"modelo",4=>"motor",5=>"part_type",6=>"position",7=>"part_number"];
    protected string $patron = '/(\w+) (\d+), (\d+)/i';
    //protected InscribeBitacora $bitacoraResgistartion;

    public function readData(string $fileName, mysqli $link, array $dataToProcess, array $highestRow) : void {

        $this->outMessage("Inicia la captura de datos desde archivo. ");
        
        //$this->bitacoraResgistartion = new InscribeBitacora($link, "transferencia");
        foreach ($dataToProcess as $rowKey => $rowValue) {
            # code...
            $readMoment = \time();
            if(!is_array($rowValue) || gettype($rowValue) !== 'array'){
                $typeOfRow = gettype($rowValue);
                $this->writeBitacora("time:{$readMoment}|row:{$rowKey}|status:'ERROR'|conflict:'No se puede procesar la informacion en fila'|row_type:{$typeOfRow}",$fileName);
            } else {
                $dataRow = "|";
                foreach($rowValue as $columnKey => $columnValue)
                    $dataRow .= "columnKey:{$columnKey}=columnValue:{$columnValue}|";
                
                $this->writeBitacora("DataToProcess key: {$rowKey} values: {$dataRow}. ",$fileName);
            }
        }

    }

    protected function writeBitacora(string $message, string $fileName):bool{

        if($this->bitacoraBasePath===$this->bitacoraPath)
            $this->setBitacoraPath($fileName);
        
        return $this->bitacoraWrittingProcessCall("{$this->getFormatDateToMessage()} => ".$message,$this->bitacoraPath);
    }

    //regresa path de bitacora
    private function setBitacoraPath(string $fileName) : void {
        $cleanName = (str_contains($fileName, '.xlsx'))?str_replace(".xlsx", "", $fileName):str_replace(".xls", "", $fileName);
        $hoy = date("Y_m_d");
        $this->bitacoraPath = "{$this->bitacoraBasePath}_{$hoy}" . "_" . "{$cleanName}.log";
    }

    protected function retrieveDataStructure(string $fileName, int $rowKey, array $rowValue) : mixed {
        $dataRow = "|";
        $dataStructure = [];
        foreach($rowValue as $columnKey => $columnValue){
            
            if(array_key_exists($columnKey,$this->processActualSequence)){
                
				$columnValue = utf8_encode($columnValue);
                $dataRow .= "{$this->processActualSequence[$columnKey]}:{$columnValue}|";
                $dataStructure=$this->transformDataIfItsNecesary($columnValue,$columnKey, $dataStructure, $fileName);
            }
        }
       // $this->writeBitacora("Datos recuperados var_export. " . var_export($dataStructure, true), $fileName);
        if($dataRow!=="|"&&count($dataStructure) > 0){
            $this->writeBitacora("Fila Actual: {$rowKey} => {$dataRow}. ",$fileName);
            if(in_array(false,$dataStructure)){
                $this->writeBitacora("No se procesara puesto que almenos uno de los datos requeridos esta vacio: {$rowKey} => N/A. ",$fileName);
                $dataStructure=false;
            }
                

        } else {
           // $this->writeBitacora("Datos recuperados var_export. " . var_export($dataRow, true), $fileName);
            $this->writeBitacora("Sin datos que procesar en la fila: {$rowKey} => N/A. ",$fileName);
            $dataStructure=false;

        }
        return $dataStructure;
    }

    protected function transformDataIfItsNecesary(mixed $value, int $key, array $dataStructure, string $fileName = "no_filename") : mixed {
        return false;
    }

    public function getExplodeAnio(mixed $value, array $dataStructure) : mixed{
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
        return $dataStructure;
    }

}

?>