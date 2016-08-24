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
require_once ".." . DS . "class" . DS .  "classEventSource.php";
require_once ".." . DS . "class" . DS .  "mysqlParams.class.php";

function toDecimal($value,$default=NULL)
{
    if(empty($value))
    {
        $value=$default;
    }
    return number_format((float)$value, 2, ",", ".");
}

function toInt($value,$default=NULL)
{
    if(empty($value))
    {
        $value=$default;
    }
    return number_format((int)$value, 0, ",", ".");
}

function setSyncValues($syncRow,$doliRow,$jshpRow)
{
    $values     = new stdClass();
    $price      = new stdClass();
    $price_min  = new stdClass();
    $tva_tx     = new stdClass();
    $stock      = new stdClass();
    
    $price->sync = toDecimal($syncRow->price_ttc);
    $price->doli = toDecimal($doliRow->price_ttc,$syncRow->price_ttc);
    $price->jshp = toDecimal($jshpRow->price_ttc,$syncRow->price_ttc);
    
    $price_min->sync = toDecimal($syncRow->price_min_ttc);
    $price_min->doli = toDecimal($doliRow->price_min_ttc,$syncRow->price_min_ttc);
    $price_min->jshp = toDecimal($jshpRow->price_min_ttc,$syncRow->price_min_ttc);
    
    $tva_tx->sync = toDecimal($syncRow->tva_tx);
    $tva_tx->doli = toDecimal($doliRow->tva_tx,$syncRow->tva_tx);
    $tva_tx->jshp = toDecimal($jshpRow->tva_tx,$syncRow->tva_tx);
    
    $stock->sync = toInt($syncRow->stock);
    $stock->doli = toInt($doliRow->stock,$syncRow->stock) - toInt($syncRow->stock);
    $stock->jshp = toInt($jshpRow->stock,$syncRow->stock) - toInt($syncRow->stock);
    $stock->tot  = toInt($stock->doli + $stock->jshp + $stock->sync);
    
    if($stock->doli>0){$stock->doli = "+".$stock->doli;}
    if($stock->jshp>0){$stock->jshp = "+".$stock->jshp;}
    
    /*
    print "<pre style='background-color: #aaaaaa;'>ROWID:\n" . print_r($syncRow->rowid,1) . "</pre>";
    print "<pre style='background-color: #ffaaaa;'>PRICE :\n" . print_r($price,1) . "</pre>";
    print "<pre style='background-color: #aaaaff;'>PRICE_MIN :\n" . print_r($price_min,1) . "</pre>";
    print "<pre style='background-color: #aaffaa;'>TVA_TX :\n" . print_r($tva_tx,1) . "</pre>";
    print "<pre style='background-color: #faffaf;'>STOCK :\n" . print_r($stock,1) . "</pre>";
    */
        
    $values->price = $price;
    $values->price_min = $price_min;
    $values->tva_tx = $tva_tx;
    $values->stock = $stock;
    
    return $values; 
}

function compareChecked($array)
{
    $out["checked"]="";
    $out["tr_checked"]="";
    
    foreach($array as $row)
    {
        if(!empty($row["checked"]))
        {
            $out["checked"] = $row["checked"];
        }
        if(!empty($row["tr_checked"]))
        {
            $out["tr_checked"] = $row["tr_checked"];
        }
    }
    return $out;
}

function prepareTableRow($sync,$doli,$jshp,$new,$tot = NULL)
{
    $blue="#5555AA";
    $red="#AA5555";
    $green="#55AA55";
    
    //print "<pre>sync:$sync, doli:$doli, jshp:$jshp, TOT:$tot</pre>";
    
    if($tot==NULL)
    {
        $tot_row="";
    }
    else
    {
        $tot_row = "<span id='tot' style='font-weight: bold;'>" . $tot . "</span>";
    }
    
    if($sync!=$doli && $sync!=$jshp) // Three different prices
    {
            $row = "<span style='color: $red;'>"   . $sync. "</span>"  .
                   "<span style='color: $blue;'>"  . $doli . "</span>" .
                   "<span style='color: $green;'>" . $jshp . "</span>" .
                   $tot_row;
                   
            $checked=" checked = 'checked' ";
            $tr_checked = " class='tr-checked' ";
    }
    elseif($sync==$doli && $sync!=$jshp) //Jshopping price different
    {
            $row = "<span style='color: $red;'>"   . $sync. "</span>"  .
                   "<span style='color: $blue;'>"  . $sync . "</span>" .
                   "<span style='color: $green;'>" . $jshp . "</span>" .
                   $tot_row;
            $checked=" checked = 'checked' ";
            $tr_checked = " class='tr-checked' ";
    }
    elseif($sync!=$doli && $sync==$jshp) //Dolibarr price different
    {
            $row = "<span style='color: $red;'>"   . $sync. "</span>"  .
                   "<span style='color: $blue;'>"  . $doli . "</span>" .
                   "<span style='color: $green;'>" . $sync . "</span>" .
                   $tot_row;
            $checked=" checked = 'checked' ";
            $tr_checked = " class='tr-checked' ";
    }
    else //Nothing changed
    {
            $checked="";
            $tr_checked="";
            $row = "<span style='color: $red;'>"   . $sync. "</span>"  .
                   "<span style='color: $blue;'>"  . $sync . "</span>" .
                   "<span style='color: $green;'>" . $sync . "</span>" .
                   $tot_row;
    }
    
    if($new)
    {
        $checked=" checked = 'checked' ";
        $tr_checked = " class='tr-checked' ";
    }
    
    if(!empty($tot) && $doli==0 && $jshp==0 && !$new)
    {
        $checked="";
        $tr_checked = "";
    }
    
    $out["row"] = $row;
    $out["checked"]=$checked;
    $out["tr_checked"]=$tr_checked;
    return $out;
}

