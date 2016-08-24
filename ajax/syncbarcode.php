<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

require_once "framework.php";
require_once ".." . DS . "class" . DS .  "classQueryMaker.php";
require_once ".." . DS . "class" . DS .  "mysqlParams.class.php";
require_once ".." . DS . "class" . DS .  "classImportUtilities.php";

$mode = GETPOST("mode");

doliStream::setSysMessage("Start Import Barcode, mode: " . $mode);
if(1==2)
{
    $db = new DoliDBMysqli();
}

$jshop_db = mysqlParams::getConnection();
$query_jshop = "select product_id,product_ean from " . mysqlParams::getPrefix() . "jshopping_products where CHAR_LENGTH(product_ean)>0";
$res = $jshop_db->query($query_jshop);
if($res)
{
    $total = $jshop_db->num_rows($res);
    $i = 0;
    $perc = 0;
    $curr_perc = 0;
    while($rec = $jshop_db->fetch_object($res))
    {
        $query_upd = "update " . MAIN_DB_PREFIX . "product set barcode = '" . $rec->product_ean . "' where rowid = " . $rec->product_id;
        doliStream::setSysMessage($query_upd);
        $db->query($query_upd);
        $i++;
        $perc = $i/$total * 100;
        $curr_perc = intval($perc);
        doliStream::setMessage("Importazione BARCODE...", $curr_perc, $total);
        doliStream::setSysMessage("Importazione BARCODE: " .  $curr_perc . "% (" . $i . "/" .  $total . ")");
    }
    doliStream::setCloseMessage($total);
}