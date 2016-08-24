<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . "/framework.php";
require_once dirname(__FILE__) . "/../class/mysqlParams.class.php";


$jdbType     = GETPOST("jdriver");
$jdbHost     = GETPOST("jhost");
$jdbport     = GETPOST("jport");
$jdbUser     = GETPOST("juser");
$jdbPass     = GETPOST("jpassword");
$jdbname     = GETPOST("jdatabase");


$connection = new DoliDBMysqli($jdbType, $jdbHost, $jdbUser, $jdbPass, $jdbname, $jdbport);
$connection->connect($jdbHost, $jdbUser, $jdbPass, $jdbName, $jdbport);

if($connection->connected)
{
    print true;
}
else 
{
    print "ERRORE: " . $connection->errno() . ": " . $connection->error();
}
