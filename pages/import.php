<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2015 Massimiliano Palermo      <maxx.palermo@gmail.com>
 * 
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/core/tools.php
 *       \brief      Home page for top menu tools
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$login=true;
	
require "../../filefunc.inc.php";
require "../../core/db/mysqli.class.php";
require "../../conf/conf.php";

require '../../main.inc.php';
require_once '../lib/jShopping.lib.php';

$langs->load("companies");
$langs->load("other");

// Security check
$socid=0;
if ($user->societe_id > 0) $socid=$user->societe_id;



/*
 * View
 */

//$socstatic=new Societe($db);

llxHeader("","Disclaimer","");

$text="Gestione Import/Export per il componente JShopping di Joomla";

print_fiche_titre($text);

// Configuration header
$head = prepareHead();
dol_fiche_head(
	$head,
	'settings',
	"Gestione Import/Export JShopping",
	0,
	"pictovalue@jShopping"
);

// QUI VA LA PARTE HTML
?>

<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.theme.css">
<link rel="stylesheet" type="text/css" href="../css/style.css">
<link rel="stylesheet" type="text/css" href="../css/ticket-table.css">

<style>
    .button-left
    {
        width:90%;
        margin-top: 3px;
        margin-bottom: 3px;
        text-align: center;
        
    }
    
    .button-left:hover
    {
        color: #c77405;
    }
    
    .div-container
    {
        -webkit-border-radius: 10px;
        border-radius: 10px;
        -webkit-box-shadow: 10px 10px 10px 0 #C9C9C9;
        box-shadow: 10px 10px 10px 0 #C9C9C9;
        border: 1px solid #aaa;
        text-shadow: 1px 1px 2px #B0B0B0;
        margin-bottom: 20px;
        padding: 5px;
    }
</style>
<h2>IMPORTA</h2>
<div>
    <table>
        <tbody>
            <tr>
                <td>
                    <h3>Categorie</h3>
                    <div class="div-container">
                        <input type="button" class="button" value="VISUALIZZA" id="btn-cat-show">
                        <div id="div-categories"></div>
                        
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>Prodotti</h3>
                    <div class="div-container">
                        
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>Magazzino</h3>
                    <div class="div-container">
                        
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript" src="../js/jquery-ui/jquery-ui.min.js" ></script>
<script type="text/javascript">
    $(document).ready(function()
    {
        $("#btn-cat-show").on("click",function(){
            $.ajax({
                url: "../ajax/getJshopCategories.php",
                method: "POST",
                success: function(msg)
                {
                    $("#div-categories").html(msg);
                }
            });
        });
    }
</script>

<?php

llxFooter();

$db->close();
