<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/admin.php
 * 	\ingroup	simplePOS
 * 	\brief		Setting page of simplePOS module
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dolibarr environment
$res = @include "../../main.inc.php"; // From htdocs directory
if (! $res) {
	$res = @include "../../../main.inc.php"; // From "custom" directory
}

global $langs, $user, $db;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once dirname(__FILE__) . '/../lib/jShopping.lib.php';
// Translations
$langs->load("admin@sjShopping");

// Access control
if (! $user->admin) {
	accessforbidden();
}

//CREATE TABLE PRODUCT_COMPARE
$query_create = "CREATE TABLE IF NOT EXISTS `" . MAIN_DB_PREFIX . "product_compare` ( "
        . "`rowid` INT NOT NULL AUTO_INCREMENT , "
        . "`label` VARCHAR(255) NOT NULL , "
        . "`barcode` VARCHAR(255) NULL , "
        . "`barcode_type` VARCHAR(255) NULL DEFAULT 'EAN13' , "
        . "`price_ttc` FLOAT NOT NULL DEFAULT '0' , "
        . "`price_min_ttc` FLOAT NOT NULL DEFAULT '0' , "
        . "`tva_tx` FLOAT NOT NULL DEFAULT '0' , "
        . "`stock` FLOAT NOT NULL DEFAULT '0' , "
        . "PRIMARY KEY (`rowid`), INDEX `product_cmp_barcode` (`barcode`)"
        . ") ENGINE = InnoDB;";
if(1==2){$db = new DoliDBMysqli();}
$db->query($query_create);

/*
 * Get XML Class
 */

$mysql_driver   = "mysql";
$mysql_host     = "localhost";
$mysql_port     = "3306";
$mysql_user     = "";
$mysql_password = "";
$mysql_database = "";
$mysql_prefix   = "";

$xmlPath = dirname(__FILE) . "/../mysqlParams.xml";
if(file_exists($xmlPath))
{
    require_once dirname(__FILE__) . "/../class/mysqlParams.class.php"; 
    $paramsXML = simplexml_load_file($xmlPath);
    $params = new stdClass();
    $mysql_driver   = $paramsXML->driver;
    $mysql_host     = $paramsXML->host;
    $mysql_port     = $paramsXML->port;
    $mysql_user     = $paramsXML->user;
    $mysql_password = $paramsXML->password;
    $mysql_database = $paramsXML->database;
    $mysql_prefix   = $paramsXML->prefix;
    
//    print "<pre>\n";
//    print "PARAMSXML:\n";
//    print_r($paramsXML);
//    print "\n";
//    print "PARAMS_CLASS:\n";
//    print_r($params);
//    print "\n</pre>";
}


/*
 * View
 */
$page_name = "Setup JShopping";
$js     = array("jShopping/js/jquery-ui/jquery-ui.min.js");
$css    = array("jShopping/js/jquery-ui/jquery-ui.min.css","simplePOS/js/jquery-ui/jquery-ui.theme.css","simplePOS/css/style.css");
llxHeader('', $langs->trans($page_name),'','',0,0,$js,$css);

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = jShoppingAdminPrepareHead();
dol_fiche_head(
	$head,
	'settings',
	$langs->trans("jShopping"),
	0,
	"settings@jShopping"
);

?>

<style>
    #message
    {
        width:60%;
        margin: 10px auto;
        padding: 5px;
        -webkit-border-radius: 10px;
        border-radius: 10px;
        -webkit-box-shadow: undefinedpx 5px 10px 0 #C9C9C9;
        box-shadow: 0 5px 10px 0 #C9C9C9;
        border: 2px solid #gray;
    }
    
    #message p
    {
        text-align: center;
    }
    
    .msg-success
    {
        border: 4px solid #006600;
        background-color: #009900;
        text-shadow: 1px 1px 2px #336600;
        color: white;
    }
    
    .msg-error
    {
        border: 4px solid #800000;
        background-color: #990000;
        text-shadow: 1px 1px 2px #770000;
        color: white;
    }
</style>

<input type='hidden' id='xmlPath' value ='<?php print $xmlPath; ?>'>
<div id="message" style="display: none;">
    <p>MESSAGGIO</p>
