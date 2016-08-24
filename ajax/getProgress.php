<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
$response = array();
$response["TOTAL"] = $_SESSION["PB_TOTAL"];
$response["CURRENT"] = $_SESSION["PB_CURRENT"];
$response["STATUS"] = $_SESSION["PB_STATUS"];

$json = json_encode($response);
print $json;