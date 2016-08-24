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

$url = filter_input(INPUT_GET, "url");
$import_images = boolval(filter_input(INPUT_GET, "images"));
doliStream::setSysMessage("URL: " . $url);
if(1==2)
{
    $db = new DoliDBMysqli();
}

$arrayIDs = doliCompare::getArrayIDs($db); //GET ARRAY OF IDs PRESENT IN COMPARE TABLE TO EXCLUDE FROM QUERY
doliStream::setSysMessage("Excluded ids: (" . count($arrayIDs) . "): " . implode("-",$arrayIDs));

$jshop_db = mysqlParams::getConnection();

//Get main resultset
$resultset = doliFetch::getMainResultset($jshop_db, $arrayIDs);
if($resultset)
{
    //FETCH RESULTSET
    $tot_records = $jshop_db->num_rows($resultset);
    doliStream::setSysMessage("Start Import: " . $tot_records);
    doliStream::setMessage("CREATE ARRAY: START.", 0, $tot_records);
    $rows = doliFetch::createArray($jshop_db, $resultset); //Get rows to import
}
else
{
    doliStream::setDBErrorMessage($jshop_db);
    dolistream::setCloseMessage(0);
    exit();
}

//GET ENTREPOT
$query_e = "select rowid from " . MAIN_DB_PREFIX . "entrepot order by rowid limit 1";
$res_e = $db->query($query_e);
if(!$res_e)
{
    doliStream::setDBErrorMessage($db);
    $entrepot=1;
}
$entrepot_rs = $db->fetch_array($res_e);
$entrepot = $entrepot_rs[0];

//DISABLE FOREIGN CHECK
$db->query("SET FOREIGN_KEY_CHECKS = 0");

//START IMPORT
doliStream::setSysMessage("START IMPORT");
$total_records = count($rows);
$i=0;

foreach($rows as $row)
{
    //INSERT PRODUCT
    $query_insert = classQueryMaker::makeInsertFromArray(MAIN_DB_PREFIX,"product", $row);
    $res_ins = $db->query($query_insert);
    if(!$res_ins)
    {
        doliStream::setDBErrorMessage($db);
    }
    else
    {   
        //INSERT STOCK
        $stock = [
            "fk_product"=>$row["rowid"],
            "fk_entrepot"=>$entrepot,
            "reel"=>$row["stock"],
            "pmp"=>$row["price"],
        ];
        $query_insert_stock = classQueryMaker::makeInsertFromArray(MAIN_DB_PREFIX,"product_stock", $stock);
        $res_ins_stock = $db->query($query_insert_stock);
        if(!$res_ins_stock)
        {
            doliStream::setDBErrorMessage($db);
        }
        //INSERT STOCK_MOUVEMENT
        $stock_m = [
            "fk_product"=>$row["rowid"],
            "fk_entrepot"=>$entrepot,
            "value"=>$row["stock"],
            "price"=>$row["price"],
            "type_mouvement"=>3,
            "label"=>"import from JSHOPPING, " . date("Y-m-d"),
        ];
        $query_insert_stock_m = classQueryMaker::makeInsertFromArray(MAIN_DB_PREFIX,"stock_mouvement", $stock_m);
        $res_ins_stock_m = $db->query($query_insert_stock_m);
        if(!$res_ins_stock_m)
        {
            doliStream::setDBErrorMessage($db);
        }
        
        if($check)
        {
           //IMAGE COPY
            $folder = dirname(__FILE__) . DS . ".." . DS . ".." . DS . ".." . DS . "documents" . DS . "produit" . DS . $row["ref"];
            $query_img = "select image_name from " . mysqlParams::getPrefix() . "jshopping_products_images where product_id=" . $row["rowid"] . ";";
            $res_img = $jshop_db->query($query_img);
            if($res_img)
            {
                $row_img = $jshop_db->fetch_array($res_img);
                $image_name = $row_img[0];
                // QUI CI VA LA COPIA IMMAGINI
                if(!file_exists($folder))
                {
                    mkdir($folder);
                }
                $target = $folder . DS . $image_name;
                $source = $url . "/components/com_jshopping/files/img_products/$image_name";
                $imageString = file_get_contents($source);
                if(!boolval($imageString))
                {
                    doliStream::setSysMessage("ERRORE DURANTE LA LETTURA DELL'IMMAGINE: $source");
                }
                else
                {
                    $save = file_put_contents($target,$imageString);
                    doliStream::setSysMessage("COPIA DA $source IN $target");
                   
                    if(!boolval($save))
                    {
                        doliStream::setSysMessage("ERRORE DURANTE LA COPIA DELL'IMMAGINE: " . $row["ref"] .": $image_name");
                    }
                    else
                    {
                        doliStream::setSysMessage("COPIA DELL'IMMAGINE: " . $row["ref"] .": $image_name => OK.");
                        chmod($target,0775);
                    }
                }
            }
            else
            {
                doliStream::setDBErrorMessage($db);
            } 
        }
            
    }
    $i++;
    $progress = $i * 100 / $total_records;
    doliStream::setMessage("INSERT ID " . $row["rowid"], $progress, $tot);
    
    //INSERT INTO COMPARE TABLE
    $table_compare = MAIN_DB_PREFIX . "product_compare ";
    $query_insert_compare = "insert into " . $table_compare . " "
        . "select p.rowid,p.label,p.barcode,b.code as barcode_type,p.price_ttc,p.price_min_ttc,p.tva_tx,p.stock "
        . "from llx_product p, llx_c_barcode_type b "
        . "where p.fk_barcode_type=b.rowid and p.rowid = " . $row["rowid"] . ";";
    $db->query($query_insert_compare);
    doliStream::setMessage("INSERT INTO COMPARE TABLE: ROWID=" . $db->last_insert_id($table_compare), $progress);
}

$db->query("SET FOREIGN_KEY_CHECKS = 1");
doliStream::setMessage("OPERAZIONE ESEGUITA", "100%");

$query_tot_product = "select count(*) as total from " . MAIN_DB_PREFIX . "product";
$res_total = $db->query($query_tot_product);
$total_product = $db->fetch_object($res_total);

doliStream::setCloseMessage($total_product->total);


