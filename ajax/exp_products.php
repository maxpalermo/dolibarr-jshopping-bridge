<?php

header("Content-Type: text/event-stream\n\n");
// recommended to prevent caching of event data.
header('Cache-Control: no-cache');

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR .  "framework.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "mysqlParams.class.php";

session_start();
$diff = $_SESSION["STRING_DIFF"];
$_SESSION["PB_STATUS"]="active";

if(1==2) {$db = new DoliDBMysqli($type, $host, $user, $pass);}
$dbOut      = mysqlParams::getConnection();
$langs      = mysqlParams::getLanguages();
$pref       = mysqlParams::getPrefix();
$imgPath    = dirname(__FILE__) . "/../media";
$tablename  = $pref . "jshopping_products";
$tableCat   = $pref . "jshopping_products_to_categories";
$tableTax   = $pref . "jshopping_taxes";
$taxes      = array(); //Taxes array field['tax'] = value;
$fields     = array(
                "product_id",
                "parent_id",
                "product_ean",
                "product_quantity",
                "unlimited",
                "product_availability",
                "product_date_added",
                "date_modify",
                "product_publish",
                "product_tax_id",
                "currency_id",
                "product_template",
                "product_url",
                "product_old_price",
                "product_buy_price",
                "product_price",
                "min_price",
                "different_prices",
                "product_weight",
                "image",
                "product_manufacturer_id",
                "product_is_add_price",
                "add_price_unit_id",
                "average_rating",
                "reviews_count",
                "delivery_times_id",
                "hits",
                "weight_volume_units",
                "basic_price_unit_id",
                "label_id",
                "vendor_id",
                "access");
foreach($langs as $lang)
{
    $fields[]="name_" . $lang->tag;
    $fields[]="alias_" . $lang->tag;
    $fields[]="short_description_" . $lang->tag;
    $fields[]="description_" . $lang->tag;
    $fields[]="meta_title_" . $lang->tag;
    $fields[]="meta_description_" . $lang->tag;
    $fields[]="meta_keyword_" . $lang->tag;
}

//set to utf8
$dbOut->query("SET NAMES utf8");

/******************************************************************************
 * CLEAR TABLES
 ******************************************************************************/
 if(empty($diff))
 {
    $tableList = array(
        "attr",
        "attr2",
        "extra_fields",
        "extra_fields_groups",
        "extra_fields_values",
        "files",
        "free_attr",
        "images",
        "option",
        "prices",
        "relations",
        "reviews",
        "to_categories",
        "videos");
    $dbOut->query("delete from " . $pref . "jshopping_products");
    $dbOut->query("ALTER TABLE " . $pref . "jshopping_products AUTO_INCREMENT = 1");
    $dbOut->query("delete from " . $pref . "jshopping_taxes");
    $dbOut->query("ALTER TABLE " . $pref . "jshopping_taxes AUTO_INCREMENT = 1");
    foreach($tableList as $tbl)
    {
        $dbOut->query("delete from " . $pref . "jshopping_products_" . $tbl);
        $dbOut->query("ALTER TABLE " . $pref . "jshopping_products_" . $tbl . "AUTO_INCREMENT = 1");
    }
 }
 
/******************************************************************************
 * SET TAXES FIELDS
 ******************************************************************************/
$sqlTax = "select distinct tva_tx from " . MAIN_DB_PREFIX . "product order by tva_tx";
$resTva = $db->query($sqlTax);
if($resTva)
{
    while($rs = $db->fetch_object($resTva))
    {
        //print "Cerco l'iva al $rs->tva_tx %\n";
        $idTax = mysqlParams::setTVA($rs->tva_tx);
        $taxes["'" . $rs->tva_tx . "'"] = $idTax;
    }
}

/******************************************************************************
 * PREPARE LOCAL IMAGE FOLDER 
 ******************************************************************************/
mysqlParams::prepareFolder($imgPath);

/******************************************************************************
 * PREPARE PRODUCT SQL STRING TO EXPORT
 ******************************************************************************/
$apcFields = mysqlParams::apcFields($fields); //sanitize fieldname
$sqlFields = "INSERT INTO $tablename (" . implode(",", $sqlFields) . ") values (";


/******************************************************************************
* GET PRODUCT CATEGORIE ASSOCIATION FROM DOLIBARR
******************************************************************************/
$arrProdCat = [];
if(empty($diff))
{
    $sqlProdCat = "select fk_product,fk_categorie from " . MAIN_DB_PREFIX . "categorie_product order by fk_product";
}
else
{
    $sqlProdCat = "select fk_product,fk_categorie from " . MAIN_DB_PREFIX . "categorie_product where fk_product in ($diff) order by fk_product";
}

$resCatProd = $db->query($sqlProdCat);
if($resCatProd)
{
   while($rsCat = $db->fetch_object($resCatProd))
   {
       $idxProd = $rsCat->fk_product;
       $idxCat  = $rsCat->fk_categorie;
       $arrProdCat[] = $idxProd.",".$idxCat;
   }
}
else
{
   $arrProdCat = [];
}
//print "\n array categories: \n" . //print_r($arrProdCat,1) . "\n";

/******************************************************************************
 * GET PRODUCT FROM DOLIBARR
 ******************************************************************************/
if(empty($diff))
{
    $sqlCount = "select count(*) as tot from " . MAIN_DB_PREFIX . "product";
}
else
{
    $sqlCount = "select count(*) as tot from " . MAIN_DB_PREFIX . "product where rowid in ($diff)";
}
$rs_count = $db->query($sqlCount);
$tot = $db->fetch_object($rs_count)->tot;
$curr = 0;

