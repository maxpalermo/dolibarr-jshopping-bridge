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
	
require dirname(__FILE__) . "/../../filefunc.inc.php";
require dirname(__FILE__) . "/../../core/db/mysqli.class.php";
require dirname(__FILE__) . "/../../conf/conf.php";

require dirname(__FILE__) . '/../../main.inc.php';
require_once dirname(__FILE__) . '/../lib/jShopping.lib.php';

$langs->load("companies");
$langs->load("other");

// Security check
$socid=0;
if ($user->societe_id > 0) {
    $socid = $user->societe_id;
}



/*
 * View
 */

//$socstatic=new Societe($db);

llxHeader("","","");

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

<fieldset>
    <legend>Disclaimer</legend>
</fieldset>

<?php

llxFooter();

$db->close();
