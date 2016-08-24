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

$url = filter_input(INPUT_GET, "url");
$import_images = boolval(filter_input(INPUT_GET, "images"));
setMessage([
    "type"=>"count",
    "msg"=>"URL: $url",
    "prog"=>"",
]);
if(1==2)
{
    $db = new DoliDBMysqli();
}
$jshop_db = mysqlParams::getConnection();

//$query = "select count(*) as tot_records from " . MAIN_DB_PREFIX . "product";
//$res = $db->query($query);
//$value = $db->fetch_row($res);
//
//print "id: 0\n";
//print "event: recordcount\n";
//print "data: {\"tot\": \"" . $value[0] . "\", \"time\": \"". date("d/m/Y H:i:s") . "\"}\n\n";

$columns = [
    "[product_id] as rowid",
    "{CONCAT('P',product_id)} as ref",
    "1 as entity",
    "[product_date_added] as datec",
    "[parent_id] as fk_parent",
    "[name_it-IT] as label",
    "[description_it-IT] as description",
    "[product_price] as price_ttc",
    "0 as price",
    "[min_price] as price_min_ttc",
    "0 as price_min",
    "ttc as price_base_type",
    "[product_tax_id] as tva_tx",
    "[product_url] as url",
    "[product_ean] as barcode",
    "2 as fk_barcode_type",
    "[product_weight] as weight",
    "[product_quantity] as stock",
    "{NOT `product_publish`} as hidden",
];

$tables = [
    mysqlParams::getPrefix() . "jshopping_products",
];

$query = classQueryMaker::makeSelect($tables, $columns, "");

$res = $jshop_db->query($query);
$rows = [];
if($res)
{
    $tot = $db->num_rows($res);
    $index=0;
    $arr = ["type"=>"select","message"=>"CREATE ARRAY","progress"=>0,"total"=>$tot];
    setMessage($arr,"message");
    $prog_number = 0;
    while($row = $jshop_db->fetch_object($res))
    {
        //GET TAX VALUE
        $tax_id = $row->tva_tx;
        $query_tx = "select tax_value from " . mysqlParams::getPrefix() . "jshopping_taxes where tax_id = $tax_id";
        $res_tx = $jshop_db->query($query_tx);
        if($res_tx)
        {
            $value = $jshop_db->fetch_array($res_tx);
            $row->tva_tx = $value[0];
        }
        else
        {
            $row->tva_tx = 0;
        }
        //CALCULATE TTC PRICES
        $price = $row->price_ttc * 100 / (100 + $row->tva_tx) ;
        $price_min = $row->price_min_ttc * 100 / (100 + $row->tva_tx);
        
        //CONVERT TO ARRAY
        $row_new = [
            "rowid"=>$row->rowid,
            "ref"=>$row->ref,
            "entity"=>$row->entity,
            "datec"=>$row->datec,
            "fk_parent"=>$row->fk_parent,
            "label"=>$db->escape($row->label),
            "description"=>$db->escape($row->description),
            "price"=>$price,
            "price_ttc"=>$row->price_ttc,
            "price_min"=>$price_min,
            "price_min_ttc"=>$row->price_min_ttc,
            "price_base_type"=>$row->price_base_type,
            "tva_tx"=>$row->tva_tx,
            "url"=>$row->url,
            "barcode"=>$row->barcode,
            "fk_barcode_type"=>$row->fk_barcode_type,
            "weight"=>$row->weight,
            "stock"=>$row->stock,
            "hidden"=>$row->hidden,
        ];
        $rows[] = $row_new;
        $index++;
        $progress = $index * 100 / $tot;
        setMessage([
            "type"=>"message",
            "message"=>"Creazione Array...",
            "progress"=>$progress
        ]);
        $prog_number++;
        setMessage([
         "type"=>"count",
         "prog"=>$prog_number . "/" . $tot,
         "msg"=>"Inserito elemento ",
        ]);
    }
    $arr = ["type"=>"sys-msg","message"=>"CREATION OK"];
    setMessage($arr,"sys-msg");
}
else
{
    $arr = ["type"=>"error","message"=>"ERRORE DURANTE LA CREAZIONE DELL'ARRAY"];
    setMessage($arr,"error");
    $arr = ["type"=>"error","message"=>$jshop_db->lasterrno() . ": " . $jshop_db->error()];
    setMessage($arr,"error");
    $arr = ["type"=>"error","query"=>$query];
    setMessage($arr,"error");
}

//GET ENTREPOT
$query_e = "select rowid from " . MAIN_DB_PREFIX . "entrepot order by rowid limit 1";
$res_e = $db->query($query_e);
if(!$res_e)
{
    $arr = ["type"=>"error","message"=>"ERRORE DURANTE LA SELEZIONE DEL MAGAZZINO"];
    setMessage($arr,"error");
    $arr = ["type"=>"error","message"=>$jshop_db->lasterrno() . ": " . $jshop_db->error()];
    setMessage($arr,"error");
    $arr = ["type"=>"error","query"=>$query_del];
    setMessage($arr,"error");
    $entrepot=1;
}
$entrepot_rs = $db->fetch_array($res_e);
$entrepot = $entrepot_rs[0];

