<?php 

require_once('Persistance.php');
require_once('../Config/GeneralLogger.php');

class InscribeProductosSalav extends Persistance{
    protected string $tableName = 'ProductosSalav';

    public function generateInsertSentece() : string {
        return (!empty($this->tableName))?
                    "INSERT INTO `{$this->tableName}`(`id`, `Marca`, `Modelo`, `Anio_inicio`, `Anio_fin`, `motor`, `Cil`, `Part_number`, `Position`, `Part_type`, `Id_catprod`) VALUES (NULL,'?','?','?','?','?','?','?','?','?','?');"
                    :"";
    }
    public function generateSelectSentece() : string {
        return (!empty($this->tableName))?
                    "SELECT `id` FROM `{$this->tableName}` WHERE `Marca`='?' AND `Modelo`= '?' AND `Anio_inicio`='?' AND `Anio_fin`='?' AND `motor`='?' AND `Cil`='?' AND `Part_number`='?' AND `Position`='?' AND `Part_type`='?' AND `Id_catprod`='?';"
                    :"";
                    
    }

    public function executeQuery($typeOf = "select",mixed ...$data){
        switch ($typeOf) {
            case 'insert':
                # code...
                echo "<br /> gonna be insert";
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