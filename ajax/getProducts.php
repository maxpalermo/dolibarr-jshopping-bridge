<?php
require_once "framework.php";

//Get records from database
$query="select p.*, c.rowid as catid, c.label as categorie, cp.* from " 
        . MAIN_DB_PREFIX . "product p, " 
        . MAIN_DB_PREFIX . "categorie c, "
        . MAIN_DB_PREFIX . "categorie_product cp" 
        . " where cp.fk_categorie=c.rowid and cp.fk_product = p.rowid"
        . " order by p.label";
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
<input type="button" class="button btn-primary" value="ESPORTA" onclick="export_to_joomla('prodotti')" style="display: inline-block;">
<br>
<h2>PRODOTTI</h2>
<br>
<div style="overflow-y: scroll; overflow-x: auto; max-height: 400px;">
    <table class="table-list">
        <thead>
            <tr>
                <th>codice</th>
                <th>rif.</th>
                <th>id cat.</th>
                <th>categoria</th>
                <th>label</th>
                <th>descrizione</th>
                <th>prezzo</th>
                <th>iva</th>
                <th>barcode</th>
                <th>qta</th>
            </tr>
        </thead>
        <tbody>
            <?php

            while($row = $db->fetch_object($result))
            {
            ?>

            <tr>
                <td><?php print $row->rowid; ?></td>
                <td><?php print $row->ref;?></td>
                <td><?php print $row->catid;?></td>
                <td><?php print $row->categorie; ?></td>
                <td><?php print $row->label; ?></td>
                <td><textarea rows="2" cols="30"><?php print strip_tags($row->description); ?></textarea></td>
                <td style="text-align: right;"><?php print $row->price; ?></td>
                <td style="text-align: right;"><?php print $row->tva_tx; ?></td>
                <td><?php print $row->barcode; ?></td>
                <td style="text-align: right;"><?php print $row->stock; ?></td>
            </tr>

            <?php
            };

            ?>
        </tbody>
    </table>
</div>
