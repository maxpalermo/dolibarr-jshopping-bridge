<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once "framework.php";
require_once ".." . DS . "class" . DS .  "classQueryMaker.php";
require_once ".." . DS . "class" . DS .  "mysqlParams.class.php";

function toDBDecimal($value)
{
    return str_replace(",", ".", $value);
}

function recordExists(DoliDBMysqli $db,$record)
{
    $query = "select count(*) as total from " . MAIN_DB_PREFIX . "product_compare where rowid = " . $record->rowid;
    $res = $db->query($query);
    if($res)
    {
        $exists = $db->fetch_object($res);
        //print "RECORD EXISTS: " . $exists->total . "\n";
        return $exists->total;
    }
}

function getEntrepot(DoliDBMysqli $db)
{
    $query = "select rowid from " . MAIN_DB_PREFIX . "entrepot order by rowid LIMIT 1";
    $res = $db->query($query);
    if($res)
    {
        $entrepot = $db->fetch_object($res);
        //print "ENTREPOT: " . $entrepot->rowid . "\n";
        return $entrepot->rowid;
    }
    return 0;
}

function updateRecord(DoliDBMysqli $db,$record)
{
    $entrepot = getEntrepot($db);
    $stock = updateTableCompare($db, $record);
    $prices = updatetableProduct($db,$record,$stock);
    insertTableProductPrice($db, $record, $prices);
    updateTableProductStock($db, $record, $stock);
    insertTableStockMouvement($db, $record, $entrepot);
    updateJShopStock($record,$stock);
}

function updateJShopStock($record,$stock)
{
    $db = mysqlParams::getConnection();
    $query = "UPDATE " . mysqlParams::getPrefix() . "jshopping_products SET product_quantity = $stock WHERE product_id = " . $record->rowid;
    //print $query . "\n";
    $res = $db->query($query);
    if(!$res)
    {
        print "ERRORE: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
}

function insertTableStockMouvement(DoliDBMysqli $db,$record,$entrepot)
{
    //INSERT TABLE STOCK MOUVEMENT
    $columns = [
        "fk_product",
        "fk_entrepot",
        "value",
        "price",
        "type_mouvement",
        "label",
    ];
    $values = [
        $record->rowid,
        $entrepot,
        $record->stock,
        $record->price,
        "3",
        "Update stock from JShopping\n" . date("Y-m-d"), 
    ];
    $tablename = "stock_mouvement";
    $query_ins = classQueryMaker::makeInsert($tablename, $columns, $values, MAIN_DB_PREFIX);
    //print "INSERT STOCK MOUVEMENT\n";
    //print $query_ins . "\n";
    $res_ins = $db->query($query_ins);
    if(!$res_ins)
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    } 
}

function insertTableProductStock(DoliDBMysqli $db,$record,$entrepot)
{
    //INSERT TABLE PRODUCT STOCK
    $columns = [
        "fk_product",
        "fk_entrepot",
        "reel",
    ];
    $values = [
        $record->rowid,
        $entrepot,
        $record->stock,
    ];
    $tablename = "product_stock";
    $query_ins = classQueryMaker::makeInsert($tablename, $columns, $values, MAIN_DB_PREFIX);
    //print "INSERT TABLE PRODUCT STOCK\n";
    //print $query_ins . "\n";
    $res_ins = $db->query($query_ins);
    if(!$res_ins)
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
}

function updateTableProductStock(DoliDBMysqli $db,$record,$stock)
{
    //INSERT TABLE PRODUCT STOCK
    $columns = [
        "reel",
    ];
    $values = [
        $stock,
    ];
    $tablename = [
        MAIN_DB_PREFIX .  "product_stock",
        ];
    $where = [
        "fk_product = " . $record->rowid,
    ];
    $query_ins = classQueryMaker::makeUpdate($tablename, $columns, $values, $where);
    //print "UPDATE TABLE PRODUCT STOCK\n";
    //print $query_ins . "\n";
    $res_ins = $db->query($query_ins);
    if(!$res_ins)
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
}

