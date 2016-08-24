<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mysqlParams
 *
 * @author Massimiliano Palermo <maxx.palermo@gmail.com>
 */
class mysqlParams {
    private static $driver;
    private static $host;
    private static $port;
    private static $user;
    private static $password;
    private static $database;
    private static $prefix;
    private static $languages;
    private static $xmlPath;
    private static $db;
    private static $token;
    private static $ftp_url;
    private static $ftp_port;
    private static $ftp_username;
    private static $ftp_password;
    private static $ftp_home;
    private static $ftp_connection;
    
    static function getXMLParams($xmlPath)
    {
        
    }
    
    static function setXMLParams($xmlPath,$xmlParams)
    {
        $xml            = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><params></params>');
        $xml->addAttribute("version","1.0");
        $xml->addChild("datetime",date('Y-m-d H:i:s'));
        
        $xml_driver     = $xml->addChild("driver",$xmlParams->driver);
        $xml_host       = $xml->addChild("host",$xmlParams->host);
        $xml_port       = $xml->addChild("port",$xmlParams->port);
        $xml_user       = $xml->addChild("user",$xmlParams->user);
        $xml_password   = $xml->addChild("password",$xmlParams->password);
        $xml_database   = $xml->addChild("database",$xmlParams->database);
        $xml_prefix     = $xml->addChild("prefix",$xmlParams->prefix);
        $xml_languages  = $xml->addChild("languages");
        $xml_ftp_url      = $xml->addChild("ftp_url",$xmlParams->ftp_url);
        $xml_ftp_port     = $xml->addChild("ftp_port",$xmlParams->ftp_port);
        $xml_ftp_username = $xml->addChild("ftp_username",$xmlParams->ftp_username);
        $xml_ftp_password = $xml->addChild("ftp_password",$xmlParams->ftp_password);
        $xml_ftp_home     = $xml->addChild("ftp_home",$xmlParams->ftp_home);
        if(count($xmlParams->langs))
        {
            foreach($xmlParams->langs as $lang)
            {
                $xml_languages->addChild("language",$lang);
            }
        }
        
        self::$driver = $xmlParams->driver;
        self::$host = $xmlParams->host;
        self::$port = $xmlParams->port;
        self::$user = $xmlParams->user;
        self::$password = $xmlParams->password;
        self::$database = $xmlParams->database;
        self::$prefix = $xmlParams->prefix;
        self::$languages = $xmlParams->langs;
        self::$token = true;
        self::$ftp_url      = $xmlParams->ftp_url;
        self::$ftp_port     = $xmlParams->ftp_port;
        self::$ftp_username = $xmlParams->ftp_username;
        self::$ftp_password = $xmlParams->ftp_password;
        self::$ftp_home     = $xmlParams->ftp_home;
        
       
        $output = $xml->asXML($xmlPath);
        return $output;
    }
    
    /**
     * Reads a XML file with connection parameters and create a connection.
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @param String $xmlPath Path to XML file
     * @return Object Returns a dolibarr connection or NULL
     **/
    static function readXML($xmlPath)
    {
        $mysql_driver    = "mysql";
        $mysql_host      = "localhost";
        $mysql_port      = "3306";
        $mysql_user      = "";
        $mysql_password  = "";
        $mysql_database  = "";
        $mysql_prefix    = "";
        $ftp_url         = "";
        $ftp_port        = "21";
        $ftp_username    = "";
        $ftp_password    = "";
        $ftp_home        = "";
        $mysql_languages = array();

        if(file_exists($xmlPath))
        {

            $paramsXML = simplexml_load_file($xmlPath);

            $mysql_driver   = (string)$paramsXML->driver;
            $mysql_host     = (string)$paramsXML->host;
            $mysql_port     = (string)$paramsXML->port;
            $mysql_user     = (string)$paramsXML->user;
            $mysql_password = (string)$paramsXML->password;
            $mysql_database = (string)$paramsXML->database;
            $mysql_prefix   = (string)$paramsXML->prefix;
            $languages      = (string)$paramsXML->languages;
            if($languages!="")
            {
                foreach ($languages as $lang)
                {
                    $mysql_languages[] = (string)$lang;
                }
            }
            else
            {
                $languages = array();
            }
            $ftp_url      = (string)$paramsXML->ftp_url;
            $ftp_port     = (string)$paramsXML->ftp_port;
            $ftp_username = (string)$paramsXML->ftp_username;
            $ftp_password = (string)$paramsXML->ftp_password;
            $ftp_home     = (string)$paramsXML->ftp_home;
            
        }

        self::setDriver($mysql_driver);
        self::setHost($mysql_host);
        self::setPort($mysql_port);
        self::setUser($mysql_user);
        self::setPassword($mysql_password);
        self::setDatabase($mysql_database);
        self::setPrefix($mysql_prefix);
        self::setFtpURL($ftp_url);
        self::setFtpPort($ftp_port);
        self::setFtpUsername($ftp_username);
        self::setFtpPassword($ftp_password);
        self::setFtpHome($ftp_home);
        self::setToken(true);
        
        return self::getConnection();
    }
    
