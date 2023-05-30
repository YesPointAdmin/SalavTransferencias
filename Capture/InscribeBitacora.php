<?php 

require_once('Persistance.php');
require_once('../Config/GeneralLogger.php');

class InscribeBitacora extends Persistance{
    protected string $tableName = 'bitacora';

    public function executeInsert($NombreArchivo = '', $MensajeProceso = '', $MensajeErrores = '', $No_NoecnotradosCatalogoProductos = '', $No_Repetidos = '', $Noerrores = '', $Nocorrectos = '' ){
        $sqlSentence = $this->generateInsertSentece();
        if(!empty($sqlSentence))
            $this->prepareAndExecuteSentece($sqlSentence,$NombreArchivo,$MensajeProceso,$MensajeErrores,$No_NoecnotradosCatalogoProductos,$No_Repetidos,$Noerrores,$Nocorrectos);
        else 
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: Query is empty");
    }

    public function generateInsertSentece() : string {
        return (!empty($this->tableName))?
                    "INSERT INTO `{$this->tableName}`(`id`, `NombreArchivo`, `MensajeProceso`, `MensajeErrores`,`No_NoecnotradosCatalogoProductos`,`No_Repetidos`, `Noerrores`, `Nocorrectos`) VALUES (NULL,?,?,?,?,?,?,?);"
                    :"";
    }
    public function generateSelectSentece() : string {
        return (!empty($this->tableName))?
                    "SELECT * FROM `{$this->tableName}`;"
                    :"";
    }

    public function executeQuery($typeOf = "select",mixed ...$data){
        switch ($typeOf) {
            case 'insert':
                # code...
                $sqlSentence = $this->generateInsertSentece();
                break;
            
            default:
                # code...
                $sqlSentence = $this->generateSelectSentece();
                break;
        }
        //$sqlSentence = $this->generateInsertSentece() or throw new Exception("Error at Generate Sentence", 1);
        if(!empty($sqlSentence))
            $this->prepareAndExecuteSentece($sqlSentence,...$data);
        else 
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: Query is empty");
    }

}

?>