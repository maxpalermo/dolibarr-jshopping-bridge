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
//require_once dirname(__FILE__) .  DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "class"  . DIRECTORY_SEPARATOR . "progressBar.class.php";

//progressBar::getInstance();

$langs->load("companies");
$langs->load("other");

// Security check
$socid=0;
if ($user->societe_id > 0){ $socid=$user->societe_id;}


/*
 * View
 */

//$socstatic=new Societe($db);

llxHeader("","Esportazione","");

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
        -webkit-border-radius: 0;
        border-radius: 0;
        -webkit-box-shadow: 10px 10px 10px 0 #C9C9C9;
        box-shadow: 5px 5px 10px 0 #ccccaa;
        border: 1px solid #ddddaa;
        text-shadow: 1px 1px 2px #B0B0B0;
        margin-bottom: 20px;
        padding: 20px;
        overflow: hidden;
    }
    
  #progressbar {
    margin-top: 20px;
  }
 
  .progress-label {
    font-weight: bold;
    text-shadow: 1px 1px 0 #fff;
  }
 
  .ui-dialog-titlebar-close {
    display: none;
  }
  .tabBar
  {
      overflow: hidden;
  }
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
<div id="dialog" title="Esporta">
  <div id="progress-counter"></div>
  <div class="progress-label">Inizio esportazione...</div>
  <div id="img-log"></div>
  <div id="progressbar"></div>
</div>


<h2>ESPORTA</h2>

<div style='float: left; overflow: hidden; width: 350px; margin-right: 10px;'>
    <div class="div-container" style="width: 300px;">
        <h3>ESPORTAZIONE TOTALE</h3>
        <h4>Questa operazione cancellerà tutti i dati</h4>
        <h4>presenti sull'e-commerce</h4>
        <hr>
        <select id="export-chooser" style="margin-right: 10px;">
            <option value="0">Seleziona la tabella da esportare</option>
            <option value="1">Categorie</option>
            <option value="2">Prodotti</option>
        </select>

        <button id="downloadButton">Esporta</button>
        <br>
    </div>

    <div class="div-container" style="width: 300px;">
        <h3>ESPORTAZIONE DIFFERENZIALE</h3>
        <h4>Questa operazione esporterà solo</h4>
        <h4>gli elementi non presenti sull'e-commerce</h4>
        <hr>
        <select id="show-chooser" style="margin-right: 10px;">
            <option value="0">Seleziona la tabella da esportare</option>
            <option value="1">Categorie</option>
            <option value="2">Prodotti</option>
        </select>

        <button id="showButton">Mostra</button>
        <br>
    </div>
    
    <br style='clear: both;'>
</div>

<div style='float: right; margin-left: 10px; padding: 20px; overflow: hidden; border: 1px solid #ddddaa; 'id='div-show'>
    <div style="display: none;" id="div-cat">
        <h2>CATEGORIE NON PRESENTI IN JSHOPPING</h2>
        <table class='table-list'>
            <thead>
                <tr>
                    <th style="text-align: center;">
                        <input type='checkbox' id='ckeckall' value="1" checked="checked" />
                    </th>
                    <th>ID</th>
                    <th>LABEL</th>
                </tr>
            </thead>
            <tbody id="tbody-cat">
                <!--FILLED WITH AJAX -->
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; padding-top: 10px; padding-bottom: 10px; padding-right: 10px;">
                        <input type="button" class="btn btn-primary" value="ESPORTA" onclick="export_diff(1);">
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div style="display: none;" id="div-prod">
        <h2>PRODOTTI NON PRESENTI IN JSHOPPING</h2>
        <table class='table-list'>
            <thead>
                <tr>
                    <th style="text-align: center;">
                        <input type='checkbox' id='ckeckall' value="1" checked="checked" />
                    </th>
                    <th>ID</th>
                    <th>LABEL</th>
                </tr>
            </thead>
            <tbody id="tbody-prod">
                <!--FILLED WITH AJAX -->
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; padding-top: 10px; padding-bottom: 10px; padding-right: 10px;">
                        <input type="button" class="btn btn-primary" value="ESPORTA" onclick="export_diff(2);">
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
     '
<div style="display: none;">
    <div>
        <div class="div-container" id="categories"><!--AJAX FILL -->
            <?php include dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "ajax" . DIRECTORY_SEPARATOR . "getCategories.php"; ?>
        </div>
    </div>
    <div>
        <div class="div-container" id="products">
            <?php include dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "ajax" . DIRECTORY_SEPARATOR . "getProducts.php"; ?>
        </div>   
    </div>
    <div class="div-container">
        <h2>Messaggi di sistema</h2>
        <textarea cols="128" rows="20" id="message-sql">
            
        </textarea>
    </div>
