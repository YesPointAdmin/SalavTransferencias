<?php 

require_once('Persistance.php');
require_once('../Config/GeneralLogger.php');

class InscribeCatalogoProductos extends Persistance{

    protected string $tableName = 'catalogo_producto';

    public function generateInsertSentece() : string {
        return (!empty($this->tableName))?
                    "INSERT INTO `{$this->tableName}`(`id`, `id_web`, `Producto_LstPrec`, `part_number`, `descripcion`, `tipo`, `url_ficha`, `imagen`, `pdf`, `clasificacionabc`, `proveedor_id`, `Precio`, `imglo`) VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?);"
                    :"";
    }
    public function generateSelectSentece() : string {
        return (!empty($this->tableName))?
                    "SELECT `id`,`part_number` FROM `{$this->tableName}` WHERE `part_number`=?;"
                    :"";
                    
    }

    public function executeQuery($typeOf = 'select',mixed ...$data) : mixed {
        $result = false;
        //echo "<br /> Into Execute";
        switch ($typeOf) {
            case 'insert':
                # code...
                echo "<br /> gonna be insert";
                $sqlSentence = $this->generateInsertSentece();
                break;
            
            case 'select':
            default:
                # code...
                $sqlSentence = $this->generateSelectSentece();
                break;
        }
        //echo "<br /> $sqlSentence <br />";
        //$sqlSentence = $this->generateInsertSentece() or throw new Exception("Error at Generate Sentence", 1);

        if(!empty($sqlSentence))
           $result = $this->prepareAndExecuteSentece($sqlSentence,...$data);
        else 
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: Query is empty");
        
        return $result;
    }

    public function excecuteSelect() : mixed {
        $sqlSentence = $this->generateSelectSentece();
        $result = $this->prepareAndExecuteSelect($sqlSentence);
        return $result;
    }
}

?>