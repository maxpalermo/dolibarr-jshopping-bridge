<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . "/framework.php";
require_once dirname(__FILE__) . "/../class/mysqlParams.class.php";


$ftp_url      = GETPOST("ftp_url");
$ftp_port     = GETPOST("ftp_port");
$ftp_username = GETPOST("ftp_username");
$ftp_password = GETPOST("ftp_password");

//print $ftp_url . PHP_EOL;
//print $ftp_port . PHP_EOL;
//print $ftp_username . PHP_EOL;
//print $ftp_password . PHP_EOL;
//return;

try 
{
    print "Connnessione";
    $con = ftp_connect($ftp_url,$ftp_port);
    if (false === $con) {
        print "0";
        print error_get_last();
        return;
    }
    
    print "login";
    $loggedIn = ftp_login($con,  $ftp_username,  $ftp_password);
    if (false === $loggedIn) {
        print "0";
        print error_get_last();
        ftp_close($con);
        return;
    }

    //print_r(ftp_nlist($con, "."));
    if(empty(ftp_nlist($con, ".")))
    {
        print "0";
        print error_get_last();
        ftp_close($con);
        return;
    }
    
} 
catch (Exception $e) 
{
    print "0";
    print error_get_last();
    return;
}

print "<pre>ROOT: " . ftp_pwd($con) . "\nFILES:\n" . print_r(ftp_nlist($con, "."),1) . "</pre>";
ftp_close($con);