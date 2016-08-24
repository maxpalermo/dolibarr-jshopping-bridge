<?php
require_once "framework.php";
require_once dirname(__FILE__) . "/../class/mysqlParams.class.php";
$db = mysqlParams::getConnection();

$tablename = GETPOST("tablename");

if($tablename=="all")
{
    $query = "SELECT TABLE_NAME "
            ."from information_schema.tables "
            ."WHERE table_schema = '" . mysqlParams::getDatabase() . "' "
            ."and TABLE_NAME like '%jshopping%' "
            ."order by TABLE_NAME;";
}
else
{
    $query = "delete from " . mysqlParams::getPrefix() . $tablename;
}

$result = $db->query($query);
if(!$result)
{
    print "<pre>";
    print "Errore " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    print "Query: " . $db->lastquery() . "</pre>";
    return;
}

$exclude  = array(
    "addons",
    "config",
    "config_seo",
    "config_statictext",
    "countries",
    "currencies",
    "import_export",
    "languages",
    "menu_config",
    "payment_method",
    "product_labels",
    "unit");

while($rec = $db->fetch_object($result))
{
    $tbName = $rec->TABLE_NAME;
    $excluded = false;
    foreach ($exclude as $table)
    {
        $pos = strpos($tbName, $table);
        //print "search: $table in $tbName = $pos \n";
        if($pos) {$excluded = true; break;}
    }
    if(!$excluded)
    {
        $clear = "delete from $tbName;";
        $res = $db->query($clear);
        if($res)
        {
            print "Tabella $tbName azzerata \n";
        }
        else 
        {
            print "errore " . $db->lasterrno() . ": " . $db->lasterror() . "; durante l'azzeramento di $tbName \n";
        }
    }
    else
    {
        print "Tabella $tbName esclusa \n";
    }
    
}
