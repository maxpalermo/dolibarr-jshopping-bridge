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
$main_inc = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.inc.php";
if(file_exists($main_inc))
{
    require_once($main_inc);
}
else if(file_exists(".." . DIRECTORY_SEPARATOR . $main_inc))
{
    require_once(".." . DIRECTORY_SEPARATOR . $main_inc);
}
else
{
    die("main.inc.php non trovato");
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

/*
 * Get XML Class
 */

$mysql_driver    = "mysql";
$mysql_host      = "localhost";
$mysql_port      = "3306";
$mysql_user      = "";
$mysql_password  = "";
$mysql_database  = "";
$mysql_prefix    = "";
$mysql_languages = array();
$ftp_url         = "";
$ftp_port        = "21";
$ftp_username    = "";
$ftp_password    = "";
$ftp_home        = "";

$xmlPath = dirname(__FILE) . "/../mysqlParams.xml";
if(file_exists($xmlPath))
{
    require_once dirname(__FILE__) . "/../class/mysqlParams.class.php"; 
    $paramsXML = simplexml_load_file($xmlPath);
    $params = new stdClass();
    $mysql_driver    = $paramsXML->driver;
    $mysql_host      = $paramsXML->host;
    $mysql_port      = $paramsXML->port;
    $mysql_user      = $paramsXML->user;
    $mysql_password  = $paramsXML->password;
    $mysql_database  = $paramsXML->database;
    $mysql_prefix    = $paramsXML->prefix;
    $ftp_url         = $paramsXML->ftp_url;
    $ftp_port        = $paramsXML->ftp_port;
    $ftp_username    = $paramsXML->ftp_username;
    $ftp_password    = $paramsXML->ftp_password;
    $ftp_home        = $paramsXML->ftp_home;
    $languages       = $paramsXML->languages;
   
    if((string)$languages!="")
    {
        foreach($languages as $lang)
        {
            $mysql_languages[] = $lang->getAttribute("tag");
        }
    }
    else
    {
        $mysql_languages = array();
    }
    
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
$head = prepareHead();
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
        -webkit-box-shadow: 5px 5px 10px 0 #C9C9C9;
        box-shadow: 0 5px 10px 0 #C9C9C9;
        border: 2px solid gray;
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
    
    .btn
    {
        border-radius: 0;
        width: 200px;
        height: 64px;
        padding: 5px;
        margin-bottom: 10px;
    }
    
    .blue
    {
        background-color: #0095cc;
        color: white;
        text-shadow: 0 -1px 0 black;
        font-weight: bold;
    }
    
    .blue:hover
    {
        background-color: #22A7EE;
    }
</style>

<input type='hidden' id='xmlPath' value ='<?php print $xmlPath; ?>'>
<div id="message" style="display: none;">
    <p>MESSAGGIO</p>
</div>
<form style="display: inline-block; float: left;">
    <fieldset>
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
                <tr valign="middle">
                    <td>Linguaggi</td>
                    <td id="lang_container">
                        <?php include(dirname(__FILE__) . "/../ajax/getLanguages.php"); ?>
                    </td>    
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <br/>
                        <div style="border: 1px solid #999; padding: 5px; margin: 0 auto;">
                            <input type="button" class="button" value="TEST"  id="btnTest">
                        </div>
                        <br/>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class="table">
            <tbody>
                <tr>
                    <td colspan="2"><h2>Connessione al sito FTP</h2></td>
                </tr>
                <tr valign="middle">
                    <td>URL</td>
                    <td><input type='text' id='ftp_url' value='<?php print $ftp_url; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Porta</td>
                    <td><input type='text' id='ftp_port' value='<?php print $ftp_port; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Username</td>
                    <td><input type='text' id='ftp_username' value='<?php print $ftp_username; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Password</td>
                    <td><input type='password' id='ftp_password' value='<?php print $ftp_password; ?>'></td>    
                </tr>
                <tr valign="middle">
                    <td>Home</td>
                    <td><input type='text' id='ftp_home' value='<?php print $ftp_home; ?>'></td>    
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <br/>
                        <div style="border: 1px solid #999; padding: 5px; margin: 0 auto;">
                            <input type="button" class="button" value="TEST"  id="btnFtpTest">
                        </div>
                        <br/>
                    </td>
                </tr>
                <tr>
                    <hr>
                    <td colspan="2" style="text-align: right;">
                        <input type="button" class="button" value="SALVA" id="btnSave" >
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</form>

<div style="display: inline-block; float: right; margin-right: 20px;">
    <table>
        <tbody>
            <tr>
                <td><input type="button" class="btn blue" value="IMPORTA LINGUAGGI" onclick="getLanguages();">
            </tr>
            <tr>
                <td><input type="button" class="btn blue" value="AZZERA TABELLE" onclick="cleartable('all')">
            </tr>
        </tbody>
    </table>
    <br>
    <div style="border: 1px solid #ddddaa; padding: 10px; overflow: hidden;" id="response-msg">
        
    </div>
</div>

<br style="clear: both;">

<script type="text/javascript">
    $(document).ready(function(){
        $("#btnSave").click(function(){
            
            var langs = new Array();
            $("#language_list").find("option").each(function(){
                console.log("option=>" + $(this).val());
                langs.push($(this).val());
            });
            
            var Params = 
            {
                driver  : $("#sql_driver").val(),
                host    : $("#sql_host").val(),
                port    : $("#sql_port").val(),
                user    : $("#sql_user").val(),
                password: $("#sql_password").val(),
                database: $("#sql_database").val(),
                prefix  : $("#sql_prefix").val(),
                langs   : langs,
                ftp_url  : $("#ftp_url").val(),
                ftp_port : $("#ftp_port").val(),
                ftp_username : $("#ftp_username").val(),
                ftp_password : $("#ftp_password").val(),
                ftp_home     : $("#ftp_home").val(),
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
            var path     = $("#xmlPath").val();
            
            $.ajax(
            {
                method: "POST",
                url: "../ajax/testXML.php",
                data:   { 
                            jdriver   :driver,
                            jhost     :host,
                            jport     :port,
                            juser     :user,
                            jpassword :password,
                            jdatabase :database,
                            jprefix   :prefix,
                            jxmlPath  :path
                        }
                            
            })
                .done(function( msg )
            {
                $("#response-msg").html(msg);
                if(msg==="0")
                {
                    $("#message")
                            .removeClass()
                            .addClass("msg-error")
                            .find("p")
                            .html("CONNESSIONE NON ESEGUITA<br>" + msg)
                            .parent()
                            .fadeIn()
                            .delay(3000)
                            .fadeOut();
                }
                else
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
                
            });
        });
        
        $("#btnFtpTest").click(function(){
            var url      = $("#ftp_url").val();
            var port     = $("#ftp_port").val();
            var user     = $("#ftp_username").val();
            var password = $("#ftp_password").val();
            
            $.ajax(
            {
                method: "POST",
                url: "../ajax/testFTP.php",
                data:   { 
                            ftp_url      :url,
                            ftp_port     :port,
                            ftp_username :user,
                            ftp_password :password
                        }
                            
            })
                .done(function( msg )
            {
                $("#response-msg").html(msg);
                if(msg!=="0")
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
                else if(msg==="0")
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
    
    function getLanguages()
    {
        $.ajax(
        {
            method: "POST",
            url: "../ajax/getLanguages.php"
        })
            .done(function( msg )
        {
            console.log(msg);
            $("#lang_container").html(msg);
        });
    }
    
    function cleartable(tablename)
    {
        $.ajax(
        {
            method: "POST",
            url: "../ajax/clearTable.php",
            data:
                    {
                        tablename: tablename
                    }
        })
            .done(function( msg )
        {
            $("#result_message").html(msg);
        });
    }
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