function getJShoppingProducts()
{
    $result = [];
    $db_shop = mysqlParams::getConnection();
    $columns = [
        "[product_id] as rowid",
        "[name_it-IT] as label",
        "[product_ean] as barcode",
        "'2' as barcode_type",            
        "[product_price] as price_ttc",
        "[min_price] as price_min_ttc",
        "[tax_value] as tva_tx",
        "[product_quantity] as stock",
    ];
    $tables = [
        mysqlParams::getPrefix() . "jshopping_products p",
        mysqlParams::getPrefix() . "jshopping_taxes t",
    ];
    $where = [
        "product_tax_id = tax_id",
    ];
    $order = [
        "name_it-IT",
    ];
    $prefix = mysqlParams::getPrefix();
    $query = classQueryMaker::makeSelect($tables, $columns, $prefix, $where, $order);
    $res = $db_shop->query($query);
    if($res)
    {
        while($row = $db_shop->fetch_object($res))
        {
            $result[] = $row;
        }
        return $result;
    }
    else
    {
        return $db_shop->error() . ": " . $db_shop->errno();
    }         
}

function getDolibarrProducts(DoliDBMysqli $db)
{
    $result = [];
    $columns = [
        "rowid",
        "label",
        "barcode",
        "[fk_barcode_type] as barcode_type",
        "price_ttc",
        "price_min_ttc",
        "tva_tx",
        "stock",
    ];
    $tables = [
        MAIN_DB_PREFIX . "product",
    ];
    $order = [
        "label",
    ];
    $prefix = MAIN_DB_PREFIX;
    $query = classQueryMaker::makeSelect($tables, $columns, $prefix, array(), $order);
    //print "<pre>query: " . $query . "</pre>";
    $res = $db->query($query);
    if($res)
    {
        while($row = $db->fetch_object($res))
        {
            $result[] = $row;
        }
        return $result;
    }
    else
    {
        throw new Exception($db->error(), $db->errno());
    }         
}

function getProductCompareIds(DoliDBMysqli $db)
{
    $query = "select rowid from " . MAIN_DB_PREFIX . "product_compare";
    $res = $db->query($query);
    $ids = [];
    if($res)
    {
        while($id = $db->fetch_object($res))
        {
            $ids[] = $id->rowid;
        }
        return $ids;
    }
    print "<pre>" . $db->lasterrno() . ": " . $db->lasterror() . "</pre>";
    print "<pre>" . $db->lastqueryerror() . "</pre>";
    return NULL;
}

function getProductCompare(DoliDBMysqli $db)
{
    $query= "select *,'0' as product_new from " . MAIN_DB_PREFIX . "product_compare order by label";
    $res = $db->query($query);
    $products = [];
    if($res)
    {
        while($product = $db->fetch_object($res))
        {
            $products[] = $product;
        }
        return $products;
    }
    print "<pre>" . $db->lasterrno() . ": " . $db->lasterror() . "</pre>";
    print "<pre>" . $db->lastqueryerror() . "</pre>";
    return NULL;
}

function getProductJShop($ids)
{
    $db = mysqlParams::getConnection();
    $columns=[
        "[product_id] as rowid",
        "[name_it-IT] as label",
        "[product_ean] as barcode",
        "'2' as barcode_type",
        "[product_price] as price_ttc",
        "[min_price] as price_min_ttc",
        "[tax_value] as tva_tx",
        "[product_quantity] as stock",
        "'1' as product_new",
    ];
    $tables = [
        mysqlParams::getPrefix() . "jshopping_products p",
        mysqlParams::getPrefix() . "jshopping_taxes t",
    ];
    $where = [
        "p.product_tax_id=t.tax_id",
        "p.product_id not in (" . implode(",",$ids) . ")",
    ];
    $prefix = mysqlParams::getPrefix();
    
    $query = classQueryMaker::makeSelect($tables, $columns, $prefix, $where);
    $res = $db->query($query);
    $products = [];
    if($res)
    {
        while($row = $db->fetch_object($res))
        {
            $products[] = $row;
        }
        return $products;
    }
    print "<pre>" . $db->lasterrno() . ": " . $db->lasterror() . "</pre>";
    print "<pre>" . $db->lastqueryerror() . "</pre>";
    return NULL;
}

