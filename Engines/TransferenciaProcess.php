<?php

require_once('MyReadFilter.php');
require_once('../Responses/ResultResponseData.php');
require_once('./Readers/ReadBosch.php');
require('./Singleton/BitacoraSingleton.php');
require('./Singleton/ProductosSingleton.php');

class TransferenciaProcess{
    private $_log;
    public array $resultsForFiles = array();
    public ResultResponseData $resultData;
    
    public function __construct(string $process = "transferencia"){
        $this->_log = new  GeneralLogger(\get_class($this),$process);
    }

    public function retrieveAndProcessFiles(mysqli $link) : mixed{
        try {
            //code...
            $conteos = count($_FILES["archivos"]["name"]);
            BitacoraSingleton::getInstance($link)->addRowToBitacora('',"Inicia proceso de lectura de la carpeta, archivos a procesar: {$conteos}",'','','','','');
            $this->_log->outMessage("Se procesaran: {$conteos} Archivos");
            for ($i = 0; $i < $conteos; $i++) {
                $ubicacionTemporal = $_FILES["archivos"]["tmp_name"][$i];
                $nombrearchivoexcel = $_FILES["archivos"]["name"][$i];
                $this->_log->outMessage("Se procesa la ubicacion: {$ubicacionTemporal}");
                $this->_log->outMessage("Para el archivo recibido con nombre: {$nombrearchivoexcel}");
                $explodeByExtension = explode(".", $nombrearchivoexcel);
                if(end($explodeByExtension )=="xlsx" || end($explodeByExtension )=="xls"){
                    $nombrearchivoexcel=$link->real_escape_string($nombrearchivoexcel);
                    BitacoraSingleton::getInstance($link)->addRowToBitacora($nombrearchivoexcel,'Inicia proceso de lectura','','','','','');
                    $this->readFileByType($ubicacionTemporal, $nombrearchivoexcel,$link);
                }else {
                    $this->setOnResult($nombrearchivoexcel,"dont_catched","El archivo no cumple la extension solicitada. ",false);
                }
            }
        } catch (\Exception $e) {
            //throw $th;
            $this->_log->outErrorMessage("Imposible continuar lectura de archivos. Error: \n ".$e->getMessage());
        }
        return $this->resultsForFiles;
    }

    protected function readFileByType(string $pathTofile, string $fileName, mysqli $link): bool {
        $result = true;
        try {
            //code...
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();

            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($pathTofile);
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            $reader->setReadFilter(new MyReadFilter());

            $spreadsheet = $reader->load($pathTofile);

            $activeSheetData = $spreadsheet->getActiveSheet()->toArray();

            $highestRow = $spreadsheet->getActiveSheet()->getHighestRowAndColumn();

            $totalDeHojas = $spreadsheet->getSheetCount();

            //Determina mediante la cantidad de hojas el tipo de procedimiento
            switch ($totalDeHojas) {
                case 4:
                case 5:
                    $this->_log->outMessage("Se trata seguramente de archivo de quimicos. ");
                    break;
                
                default:

                    //Determina mediante la cantidad de columnas en la primera hoja el tipo de procedimiento
                    switch ($highestRow["column"]) {
                        case 'U':
                            $this->_log->outMessage("Se trata de un archivo Bosch. ");
                            $readBosch = new ReadBosch(NULL,PROCESS_NAME);
                            $readBosch->readData($fileName,$link,$activeSheetData,$highestRow);
                            $this->setOnResult($fileName,"BOSCH","Se procesa correctamente.",true);
                            break;
                        
                        case 'D':
                            $this->_log->outMessage("Se trata de un archivo Fritec. ");
                            $this->setOnResult($fileName,"FRITEC","Se procesa correctamente.",true);
                            break;
                            
                        case 'L':
                            $this->_log->outMessage("Se trata de un archivo Fulo. ");
                            $this->setOnResult($fileName,"FULO","Se procesa correctamente.",true);
                            break;
                        
                        case 'K':
                            $this->_log->outMessage("Se trata de un archivo Bujias. ");
                            $this->setOnResult($fileName,"BUJIA","Se procesa correctamente.",true);
                            break;
                            
                        case 'I':
                        case 'J':
                            $this->_log->outMessage("Se trata de un archivo Interfil. ");
                            $this->setOnResult($fileName,"INTERFIL","Se procesa correctamente.",true);
                            break;
                        
                        case 'AQ':
                            $this->_log->outMessage("Se trata de un archivo Lubricantes. ");
                            $this->setOnResult($fileName,"LUBRICANTES","Se procesa correctamente.",true);
                            break;

                        default:
                            $this->setOnResult($fileName,"undefined_type","El tipo de archivo no se ha identificado.",false);
                            break;
                    }
                    break;
            }
        } catch (\Exception $e) {
            //throw $th;
            $this->_log->outErrorMessage("Imposible continuar lectura de archivos para {$fileName}. Error: \n ".$e->getMessage());
            $this->setOnResult($fileName,"undefined_type","Error al procesar lectura de datos.",false);
            $result = false;
        }
        return $result;
    }

    public function setOnResult(string $fileName, string $type, string $message, bool $status): void{
        $this->resultData = new ResultResponseData($fileName,$type,$message, $status);
        if(!$status){
            $this->_log->outMessage("Se registra en Errores. ");
            $this->_log->outErrorMessage("Imposible procesar el archivo: \n ".$this->resultData->toString());
        }
        $this->_log->outMessage("Se concluye el procesamiento del archivo. ");
        $this->resultsForFiles[] = $this->resultData->toArray();
    }

}
?>