    static function setClass($params)
    {
        self::$driver    = $params->driver;
        self::$host      = $params->host;
        self::$port      = $params->port;
        self::$user      = $params->user;
        self::$password  = $params->password;
        self::$database  = $params->database;
        self::$prefix    = $params->prefix;
        self::$xmlPath   = $params->xmlPath;
        self::$languages = $params->languages; 
        self::$ftp_url        = $params->ftp_url;
        self::$ftp_port       = $params->ftp_port;
        self::$ftp_username   = $params->ftp_username;
        self::$ftp_password   = $params->ftp_password;
        self::$ftp_home       = $params->ftp_home;
    }
    
    /**
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @return Object Returns a Dolibarr Connection
     */
    static function getFTPConnection()
    {
        if(empty(self::$token)) {self::readXML(dirname(__FILE__) . "/../mysqlParams.xml");}
        $conn_id = ftp_connect(self::$ftp_url);
        if(!$conn_id)
        {
            self::send_message("IMG", "ERRORE DURANTE LA CONNESSIONE FTP <br> <pre>" 
                        . print_r(error_get_last(),1) 
                        . "</pre>", 100);
            sleep(3);
        }

        $login   = ftp_login($conn_id, self::$ftp_username, self::$ftp_password);
        if($login)
        {
            $dir = ftp_pwd($conn_id) . "/" . self::$ftp_home;
            if(!ftp_chdir($conn_id, $dir))
            {
                self::send_message("IMG", "ERRORE DURANTE IL CAMBIO DIRECTORY <br> <pre>" 
                        . print_r(error_get_last(),1) 
                        . "\nCurrent DIR: " . ftp_pwd($conn_id)
                        . "\nChange  DIR: " . $dir
                        . "</pre>", 100);
                sleep(3);
            }
            self::$ftp_connection = $conn_id;
            return self::$ftp_connection;
        }
        else
        {
            self::send_message("IMG", "ERRORE DURANTE IL LOGIN <br> <pre>" 
                        . print_r(error_get_last(),1) 
                        . "</pre>", 100);
                sleep(3); 
        }
        self::$ftp_connection = null;
        return null;
    }
    
    static function closeFTPConnection()
    {
        if(ftp_close(self::$ftp_connection))
        {
            return true;
        }
        else
        {
            return print_r(error_get_last(),1);
        }
    }
    
    /**
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @return DoliDBMysqli Returns a Dolibarr Connection
     */
    static function getConnection()
    {
        if(empty(self::$token)) {self::readXML(dirname(__FILE__) . "/../mysqlParams.xml");}
        $db=new DoliDBMysqli(self::$driver, self::$host, self::$user, self::$password, self::$database, self::$port);
        $db->connect(self::$host, self::$user, self::$password, self::$database, self::$port);

        if ($db->connected)
        {
            self::$db = $db;
        }
        else
        {
            print "<p>ERRORE DURANTE LA CONNESSIONE<p>";
            self::$db = null;
        }
        return $db;
    }
    
