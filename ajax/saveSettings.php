<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once "connect.php";

$fk_customer    = filter_input(INPUT_POST, "fk_customer");
$fk_warehouse   = filter_input(INPUT_POST, "fk_warehouse");
$fk_pricelevel  = filter_input(INPUT_POST, "fk_pricelevel");
$cash_register  = filter_input(INPUT_POST, "cash_register");
$serial_port    = filter_input(INPUT_POST, "serial_port");
$baudrate       = filter_input(INPUT_POST, "baudrate");
$parity         = filter_input(INPUT_POST, "parity");
$charlength     = filter_input(INPUT_POST, "charlength");
$stopbits       = filter_input(INPUT_POST, "stopbits");
$flowcontrol    = filter_input(INPUT_POST, "flowcontrol");

$query="delete from ".MAIN_DB_PREFIX."simplepos_settings";
$db->query($query);

$insert = "INSERT INTO ".MAIN_DB_PREFIX."simplepos_settings (`fk_customer`, `fk_warehouse`, `fk_pricelevel`, `cash_register`, `serial_port`, `baudrate`, `parity`, `charlength`, `stopbits`, `flowcontrol`) "
          ."VALUES ("
        . $fk_customer .","
        . $fk_warehouse .","
        . $fk_pricelevel .","
        . "'" . $cash_register ."',"
        . "'" . $serial_port ."',"
        . $baudrate .","
        . "'" . $parity ."',"
        . $charlength .","
        . $stopbits .","
        . "'" . $flowcontrol ."');";
print $insert;

print $db->query($insert);
        
        
        