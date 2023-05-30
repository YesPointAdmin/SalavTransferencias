<?php

/**
 * @property string $fileName
 * @property string $type
 * @property string $message
 * @property bool $status
 */
interface ResultInterface{
    public function __construct(string $fileName,string $type,string $message,bool $status);
    public function setFileName(string $fileName) : void;
    public function getFileName() : string;
    public function setType(string $type) : void;
    public function getType() : string;
    public function setMessage(string $message) : void;
    public function getMessage() : string;
    public function setStatus(mixed $status) : void;
    public function getStatus() : bool;
    public function toArray() : array;
    public function toString() : string;
}


?>