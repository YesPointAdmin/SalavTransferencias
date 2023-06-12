<?php
namespace App\Capture;

require_once('../Config/GeneralLogger.php');

class InscribeCatalogoAditivoSI extends Persistance{

    protected string $tableName = 'aditivo_sistema_inyeccion';

    public function generateInsertSentece() : string {
        return (!empty($this->tableName))?
                    "INSERT INTO `{$this->tableName}`(`id`, `opcion_id`) VALUES (NULL,?);"
                    :"";
    }

    public function generateSelectSentece() : string {
        return (!empty($this->tableName))?
                    "SELECT * FROM `{$this->tableName}` ;"
                    :"";
    }

    public function executeQuery($typeOf = "select", string $fileName, mixed ...$data) : mixed{
        $result = false;

        switch ($typeOf) {
            case 'insert':
                # code...
                //echo "<br /> gonna be insert";
                $sqlSentence = $this->generateInsertSentece();
                break;
            
            default:
                # code...
                $sqlSentence = $this->generateSelectSentece();
                break;
        }
        //$sqlSentence = $this->generateInsertSentece() or throw new Exception("Error at Generate Sentence", 1);
        
        if(!empty($sqlSentence))
            $result = $this->prepareAndExecuteSentece($typeOf, $sqlSentence, $fileName, ...$data);
            
        else 
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: Query is empty");

        return $result;
    }   

}

?>