<?php
header("content-type:application/json");
require_once "connect.php";

//Get records from database
$term=escape($_GET['term']);

//Get records from database
$query="select rowid,nom,address,town from ".MAIN_DB_PREFIX."societe where client=1 and nom like '%$term%' order by nom, town LIMIT 50";
//print $query;
$rows = array();
$result = $db->query($query);

while($row = $db->fetch_object($result))
{
	$record=new stdClass();
	/*
        $record['result']="OK";
	
	$structRec=array();
		$structRec['rowid']=$row->rowid;
		$structRec['name']=htmlentities($row->nom);
		$structRec['address']=htmlentities($row->address);
		$structRec['town']=htmlentities($row->town);
	
	$record['record']=$structRec; 
         */
        $record->id = $row->rowid;
        $record->label = $row->nom . " " .$row->address . " " . $row->town;
        $record->value = $row->nom;
	$rows[]=$record;
};

//Return result to jTable
print json_encode($rows);
//print_r ($rows);
?>