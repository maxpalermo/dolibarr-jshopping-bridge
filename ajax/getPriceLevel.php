<?php
header("content-type:application/json");
require_once "connect.php";

//Get records from database
$query="select distinct price_level from ".MAIN_DB_PREFIX."product_price order by price_level";
	
$record=array();
$rows = array();
$result = $db->query($query);

while($row = $db->fetch_object($result))
{
	$record=new stdClass();
	
	$record->id=$row->price_level;
        $record->label="Listino " . $row->price_level;
        $record->value="Listino " . $row->price_level;
	$rows[]=$record;
};

//Return result to jTable
print json_encode($rows);
//print_r ($rows);
?>