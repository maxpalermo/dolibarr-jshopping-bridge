<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

function delLastAnd($input)
{
	return substr($input, 0, strlen($input)-4);
}

function getProduct($ret,$queryString)
{
    global $db, $warehouse, $pricelevel;
    $qryStock="";
    $qryPrice="";
    $qryBatch="";
    
    try 
    {
        while ($record = $db->fetch_object($ret))       
        {
            $product                = new ticketProduct();
            $product->rowid         = $record->rowid;
            $product->ref           = $record->ref;
            $product->label         = $record->label;
            $product->isBatch       = $record->tobatch;
            $product->barcode       = $record->barcode;
            $product->queryString   = $queryString;

            //get Stock
            $qryStock   = "select rowid,reel from ".MAIN_DB_PREFIX."product_stock where fk_entrepot = $warehouse and fk_product = $product->rowid ";
            $retStock   = $db->query($qryStock);
            if($retStock->num_rows)
            {
                $record_stock = $db->fetch_object($retStock);
                if($record_stock)
                {
                    $product->fk_stock  = $record_stock->rowid;
                    $product->qty       = $record->reel;
                }
            }

            $qryPrice   = "select price,tva_tx from ".MAIN_DB_PREFIX."product_price where price_level = $pricelevel and fk_product = $product->rowid order by date_price";
            $retPrice   = $db->query($qryPrice);
            if($retPrice->num_rows)
            {
                $record_price = $db->fetch_object($retPrice);
                if($record_price)
                {
                    $product->price     = number_format($record_price->price,2);
                    $product->tva_tx    = number_format($record->tva_tx,2);
                }
            }


            if($product->isBatch) //Looking for lotti
            {
                $qryBatch       = "select rowid,batch,eatby,qty from ".MAIN_DB_PREFIX."product_batch where fk_product_stock = $product->fk_stock";
                $retBatch   = $db->query($qryBatch);
                if($retBatch)
                {
                    while($row = $db->fetch_object($retBatch))
                    {
                        $batchProduct           = new ticketProduct();
                        $batchProduct           = clone $product;
                        $batchProduct->fk_batch = $row->rowid;
                        $batchProduct->batch    = $row->batch;
                        $eatby                  = new DateTime($row->eatby);
                        $batchProduct->eatby    = $eatby->format("m/Y");
                        $batchProduct->qty      = $row->qty; 
                        $batchProduct->visible  = true;
                        $products[] = $batchProduct;
                    }
                }
            }
            else
            {
                $products[] = $product;
            }    
        }

        return $products;
    } 
    catch (Exception $ex) 
    {
        print $ex->getMessage();
        print $qryPrice;
    }
    
    
}

session_start();
require_once "connect.php";
require_once "product.class.php";

//Get records from database
$barcode = escape(filter_input(INPUT_GET, "term"));
$term=escape(filter_input(INPUT_GET, "term"));
//$term = explode(" ", $term);

$pricelevel = escape($_SESSION['id-pricelist']);
$warehouse  = escape($_SESSION['id-warehouse']);
$product    = new ticketProduct();
$products   = array();

if(empty($pricelevel)){$pricelevel=1;}
if(empty($warehouse)){$warehouse=1;}
//FIND PRODUCT BY BARCODE

try 
{    
    $queryBarCode = "select rowid,ref,label,tva_tx,tobatch,barcode from ".MAIN_DB_PREFIX."product where barcode = '$barcode'";
    $ret = $db->query($queryBarCode);
    if($ret->num_rows) 
    {
        //Return result to jTable
        print json_encode(getProduct($ret,$queryBarCode));
        return;
    }
} 
catch (Exception $ex) 
{
    print $ex->getMessage();
    print $queryBarCode;
}

try 
{
    $queryRef = "select rowid,ref,label,tva_tx,tobatch,barcode from ".MAIN_DB_PREFIX."product where ref like '%$term%'";
    $ret = $db->query($queryRef);
    if($ret->num_rows) 
    {
        //Return result to jTable
        print json_encode(getProduct($ret,$queryRef));
        return;
    }
} 
catch (Exception $ex) 
{
    print $ex->getMessage();
    print $queryRef;
}

try 
{
    $queryLabel = "select rowid,ref,label,tva_tx,tobatch,barcode from ".MAIN_DB_PREFIX."product where label like '%$term%'";
    $ret = $db->query($queryLabel);
    if($ret->num_rows) 
    {
        //Return result to jTable
        print json_encode(getProduct($ret,$queryLabel));
        return;
    }
} 
catch (Exception $ex) 
{
    print $ex->getMessage();
    print $queryLabel;
}


//print_r ($rows);
?>