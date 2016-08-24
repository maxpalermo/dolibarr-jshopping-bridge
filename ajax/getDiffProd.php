<?php
require_once "framework.php";
require_once ".." . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "mysqlParams.class.php";

if(1==2){$db = new DoliDBMysqli("mysql", "localhost", "user", "pass");} 
$dbOut = mysqlParams::getJoomlaConnection();

//GET DOLIBARR PRODUCT ID
$sqlCatIdDolibarr = "select rowid from " . MAIN_DB_PREFIX . "product order by rowid";
$result_d = $db->query($sqlCatIdDolibarr);
if($result_d)
{
    $cat_d = [];
    while($arrCatId_d = $db->fetch_array($result_d))
    {
        $cat_d[] = $arrCatId_d[0];
    }
}
else
{
    ?>
        <div>
            <h3>ERRORE</h3>
            <p><i>Errore durante la lettura dei prodotti di <strong>Dolibarr</strong></i></p>
        </div>
    <?php
    return;
}

//GET JSHOPPING CATEGORIES ID
$sqlCatIdJShopping = "select product_id from " . mysqlParams::getPrefix() . "jshopping_products order by product_id";
$result_j = $dbOut->query($sqlCatIdJShopping);
if($result_j)
{
    $cat_j = [];
    while($arrCatId_j = $dbOut->fetch_array($result_j))
    {
        $cat_j[] = $arrCatId_j[0]; 
    }
}
else
{
    ?>
        <div>
            <h3>ERRORE</h3>
            <p><i>Errore durante la lettura dei prodotti di <strong>JShopping</strong></i></p>
        </div>
    <?php
    return;
}

$diff = array_diff($cat_d, $cat_j);

if(empty($diff))
{
    ?>
        <tr>
            <td colspan="3"> 
                <div>
                    <h3>ATTENZIONE</h3>
                    <p><i>Nessuna prodotto nuovo da esportare</i></p>
                </div>
            </td>
        </tr>
    <?php
    return;
}
else
{
    $sqlCategories = "select * from " . MAIN_DB_PREFIX . "product where rowid in (" . implode(",",$diff) . ") order by rowid;";
    $resDiff = $db->query($sqlCategories);
    if($resDiff)
    {
        ?>
    <tr style="display: none;">
        <td colspan="3">
            <input type="hidden" id="hidden-diff-cat" value="<?php print implode(",",$diff); ?>">
        </td>
    </tr>
        <?php
        while($rs = $db->fetch_object($resDiff))
        {
        ?>
        <tr>
            <td><input type='checkbox' value="1" checked="checked"></td>
            <td><?php print $rs->rowid;?></td>
            <td><?php print $rs->label;?></td>
        </tr>
        <?php
        }
        ?>
        <?php
    }
    else
    {
        ?>
        <tr>
            <td colspan="3">
                <div>
                    <h3>ERRORE</h3>
                    <p><i>Errore durante la lettura delle categorie di <strong>DOLIBARR</strong></i></p>
                    <p><?php print $db->lasterrno() . ": " . $db->lasterror(); ?></p>
                    <p><?php print $db->lastquery; ?></p>
                </div>
            </td>
        </tr>
        <?php
        return;
    }
            
}