<?php
set_time_limit(999999999);
ini_set('memory_limit', '9999999999999G');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR);
require("../../vendor/autoload.php");
use App\Config\DbConfig;

function writeFile($message, $pathToWrite = null) : bool {
    $validateProcess = true;
    try{
        $fileName = $pathToWrite?? throw new \Exception("Path to log must be a valid string");
        $dirname = dirname($fileName);
        if (!is_dir($dirname))
            mkdir($dirname, 0755, true);
        
        if($logFile = fopen($fileName, "a")){
            fwrite($logFile, "$message" . PHP_EOL);
            fclose($logFile);
        } else throw new \Exception("Impossible to open or create file at: {$fileName}",1);
    }catch(\Exception $e){
        $validateProcess = false;
    }
    return $validateProcess;
}

function getToken($servicePath) : string | bool {
    $requestToken="   
    <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsc=\"WSConsultas\">
    <soapenv:Header/>
    <soapenv:Body>
    <wsc:Consultas.OBTENERTOKEN>
    <wsc:Username>Enteratek</wsc:Username>
    <wsc:Password>C0nsult15</wsc:Password>
    </wsc:Consultas.OBTENERTOKEN>
    </soapenv:Body>
    </soapenv:Envelope>
    
    ";
    
    $headerToken=[
        'Method: POST',
        'Connection: Keep-Alive',
        'User-Agent: PHP-SOAP-CURL',
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: Consultas.OBTENERTOKEN',
    ];
    $cUrlToken = curl_init($servicePath);
    curl_setopt($cUrlToken, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cUrlToken, CURLOPT_HTTPHEADER, $headerToken);
    curl_setopt($cUrlToken, CURLOPT_POST, true);
    curl_setopt($cUrlToken, CURLOPT_POSTFIELDS, $requestToken);
    curl_setopt($cUrlToken, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    $responseToken = curl_exec($cUrlToken);
    $err_status = curl_errno($cUrlToken);

    if(is_string($responseToken)){ 
        $leftCleanToken = explode('<Token xmlns="WSConsultas">',$responseToken);
        $rigthCleanToken = explode('</Token>',$leftCleanToken[1]);
        echo "\r\r\n Clean Token: {$rigthCleanToken[0]}\n"; 
		return trim($rigthCleanToken[0]);
    }else if(is_string($err_status)){
        echo "\r\r\n Got error at token: {$err_status}\n";    
		return false;
    }
}

function getInventory($servicePath, $tokenToRequest) {

    $requestInventario="   
    <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsc=\"WSConsultas\">
    <soapenv:Header/>
    <soapenv:Body>
    <wsc:Consultas.INVENTARIOS>
    <wsc:Token>$tokenToRequest</wsc:Token>
    </wsc:Consultas.INVENTARIOS>
    </soapenv:Body>
    </soapenv:Envelope>    
    ";

    $headerInventario=[
        'Method: POST',
        'Connection: Keep-Alive',
        'User-Agent: PHP-SOAP-CURL',
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: Consultas.INVENTARIOS',
    ];
    $cUrlInventario = curl_init($servicePath);
    curl_setopt($cUrlInventario, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cUrlInventario, CURLOPT_HTTPHEADER, $headerInventario);
    curl_setopt($cUrlInventario, CURLOPT_POST, true);
    curl_setopt($cUrlInventario, CURLOPT_POSTFIELDS, $requestInventario);
    curl_setopt($cUrlInventario, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    $responseInventario = curl_exec($cUrlInventario);
    $err_status = curl_errno($cUrlInventario);

    if(is_string($responseInventario)){ 
        $leftCleanBody = str_replace("<SOAP-ENV:Body>","",$responseInventario);
        $rigthCleanBody = str_replace("</SOAP-ENV:Body>","",$leftCleanBody);
        $xmlBody = simplexml_load_string($rigthCleanBody);
        $xmlBody->registerXPathNamespace("xsd", "http://www.w3.org/2001/XMLSchema");

        $datatToRetrieve = [];
        for ($xmlBody->rewind(); $xmlBody->valid(); $xmlBody->next()) {
            foreach($xmlBody->getChildren() as $name => $data) {
                if($name = "Inventarioslist")
                    $datatToRetrieve = $data;
            }
        }
		return $datatToRetrieve;
    }else if(is_string($err_status)){
        echo "\r\r\n Got error at token: {$err_status}\n";    
		return false;
    }
}

function cleanMemoryAfterQuery(mixed $dataToliberate)
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

function executeQueries($typeOf, $sentence, $link, $pathToWrite, $element1, $element2) : mixed {
    $result = null;
    $sentenceToExecute = null;
    try {
        if ($sentenceToExecute = mysqli_prepare($link, $sentence)) {
            switch ($typeOf) {
                case 'update':
                    mysqli_stmt_bind_param($sentenceToExecute, 'si', $element1, $element2);
                    break;
                
                case 'select':
                default:
                    mysqli_stmt_bind_param($sentenceToExecute, 'ss', $element1, $element2);
                    break;
            }
            if ($result = mysqli_stmt_execute($sentenceToExecute)) 

                $result = retrieveResult($typeOf,  $sentence, $sentenceToExecute);

            else
                throw new Exception("Error at execute Query => " . mysqli_error($link), 1);
        } else
            throw new Exception("Error at Prepare Sentence => " . mysqli_error($link), 1);
    } catch (Exception $e) {
        writeFile("Error into match productos-inventario: \n " . $e->getMessage(), $pathToWrite);
        $result = false;
    } finally {
        cleanMemoryAfterQuery($sentenceToExecute);
    }
    return $result ?? false;

}

function retrieveDataInventories($link,$inventoryData,$pathToWrite){
    $datatToRetrieve = [];
    for ($inventoryData->rewind(); $inventoryData->valid(); $inventoryData->next()) {
		
		$datatToRetrieveElement = [];
        foreach($inventoryData->getChildren() as $name => $data) 
            $datatToRetrieveElement[$name] = $data;

        if(!empty($datatToRetrieveElement["Inventario_ProductoId"])&&!empty($datatToRetrieveElement['InventarioId'])){
            
            writeFile(" Search in catalogo_produto for id web: {$datatToRetrieveElement['Inventario_ProductoId']} And inventario for id web: {$datatToRetrieveElement['InventarioId']}", $pathToWrite ) or throw new \Exception("Error at process log.");
            $sqlQuery = "SELECT `inventario`.`id` as inventario_id, `inventario`.`id_web` as inventario_id_web, `inventario`.`producto_id` as inventario_producto_id, `catalogo_producto`.`id` as catalogo_producto_id, `catalogo_producto`.`id_web` as catalogo_producto_id_web FROM `inventario`";
            $sqlQuery .= "INNER JOIN `catalogo_producto` ON (`catalogo_producto`.`id_web`=? AND `inventario`.`producto_id` != `catalogo_producto`.`id`)  where `inventario`.`id_web`=?;";
            
            $result = executeQueries('select', $sqlQuery, $link, $pathToWrite, $datatToRetrieveElement['Inventario_ProductoId'], $datatToRetrieveElement['InventarioId']);
			
/* 	result=	 array (
			  0 => 
			  array (
				'inventario_id' => ,
				'inventario_id_web' => ,
				'inventario_producto_id' => ,
				'catalogo_producto_id' => ,
				'catalogo_producto_id_web' => ,
			  ),
			) */
            if(!empty($result) || $result !== false){

                    $sqlUpdate = "UPDATE `inventario` SET `inventario`.`producto_id`=? WHERE `inventario`.`id`=?;";
                    writeFile("Must to update Row {$result[0]['inventario_id']} at inventario table to change actual catalogo producto id reference < {$result[0]['inventario_producto_id']} > TO < {$result[0]['catalogo_producto_id']} >", $pathToWrite);
                    if($result = executeQueries('update', $sqlUpdate, $link, $pathToWrite,$result[0]['catalogo_producto_id'],$result[0]['inventario_id']))
                        if(!empty($result))
                            writeFile("Succefuly updated {$result} row(s). ", $pathToWrite);

            }

        }

		$datatToRetrieve[] = $datatToRetrieveElement;
    }
    return var_export($datatToRetrieve,true);

}

function retrieveResult(string $typeOf, string $sqlQuery, mysqli_stmt $sentenceToExecute): mixed
{
    $result = null;
    switch ($typeOf) {
        case 'update':
           
            $result = ($sentenceToExecute->affected_rows > 0) ? $sentenceToExecute->affected_rows : false;

            break;

        case 'select':
        default:
            $resultData = mysqli_stmt_get_result($sentenceToExecute);

            if ($resultData->num_rows === 0)
                return 0;
                
            $prepareResult = array();
            while ($row = mysqli_fetch_assoc($resultData)) {
                if (is_array($row)) {
                    $elementResult = [];
                    foreach ($row as $rowKey => $rowValue) {
                        $elementResult[$rowKey] = $rowValue;
                    }
                    if (count($elementResult) > 0)
                        $prepareResult[] = $elementResult;
                }
            }

            $result = $prepareResult;

            cleanMemoryAfterQuery($resultData);
            break;
    }
    return $result;
}

$time_pre = microtime(true);
$time_post = 0;
try{
    echo "\r\n Init Recovery...\n";
    $dbConfig = new DbConfig('recovery_innventory');
    if($link = $dbConfig->openConnect()){
        echo "\r\r\n Connected with DB...\n";    

        $servicePath="http://148.245.79.117:8088/Consultas/aConsultas.aspx?wsdl";

        if($tokenToRequest = getToken($servicePath)){

			echo "\r\r\n Init request for get Inventory... ";
            if($inventoryData = getInventory($servicePath, $tokenToRequest)){
			    echo "\r\r\n Init retrieveData... ";
                $pathToWrite = "/opt/lampp//htdocs/SalavRefaccion/SalavTransferencias/logs/general/recovery/inventory_".time().".log";
                retrieveDataInventories($link,$inventoryData,$pathToWrite);
                //writeFile($inventoryData, $pathToWrite ) or throw new \Exception("Path to log must be a valid string");

            }
                
        }

        if(\get_class($link) === "mysqli")
            $dbConfig->closeConnect($link);
        

    } else {

        echo "\r\r\n Impossible to connect with DB...\n";
    }
}catch(\Exception $err){
    echo "\r\n[ERROR]: ".$err->getMessage();
}finally{
    $time_post = microtime(true);
    if($time_post>$time_pre)
        echo "\r\n Time in process: ".$time_post - $time_pre;
}
?>