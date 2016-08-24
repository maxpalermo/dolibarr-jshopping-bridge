<?php
require_once "framework.php";

//Get records from database
$query="select c1.*, c2.label as parent from " . MAIN_DB_PREFIX . "categorie c1, " . MAIN_DB_PREFIX . "categorie c2 where c2.rowid=c1.fk_parent order by parent,label";
//print $query;
//return;
$result = $db->query($query);
if(!$result)
{
    print "<pre>";
    print "Errore " . $db->lasterrno() . ": " . $db->lasterror() . "\n";
    print "Query: " . $db->lastquery() . "</pre>";
    return;
}
?>

<style>
    .table-list
    {
        border: 1px solid #595959;
        border-collapse: collapse;
        margin: 10px;
    }
    
    .table-list thead tr th
    {
        text-align: center;
        border: 1px solid #595959;
        background-color: #a9a9a9;
        color: white;
        font-size: 1.2em;
        font-weight: bold;
        text-shadow: 0 -1px 1px #black;
    }
    
    .table-list tbody tr td
    {
        text-align: left;
        border: 1px solid #a9a9a9;
        color: #595959;
        font-size: 1.0em;
        font-weight: lighter;
        text-shadow: 1px 1px 2px #a9a9a9;
        padding: 5px;
    }
    
    .table-list tbody tr:nth-child(even)
    {
        background-color: white;
    }
    
    .table-list tbody tr:nth-child(odd)
    {
        background-color: #f0f0f0;
    }
    
    .table-list tbody tr:hover
    {
        cursor: pointer;
        background-color: #f9f0f0;
    }    
</style>

<input type="button" class="button btn-primary" value="ESPORTA" onclick="export_to_joomla('categorie')" style="display: inline-block;">
<br>
<h2>CATEGORIE</h2>
<br>
<div style="overflow-y: scroll; overflow-x: auto; max-height: 400px;">
    <table class="table-list">
        <thead>
            <tr>
                <th>codice</th>
                <th>riferimento</th>
                <th>categoria</th>
            </tr>
        </thead>
        <tbody>
            <?php

            while($row = $db->fetch_object($result))
            {
            ?>

            <tr>
                <td><?php print $row->rowid; ?></td>
                <td><?php print $row->parent;?></td>
                <td><?php print $row->label; ?></td>
            </tr>

            <?php
            };

            ?>
        </tbody>
    </table>
</div>
<br>
