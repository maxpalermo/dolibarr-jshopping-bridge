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
 * 	\file		admin/about.php
 * 	\ingroup	mymodule
 * 	\brief		This file is an example about page
 * 				Put some comments here
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dolibarr environment
$res = @include "../../main.inc.php"; // From htdocs directory
if (! $res) {
	$res = @include "../../../main.inc.php"; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/simplePOS.lib.php';

//require_once "../class/myclass.class.php";
// Translations
$langs->load("simplePOS@simplePOS");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

require 'includes/config.php';
require 'includes/aboutPage.class.php';
require 'includes/vcard.class.php';

$profile = new AboutPage($info);

if(array_key_exists('json',$_GET)){
	$profile->generateJSON();
	exit;
}
else if(array_key_exists('vcard',$_GET)){
	$profile->downloadVcard();
	exit;
}

/*
 * Actions
 */

/*
 * View
 */
$page_name = "About simplePOS";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = prepareHead();
dol_fiche_head(
	$head,
	'about',
	$langs->trans("PAGE_ABOUT"),
	0,
	'about@simplePOS'
);

// About page goes here
?>
<link rel="stylesheet" href="assets/css/styles.css" />
<div class="html">
    <div class="body">
        <section id="infoPage">

        <img src="<?php echo $profile->photoURL()?>" alt="<?php echo $profile->fullName()?>" width="164" height="164" />

        <header>
            <h1><?php echo $profile->fullName()?></h1>
            <h3>simplePOS, gestione semplificata di un punto vendita con possibilità di stampa degli scontrini.</h3>
            <h2><?php echo $profile->tags()?></h2>
        </header>

        <p class="description"><?php echo nl2br($profile->description())?></p>

        <a href="<?php echo $profile->facebook()?>" class="grayButton facebook">Trovami su facebook</a>
        <a href="<?php echo $profile->twitter()?>" class="grayButton twitter">Seguimi su twitter</a>
        <a href="mailto:maxx.palermo@gmail.com" class="grayButton email">Email</a>

        <ul class="vcard">
            <li class="fn"><?php echo $profile->fullName()?></li>
            <li class="org"><?php echo $profile->company()?></li>
            <li class="tel"><?php echo $profile->cellphone()?></li>
            <li><a class="url" href="<?php echo $profile->website()?>"><?php echo $profile->website()?></a></li>
        </ul>

        </section>

        <section id="links">
                <a href="?vcard" class="vcard">Download as V-Card</a>
            <a href="?json" class="json">Get as a JSON feed</a>
        </section>
    </div>
</div>

<?php
// Page end
dol_fiche_end();
llxFooter();
