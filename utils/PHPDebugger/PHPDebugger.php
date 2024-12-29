<?php

/**
 * PHPDebugger Main File
 * @author Connell Reffo
 */

class Debug
{
    public function log(string $string)
    {
        echo "<script>console.log('$string')</script>";
    }

    public function log_array($array)
    {
        $script = "<script>console.log(`" . print_r($array, true) . "`)</script>";
        echo $script;
    }

    public function alert(string $string)
    {
        echo "<script>alert('$string')</script>";
    }

    public function alert_array($array)
    {
        $script = "<script>alert(`" . print_r($array, true) . "`)</script>";
        echo $script;
    }

    public function log_to_file(string $string, string $path)
    {
        file_put_contents($path, $string);
    }

    public function log_array_to_file($array, string $path)
    {
        file_put_contents($path, print_r($array, true));
    }

    public function document_write(string $string)
    {
        echo $string;
    }

    public function document_write_array($array)
    {
        print_r($array);
    }
}
