<?php 
namespace App\Capture;

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

    public function executeQuery($typeOf = 'select', string $fileName, mixed ...$data) : mixed {
        $result = false;
        //$this->_log->outDebugMessage("Part Number =>  " .$data[0]." type of: ".\gettype(($data[0])));
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
           $result = $this->prepareAndExecuteSentece($typeOf, $sqlSentence, $fileName, ...$data);
        else 
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: Query is empty");
        
        return $result;
    }

    public function executeSelect( string $data) : mixed {
        /* $sqlSentence = $this->generateSelectSentece();
        $result = $this->prepareAndExecuteSelect($sqlSentence);
        return $result; */
        $result = false;
            
        $sqlSentence = $this->generateSelectSentece();

                
        if(!empty($sqlSentence))
            $result = $this->prepareAndExecSelectProcedural($sqlSentence, $data);
        else 
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: Query is empty");
        
        return $result;
    }      

}
