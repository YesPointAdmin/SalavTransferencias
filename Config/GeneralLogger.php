<?php
namespace App\Config;
use Exception;
class GeneralLogger{
    private string $className;
    private string $outFilePath;
    private string $errorOutFilePath;
    protected string $standartPath = "../logs/%process%/%oe%_execution_%date%.log";
    protected string $standartMessage = "[%type%]:[%date%]:[%className%] => %message% ";
    public string $message;

    function __construct($className = null, $process = "general"){
        $this->className = ($className)??\get_class($this);
        $this->outFilePath = $this->getFormatPath($process);
        $this->errorOutFilePath = $this->getFormatPath($process,'ERROR');
    }

    protected function writtingProcessCall($message = "",$type = "DEBUG"): bool{
        return $this->writeFile($this->getFomatMessage($message, $type ),$this->validateType($type));
    }

    protected function bitacoraWrittingProcessCall($message = "",$path) : bool{
        return $this->writeFile($message,$path);
    }

    protected function validateType($type = 'DEBUG') : string {
        switch ($type) {
            case 'ERROR':
                # code...
                return $this->errorOutFilePath;
                break;
            
            default:
                return $this->outFilePath;
                break;
        }
    }

    private function writeFile($message, $pathToWrite = null) : bool {
        $validateProcess = true;
        try{
            $fileName = $pathToWrite?? throw new Exception("Path to log must be a valid string");
            $dirname = dirname($fileName);
            if (!is_dir($dirname))
                mkdir($dirname, 0755, true);
            
            if($logFile = fopen($fileName, "a")){
                fwrite($logFile, "$message" . PHP_EOL);
                fclose($logFile);
            } else throw new Exception("Impossible to open or create file at: {$fileName}",1);
        }catch(Exception $e){
            $validateProcess = false;
        }
        return $validateProcess;
    }
    
    protected function getFormatPath($process,$oe = null): string {
        $pathToReturn = str_replace ( "%date%", $this->getFormatDateToPath(), $this->standartPath );
        $pathToReturn = str_replace ( "%process%", $process, $pathToReturn );
        return (!empty($oe)&&$oe === 'ERROR')?str_replace ( "%oe%", "error_out", $pathToReturn ):str_replace ( "%oe%", "out", $pathToReturn );
    }

    protected function getFomatMessage($message = "", $type = "DEBUG") : string {
        $messageToReturn = str_replace ( "%type%", $type ?? "DEBUG", $this->standartMessage );
        $messageToReturn = str_replace ( "%date%", $this->getFormatDateToMessage(), $messageToReturn );
        $messageToReturn = str_replace ( "%className%", $this->className, $messageToReturn );
        $messageToReturn = str_replace ( "%message%", $message ?? "mensaje vacio", $messageToReturn );
        return $messageToReturn;
    }

    protected function getFormatDateToMessage() : string {
        return date("Y/m/d H:i:s");
    }

    protected function getFormatDateToPath() : string {
        return date("Ymd");
    }

    protected function accessToWrite($message,$type){
        if(!$this->writtingProcessCall($message, $type))
            throw new Exception("Error at writting Log File", 1);
    }

    public function outMessage($message = "") : void {
        $this->accessToWrite($message, 'INFO');
    }

    public function outErrorMessage($message = "") : void {
        $this->accessToWrite($message, 'ERROR');
    }

    public function outDebugMessage($message = "") : void {
        $this->accessToWrite($message, 'DEBUG');
    }

}

?>