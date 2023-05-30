<?php

require_once("../Interfaces/ResultInterface.php");

class ResultResponseData implements ResultInterface{

    public string $fileName;
    public string $type;
    public string $message;
    public bool $status;

    public function __construct(string $fileName,string $type,string $message,bool $status){

        $this->fileName = $fileName;
        $this->type = $type;
        $this->message = $message;
        $this->status = $status;
    }

    public function setFileName(string $fileName) : void{
        $this->fileName = $fileName;
    }
    public function getFileName() : string{
        return $this->fileName;

    }

    public function setType(string $type) : void{
        $this->type = $type;

    }
    public function getType() : string{
        return $this->type;

    }

    public function setMessage(string $message) : void{
        $this->message = $message;

    }
    public function getMessage() : string{
        return $this->message;

    }

    public function setStatus(mixed $status) : void{
        $this->status = $status;

    }
    public function getStatus() : bool{
        return $this->status;

    }

    public function toArray() : array{
        return [ 'fileName'=>$this->fileName,'type'=>$this->type,'message'=>$this->message,'status'=>$this->status];
    }

    public function toString() : string {
        return \get_class($this).": ['fileName'=>{$this->fileName},'type'=>{$this->type},'message'=>{$this->message},'status'=>'{$this->status}']";
    }


}

?>