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
abstract class DataBase
{
    
	public static function objectToTableName( $obj )
	{
		return stripslashes(get_class($obj));
	}
	
	public static function count( $dbDsnCache, $tableName, $where = null )
	{
		$sql = "SELECT COUNT(*) FROM `'.$tableName.'`';";
		if ( $where !== null ) { $sql .= ' WHERE '.$where; }
		
		try
		{
			$db = new \PDO( $dbDsnCache, _DB_USER, _DB_PASS, _DB_OPTIONS );
			$res = $db->query($sql);
			if ( $res )
			{
				$count = $res->fetchColumn();
				$res = null;
				$db = null;
				return $count;
			}
			
			$res = null;
			$db = null;
			return 0;
		}
		catch (PDOException $e)
		{
			return 0;
		}	
		
	}
	
	public static function exist( $dbDsnCache, $tableName )
    {
		try
		{
			$sql = 'SELECT 1 FROM `'.$tableName.'` LIMIT 1';
			$db = new \PDO($dbDsnCache, _DB_USER, _DB_PASS, _DB_OPTIONS );
			$result = $db->query($sql);
			$db = null;
		}
		catch (PDOException $e)
		{
			return false;
		}

		return ($result !== false);
	}
	
    public static function create( $dbDsnCache, array $getObjectVars, $tableName, $exec = true )
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
			return $this->execute( $dbDsnCache, $sql );
		}
		
		return false;
    }

	public static function insert( $dbDsnCache, array $getObjectVars, $tableName, $exec = true )
    {
		$sqls[0] = 'INSERT INTO `'.$tableName.'` VALUES ( ';
		$first = true;
		foreach ( $getObjectVars as $key => $value )
		{
			
			if ( gettype($value) == 'boolean' )
			{
				$sqls[0] .= ( ($first)?'':', ' ).(($value)?'1':'0');
				$first = false;
			}
			elseif ( gettype($value) == 'integer' )
			{
				$sqls[0] .= ( ($first)?'':', ' ).$value;
				$first = false;
			}
			elseif ( gettype($value) == 'double' )
			{
				$sqls[0] .= ( ($first)?'':', ' ).$value;
				$first = false;
			}
			elseif ( gettype($value) == 'string' )
			{
				$sqls[0] .= ( ($first)?'':', ' ).'\''.addslashes($value).'\'';
				$first = false;
			}
			/*elseif ( gettype($value) == 'array' )
			{
				$sqlTemp = '';
				foreach ($value as $key2 => $val2)
				{
					$sqlTemp .= 'INSERT INTO `'.$tableName.'_array` VALUES ( \''.$getObjectVars['_id'].'\'';
					$sqlTemp .= ', \''.addslashes($key).'\' );';
					$sqlTemp .= ', \''.addslashes($key2).'\', \''.addslashes($val2).'\' );';
				}
				if ( $sqlTemp != '' ) $sqls[] = $sqlTemp;
			}
			elseif( get_class($value) == get_class( DataList::getEmptyDataList() ) )
			{
				$sqlTemp = '';
				foreach ($value->getArray() as $key2 => $val2)
				{
					$sqlTemp .= 'INSERT INTO `'.$tableName.'_array` VALUES ( \''.$getObjectVars['_id'].'\'';
					$sqlTemp .= ', \''.addslashes($key).'\' );';
					$sqlTemp .= ', \''.addslashes($key2).'\', \''.addslashes($val2).'\' );';
				}
				if ( $sqlTemp != '' ) $sqls[] = $sqlTemp;
				//$sqls[1] = 'CREATE TABLE `'.$tableName.'_array` ( parent_name TEXT, parent_lang TEXT, _key TEXT, _value TEXT );';
			}*/
		}
		$sqls[0] .= ' );';
		$sql = implode('', $sqls);
		
		/*echo "\n-->"
			. $sql
			. "<--\n";*/
		
		if ( $exec )
		{
			$this->execute( $dbDsnCache, $sql );
		}
		
		return $sql;
    }
	
	public static function execute( $dbDsnCache, $request )
	{
		try
		{
			$db = new \PDO($dbDsnCache, _DB_USER, _DB_PASS, _DB_OPTIONS );
			//$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
			$db->exec($request);
			$db = null;
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
	
	public static function query( $dbDsnCache, $request )
	{
		try
		{
			$db = new \PDO($dbDsnCache, _DB_USER, _DB_PASS, _DB_OPTIONS );
			//$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
			return $db->query($request);
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
	
}
