<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClassDB
 *
 * @author Massimiliano Palermo <maxx.palermo@gmail.com>
 */
class ClassDB {
    private $db;
    
    public function __construct(DoliDBMysqli $db) 
    {
        $this->db = $db;
    }
    
    public function setQuery($query)
    {
        $str_query = implode(" ",$query);
        if(strpos($str_query, "#__")>0)
        {
            return str_replace("#__", MAIN_DB_PREFIX, $str_query);
        }
        else
        {
            return implode(" ",$query);
        }
    }
    
    /**
     * 
     * @param Array $tablename
     * @param Array $fields
     * @param Array $where
     * @param Array $order
     * @return String The final query
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @version 1.1
     * @copyright (c) 2016, Massimiliano Palermo <http://www.mpsoft.it>
     */
    public function Select($tablename,$fields,$where = [],$order = [])
    {
        $query = [];
        $query[] = "SELECT " . implode(",",$fields);
        $query[] = "FROM "   . implode(",",$tablename);
        if(count($where))
        {
            $query[] = "WHERE " . implode(" AND ",$where);
        }
        if(count($order))
        {
            $query[] = "ORDER BY " . implode(",",$order);
        }
        
        return $this->setQuery($query);
    }
    
    /**
     * 
     * @param Array $tablename :Tablename
     * @param Array $fields : Fields array
     * @param Array $values : Values array
     * @param Array $where : Where clause
     * @return String The final query
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @version 1.1
     * @copyright (c) 2016, Massimiliano Palermo <http://www.mpsoft.it> 
     */
    public function Insert($tablename,$fields,$values,$where = [])
    {
        $query = [];
        $query[] = "INSERT INTO " . implode(",",$tablename);
        $query[] = "(" . implode(",",$fields) . ")";
        $query[] = "VALUES (" . implode(",",$values) . ")";
        if(count($where))
        {
            $query[] = "WHERE " . implode(" AND ",$where);
        }
        
        return $this->setQuery($query);
    }
    
    /**
     * 
     * @param Array $tablename : Tablename
     * @param Array $set : Set clause
     * @param Array $where : Where clause
     * @return String The final query
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @version 1.1
     * @copyright (c) 2016, Massimiliano Palermo <http://www.mpsoft.it> 
     */
    public function Update($tablename,$set,$where = [])
    {
        $query = [];
        $query[] = "UPDATE " .implode(",",$tablename);
        $query[] = "SET " . implode(",",$set);
        if(count($where))
        {
            $query[] = "WHERE " . implode(" AND ",$where);
        }
        
        return $this->setQuery($query);
    }
    
    /**
     * 
     * @param Array $tablename : Tablename
     * @param Array $where : Where clause
     * @return String The final query
     * @author Massimiliano Palermo <maxx.palermo@gmail.com>
     * @version 1.1
     * @copyright (c) 2016, Massimiliano Palermo <http://www.mpsoft.it> 
     */
    public function Delete($tablename,$where = [])
    {
        $query = [];
        $query[] = "DELETE FROM " . implode(",",$tablename);
        if(count($where))
        {
            $query[] = "WHERE " . implode(" AND ",$where);
        }
        
        return $this->setQuery($query);
    }
    
    /**
     * Returns current connection.
     * @return DoliDBMysqli Connection
     */
    public function db()
    {
        return $this->db;
    }
}
