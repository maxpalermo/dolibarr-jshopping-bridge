<?php
header("content-type:application/json");
require_once "connect.php";

//Get records from database
$query="select rowid,label,address,town from ".MAIN_DB_PREFIX."entrepot order by label, town";
	
$rows = array();
$result = $db->query($query);

while($row = $db->fetch_object($result))
{
	$record=new stdClass();
	$record->id=$row->rowid;
        $record->label=$row->label . " " . $row->address . " " . $row->town;
        $record->value=$row->label;
	
	$rows[]=$record;
};

//Return result to jTable
print json_encode($rows);
//print_r ($rows);
?>