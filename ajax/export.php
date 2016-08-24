<?php
require_once dirname(__FILE__) . "/framework.php";
 $type = GETPOST("exp_type");
 
 switch ($type) {
    case "categorie":
        include dirname(__FILE__) . "/exp_categories.php";
        break;
    case "prodotti":
        include dirname(__FILE__) . "/exp_products.php";
        break;
    default:
        break;
}