</div>
<form>
    <fieldset style="display: inline-block; width: calc(50% - 40px);">
        <legend><?php print $langs->trans("PARAMETRI DI CONNESSIONE");?></legend>
        <table class="table">
            <tbody>
                <tr>
                    <td colspan="2"><h2>Connessione al database JShopping</h2></td>
                </tr>
                <tr valign="middle">
                    <td>Driver</td>
                    <td><input type='text' id='sql_driver' value='<?php print $mysql_driver; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Host</td>
                    <td><input type='text' id='sql_host' value='<?php print $mysql_host; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Porta</td>
                    <td><input type='text' id='sql_port' value='<?php print $mysql_port; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Username</td>
                    <td><input type='text' id='sql_user' value='<?php print $mysql_user; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Password</td>
                    <td><input type='password' id='sql_password' value='<?php print $mysql_password; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Database</td>
                    <td><input type='text' id='sql_database' value='<?php print $mysql_database; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Prefisso</td>
                    <td><input type='text' id='sql_prefix' value='<?php print $mysql_prefix; ?>'></td>    
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <br/>
                        <div style="border: 1px solid #999; padding: 5px; margin: 0 auto;">
                            <input type="button" class="button" value="TEST"  id="btnTest">
                            <input type="button" class="button" value="SALVA" id="btnSave" >
                        </div>
                        <br/>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <fieldset style="display: inline-block; width: calc(50% - 40px); text-align: center;">
        <legend>Imposta l'archivio</legend>
        <input type='button' class="button" value='Aggiungi immagine alle categorie' style="dsplay: block; margin: 0 auto;">
        <!--
        ALTER TABLE `llx_categorie` ADD `image` VARCHAR(255) NOT NULL DEFAULT 'noimage.png' AFTER `color`;
        ->
    </fieldset>
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $("#btnSave").click(function(){
            var Params = 
            {
                driver  : $("#sql_driver").val(),
                host    : $("#sql_host").val(),
                port    : $("#sql_port").val(),
                user    : $("#sql_user").val(),
                password: $("#sql_password").val(),
                database: $("#sql_database").val(),
                prefix  : $("#sql_prefix").val(),
                xmlPath : ''
            };
            
            console.log(Params);
            
            $.ajax(
            {
                method: "POST",
                url: "../ajax/writeXML.php",
                data:   { 
                            jshopping_params    : JSON.stringify(Params),
                            xmlPath             : $("#xmlPath").val()
                        }
                            
            })
                .done(function( msg )
            {
                console.log(msg);
                if(msg==="1")
                {
                    $("#message")
                            .removeClass()
                            .addClass("msg-success")
                            .find("p")
                            .html("SALVATAGGIO ESEGUITO")
                            .parent()
                            .fadeIn()
                            .delay(3000)
                            .fadeOut();
                    
                }
                else
                {
                    $("#message")
                            .removeClass()
                            .addClass("msg-error")
                            .find("p")
                            .html("ERRORE DURANTE IL SALVATAGGIO<br>CONTROLLA I PARAMETRI E RIPROVA")
                            .parent()
                            .fadeIn()
                            .delay(3000)
                            .fadeOut();
                }
            });
            
        });
        
        $("#btnTest").click(function(){
            var driver   = $("#sql_driver").val();
            var host     = $("#sql_host").val();
            var port     = $("#sql_port").val();
            var user     = $("#sql_user").val();
            var password = $("#sql_password").val();
            var database = $("#sql_database").val();
            var prefix   = $("#sql_prefix").val();
            
            $.ajax(
            {
                method: "POST",
                url: "../ajax/testXML.php",
                data:   { 
                            jdriver   :$("#sql_driver").val(),
                            jhost     :$("#sql_host").val(),
                            jport     :$("#sql_port").val(),
                            juser     :$("#sql_user").val(),
                            jpassword :$("#sql_password").val(),
                            jdatabase :$("#sql_database").val(),
                            jprefix   :$("#sql_prefix").val(),
                            jxmlPath  : $("#xmlPath").val()
                        }
                            
            })
                .done(function( msg )
            {
                console.log(msg);
                if(msg==="1")
                {
                    $("#message")
                            .removeClass()
                            .addClass("msg-success")
                            .find("p")
                            .html("CONNESSIONE ESEGUITA")
                            .parent()
                            .fadeIn()
                            .delay(3000)
                            .fadeOut();
                    
                }
                else
                {
                    $("#message")
                            .removeClass()
                            .addClass("msg-error")
                            .find("p")
                            .html("CONNESSIONE NON ESEGUITA<br>CONTROLLA I PARAMETRI E RIPROVA")
                            .parent()
                            .fadeIn()
                            .delay(3000)
                            .fadeOut();
                }
            });
        });
    });
</script>

<?php
// Page end
dol_fiche_end();
llxFooter();


/*
 * Fatal error: Call to a member function getPhotoUrl() on null in /home/massimiliano/www/dolibarr/dolibarr-3.8.2/htdocs/main.inc.php on line 1447
Call Stack
#	Time	Memory      Function                Location
1	0.0016	251272      {main}( )               .../admin.php:0
2	0.0660	1178760     accessforbidden( )      .../admin.php:47
3	0.0699	1228624     llxHeader( )            .../security.lib.php:541
4	0.0702	1232416     top_menu( )             .../main.inc.php:918
 */