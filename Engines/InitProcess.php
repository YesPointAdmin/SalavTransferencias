<?php
set_time_limit(999999999);
ini_set('memory_limit', '9999999999999G');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('file_uploads', 1);
define('PROCESS_NAME', 'transferencia');
error_reporting(E_ERROR);
$errorsPath = "../logs/errors/php-error-".date("Ymd").".log";
$errorsDir = dirname($errorsPath);
if (!is_dir($errorsDir))
    mkdir($errorsDir, 0755, true);

ini_set("error_log", $errorsPath);
/*

todo el archivo tiene funciones de lectura de excel, la cuales se encargan de interpretar las lineas y celdas del excel para la organozacion de datos, los datos se codifican para lograr mantener los acentos y las letras ñ, una vez se codifican, se buscan a en base de datos para ver si existe el registro y no tengamos duplicados, una ves verificado que no exstan duplicados se hace un insert bulk, el insert bulk se encarga de subir de 5000 en 5000 registros para optimizar el sistema, todo los procesos se mandan a bitacoras, al igual se crea un txt el cual ayuda al usuario a lograr redactar lo que paso en la funcion, los txt se guardan en cada carpeta correspondida a los archivos leidos, ejemplo fulo, tiene su carpeta llamada fulo

*/

require ('../vendor/autoload.php');

use App\Config\GeneralLogger;
use App\Config\DbConfig;
use App\Engines\TransferenciaProcess;
use App\Responses\GenericResponse;

$_log = new GeneralLogger("InitProcess",PROCESS_NAME);
$_log->outMessage("Se inicia el proceso: ".PROCESS_NAME);

$response = null;
$toSend = null;
function sendResponse(GenericResponse $response, int $codeStatus) : mixed{
    header('Content-type: application/json');
    http_response_code($codeStatus);    
    return json_encode($response->toArray());

}

$dbConfig = new DbConfig(PROCESS_NAME);
if($link = $dbConfig->openConnect()){

    $transferencia = new TransferenciaProcess();
    $result = $transferencia->retrieveAndProcessFiles($link);

    if(\get_class($link) === "mysqli")
        $dbConfig->closeConnect($link);
    
    $response = new GenericResponse("OK","Proceso ejecutado correctamente.",$result);
    $toSend =  sendResponse($response,200,$_log);

} else {
    $response = new GenericResponse("ERROR","Error de sistema: Imposible conectar a fuente.",null);
    $toSend =  sendResponse($response,500,$_log);
}

$_log->outMessage("Response to send: ".$toSend);
echo $toSend;
return;

?>