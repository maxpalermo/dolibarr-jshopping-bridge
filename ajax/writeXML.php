<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . "/framework.php";
require_once dirname(__FILE__) . "/../class/mysqlParams.class.php";

$params  = json_decode(GETPOST("jshopping_params"));
$xmlPath = GETPOST("xmlPath");
mysqlParams::setClass($params);

//print "PARAMETRI DI CONNESSIONE \n";
//print_r($params);

print mysqlParams::setXMLParams($xmlPath,$params);