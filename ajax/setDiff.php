<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once './framework.php';
$diff = GETPOST("diff");
session_start();
$_SESSION["STRING_DIFF"] = $diff;
print $diff;