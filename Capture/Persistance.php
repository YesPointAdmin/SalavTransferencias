<?php

require_once('../Config/GeneralLogger.php');

class Persistance
{
    protected mixed $_log;
    protected string $tableName = 'general';
    private mixed $connection;

    function __construct($connection, $process = "general")
    {
        $this->connection = $connection;
        $this->_log = new GeneralLogger(\get_class($this), $process);
    }

    public function prepareAndExecuteSentece(string $typeOf ="select", string $sqlQuery, mixed ...$names): mixed
    {
        var_dump($names);
        echo "<br />";

        $sentenceToExecute = null;
        $result = true;
        $data = null;
        try {
            //$sentenceInitialized = mysqli_stmt_init($this->connection);
            if ($sentenceToExecute = mysqli_prepare($this->connection, $sqlQuery)) {
                $this->_log->outMessage("Se ha ejecutara {$sqlQuery}. ");
                $assigmentArgs = $this->getBindingNamesType( ...$names);
                $this->_log->outMessage("Se procesaran los tipos: {$assigmentArgs}, Para las variables: ".json_encode($names));
                if ($sentenceToExecute === "")
                    throw new Exception("Error at binding types => " . mysqli_error($this->connection), 1);
                mysqli_stmt_bind_param($sentenceToExecute,$assigmentArgs,...$names);
                if ($result = mysqli_stmt_execute($sentenceToExecute)) {

                    //Switch affectedRows - NumRows
                    $result = $this->retrieveResult( $typeOf,  $sqlQuery, $sentenceToExecute);

                } else
                    throw new Exception("Error at execute Query => " . mysqli_error($this->connection), 1);
            } else
                throw new Exception("Error at Prepare Sentence => " . mysqli_error($this->connection), 1);
        } catch (Exception $e) {
            $this->_log->outErrorMessage("Error al ejecutar en '{$this->tableName}' error: \n " . $e->getMessage());
            $result = false;
        } finally {

            $this->cleanMemoryAfterQuery($sentenceToExecute);
        }
        return $result;
    }

    public function retrieveResult(string $typeOf, string $sqlQuery,mysqli_stmt $sentenceToExecute) : mixed {
        $result = null;
        switch ($typeOf) {
            case 'insert':
                # code...
                $this->_log->outMessage("Se ha ejecutado | {$sqlQuery} | correctamente sobre {$this->tableName}. Afected Rows: {$sentenceToExecute->affected_rows} ");
                
                $result = ($sentenceToExecute->affected_rows > 0) ? (isset($sentenceToExecute->insert_id) ? $sentenceToExecute->insert_id : true) : false;

                break;
            case 'select':
            default:
                // Paso 6: Obtener los resultados de la consulta
                $resultData = mysqli_stmt_get_result($sentenceToExecute);
            
                $this->_log->outMessage("Se ha ejecutado |{$sqlQuery}| correctamente sobre {$this->tableName}. Num Rows: {$resultData->num_rows} ");
                
                if($resultData->num_rows === 0)
                    return 0;
                // Paso 7: Procesar los resultados
                $prepareResult = array();
                while ($row = mysqli_fetch_assoc($resultData)) {   
                    //echo "id: " . $row["id"] . " - part_number: " . $row["part_number"] . "<br>";
                    if(is_array($row)){
                        $elementResult = [];
                        foreach($row as $rowKey =>$rowValue){
                            $elementResult[$rowKey] = $rowValue;
                            
                        }
                        if(count($elementResult)>0)
                            $prepareResult[] = $elementResult;

                        
                    }
                }
                $resultMessage = \json_encode($prepareResult);
                $this->_log->outMessage("Se ha ejecutado correctamente. Num Rows: {$sentenceToExecute->num_rows} , Result: {$resultMessage}");
                $result = $prepareResult;
                
                $this->cleanMemoryAfterQuery($resultData);
                break;
        }
        return $result;
    }

    public function prepareAndExecSelectProcedural(string $sqlQuery, string $partNumber): mixed
    {
        try {

            $stmt = mysqli_prepare($this->connection, $sqlQuery);

            if (!$stmt) 
                throw new Exception("Error al preparar la consulta: " . mysqli_error($this->connection));
            

            mysqli_stmt_bind_param($stmt, "s", $partNumber); // "s" indica que se espera un valor de tipo cadena (string)

            if (mysqli_stmt_execute($stmt)) {

                $this->_log->outMessage("Se ha ejecutado |{$sqlQuery}| correctamente sobre {$this->tableName}. Num Rows: {$stmt->num_rows} ");
                // Paso 6: Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);
            
                // Paso 7: Procesar los resultados
                while ($row = mysqli_fetch_assoc($result)) {
                    //echo "id: " . $row["id"] . " - part_number: " . $row["part_number"] . "<br>";
                    $this->_log->outMessage("Se ha ejecutado correctamente. Num Rows: {$stmt->num_rows} , SELECT Id: {$row["id"]}, SELECT part_number: {$row["part_number"]}");
                }
                //echo "Se ha insertado correctamente. ";
                //$this->_log->outMessage("Se ha ejecutado correctamente. Afected Rows: {$stmt->num_rows} , SELECT Id: {$stmt->insert_id} ");
            } else 
                throw new Exception("Error al ejecutar la consulta: " . mysqli_error($this->connection),1);
            

        } catch (Exception $e) {
            $this->_log->outErrorMessage("Error al ejecutar en '{$this->tableName}' error: \n " . $e->getMessage());
            $result = false;
        }
        return $result;
    }

    public function cleanMemoryAfterQuery(mixed $dataToliberate)
    {

        if (empty($dataToliberate) || is_bool($dataToliberate))
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

    public function getBindingSentenceIfRequired(mixed $sentenceToExecute, mixed ...$names): mixed
    {
        $catchResut = true;
        $countDatas = count($names) > 0;
        if ($countDatas > 0) {
            $assingmentArgs = "";
            foreach ($names as $name)
                $assingmentArgs .= "s";


            if ($countDatas === 1)
                $catchResut = mysqli_stmt_bind_param($sentenceToExecute, $assingmentArgs, $names[0]) or throw new Exception("Error at Binding Sentece => " . mysqli_error($this->connection), 1);
            else
                $catchResut = mysqli_stmt_bind_param($sentenceToExecute, $assingmentArgs, ...$names) or throw new Exception("Error at Binding Sentece => " . mysqli_error($this->connection), 1);
        }
        return ($catchResut) ? $sentenceToExecute : $catchResut;
    }

    public function getBindingNamesType( mixed ...$names): mixed
    {
        $countDatas = count($names) > 0;
        $assingmentArgs = "";
        if ($countDatas > 0) {
            foreach ($names as $name){
                var_dump($name);
                echo "<br />";
                switch (\gettype($name)) {
                    case 'integer':
                        # code...
                        $assingmentArgs .= "i";
                        break;
                    
                    case 'string':
                    default:
                        # code...
                        $assingmentArgs .= "s";
                        break;
                }

            }
           
        }
        return $assingmentArgs;
    }
}