    /**
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @return DoliDBMysqli Returns a Dolibarr Connection
     */
    static function getJoomlaConnection()
    {
        if(empty(self::$token)) {self::readXML(dirname(__FILE__) . "/../mysqlParams.xml");}
        $db=new DoliDBMysqli(self::$driver, self::$host, self::$user, self::$password, self::$database, self::$port);
        $db->connect(self::$host, self::$user, self::$password, self::$database, self::$port);

        if ($db->connected)
        {
            self::$db = $db;
        }
        else
        {
            self::$db = null;
        }
        return $db;
    }

    /**
     * Get alll available languages in Joomla Site
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @since 1.0
     * @version 1.0
     * 
     * @return \stdClass Classe language
     *                                  ->tag
     *                                  ->label
     *                                  ->code
     */
    static function getLanguages()
    {
        $langs = array();
        $db = self::getConnection();
        $query = "select lang_code,title,sef from " . self::$prefix . "languages order by lang_code";
        try 
        {
            $result = $db->query($query);
            while($row = $db->fetch_object($result))
            {
                $lang = new stdClass();
                $lang->tag   = $row->lang_code;
                $lang->label = $row->title;
                $lang->code  = $row->sef;
                $langs[]     = $lang;
            }
        } 
        catch (Exception $exc) 
        {
            $lang = new stdClass();
            $lang->tag   = "it-IT";
            $lang->label = "Italiano";
            $lang->code  = "it-IT";
            $langs[]     = $lang;
            return $query . "\n" . $exc->getCode() . ": " . $exc->getMessage();
        }
        
        self::$languages = $langs;
        return $langs;
    }
    
    static function getDriver() {
        return self::$driver;
    }

    static function getHost() {
        return self::$host;
    }

    static function getPort() {
        return self::$port;
    }

    static function getUser() {
        return self::$user;
    }

    static function getPassword() {
        return self::$password;
    }

    static function getDatabase() {
        return self::$database;
    }

    static function getPrefix() {
        return self::$prefix;
    }

    static function getXmlPath() {
        return self::$xmlPath;
    }
    
    static function getFtpURL() {
        return self::$ftp_url;
    }
    
    static function getFtpPort() {
        return self::$ftp_port;
    }
    
    static function getFtpUsername() {
        return self::$ftp_username;
    }
    
    static function getFtpPassword() {
        return self::$ftp_password;
    }
    
    static function getFtpHome() {
        return self::$ftp_home;
    }

    static function setDriver($driver) {
        self::$driver = $driver;
    }

    static function setHost($host) {
        self::$host = $host;
    }

    static function setPort($port) {
        self::$port = $port;
    }

    static function setUser($user) {
        self::$user = $user;
    }

    static function setPassword($password) {
        self::$password = $password;
    }

    static function setDatabase($database) {
        self::$database = $database;
    }

    static function setPrefix($prefix) {
        self::$prefix = $prefix;
    }
    
    static function setToken($bool) {
        self::$token = $bool;
    }

    static function setXmlPath($xmlPath) {
        self::$xmlPath = $xmlPath;
    }
    
    static function setFtpURL($url) {
        self::$ftp_url = $url;
    }
    
    static function setFtpPort($port) {
        self::$ftp_port = $port;
    }
    
    static function setFtpUsername($username) {
        self::$ftp_username = $username;
    }
    
    static function setFtpPassword($password) {
        self::$ftp_password = $password;
    }
    
    static function setFtpHome($home) {
        self::$ftp_home = $home;
    }
    
    static function apc($string)
    {
        return "`" . $string . "`";
    }

    static function str($string)
    {
        return "'" . self::$db->escape($string) . "'";
    }
    
    static function apcFields($array)
    {
        $i=0;
        foreach($array as $elem)
        {
            $array[$i] = self::apc($elem);
            $i++;
        }
        
        return $array;
    }
    
    static function tms()
    {
        return date("Y-m-d h:i:s");
    }
    
