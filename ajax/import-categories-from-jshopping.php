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

function import()
{
    global $db;
    if(1==2){$db = new DoliDBMysqli();}
    $params = new mysqlParams();
    $evt = new classEventSource();
    $db_joom = mysqlParams::getConnection();
    $fields = [
        "[category_id] as rowid",
        "1 as entity",
        "[category_parent_id] as fk_parent",
        "[name_it-IT] as label",
        "0 as type",
        "[description_it-IT] as description",
        "[category_publish] as visible",
        "[category_image] as image",
    ];
    $prefixFrom = $params->getPrefix();
    $tablenameFrom  = "jshopping_categories";
    $prefixTo = MAIN_DB_PREFIX;
    $tablenameTo    = "categorie";

    $querySelect = classQueryMaker::makeSelect($tablenameFrom, $fields, $prefixFrom);
    //print "<pre>$querySelect</pre>";
    
    $result = $db_joom->query($querySelect);
    if(!boolval($result))
    {
        $evt->setError($db_joom->errno(), $db_joom->error, $db_joom->lastqueryerror);
    }
    else
    {
        $db->query("SET FOREIGN_KEY_CHECKS = 0");
        $del = $db->query("truncate table " . MAIN_DB_PREFIX . "categorie;");
        if(!boolval($del))
        {
            $evt->setError($db->errno(), $db->error(), $db->lastqueryerror());
        }
        $tot = $db_joom->num_rows($result);
        $index=0;
        
        $evt->setNotify("sys-msg","Inizio importazione categorie");
        while($res = $db->fetch_array($result))
        {
            //print "<br><hr>" . print_r($res) . "</hr><br>";
            $query_insert = classQueryMaker::makeInsertFromArray($prefixTo, $tablenameTo, $res);
            $ins = $db->query($query_insert);
            if(!boolval($ins))
            {
                $evt->setError($db->errno(), $db->error(), $db->lastqueryerror());
            }
            
            $index++;
            $progress = $index * 100 / $tot;
            $evt->setMessage("Categorie: Inserimento record $index/$tot",$progress);
        }
        $db_joom->query("SET FOREIGN_KEY_CHECKS = 0");
        $evt->setNotify("sys.msg","Importazione eseguita");
        $evt->close($tot);
    }
}

//IMPORT RECORDS
import();