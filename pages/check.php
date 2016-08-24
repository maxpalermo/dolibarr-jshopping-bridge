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
                        <?php include dirname(__FILE__) . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "checkQuantity.php"; ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript" src="../js/jquery-ui/jquery-ui.min.js" ></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#cmbCustomer").focus(function(){$(this).removeClass("border-error");});
        $("#cmbWarehouse").focus(function(){$(this).removeClass("border-error");});
        $("#cmbPriceLevel").focus(function(){$(this).removeClass("border-error");});
        
        $('#cmbCustomer').autocomplete({
            source      : '../ajax/getCustomers.php',
            minLength   : 1,
            select      : function(event,ui)
                        {
                           event.preventDefault();
                           console.log("Customer id: " + ui.item.id);
                           $("#cmbCustomer").attr("rowid",ui.item.id);
                           $("#cmbCustomer").val(ui.item.label);
                        }
            
        })
        .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
        .append( "<a>" + item.label + "<br>" + item.desc + "</a>" )
        .appendTo( ul );
        };
        
        $('#cmbItem').autocomplete({
            source      : '../ajax/getProduct.php',
            minLength   : 1,
            select      : function(event,ui)
                        {
                           event.preventDefault();
                           $("#cmbItem").val(ui.item.label);
                           $("#product_rowid").val(ui.item.rowid);
                           $("#product_fk_stock").val(ui.item.fk_stock);
                           $("#product_fk_batch").val(ui.item.fk_batch);
                           $("#product_batch").val(ui.item.batch);
                           $("#product_eatby").val(ui.item.eatby);
                           $("#txtProduct_price").val(ui.item.price);
                           $("#txtProduct_tva").val(ui.item.tva_tx);
                           addProduct();
                           clearProduct();
                        }
            
        })
        .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
        .append( "<a>" + 
                    "<table>\n" +
                    "\t<tbody>\n" +
                    "\t\t<tr>\n" +
                    "\t\t\t<td colspan='4'>" +
                    "<img src='../img/icon_product_item.png'  style='padding-right: 5px; padding-left: 7px;'><strong>" + item.label + "</strong></td>" +
                    "\t\t</tr>\n" +
                    "\t\t<tr>\n" +
                    "\t\t\t<td>" +
                    "<img src='../img/icon_product_batch.png' style='padding-right: 5px; padding-left: 7px;'>" + item.batch + "</td>\n" +
                    "\t\t\t<td>" +
                    "<img src='../img/icon_product_eatby.png' style='padding-right: 5px; padding-left: 7px;'>" + item.eatby + "</td>\n" +
                    "\t\t\t<td>" +
                    "<img src='../img/icon_product_stock.png' style='padding-right: 5px; padding-left: 7px;'>" + item.qty +"</td>\n" +
                    "\t\t\t<td>" +
                    "<img src='../img/icon_euro.png' style='padding-right: 5px; padding-left: 7px;'>" + item.price +"</td>\n" +
                    "\t\t</tr>\n" +
                    "\t</tbody>\n" +
                    "</table>\n" +
                     "</a>" )
        .appendTo( ul );
        };
        
        $("#btnSave").click(function()
        {
            var error=false;
            
            if($("#cmbCustomer").attr("rowid")==="0")
            {
                $("#cmbCustomer").addClass("border-error");
                error=true;
            }
            
            if($("#cmbWarehouse").attr("rowid")==="0")
            {
                $("#cmbWarehouse").addClass("border-error");
                error=true;
            }
            
            if($("#cmbPriceLevel").attr("rowid")==="0")
            {
                $("#cmbPriceLevel").addClass("border-error");
                error=true;
            }
            
            if(error) return;
            
            $.ajax(
            {
                method: "POST",
                url: "../ajax/saveSettings.php",
                data:   { 
                            fk_customer:    $("#cmbCustomer").attr("rowid"),
                            fk_warehouse:   $("#cmbWarehouse").attr("rowid"),
                            fk_pricelevel:  $("#cmbPriceLevel").attr("rowid"),
                            cash_register:  $("#txtCashRegister").val(),
                            serial_port:    $("#txtSerialPort").val(),
                            baudrate:       $("#txtBaudRate").val(),
                            parity:         $("#txtParity").val(),
                            charlength:     $("#txtCharLength").val(),
                            stopbits:       $("#txtStopBits").val(),
                            flowcontrol:    $("#txtFlowControl").val()
                        }
                            
            })
                .done(function( msg )
            {
                  alert( "Data Saved: " + msg );
            });
        });
    });
    
    function clearProduct()
    {
        $("#txtQty").val("1");
        $("#cmbItem").val("");
        $("#txtProduct_price").val("0.00");
        $("#txtProduct_tva").val("0.00");
    }
    
    function addProduct()
    {
        var row;
        var rowid=$("#product_rowid").val();
        var fk_stock=$("#product_fk_stock").val();
        var fk_batch=$("#product_fk_batch").val();
        var batch=$("#product_batch").val();
        var eatby=$("#product_eatby").val();
        var qty=$("#txtQty").val();
        var label=$("#cmbItem").val();
        var price=$("#txtProduct_price").val();
        var tva_tx=$("#txtProduct_tva").val();
        var imponibile=Number(Number(qty) * Number(price)).toFixed(2);
        var iva=Number((Number(tva_tx)/100)*imponibile).toFixed(2);
        var totale=Number(Number(iva) + Number(imponibile)).toFixed(2);
        
        row = "<tr id=\"N\" fk_stock=\"" + fk_stock + "\" fk_batch=\"" + fk_batch + "\" batch=\"" + batch + "\" eatby = \"" + eatby + "\">\n";
        row = row + "<td>\n" +
              "<input type=\"checkbox\" name=\"checkRow\" style=\"margin-top:5px;\">\n" +
              "\t<input type=\"button\" onclick=\"delRow(this);\" class=\"td-icon-delete\">\n" +
              "</td>";
        row = row + "<td>" + qty + "</td>\n";
        row = row + "<td>" + label + "</td>\n";
        row = row + "<td>" + price + "</td>\n";
        row = row + "<td>" + tva_tx + "</td>\n";
        row = row + "<td>" + imponibile + "</td>\n";
        row = row + "<td>" + iva + "</td>\n";
        row = row + "<td>" + totale + "</td>\n";
        row = row + "</tr>\n";
        
        $("#ticket-list tbody").append(row);
        if($("#ticket-list > tbody >tr:first").attr("id")==="0")
        {
            $("#ticket-list > tbody >tr:first").remove();
        }
        calculateTotal();
    }
    
    function calculateTotal()
    {
        var cellTotal=0;
        $("#ticket-list > tbody > tr").each(function(){
            var td = $(this).find("td").eq(7).text();
            cellTotal += Number(td);
        });
        $("#ticket-list > tfoot > tr > td:last").text(Number(cellTotal).toFixed(2));
    }
    
    function delRow(button)
    {
        $(button).closest("tr").remove();
        calculateTotal();
    }
    
    function newTicket()
    {
        console.log("reload");
        location.reload();
    }
    
    function printTicket()
    {
        var rows = new Array();
        $("#ticket-list > tbody > tr").each(function(){
            var tr = new Array();
            $(this).find("td").each(function(){
                tr.push($(this).text());
            });
            rows.push(tr);
        });
        
        console.log(rows);
        
        $.ajax({
            method: "POST",
            url: "../ajax/printTicket.php",
            data: { array: rows }
          })
            .done(function( msg ) {
              alert( "Data Saved: " + msg );
            });
        
    }
</script>

<?php

llxFooter();

$db->close();
