<?php

/*
 * The MIT License
 *
 * Copyright 2014 Damien Doussaud (namide.com).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Flea;

/**
 * Base object used to generate a save of object.
 * With this saving you can instantiate the same object.
 *
 * @author Namide
 */
class DataBase
{
    private static $_INSTANCE = array();
	
	private $_pdo;
	 
	public static function objectToTableName( $obj )
	{
		return stripslashes(get_class($obj));
	}
	
	public function count( $tableName, $where = null )
	{
		$sql = 'SELECT COUNT(*) FROM `'.$tableName.'`';
		if ( $where !== null ) { $sql .= ' WHERE '.$where; }
		$sql .= ';';
		
		try
		{
			/*if ( _DEBUG ) { $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING); }
			else { $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT); }*/
			
			$res = $this->_pdo->query($sql);
			if ( $res )
			{
				$count = $res->fetchColumn();
				$res = null;
				//$db = null;
				return $count;
			}
			
			$res = null;
			//$db = null;
			return 0;
		}
		catch (PDOException $e)
		{
			return 0;
		}	
		
	}
	
	public function exist( $tableName )
    {
		try
		{
			$sql = 'SELECT 1 FROM `'.$tableName.'` LIMIT 1';
			/*$this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);*/
			
			$result = $this->_pdo->query($sql);
			//$db = null;
		}
		catch (PDOException $e)
		{
			return false;
		}

		return ($result !== false);
	}
	
    public function create( array $getObjectVars, $tableName, $exec = true )
    {
		$sqls = array();
		$sqls[0] = 'CREATE TABLE `'.$tableName.'` ( ';
		$first = true;
		foreach ( $getObjectVars as $key => $value )
		{

			if ( gettype($value) == "boolean" )
			{
				$sqls[0] .= ( ($first)?'':', ' ).$key.' BOOLEAN';
				if ( $first ) $first = false;
			}
			elseif ( gettype($value) == "integer" )
			{
				$sqls[0] .= ( ($first)?'':', ' ).$key.' INT';
				if ( $first ) $first = false;
			}
			elseif ( gettype($value) == "double" )
			{
				$sqls[0] .= ( ($first)?'':', ' ).$key.' DOUBLE';
				if ( $first ) $first = false;
			}
			elseif ( gettype($value) == "string" )
			{
				$sqls[0] .= ( ($first)?'':', ' ).$key.' TEXT';
				if ( $first ) $first = false;
			}
		}
		$sqls[0] .= ' );';
		$sql = implode('', $sqls);
		
		if ( $exec )
		{
			return DataBase::execute( $sql );
		}
		
		return false;
    }

	public function insert( array $getObjectVars, $tableName, $exec = true )
    {
		$sqls[0] = 'INSERT INTO `'.$tableName.'` VALUES ( ';
		$binds = array();
		$first = true;
		foreach ( $getObjectVars as $key => $value )
		{
			if ( gettype($value) == 'boolean' )
			{
				$sqls[0] .= ( ($first)?':':', :' ).$key;
				$binds[] = array( ':'.$key, (($value)?'1':'0'), \PDO::PARAM_BOOL );
				$first = false;
			}
			elseif ( gettype($value) == 'integer' )
			{
				$sqls[0] .= ( ($first)?':':', :' ).$key;
				$binds[] = array( ':'.$key, $value, \PDO::PARAM_INT );
				$first = false;
			}
			elseif ( gettype($value) == 'double' )
			{
				$sqls[0] .= ( ($first)?':':', :' ).$key;
				$binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				$first = false;
			}
			elseif ( gettype($value) == 'string' )
			{
				$sqls[0] .= ( ($first)?':':', :' ).$key;
				$binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				$first = false;
			}
		}
		$sqls[0] .= ' );';
		$sql = implode('', $sqls);
		
		if ( $exec )
		{
			DataBase::execute( $sql, $binds );
		}
		
		return $sql;
    }
	
	public function execute( $request, array $binds = null )
	{
		try
		{
			/*if ( _DEBUG ) { $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING); }
			else { $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT); }*/
			
			//var_dump($request);
			
			$stmt = $this->_pdo->prepare($request); // Préparation de ton statement
			
			if ( $binds !== null )
			{
				foreach ($binds as $bind)
				{
					$stmt->bindValue($bind[0], $bind[1], $bind[2]);
				}
			}
			$stmt->execute();
			$stmt = null;
			
			return true;
		}
		catch(PDOException $e)
		{
			if ( _DEBUG )
			{
				Debug::getInstance()->addError( 'Execution database error: '.$e->getMessage() );
			}
		}
		return false;
	}
	
	
	public function fetchAll( SqlQuery $query )
	{
		try
		{
			$stmt = $this->_pdo->prepare( $query->getRequest() );
			
			if ( $stmt === false )
			{
				return array();
			}
			
			$binds = $query->getBinds();
			if ( $binds !== null )
			{
				foreach ($binds as $bind)
				{
					$stmt->bindValue($bind[0], $bind[1], $bind[2]);
				}
			}
			$stmt->execute();
			$arrValues = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$stmt = null;
			
			return $arrValues;
		}
		catch(PDOException $e)
		{
			if ( _DEBUG )
			{
				Debug::getInstance()->addError( 'fetch_all() database error: '.$e->getMessage() );
			}
		}
		return false;
	}
	
	
	/*public function fetchAll( $request )
	{
		try
		{
			$stmt = $this->_pdo->prepare($request);
			
			if ( $stmt === false )
			{
				return array();
			}
			
			$stmt->execute();
			$arrValues = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			return $arrValues;
		}
		catch(PDOException $e)
		{
			if ( _DEBUG )
			{
				Debug::getInstance()->addError( 'Execution database error: '.$e->getMessage() );
			}
		}
		return false;
	}*/
	
	
	private function __construct( $dbDsnCache ) 
    {
		try
		{
			$this->_pdo = new \PDO($dbDsnCache, _DB_USER, _DB_PASS, _DB_OPTIONS );
			
			if ( _DEBUG )
			{
				$this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
			}
			else
			{
				$this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
			}
		}
		catch(PDOException $e)
		{
			if ( _DEBUG )
			{
				Debug::getInstance()->addError( 'Initialize database error: '.$e->getMessage() );
			}
		}
    }
	
	/**
	 * 
	 * @param string $dbDsnCache
	 * @return DataBase
	 */
	public static function getInstance( $dbDsnCache ) 
    {
		if(!isset(self::$_INSTANCE[$dbDsnCache]))
        {
            self::$_INSTANCE[$dbDsnCache] = new DataBase($dbDsnCache);
        }
        return self::$_INSTANCE[$dbDsnCache];
    }
	
	final public function __clone()
    {
        if ( _DEBUG ) 
		{
			Debug::getInstance()->addError('You can\'t clone a multiton');
		}
    }
	
}
