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
require_once dirname(__FILE__) .  DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "class"  . DIRECTORY_SEPARATOR . "mysqlParams.class.php";

if(1==2){$db = new DoliDBMysqli("mysql", "localhost", "user", "pass");}
$dbOut      = mysqlParams::getConnection();
$langs      = mysqlParams::getLanguages();
$pref       = mysqlParams::getPrefix();
$tablename  = "jshopping_categories";

session_start();
$_SESSION["PB_STATUS"]="active";

//DELETE CONTENT
$sqlDelete = "DELETE FROM " . $pref . $tablename;
$del = $dbOut->query($sqlDelete);
//print "DELETE $pref$tablename: $del\n";
if($del)
{
    //RESET COUNTER
    $SqlResetIncrement = "ALTER TABLE " . $pref . $tablename .  " AUTO_INCREMENT = 1";
    $AI = $dbOut->query($SqlResetIncrement);
    //print "AI: $AI\n";
    //PREPARE INSERT STATEMENT
    $columns = [
        "`category_id`",
        "`category_parent_id`",
        "`category_image`"
    ];
    foreach($langs as $lang)
    {
        $columns[] = "`name_" . $lang->tag . "`";
        $columns[] = "`alias_" . $lang->tag . "`";
    }
    //GET DOLIBARR CATEGORIES COUNT
    $queryTot = "select count(*) as tot from " . MAIN_DB_PREFIX . "categorie";
    $count = $db->query($queryTot);
    if($count)
    {
        $tot = $db->fetch_object($count)->tot;
        //print "TOTAL RECORDS: $tot\n";
    }
    else
    {
        //print "Nessun record trovato: USCITA FORZATA\n";
        return;
    }
    //GET DOLIBARR CATEGORIES
    $queryCategorie="select * from " . MAIN_DB_PREFIX . "categorie";
    $resultCategorie = $db->query($queryCategorie);
    if($resultCategorie)
    {
        $curr = 0;
        
        session_start();
        if($_SESSION["PB_STATUS"]=="terminated"){send_message("CLOSE", "OPERAZIONE ANNULLATA", 100); return;}
        
        //FETCH RECORDSET
        while($rs = $db->fetch_object($result))
        {
            $values = [
                $rs->rowid,
                $rs->fk_parent,
                mysqlParams::getImageCat("")
            ];
            foreach($langs as $lang)
            {
                $values[] = "'" . $db->escape($rs->label) . "'";
                $values[] = "'" . $db->escape($rs->label) . "'";
            }       
            
            $QueryInsert = "INSERT INTO " . $pref . $tablename . "(" . implode(",",$columns) . ") VALUES (" . implode(",",$values) . ");";
            $success = $dbOut->query($QueryInsert);
            if($success)
            {
                $curr++;
                send_message('LOOP', "FETCHING DATA...",  intval($curr*100/$tot));
            }
            else
            {
                $curr++;
                send_message('CLOSE', 'Process error',100);
            }
        }
        send_message('CLOSE', 'Process complete',100);
    }
    else
    {
        
    }
}
else
{
    send_message('CLOSE', "errore " . $db->lasterrno() . ": " . $db->lasterror(),100);
}


function send_message($id, $message, $progress) {
    $d = array('message' => $message , 'progress' => $progress); //prepare json
    echo "id: $id" . PHP_EOL;
    echo "data: " . json_encode($d) . PHP_EOL;
    echo PHP_EOL;

    ob_end_flush();
    flush();
}