//DISABLE FOREIGN CHECK
$db->query("SET FOREIGN_KEY_CHECKS = 0");
//TRUNCATE STOCK MOUVEMENT
$del = $db->query("TRUNCATE TABLE `" . MAIN_DB_PREFIX . "stock_mouvement`");
if(!$del)
{
    $arr = ["type"=>"error","message"=>"ERRORE DURANTE L'ELIMINAZIONE DELLE TABELLE"];
    setMessage($arr,"error");
    $arr = ["type"=>"error","message"=>$db->lasterrno() . ": " . $db->error()];
    setMessage($arr,"error");
    $arr = ["type"=>"error","query"=>$query_del];
    setMessage($arr,"error");
}
//GET ALL PRODUCT TABLES
$query_tbl = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dolibarr_main_db_name' AND TABLE_NAME LIKE '%product%'";
$res_tbl = $db->query($query_tbl);
while($tablename = $db->fetch_array($res_tbl))
{
    //TRUNCATE EACH PRODUCT TABLE
    $query_del = "TRUNCATE TABLE `" . $tablename[0] . "`";
    $del = $db->query($query_del);
    if(!$del)
    {
        $arr = ["type"=>"error","message"=>"ERRORE DURANTE L'ELIMINAZIONE DELLE TABELLE"];
        setMessage($arr,"error");
        $arr = ["type"=>"error","message"=>$db->lasterrno() . ": " . $db->error()];
        setMessage($arr,"error");
        $arr = ["type"=>"error","query"=>$query_del];
        setMessage($arr,"error");
    }
}

//START IMPORT
$arr = ["type"=>"message","message"=>"START IMPORT"];
setMessage($arr,"message");
$total_records = count($rows);
$i=0;
$percentage = $total_records * $tot_import / 100;
$arr = ["type"=>"message","message"=>"IMPORTING","query"=>$query,"progress"=>$percentage,"tot"=>$total_records];
setMessage($arr,"message",$row["rowid"]);

$index=0;
$tot=count($rows);
$prog_number=0;
foreach($rows as $row)
{
    //INSERT PRODUCT
    $query_insert = classQueryMaker::makeInsertFromArray(MAIN_DB_PREFIX,"product", $row);
    $res_ins = $db->query($query_insert);
    if(!$res_ins)
    {
        $arr = ["type"=>"error","message"=>"ERRORE DURANTE L'INSERIMENTO DEI PRODOTTI"];
        setMessage($arr,"error");
        $arr = ["type"=>"error","message"=>$db->lasterrno() . ": " . $db->error()];
        setMessage($arr,"error");
        $arr = ["type"=>"error","message"=>$query_insert];
        setMessage($arr,"error");
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
            $arr = ["type"=>"error","message"=>"ERRORE DURANTE L'INSERIMENTO NEL MAGAZZINO"];
            setMessage($arr,"error");
            $arr = ["type"=>"error","message"=>$db->lasterrno() . ": " . $db->error()];
            setMessage($arr,"error");
            $arr = ["type"=>"error","message"=>$query_insert_stock];
            setMessage($arr,"error");
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
            $arr = ["type"=>"error","message"=>"ERRORE DURANTE L'INSERIMENTO NEI MOVIMENTI DI MAGAZZINO"];
            setMessage($arr,"error");
            $arr = ["type"=>"error","message"=>$db->lasterrno() . ": " . $db->error()];
            setMessage($arr,"error");
            $arr = ["type"=>"error","message"=>$query_insert_stock_m];
            setMessage($arr,"error");
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
                    setMessage([
                        "type"=>"error",
                        "message"=>"ERRORE DURANTE LA LETTURA DELL'IMMAGINE",
                    ],"error");
                    setMessage([
                        "type"=>"error",
                        "message"=>"SOURCE: $source",
                    ],"error");
                }
                else
                {
                    $save = file_put_contents($target,$imageString);
                    setMessage([
                            "type"=>"count",
                            "msg"=>"copia di $source in $target",
                            "prog"=>"",
                        ]);
                    if(!boolval($save))
                    {
                        setMessage([
                            "type"=>"error",
                            "message"=>"ERRORE DURANTE LA COPIA DELL'IMMAGINE",
                        ],"error");
                        setMessage([
                            "type"=>"error",
                            "message"=>"IMMAGINE REF " . $row["ref"] .": $image_name",
                        ],"error");
                    }
                    else
                    {
                        setMessage([
                            "type"=>"count",
                            "message"=>"COPIA " . $row["ref"] . "=>$image_name OK",
                            "progress"=>""
                        ]);
                        chmod($target,0775);
                    }
                }
            }
            else
            {
                setMessage([
                    "type"=>"error",
                    "message"=>"ERRORE DURANTE LA SELEZIONE DELL'IMMAGINE",
                ],"error");
                setMessage([
                    "type"=>"error",
                    "message"=>$query_img,
                ],"error");
                setMessage([
                    "type"=>"error",
                    "message"=>$jshop_db->errno() . ": " . $jshop_db->error(),
                ],"error");
            } 
        }
            
    }
    $i++;
    $index++;
    $progress = $index * 100 / $tot;
    setMessage([
        "type"=>"message",
        "message"=>"IMPORTAZIONE IN CORSO",
        "progress"=>$progress,
    ]);
    $prog_number++;
    setMessage([
    "type"=>"count",
    "prog"=>$prog_number . "/" . $tot,
    "msg"=>"Inserito record ",
]);
    //if ($i==10){break;}
}

$db->query("SET FOREIGN_KEY_CHECKS = 1");

$query_truncate_compare = "truncate table llx_product_compare;";
$db->query($query_truncate_compare);
$query_insert_compare = "insert into llx_product_compare "
        . "select p.rowid,p.label,p.barcode,b.code as barcode_type,p.price_ttc,p.price_min_ttc,p.tva_tx,p.stock "
        . "from llx_product p, llx_c_barcode_type b "
        . "where p.fk_barcode_type=b.rowid;";
$db->query($query_insert_compare);

$arr = ["type"=>"end","message"=>"OPERAZIONE ESEGUITA.","tot"=>$tot];
setMessage($arr,"close");

function setMessage($array,$event="message",$id=0)
{
    $data = json_encode($array);
    print "id: $id\n";
    print "event: $event\n";
    print "data: $data\n\n";
    ob_flush();
    flush();
}