    static function getImagePath($path, $ref, $id)
    {
        //Copy image to media local folder
        $image = mysqlParams::copyPic($path, $ref);
        if(empty($image)){return "'product_512x768.gif'";}
        
        $sqlImg = "insert into " . self::$prefix . "jshopping_products_images ("
                ."`product_id`,"
                ."`image_name`,"
                ."`name`,"
                ."`ordering`) values ("
                .$id . ","
                . self::str($image) . ","
                . self::str("") . ","
                . "1);";
        $result = self::$db->query($sqlImg);
        if(!$result)
        {
            self::send_message("IMG", self::$db->lasterrno() . ":" . self::$db->lasterror() . ", " . $sqlImg,100);
        }
        
        return self::str($image);
    }
    
    static function getImageCat($ref)
    {
        if(empty($ref))
        {
            return "'biovita-900x307.jpg'";
        }
        return $ref;
    }
    
    static function removeNL($string)
    {
        $trim = trim(preg_replace('/\s+/', ' ', $string));
        return $trim;
    }
    
    static function stripNL($string)
    {
        return self::str(self::removeNL(strip_tags($string)));
    }
    
    static function NN($value)
    {
        if(empty($value))
        {
            return 0;
        }
        
        return $value;
    }
    
    static function setTVA($tva)
    {
        $db = self::$db;
        $tablename = self::$prefix . "jshopping_taxes";
        $query = "select id from $tablename where tax_value = $tva";
        ////print "query: $query \n";
        
        $res = $db->query($query);
        if($res)
        {
            $rs = $db->fetch_row($res);
            if($rs)
            {
                //print "Trovata tassa al $tva% con id $rs[0] \n";
                return $rs[0];
            }
            else
            {
                $query = "insert into $tablename (`tax_name`,`tax_value`) values ('IVA al $tva%',$tva)";
                $db->query($query);
                $last_q = $db->query("select max(tax_id) from $tablename");
                $lastid = $db->fetch_row($last_q);
                //print "inserita tassa al $tva% con id $lastid[0] \n";
                return $lastid[0];
            }
        }
        else
        {
            $query = "insert into $tablename (`tax_name`,`tax_value`) values ('IVA al $tva%',$tva)";
            $db->query($query);
            $last_q = $db->query("select max(tax_id) from $tablename");
            $lastid = $db->fetch_row($last_q);
            //print "inserita tassa al $tva% con id $lastid[0] \n";
            return $lastid[0];
        }
    }
    
    static function emptyFolder($dirpath) 
    {
        $handle = opendir($dirpath);
        while (($file = readdir($handle)) !== false) {
          ////echo "Cancellato: " . $file . "<br/>";
          @unlink($dirpath . $file);
        }
        closedir($handle);
    }
    
    static function prepareFolder($path)
    {
        if(!file_exists($path))
        {
            mkdir($path);
        }
        
        $img_prod_folder = $path . "/" . "img_products";
        if(!file_exists($img_prod_folder))
        {
            mkdir($img_prod_folder);
        }
        
        $img_cat_folder = $path . "/" . "img_categories";
        if(!file_exists($img_cat_folder))
        {
            mkdir($img_cat_folder);
        }
        
        self::emptyFolder($path);
        self::emptyFolder($img_cat_folder);
        self::emptyFolder($img_prod_folder);
    }
    
