<?php

require_once('Persistance.php');
require_once('../Config/GeneralLogger.php');

class InscribeCatalogoOpcion extends Persistance{

    protected string $tableName = 'catalogo_lubricantes';

    public function generateInsertSentece() : string {
        return (!empty($this->tableName))?
                    "INSERT INTO `{$this->tableName}`(`id`, `opcion_1`, `opcion_2`) VALUES (NULL,?,?);"
                    :"";
    }

    public function generateSelectSentece() : string {
        return (!empty($this->tableName))?
                    "SELECT `opcion_1`, `opcion_2` FROM `{$this->tableName}` WHERE `opcion_1`=? AND `opcion_1`=? AND `opcion_1`=? AND `opcion_1`=? AND `opcion_1`=? AND `opcion_1`=? AND `opcion_1`=? AND `opcion_1`=? AND `opcion_1`=? AND`opcion_2`=? AND `opcion_2`=? AND `opcion_2`=? AND `opcion_2`=? AND `opcion_2`=? AND `opcion_2`=? AND `opcion_2`=? AND `opcion_2`=? AND `opcion_2`=?"
                    :"";
    }

    public function executeQuery($typeOf = "select",mixed ...$data) : mixed{
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
            $result = $this->prepareAndExecuteSentece($typeOf, $sqlSentence,...$data);
            
        else 
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: Query is empty");

        return $result;
    }   

}

?>