<?php

require_once('Persistance.php');

class InscribeCatalogoQuimicos extends Persistance {

    protected string $tableName = 'catalogo_quimicos';

    public function generateInsertSentece() : string {
        return (!empty($this->tableName))? 
                "INSERT INTO `{$this->tableName}`(`id`, `nombre`, `descripcion`, `division`, `marca`, `aplicacion`, `catalogoprod_id`) VALUES (NULL,?,?,?,?,?);"
                :"";
    }

    public function generateSelectSentece() : string {
        return (!empty($this->tableName))?
                    "SELECT `id` FROM `{$this->tableName}` WHERE `nombre`=? AND `descripcion`=? AND `division`=? AND `marca`=? AND `aplicacion`=? AND `catalogoprod_id`=?;"
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