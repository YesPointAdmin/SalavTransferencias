<?php 

require_once('../Config/GeneralLogger.php');

class Persistance {
    protected mixed $_log;
    protected string $tableName = 'general';
    private mixed $connection;

    function __construct($connection, $process = "general") {
        $this->connection = $connection;
        $this->_log = new  GeneralLogger(\get_class($this),$process);
    }

    public function prepareAndExecuteSentece(string $sqlQuery,mixed ...$names) : mixed {
        

        $sentenceToExecute = null;
        $result = true;
        $data = null;
        try{
            $sentenceInitialized = mysqli_stmt_init($this->connection);
            if($sentenceToExecute = mysqli_stmt_prepare($sentenceInitialized,$sqlQuery)){
                //$this->_log->outMessage("Se ha ejecutara {$sqlQuery}. ");
                $sentenceToExecute = $this->getBindingSentenceIfRequired( $sentenceInitialized ,$names);
                if($sentenceToExecute === false)
                    throw new Exception("Error at binding Query => ".mysqli_error($this->connection), 1);   

                if ($result = mysqli_stmt_execute($sentenceToExecute)) {
                    $this->_log->outMessage("Se ha ejecutado |{$sqlQuery}| correctamente sobre {$this->tableName}. Afected Rows: {$sentenceToExecute->affected_rows} ");
                    if($data = mysqli_stmt_get_result($sentenceToExecute)){
                        $result = $data;
                        $this->_log->outMessage("Type of existing data: ".gettype($data));
                        while ($rowResult = mysqli_fetch_array($data, MYSQLI_ASSOC))
                        {
                            $this->_log->outMessage("Type of existing rowResult: ".gettype($rowResult));
                            $rowMessage = "|";
                            foreach ($rowResult as $keyRowResult => $valueRowResult)
                            {
                                $this->_log->outMessage("Type of valueRowResult: ".gettype($valueRowResult));
                                $rowMessage .= " keyRowResult:{$keyRowResult}<=>valueRowResult:{$valueRowResult} |";
                            }
                            $this->_log->outMessage("Datos recuperados, resultado de consultar Catalogo de producto: \n\t ".$rowMessage);
                        }
                    }
                    
                    //$this->_log->outMessage("Se ha ejecutado correctamente sobre {$this->tableName}. Afected Rows: {$sentenceToExecute->affected_rows} ");

                    //echo "Se ha insertado correctamente. ";
                    //$this->_log->outMessage("Se ha ejecutado correctamente. Afected Rows: {$sentenceToExecute->affected_rows} , Inserted Id: {$sentenceToExecute->insert_id} ");
                    
                } else 
                    throw new Exception("Error at execute Query => ".mysqli_error($this->connection), 1);                  

            }   else
                throw new Exception("Error at Prepare Sentence => ".mysqli_error($this->connection), 1);
            
        }catch(Exception $e){
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: \n ".$e->getMessage());
            $result = false;
        } finally {
            
            $this->cleanMemoryAfterQuery($sentenceToExecute);
        }
        return $result;
    }

    public function prepareAndExecuteSelect(string $sqlQuery) : mixed {
        

        $sentenceToExecute = null;
        $result = true;
        $data = null;
        try{
            $sentenceInitialized = mysqli_stmt_init($this->connection);
            if($sentenceToExecute = mysqli_stmt_prepare($sentenceInitialized,$sqlQuery)){
                //$this->_log->outMessage("Se ha ejecutara {$sqlQuery}. ");

                if ($result = mysqli_stmt_execute($sentenceInitialized)) {
                    $this->_log->outMessage("Se ha ejecutado |{$sqlQuery}| correctamente sobre {$this->tableName}. Afected Rows: {$sentenceInitialized->affected_rows} ");
                    if($data = mysqli_stmt_get_result($sentenceInitialized)){
                        $result = $data;
                        $this->_log->outMessage("Type of existing data: ".gettype($data));
                        while ($rowResult = mysqli_fetch_array($result, MYSQLI_ASSOC))
                        {
                            $this->_log->outMessage("Type of existing rowResult: ".gettype($rowResult));
                            $rowMessage = "|";
                            foreach ($rowResult as $keyRowResult => $valueRowResult)
                            {
                                $this->_log->outMessage("Type of valueRowResult: ".gettype($valueRowResult));
                                $rowMessage .= " keyRowResult:{$keyRowResult}<=>valueRowResult:{$valueRowResult} |";
                            }
                            $this->_log->outMessage("Datos recuperados, resultado de consultar Catalogo de producto: \n\t ".$rowMessage);
                        }
                    }
                    
                    //$this->_log->outMessage("Se ha ejecutado correctamente sobre {$this->tableName}. Afected Rows: {$sentenceToExecute->affected_rows} ");

                    //echo "Se ha insertado correctamente. ";
                    //$this->_log->outMessage("Se ha ejecutado correctamente. Afected Rows: {$sentenceToExecute->affected_rows} , Inserted Id: {$sentenceToExecute->insert_id} ");
                    
                } else 
                    throw new Exception("Error at execute Query => ".mysqli_error($this->connection), 1);                  

            }   else
                throw new Exception("Error at Prepare Sentence => ".mysqli_error($this->connection), 1);
            
        }catch(Exception $e){
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: \n ".$e->getMessage());
            $result = false;
        } finally {
            
            $this->cleanMemoryAfterQuery($sentenceToExecute);
        }
        return $result;
    }

    public function cleanMemoryAfterQuery( mixed $dataToliberate ){
        
        if(empty($dataToliberate)||is_bool($dataToliberate))
            return;

        switch (\get_class($dataToliberate)) {
            case 'mysqli_stmt':
                mysqli_stmt_close($dataToliberate);
                break;

            case 'mysqli_result':
                mysqli_free_result($dataToliberate);
                break;
            
            default:
                break;
        }
    }

    public function getBindingSentenceIfRequired(mixed $sentenceToExecute ,mixed ...$names) : mixed {
        $catchResut = true;
        $countDatas = count($names)>0;
        if($countDatas > 0){
            $assingmentArgs = "";
            foreach ($names as $name) 
                $assingmentArgs .= "s";
            

            if ($countDatas === 1) 
                $catchResut = mysqli_stmt_bind_param($sentenceToExecute,$assingmentArgs,$names[0]) or throw new Exception("Error at Binding Sentece => ".mysqli_error($this->connection), 1);      
            else
                $catchResut = mysqli_stmt_bind_param($sentenceToExecute,$assingmentArgs,...$names) or throw new Exception("Error at Binding Sentece => ".mysqli_error($this->connection), 1);

            
           
        }
        return ($catchResut)?$sentenceToExecute:$catchResut;
    }
}

?>