function insertTableProductPrice(DoliDBMysqli $db,$record,$prices)
{
    
    //INSERT TABLE PRODUCT PRICE
    $columns = [
        "entity",
        "fk_product",
        "date_price",
        "price_level",
        "price_ttc",
        "price_min_ttc",
        "price",
        "price_min",
        "price_base_type",
        "tva_tx",
        "tosell"
    ];
    $values = [
        "1",
        $record->rowid,
        date("Y-m-d H:i:s"),
        "1",
        $record->price,
        $record->price_min,
        $prices[0],
        $prices[1],
        "TTC",
        $record->tva_tx,
        "1",
    ];
    $tablename = "product_price";
   
    $query_ins = classQueryMaker::makeInsert($tablename, $columns, $values, MAIN_DB_PREFIX);
    //print "INSERT TABLE PRODUCT PRICE\n";
    //print $query_ins . "\n";
    $res_ins = $db->query($query_ins);
    if(!$res_ins)
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
}

function updateTableProduct(DoliDBMysqli $db,$record,$stock)
{
    $price = ($record->price * 100) / ($record->tva_tx + 100);
    $price_min = ($record->price_min * 100) / ($record->tva_tx + 100);
    $prices = [
        $price,
        $price_min,
    ];
    
    //UPDATE TABLE PRODUCT
    $columns = [
        "label",
        "barcode",
        "price_ttc",
        "price_min_ttc",
        "price",
        "price_min",
        "tva_tx",
    ];
    $values = [
        $record->label,
        $record->barcode,
        $record->price,
        $record->price_min,
        $price,
        $price_min,
        $record->tva_tx,
    ];
    $where = [
        "rowid = " . $record->rowid,
    ];
    $tablename = [
        MAIN_DB_PREFIX . "product",
    ];
    $query_upd = classQueryMaker::makeUpdate($tablename, $columns, $values, $where);
    //print "INSERT TABLE PRODUCT\n";
    //print $query_upd . "\n";
    $res_upd = $db->query($query_upd);
    if(!$res_upd)
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
    
    //UPDATE STOCK VALUE
    $query_upd_stock = "UPDATE " . MAIN_DB_PREFIX . "product SET stock = $stock WHERE rowid = " . $record->rowid;
    //print $query_upd_stock . "\n";
    $res_upd_stock = $db->query($query_upd_stock);
    if(!$res_upd_stock)
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
    
    return $prices;
}

function updateTableCompare(DoliDBMysqli $db, $record)
{
    //UPDATE TABLE COMPARE
    $columns = [
        "label",
        "barcode",
        "price_ttc",
        "price_min_ttc",
        "tva_tx",
    ];
    $values = [
        $record->label,
        $record->barcode,
        $record->price,
        $record->price_min,
        $record->tva_tx,
    ];
    $where = [
        "rowid = " . $record->rowid,
    ];
    $tablename = [
        MAIN_DB_PREFIX . "product_compare",
    ];
    $query_upd_compare = classQueryMaker::makeUpdate($tablename, $columns, $values, $where);
    //print "UPDATE TABLE COMPARE\n";
    //print $query_upd_compare . "\n";
    $res_upd_compare = $db->query($query_upd_compare);
    if(!$res_upd_compare)
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
    
    
    //UPDATE STOCK VALUE
    $query_upd_stock = "UPDATE " . MAIN_DB_PREFIX . "product_compare SET stock = stock + " . $record->stock . " WHERE rowid = " . $record->rowid;
    //print $query_upd_stock . "\n";
    $res_upd_stock = $db->query($query_upd_stock);
    if(!$res_upd_stock)
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
    
    //GET STOCK
    $query_get = "select stock from " . MAIN_DB_PREFIX . "product_compare where rowid = " . $record->rowid;
    $res_get = $db->query($query_get);
    if($res_get)
    {
        $stock = $db->fetch_object($res_get);
        return $stock->stock;
    }
    else 
    {
        print "Errore: " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    }
}

function insertRecord(DoliDBMysqli $db,$record)
{
    //TO DO
}

function insertImage($record)
{
    // TO DO
}

$method = GETPOST("method");
$object = json_decode(GETPOST("object"));
if(1==2){$db=new DoliDBMysqli();}

foreach($object as $record)
{
    if(recordExists($db, $record))
    {
        updateRecord($db, $record);
    }
    else
    {
        insertRecord($db, $record);
    }
    insertImage($record);
}

print "Operazione completata." . "\n";