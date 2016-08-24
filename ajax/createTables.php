<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$query="CREATE TABLE IF NOT EXISTS `".MAIN_DB_PREFIX."simplepos_settings` ( `rowid` INT NOT NULL AUTOI_NCREMENT , `fk_customer` INT NOT NULL , `fk_warehouse` INT NOT NULL , `fk_pricelevel` INT NOT NULL , `cash_register` VARCHAR(255) NULL , `serial_port` VARCHAR(255) NULL , `baudrate` INT NULL , `parity` VARCHAR(1) NULL , `charlength` INT NULL , `stopbits` INT NULL , `flowcontrol` VARCHAR(255) NULL , PRIMARY KEY (`rowid`)) ENGINE = InnoDB COMMENT = 'simplePOS module settings';";
$db->query($query);

$query="CREATE TABLE IF NOT EXISTS `".MAIN_DB_PREFIX."ticket` ( `rowid` INT NOT NULL AUTO_INCREMENT, `date` DATE NOT NULL , `number` INT NOT NULL , `fk_product` INT NOT NULL , `fk_stock` INT NOT NULL , `fk_batch` INT NOT NULL , `batch` VARCHAR(100) NOT NULL , `eatby` DATE NOT NULL , `price` FLOAT NOT NULL , `tva_tx` FLOAT NOT NULL , PRIMARY KEY (`rowid`)) ENGINE = InnoDB;";
$db->query($query);