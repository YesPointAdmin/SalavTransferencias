<?php

use App\config\GeneralLogger;

echo "Welcome Test <br />";
class GeneralLoggerTest extends GeneralLogger{

    public function testGetDatePath(){
        echo " Se testea fecha para path: ".$this->getFormatDateToPath()."<br />";
    }

    public function testGetPath(){
        echo " Se testea fecha para path: ".$this->getFormatPath("transferencia")."<br />";
        echo " Se testea fecha para path error: ".$this->getFormatPath("transferencia","ERROR")."<br />";
    }

    public function testGetDateMessage(){
        echo " Se testea fecha para message: ".$this->getFormatDateToMessage()."<br />";
    }

    public function testGetFormatMessage(){
        echo " Se testea format message: ".$this->getFomatMessage("test message")."<br />";
        echo " Se testea format message: ".$this->getFomatMessage("test message","INFO")."<br />";
        echo " Se testea format message: ".$this->getFomatMessage("test message","ERROR")."<br />";
    }

    public function testCaseForType(){
        $valueRouteInfoDebug = $this->getFormatPath("transferencia");
        echo " Validate type InfoDebug path to eval: {$valueRouteInfoDebug} <br />";
        if($testType = $this->validateType())
            echo ($testType === $valueRouteInfoDebug)?"Se cumple test Debug {$testType} <br />":"No se cumple test Debug {$testType} <br />";
        else
            echo "Error en el test Debug, posible excepcion <br />";
        if($testType = $this->validateType('INFO'))
            echo ($testType === $valueRouteInfoDebug)?"Se cumple test Info {$testType} <br />":"No se cumple test Info {$testType} <br />";
        else
            echo "Error en el test Info, posible excepcion <br />";

        $valueRouteError = $this->getFormatPath("transferencia",'ERROR');
        echo " Validate type Error path to eval: {$valueRouteError} <br />";
        if($testType = $this->validateType('ERROR'))
            echo ($testType === $valueRouteError)?"Se cumple test Error {$testType} <br />":"No se cumple test Error {$testType} <br />";
        else
            echo "Error en el test Error, posible excepcion <br />";
            
    }

    public function testGetWriteMessageInFile(){
        if($eval = $this->writtingProcessCall("test message"))
            echo " Se testea escritura en archivo de message: ".$eval."<br />";
        else
            echo " Error en test message <br />";
        
        if($eval = $this->writtingProcessCall("test message",'INFO'))
            echo " Se testea escritura en archivo de message: ".$eval."<br />";
        else
            echo " Error en test message <br />";

        if($eval = $this->writtingProcessCall("test message",'ERROR'))
            echo " Se testea escritura en archivo de message: ".$eval."<br />";
        else
            echo " Error en test message <br />";
    }

    public function testAccessToWrite(){
        $this->accessToWrite(null,null);
        echo " Se testea acceso de escritura en archivo de message: <br />";
    }

    public function testOutMessage(){
        $this->outMessage(" test mesagge out");
        echo " Se testea logger out: <br />";
    }

    public function testOutErrorMessage(){
        $this->outErrorMessage(" test mesagge error");
        echo " Se testea logger out error: <br />";
    } 

    public function testOutDebugMessage(){
        $this->outDebugMessage(" test mesagge debug");
        echo " Se testea logger out debug: <br />";
    }

}
$testObject = new  GeneralLoggerTest(NULL,"transferencia");
$testObject->testGetDatePath();
$testObject->testGetPath();
$testObject->testGetDateMessage();
$testObject->testGetFormatMessage();
$testObject->testCaseForType();
$testObject->testGetWriteMessageInFile();
$testObject->testAccessToWrite();
$testObject->testOutMessage();
$testObject->testOutErrorMessage();
$testObject->testOutDebugMessage();
?>