function getProductFromArray($array,$rowid)
{
    if(!is_array($array)){return NULL;}
    foreach($array as $product)
    {
        if($product->rowid==$rowid)
        {
            return $product;
        }
    }
    return NULL;
}

$action = GETPOST("show");

if(1==2){$db=new DoliDBMysqli();}
$jshopProducts = getJShoppingProducts();
$dolibarrProducts = getDolibarrProducts($db);
$ids = getProductCompareIds($db);
$products_compare = getProductCompare($db);
$products_jshop   = getProductJshop($ids);
?>

<table id="product-compare">
    <thead>
        <tr>
            <th><input type="checkbox" name="check-all" id="check-all"></th>
            <th>CODICE</th>
            <th>PRODOTTO</th>
            <th>BARCODE</th>
            <th>PREZZO</th>
            <th>PREZZO MIN.</th>
            <th>IVA</th>
            <th>MAGAZZINO</th>
        </tr>
    </thead>
    <tbody>
        <?php
            if(!empty($products_compare) || !empty($products_jshop))
            {
                $blue="#5555AA";
                $red="#AA5555";
                $green="#55AA55";
                
                /*
                if(!empty($products_compare) && !empty($products_jshop))
                {
                    $products = array_merge($products_compare,$products_jshop);
                }
                elseif(!empty($products_compare) && empty($products_jshop))
                {
                    $products = $products_compare;
                }
                elseif(empty($products_compare) && !empty($products_jshop))
                {
                    $products = $products_jshop;
                }
                
                usort($products, function($a, $b)
                {
                    return strcmp($a->label, $b->label);
                });
                */
                
                $products = $products_compare;
                
                //print "<pre style='background-color: #aaaaff;'>DOLI:\n" . print_r($dolibarrProducts,1) . "</pre>";
                //print "<pre style='background-color: #aaffaa;'>JSHP:\n" . print_r($jshopProducts,1) . "</pre>";
                
                foreach($products as $row)
                {
                    $rowid      = $row->rowid; //get rowid to compare
                    $row_doli   = getProductFromArray($dolibarrProducts,$rowid);
                    $row_jshp   = getProductFromArray($jshopProducts,$rowid);
                    $values     = setSyncValues($row, $row_doli, $row_jshp);
                    
                    $checked="";
                    $tr_checked="";
                    
                    //PRICE
                    $price_row = prepareTableRow(
                            $values->price->sync,
                            $values->price->doli,
                            $values->price->jshp,
                            $row->product_new);
                    //print "<pre>price_row: " . print_r($price_row,1) . "</pre>";
                    
                    //PRICE MIN
                    $price_min_row = prepareTableRow(
                            $values->price_min->sync,
                            $values->price_min->doli,
                            $values->price_min->jshp,
                            $row->product_new);
                    //print "<pre>price_min_row: " . print_r($price_min_row,1) . "</pre>";
                    
                    //TVA_TX
                    $tva_tx_row = prepareTableRow(
                            $values->tva_tx->sync,
                            $values->tva_tx->doli,
                            $values->tva_tx->jshp,
                            $row->product_new);
                    //print "<pre>tva_tx: " . print_r($tva_tx_row,1) . "</pre>";
                    
                    //STOCK
                    $stock_row = prepareTableRow(
                            $values->stock->sync,
                            $values->stock->doli,
                            $values->stock->jshp,
                            $row->product_new,
                            $values->stock->tot);
                    //print "<pre>stock_row: " . print_r($stock_row,1) . "</pre>";
                    
                    $arrayComp = [
                        $price_row,
                        $price_min_row,
                        $tva_tx_row,
                        $stock_row,
                    ];
                    $checked_array=compareChecked($arrayComp);
                    
                    if($action=="all" || ($action=="diff" && !empty($checked_array["tr_checked"])))
                    {
                        print "<tr " . $checked_array["tr_checked"] . ">\n";
                            print "<td><input type='checkbox' name='check-row[]' rowid='$rowid' " . $checked_array["checked"] . "></td>";
                            print "<td>" . $row->rowid . "</td>";
                            print "<td>" . $row->label . "</td>";
                            print "<td>" . $row->barcode . "</td>";
                            print "<td>" . $price_row["row"] . "</td>"; 
                            print "<td>" . $price_min_row["row"] . "</td>";
                            print "<td>" . $tva_tx_row["row"] . "</td>";
                            print "<td>" . $stock_row["row"] . "</td>";
                        print "</tr>\n";
                    }
                }
            }
        ?>
    </tbody>
</table>