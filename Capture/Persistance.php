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
        $this->_log = new  GeneralLogger(\get_class($this), $process);
    }

    public function prepareAndExecuteSentece(string $sqlQuery, mixed ...$names): mixed
    {


        $sentenceToExecute = null;
        $result = true;
        $data = null;
        try {
            $sentenceInitialized = mysqli_stmt_init($this->connection);
            if ($sentenceToExecute = mysqli_stmt_prepare($sentenceInitialized, $sqlQuery)) {
                //$this->_log->outMessage("Se ha ejecutara {$sqlQuery}. ");
                $sentenceToExecute = $this->getBindingSentenceIfRequired($sentenceInitialized, $names);
                if ($sentenceToExecute === false)
                    throw new Exception("Error at binding Query => " . mysqli_error($this->connection), 1);

                if ($result = mysqli_stmt_execute($sentenceToExecute)) {

                    //Switch affectedRows - NumRows
                    $this->_log->outMessage("Se ha ejecutado | {$sqlQuery} | correctamente sobre {$this->tableName}. Afected Rows: {$sentenceToExecute->affected_rows} ");

                    //$output = [0, 0];


                    /* mysqli_stmt_bind_result($sentenceToExecute, ...$output);

                    while (mysqli_stmt_fetch($sentenceToExecute)){
                        echo "id: " . $output[0] ."\n";
                        echo "part_number: " . $output[1] ."\n";
                        $this->_log->outMessage("Type of existing output: ".gettype($output[1]));
                    } */
                    /* if($data = mysqli_stmt_get_result($sentenceToExecute)){
                        $result = $data;
                        $this->_log->outMessage("Type of existing data: ".gettype($data));
                        if(gettype($data) === 'object'){
                            $this->_log->outMessage("Validacion de => ".get_class($data));
                            if(get_class($data) == "mysqli_result"){

                                do{
                                    $rowResult = mysqli_fetch_array($data, MYSQLI_ASSOC);
                                    $this->_log->outMessage("Type of existing rowResult: ".gettype($rowResult));
                                    $rowMessage = "|";
                                    foreach ($rowResult as $keyRowResult => $valueRowResult)
                                    {
                                        $this->_log->outMessage("Type of valueRowResult: ".gettype($valueRowResult));
                                        $rowMessage .= " keyRowResult:{$keyRowResult}<=>valueRowResult:{$valueRowResult} |";
                                    }
                                    $this->_log->outMessage("Datos recuperados, resultado de consultar Catalogo de producto: \n\t ".$rowMessage);
                                }
                                while ($rowResult);
                            }
                        }
                        
                       
                    } */

                    //$this->_log->outMessage("Se ha ejecutado correctamente sobre {$this->tableName}. Afected Rows: {$sentenceToExecute->affected_rows} ");

                    //echo "Se ha insertado correctamente. ";
                    //$this->_log->outMessage("Se ha ejecutado correctamente. Afected Rows: {$sentenceToExecute->affected_rows} , Inserted Id: {$sentenceToExecute->insert_id} ");

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


    public function prepareAndExecuteSelect(string $sqlQuery, string $names): mixed
    {

        try {
            $sentenceInitialized = mysqli_stmt_init($this->connection);

            if ($sentenceToExecute = mysqli_stmt_prepare($sentenceInitialized, $sqlQuery)) {
                $this->_log->outMessage("Type of existing data: => " . (gettype($sentenceToExecute)));

                $sentenceToExecute = $this->getBindingSentenceIfRequired($sentenceInitialized, $names);
                if ($sentenceToExecute === false)
                    throw new Exception("Error at binding Query => " . mysqli_error($this->connection), 1);

                mysqli_stmt_bind_param($sentenceToExecute, 's', $names);
                $this->_log->outMessage("Preparando secuencia param => " . (gettype($names)));


                if ($result = mysqli_stmt_execute($sentenceToExecute)) {

                    $output = [0, 0];

                    while (mysqli_stmt_fetch($sentenceToExecute)) {
                        echo "id: " . $output[0] . "\n";
                        echo "part_number: " . $output[1] . "\n";
                        $this->_log->outMessage("Type of existing output: " . gettype($output[1]));
                    }

                    $this->_log->outMessage("Se ha ejecutado |{$sqlQuery}| correctamente sobre {$this->tableName}. Afected Rows: {$sentenceToExecute->affected_rows} ");
                } else
                    throw new Exception("Error at execute Query => " . mysqli_error($this->connection), 1);
            } else
                throw new Exception("Error at Prepare Sentence => " . mysqli_error($this->connection), 1);
        } catch (Exception $e) {
            $this->_log->outErrorMessage("Error al insertar en '{$this->tableName}' error: \n " . $e->getMessage());
            $result = false;
        } finally {

            $this->cleanMemoryAfterQuery($sentenceToExecute);
        }
    }

    public function prepareAndExecSelectOrientedObj(string $sqlQuery, string $names): mixed
    {

        //$sentenceToExecute = null;
        $result = true;
        $data = null;

        //$sentenceInitialized = mysqli_stmt_init($this->connection);
        $stmt = $this->connection->stmt_init();

        $stmt->prepare($sqlQuery);

        // bind parameters for markers 
        $stmt->bind_param("s", $names);

        // execute query 
        $stmt->execute();

        //bind result variables 
        $stmt->bind_result($data);

        // fetch value 
        $stmt->fetch();

        printf("%s is part_number %s\n", $names, $data);
    }

    public function prepareAndExecSelectProcedural(string $sqlQuery, string $partNumber): mixed
    {
        try {
           /*  $servername = "localhost:3306";
            $username = "prueba_salav2";
            $password = "147258369";
            $dbname = "salav_test";
 */
            //$conn = mysqli_connect($servername, $username, $password, $dbname);

            /* if (!$conn) {
                die("Error de conexión: " . mysqli_connect_error());
            } */

            // Paso 2: Definir la consulta preparada
            //print_r($sqlQuery);

            $this->connection;
            // Paso 3: Preparar la consulta
            $stmt = mysqli_prepare($this->connection, $sqlQuery);

            if (!$stmt) {
                die("Error al preparar la consulta: " . mysqli_error($this->connection));
            }

            // Paso 4: Vincular los parámetros de la consulta
            //print_r($partNumber);

            mysqli_stmt_bind_param($stmt, "s", $partNumber); // "s" indica que se espera un valor de tipo cadena (string)

            if (mysqli_stmt_execute($stmt)) {

                $this->_log->outMessage("Se ha ejecutado |{$sqlQuery}| correctamente sobre {$this->tableName}. Num Rows: {$stmt->num_rows} ");
                // Paso 6: Obtener los resultados de la consulta
                $result = mysqli_stmt_get_result($stmt);
            
                // Paso 7: Procesar los resultados
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "id: " . $row["id"] . " - part_number: " . $row["part_number"] . "<br>";
                    $this->_log->outMessage("Se ha ejecutado correctamente. Num Rows: {$stmt->num_rows} , SELECT Id: {$row["id"]}, SELECT part_number: {$row["part_number"]}");
                }
                echo "Se ha insertado correctamente. ";
                //$this->_log->outMessage("Se ha ejecutado correctamente. Afected Rows: {$stmt->num_rows} , SELECT Id: {$stmt->insert_id} ");
            } else {
                die("Error al ejecutar la consulta: " . mysqli_error($this->connection));
            }

        } catch (Exception $e) {
            $this->_log->outErrorMessage("Error al ejecutar en '{$this->tableName}' error: \n " . $e->getMessage());
            $result = false;
        }
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
}
