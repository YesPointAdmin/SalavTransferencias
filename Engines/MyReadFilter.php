<?php

class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter

{
    public function readCell($columnAddress, $row, $worksheetName = '')
    {


        return true;
    }
}

?>