</div>

<script type="text/javascript" src="../js/jquery-ui/jquery-ui.min.js" ></script>
<script type="text/javascript">
    var dialog;
    var downloadButton;
    var showButton;
    var progressbar;
    var progressLabel;
    var dialogButtons;
    var es;
    
    function export_diff($id)
    {
        $phpFile = "";
        $diff = $("#hidden-diff-cat").val();

        if($id===1)//categories
        {
            $phpFile = "../ajax/exp_categories.php";
        }
        else if($id===2)//products
        {
            $phpFile = "../ajax/exp_products.php";
        }
        else
        {
            return;
        }
        
        $.ajax({
            url: "../ajax/setDiff.php",
            method: "POST",
            data:{ "diff" : $diff}
        }).success(function(msg){
            console.log(msg);
            dialog.dialog("open");
            export_tables($phpFile);
        });
    }
    
    function export_tables($phpFile) 
    {
        console.log($phpFile);
        es = new EventSource($phpFile);

        //a message is received
        es.onmessage = function(e) 
        {   
            if(e.lastEventId==="LOOP")
            {
                var data = JSON.parse(e.data);
                console.log(data.progress);
                progressbar.progressbar("value",data.progress);
                $("#progress-counter").html(data.message);
            }
            else if(e.lastEventId==="IMG")
            {
                var data = JSON.parse(e.data);
                console.log(data.progress);
                $("#img-log").html(data.message);
            }
            else if(e.lastEventId==="CLOSE")
            {
                console.log("OPERAZIONE TERMINATA");
                es.close();
                $.ajax({
                    url : "../ajax/setTerminated.php",
                    method: "POST"
                });
            }
        };
    }
    
    $(document).ready(function()
    {
        progressbar = $("#progressbar");
        progressLabel = $(".progress-label");
        dialogButtons = [{
                                text: "ANNULLA",
                                click: closeDownload
        }];
    
        dialog = $( "#dialog" ).dialog({
            autoOpen: false,
            closeOnEscape: false,
            resizable: true,
            buttons: dialogButtons,
            open: function() {
                
            },
            beforeClose: function() {
                downloadButton.button( "option", 
                {
                    disabled: false,
                    label: "Esporta"
                });
            }
        });
        
        showButton = $("#showButton")
                .button()
                .on("click",function()
                {
                    var $option = $("#show-chooser").val();
                    if($option==="1")
                    {
                        $php = "../ajax/getDiffCat.php";
                    }
                    else if($option==="2")
                    {
                        $php = "../ajax/getDiffProd.php";
                    }
                    else
                    {
                        return;
                    }
                    $.ajax({
                        url: $php,
                        method: "POST"
                    })
                    .success(function(msg){
                        if($option==="1")
                        {
                            $("#tbody-cat").html(msg);
                            $("#div-cat").show();
                            $("#div-prod").hide();
                        }
                        else if($option==="2")
                        {
                            $("#tbody-prod").html(msg);
                            $("#div-cat").hide();
                            $("#div-prod").show();
                        }
                    });
                });
        
        downloadButton = $("#downloadButton")
            .button()
            .on( "click", function() 
            {
                var $option = $("#export-chooser").val();
                
                if($option==="1")
                {
                    $("#dialog").attr("title","ESPORTAZIONE CATEGORIE");
                    $phpFile = '../ajax/exp_categories.php';
                }
                else if($option==="2")
                {
                    $("#dialog").attr("title","ESPORTAZIONE PRODOTTI");
                    $phpFile = '../ajax/exp_products.php';
                }
                else
                {
                    $phpFile = "";
                }
                
                if($phpFile!=="")
                {
                    $(this).button( "option", 
                    {
                        disabled: true,
                        label: "Esportazione..."
                    });
                    dialog.dialog( "open" );
                    export_tables($phpFile);
                }
            });

        progressbar.progressbar({
            value: false,
            change: function() 
            {
                progressLabel.text( "Progresso: " + progressbar.progressbar( "value" ) + "%" );
            },
            complete: function() 
            {
                progressLabel.text( "Esportazione effettuata." );
                dialog.dialog( "option", "buttons", 
                [{
                    text: "Chiudi.",
                    click: closeDownload
                }]);
                $(".ui-dialog button").last().focus();
            }
        });

        function closeDownload() 
        {
            $.ajax({
                url : "../ajax/setTerminated.php",
                method: "POST"
            });

            es.close();
            dialog
                .dialog( "option", "buttons", dialogButtons )
                .dialog( "close" );
            progressbar.progressbar( "value", false );
            progressLabel.text( "Inizio esportazione..." );
            downloadButton.focus();
        }
        
        
    });
  </script>

<?php

llxFooter();

$db->close();