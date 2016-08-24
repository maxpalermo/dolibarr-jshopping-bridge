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
 *	\file		lib/mymodule.lib.php
 *	\ingroup	mymodule
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function jShoppingAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("simplePOS@simplePOS");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/jShopping/admin/admin.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
	$head[$h][0] = dol_buildpath("/jShopping/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'jShopping');

	return $head;
}

function prepareHead()
{
	global $langs, $conf;

	$langs->load("admin@jShopping");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/jShopping/pages/categories.php", 1);
	$head[$h][1] = $langs->trans("CATEGORIE");
	$head[$h][2] = 'IMPORTA';
	$h++;
        $head[$h][0] = dol_buildpath("/jShopping/pages/products.php", 1);
	$head[$h][1] = $langs->trans("PRODOTTI");
	$head[$h][2] = 'ESPORTA';
	$h++;
        $head[$h][0] = dol_buildpath("/jShopping/pages/images.php", 1);
	$head[$h][1] = $langs->trans("IMMAGINI");
	$head[$h][2] = 'CONFRONTA';
	$h++;
        $head[$h][0] = dol_buildpath("/jShopping/pages/settings.php", 1);
	$head[$h][1] = $langs->trans("IMPOSTAZIONI");
	$head[$h][2] = 'IMPOSTAZIONI';
	$h++;
	$head[$h][0] = dol_buildpath("/jShopping/pages/about.php", 1);
	$head[$h][1] = $langs->trans("ABOUT");
	$head[$h][2] = 'ABOUT';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'jShopping');

	return $head;
}


