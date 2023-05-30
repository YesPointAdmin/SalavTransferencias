<?php

/**
 * @property string $endResult
 * @property string $message
 * @property mixed $responseData
 */
interface ResponseInterface{
    public function __construct(string $endResult,string $message,mixed $responseData);
    public function setEndResult(string $endResult) : void;
    public function getEndResult() : string;
    public function setMessage(string $message) : void;
    public function getMessage() : string;
    public function setResponseData(mixed $responseData) : void;
    public function getResponseData() : mixed;
    public function toArray() : array;
    public function toString() : string;
}


?>