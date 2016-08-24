<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "framework.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "ClassDB.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "classJShopCategories.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "classDoliCategories.php";

$jshopCategories = new classJShopCategories();
$doliCategories = new classDoliCategories($db);
$export = $jshopCategories->export();

print $db->query("DELETE FROM " . MAIN_DB_PREFIX . "categorie");
print "<pre> . $export . </pre>";
$res = $db->query($export);
print "<pre> RESULT: $res </pre>";
if($db->errno())
{
    print "<pre>";
    print $db->errno() . "\n";
    print $db->error() . "\n";
    print "</pre>";
}

print $doliCategories->renderTableCategories();

