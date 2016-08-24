<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . "/framework.php";
require_once dirname(__FILE__) . "/../class/mysqlParams.class.php";

$xmlPath = GETPOST("xmlPath");

$mysql_driver   = "mysql";
$mysql_host     = "localhost";
$mysql_port     = "3306";
$mysql_user     = "";
$mysql_password = "";
$mysql_database = "";
$mysql_prefix   = "";

if(file_exists($xmlPath))
{
    
    $paramsXML = simplexml_load_file($xmlPath);
    
    $mysql_driver   = $paramsXML->driver;
    $mysql_host     = $paramsXML->host;
    $mysql_port     = $paramsXML->port;
    $mysql_user     = $paramsXML->user;
    $mysql_password = $paramsXML->password;
    $mysql_database = $paramsXML->database;
    $mysql_prefix   = $paramsXML->prefix;
}

mysqlParams::setDriver($mysql_driver);
mysqlParams::setHost($mysql_host);
mysqlParams::setPort($mysql_port);
mysqlParams::setUser($mysql_user);
mysqlParams::setPassword($mysql_password);
mysqlParams::setDatabase($mysql_database);
mysqlParams::setPrefix($mysql_prefix);
mysqlParams::setXMLParams($xmlPath);