if(empty($diff))
{
    $sqlProducts = "select * from " . MAIN_DB_PREFIX . "product order by label";
}
else
{
    $sqlProducts = "select * from " . MAIN_DB_PREFIX . "product where rowid in ($diff) order by label";
}

//print "\n\n" . $query . "\n\n";

/******************************************************************************
 * START FTP CONNECTION
 ******************************************************************************/
mysqlParams::getFTPConnection();

$result = $db->query($sqlProducts);
$i=0;
if($result)
{
    while($rs = $db->fetch_object($result))
    {
        session_start();
        if($_SESSION["PB_STATUS"]=="terminated"){send_message("CLOSE", "OPERAZIONE ANNULLATA", 100); return;}
            
        //print "\n\nIMPORTAZIONE PRODOTTO " . $rs->ref . "\n";
        $values = [];
        $values['product_id']               = $rs->rowid;
        $values['parent_id']                = mysqlParams::NN($rs->fk_parent);
        $values['product_ean']              = mysqlParams::str($rs->barcode);
        $values['product_quantity_stock']   = mysqlParams::NN($rs->stock);
        $values['unlimited']                = 0;
        $values['product_availability']     = mysqlParams::str("");
        $values['product_date_added']       = mysqlParams::str(mysqlParams::tms());
        $values['date_modify']              = mysqlParams::str(mysqlParams::tms());
        $values['product_publish']          = 1;
        $values['product_tax_id']           = $taxes["'" . $rs->tva_tx . "'"];
        $values['currency_id']              = 1;
        $values['product_template']         = mysqlParams::str("default");
        $values['product_url']              = mysqlParams::str("");
        $values['product_old_price']        = 0;
        $values['product_buy_price']        = 0;
        $values['product_price']            = mysqlParams::NN($rs->price_ttc);
        $values['min_price']                = mysqlParams::NN($rs->price_min_ttc);
        $values['different_prices']          = 0;
        $values['product_weight']           = mysqlParams::NN($rs->weight);
        $values['image']                    = mysqlParams::getImagePath($imgPath,$rs->ref,$rs->rowid);
        $values['product_manufacturer_id']  = 0;
        $values['product_is_add_price']     = 0;
        $values['add_price_unit_id']        = 1;
        $values['average_rating']           = 0;
        $values['reviews_count']            = 0;
        $values['delivery_times_id']        = 0;
        $values['hits']                     = 0;
        $values['weight_volume_units']      = 0;
        $values['basic_price_unit_id']      = 0;
        $values['label_id']                 = 1;
        $values['vendor_id']                = 0;
        $values['access']                   = 1;
        foreach($langs as $lang)            
        {
            $values['name_' . $lang->tag]                   = mysqlParams::str($rs->label);
            $values['alias_' . $lang->tag]                  = mysqlParams::str($rs->label);
            $values['short_description_' . $lang->tag]      = mysqlParams::str($rs->label);
            $values['description_' . $lang->tag]            = mysqlParams::stripNL($rs->description);
            $values['meta_title_' . $lang->tag]             = mysqlParams::str($rs->label);
            $values['meta_description_' . $lang->tag]       = mysqlParams::stripNL($rs->description);
            $values['meta_keyword_' . $lang->tag]           = mysqlParams::str("");
        }
        
        $exportSQL = $sqlFields . implode(",",$values) . ");";
        
        /**********************************************************************
        * INSERT PRODUCT IN JSHOPPING
        ***********************************************************************/
        $resProd = $dbOut->query($exportSQL);
        $curr++;
        send_message("LOOP", "Esportazione $curr/$tot effettuata.", intval($curr*100/$tot));
        if(!$resProd)
        {
            send_message("CLOSE", "errore " . $db->lasterrno() . ": " . $db->lasterror(), 100);
        }
        
        
        
        /**********************************************************************
        * INSERT CATEGORIE ASSOCIATION IN JSHOPPING
        ***********************************************************************/
        $product_id = $rs->rowid;
        foreach($arrProdCat as $prodCat)
        {
            $elem = explode(",", $prodCat);
            if($elem[0]==$product_id)
            {
                //print "trovato $product_id in $prodCat\n";
                
                $exportCAT = "insert into " 
                            . $pref . "jshopping_products_to_categories "
                            . "(`product_id`,`category_id`) values "
                            . "(" . $elem[0]. "," . $elem[1] . ");";
                //DO QUERY
                $resCat = $dbOut->query($exportCAT);
                if(!$resCat)
                {
                    //print "EXP CAT (err  " . $db->lasterrno() . ") " . $db->lasterror() . ", id: $rs->rowid \n"; 
                    //print "query:\n$exportCAT\n";
                }
            }
        }
        
      //Increment counter
      $i++;
      //if ($i == 30) {return;}
    }
    send_message("CLOSE", "OPERAZIONE ESEGUITA", 100);
    mysqlParams::closeFTPConnection();
}
else
{
    send_message("CLOSE", "errore " . $db->lasterrno() . ": " . $db->lasterror(), 100);
}

function send_message($id, $message, $progress) {
    $d = array('message' => $message , 'progress' => $progress); //prepare json
    echo "id: $id" . PHP_EOL;
    echo "data: " . json_encode($d) . PHP_EOL;
    echo PHP_EOL;

    ob_end_flush();
    flush();
}