    static function copyPic($path,$ref)
    {
        //find img in dolibarr
        $dolibarr_img_folder = dirname(__FILE__) 
                                . DIRECTORY_SEPARATOR . ".."
                                . DIRECTORY_SEPARATOR . ".."
                                . DIRECTORY_SEPARATOR . ".." 
                                . DIRECTORY_SEPARATOR . "documents"
                                . DIRECTORY_SEPARATOR . "produit"
                                . DIRECTORY_SEPARATOR . $ref;
        $dolibarr_img_name   = "";
        $ext = array(".jpg",".jpeg",".png",".gif");
        if(file_exists($dolibarr_img_folder))
        {
            $files = self::getfiles($dolibarr_img_folder, $ext);
            if(count($files))
            {
                $dolibarr_img_name = $files[0];
            }
            else
            {
                return false;
            }
        }
        else
        {
            //print "cartella $dolibarr_img_folder non presente\n\n";
            return false;
        }
        
        //set jshopping path
        $img_ext = "." . pathinfo($dolibarr_img_name, PATHINFO_EXTENSION);
        $jshopping_img_name = strtolower($ref . $img_ext);
        
        //Copy file
        $source = $dolibarr_img_folder . DIRECTORY_SEPARATOR . $dolibarr_img_name;
        $destination = $path . DIRECTORY_SEPARATOR . "img_products" . DIRECTORY_SEPARATOR . $jshopping_img_name;
        $dest_thumb  = $path . DIRECTORY_SEPARATOR . "img_products" . DIRECTORY_SEPARATOR . "thumb_" . $jshopping_img_name;
        $dest_full   = $path . DIRECTORY_SEPARATOR . "img_products" . DIRECTORY_SEPARATOR . "full_" . $jshopping_img_name;
        $remote      = "components/com_jshopping/files/img_products/$jshopping_img_name";
        $remote_thumb= "components/com_jshopping/files/img_products/thumb_$jshopping_img_name";
        $remote_full = "components/com_jshopping/files/img_products/full_$jshopping_img_name";
        
        if(copy($source,$destination))
        {
            copy($source,$dest_thumb);
            copy($source,$dest_full);
            
            if(is_array(ftp_nlist(self::$ftp_connection, ".")))
            {
                //Transfer to FTP
                // upload a file
                if (ftp_put($conn_id, $remote, $source, FTP_BINARY)) 
                {
                    self::send_message("IMG", "<div style='margin: 10px; padding: 5px; overflow: hidden' ><h4 style='color: #aa5555;'>IMMAGINE CARICATA</h4><strong>source</strong>: $source <br> <strong>dest</strong>: $dir$remote</div>", 99);
                } 
                else 
                {
                    self::send_message("IMG", "ERRORE DURANTE IL CARICAMENTO DI $remote <br> <pre>" . print_r(error_get_last(),1) . "</pre>", 99);
                }

                if (ftp_put($conn_id, $remote_thumb, $source, FTP_BINARY)) 
                {
                    self::send_message("IMG", "<div style='margin: 10px; padding: 5px; overflow: hidden' ><h4 style='color: #aa5555;'>IMMAGINE CARICATA</h4><strong>source</strong>: $source <br> <strong>dest</strong>: $dir$remote_thumb</div>", 99);
                } 
                else 
                {
                    self::send_message("IMG", "ERRORE DURANTE IL CARICAMENTO DI $remote_thumb <br> <pre>" . print_r(error_get_last(),1) . "</pre>", 99);
                }

                if (ftp_put($conn_id, $remote_full, $source, FTP_BINARY)) 
                {
                    self::send_message("IMG", "<div style='margin: 10px; padding: 5px; overflow: hidden' ><h4 style='color: #aa5555;'>IMMAGINE CARICATA</h4><strong>source</strong>: $source <br> <strong>dest</strong>: $dir$remote_full</div>", 99);
                } 
                else 
                {
                    self::send_message("IMG", "ERRORE DURANTE IL CARICAMENTO DI $remote_full <br> <pre>" . print_r(error_get_last(),1) . "</pre>", 99);
                }
            }
            return $jshopping_img_name;
        }
        else
        {
            self::send_message("IMG", "ERRORE DURANTE LA COPIA IN LOCALE <br> <pre>" 
                            . print_r(error_get_last(),1) 
                            . "\nURL: " . self::$ftp_url
                            . "</pre>", 100);
            sleep(3);
            return "";
        }
    }
    
    static function getfiles($dirname,$arrayext)
    {
	$arrayfiles=Array();
	if(file_exists($dirname))
        {
            $handle = opendir($dirname);
            while (false !== ($file = readdir($handle))) 
            { 
                if(is_file($dirname . "/" . $file))
                {
                    $ext = strtolower(substr($file, strrpos($file, "."), strlen($file)-strrpos($file, ".")));
                    if(in_array($ext,$arrayext))
                    {
                        array_push($arrayfiles,$file);
                    }
                }
            }
            $handle = closedir($handle);
	}
	sort($arrayfiles);
	return $arrayfiles;
    }
    
    static function send_message($id, $message, $progress) 
    {
        $d = array('message' => $message , 'progress' => $progress); //prepare json
        echo "id: $id" . PHP_EOL;
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;

        ob_end_flush();
        flush();
    }
}

