<?php
require_once "framework.php";
require_once dirname(__FILE__) . "/../class/mysqlParams.class.php";

//print "<pre>" . print_r(mysqlParams,1) . "</pre>";
?>
<style>
    #language_list option
    {
        background-repeat: no-repeat;
        background-position: left;
        padding-left: 20px;
    }
</style>
<?php 
try 
{
    $languages = mysqlParams::getLanguages();
} 
catch (Exception $exc) 
{
    print "<option value='0'>Impossibile leggere i linguaggi</option>";
    return;
}

$size = count($languages);
?>
<select id="language_list" size ="<?php print $size ?>">
<?php
    foreach($languages as $lang)
    {
     ?>
        <option style="background-image: url('<?php print "../img/flag_" . $lang->tag . ".png" ?>')" value = '<?php print $lang->tag; ?>'><?php print $lang->label ?></option>
     <?php
    }
?>
</select>