<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../../filefunc.inc.php";
require "../../core/db/mysqli.class.php";
require "../../conf/conf.php";
require '../../main.inc.php';

if(!defined("DS")){define("DS",DIRECTORY_SEPARATOR);}