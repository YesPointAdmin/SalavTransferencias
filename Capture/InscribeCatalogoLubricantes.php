<?php

require_once('Persistance.php');
require_once('../Config/GeneralLogger.php');

class InscribeCatalogoLubricantes extends Persistance{

    protected string $tableName = 'catalogo_lubricantes';

    public function generateInsertSentece() : string {
        return (!empty($this->tableName))?
                    "INSERT INTO `{$this->tableName}`(`id`, `marca`, `modelo`,`anio_inicio`,`anio_fin`, `motor`, `viscocidad`, `servicio`, `homologacion`, `inventario_id`, `sucursal_id`) VALUES (NULL,?,?,?,?,?,?,?,?,?,?);"
                    :"";
    }

    public function generateSelectSentece() : string {
        return (!empty($this->tableName))?
                    "SELECT `id`, `marca`, `modelo`, `anio_inicio`,`anio_fin`, `motor`, `viscocidad`, `servicio`, `homologacion` FROM `{$this->tableName}` WHERE `marca`=? AND `modelo`=? AND `anio_inicio`=? AND `anio_fin`=? AND `motor`=? AND `viscocidad`=? AND `servicio`=? AND `homologacion`=?;"
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