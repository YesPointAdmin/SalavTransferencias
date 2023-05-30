<?php

require_once("../Interfaces/ResponseInterface.php");

class GenericResponse implements ResponseInterface{
    public string $endResult;
    public string $message;
    public mixed $responseData;

    public function __construct(string $endResult,string $message,mixed $responseData){
        $this->endResult = $endResult;
        $this->message = $message;
        $this->responseData= $responseData;
    }

    public function setEndResult(string $endResult) : void{
        $this->endResult = $endResult;
    }
    public function getEndResult() : string{
        return $this->endResult;
    }

    public function setMessage(string $message) : void{
        $this->message = $message;
    }
    public function getMessage() : string{
        return $this->message;
    }

    public function setResponseData(mixed $responseData) : void{
        
        $this->responseData= $responseData;
    }
    public function getResponseData() : mixed{
        return $this->responseData;
    }

    public function toArray() : array{
        return [ 'endResult'=>$this->endResult,'message'=>$this->message,'responseData'=>$this->responseData];
    }

    public function toString() : string {
        return \get_class($this).": ['endResult'=>{$this->endResult},'message'=>{$this->message},'responseData'=>{$this->responseData}]";
    }
}
?>