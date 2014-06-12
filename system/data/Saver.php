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
abstract class Saver
{
    
	protected $_id = 0;
	/**
	 * 
	 * @return int
	 */
	public function getId()
	{
		return _id;
	}
	
	protected static $_ID_LENGTH = 0;
	
	protected function __construct()
	{
		$this->_id = self::$_ID_LENGTH;
		self::$_ID_LENGTH++;
	}


	/**
     * Get a script for create the same object
     * 
     * @return string		The save text
     */
    //abstract public function getSave();

    /**
     * Called by getSaver()
     * 
     * @param type $getObjectVars		Associative array generated in the class with get_called_class()
     * @return string					Text usable to construc the same object
     */
    /*protected function constructSave( array $getObjectVars )
    {
	    $c = get_called_class();
	    $output = $c.'::create(';
	    $output .= self::getStrConstructor($getObjectVars);
	    $output .= ')';

	    return $output;
    }*/

	public static function db_exist( $dbDsnCache, $tableName = null )
    {
		if ( $tableName === null )
		{
			$tableName = stripslashes(get_called_class());
		}
		
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
	
    public static function db_create( $dbDsnCache, array $getObjectVars, $exec = true, $tableName = null )
    {
		if ( $tableName === null )
		{
			$tableName = stripslashes(get_called_class());
		}
		
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
			elseif ( gettype($value) == "array" )
			{
				$sqls[1] = 'CREATE TABLE `'.$tableName.'_array` ( _id INT, _key TEXT, _value TEXT );';
			}
		}
		$sqls[0] .= ' );';
		$sql = implode('', $sqls);
		
		echo "-- Saver::db_create()\n"
			. $sql
			. "\n --";
		
		if ( $exec )
		{
			try
			{	
				$db = new \PDO($dbDsnCache, _DB_USER, _DB_PASS, _DB_OPTIONS );
				//$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
				$db->exec($sql);
				$db = null;
				
				return $sql;
			}
			catch(PDOException $e)
			{
				if ( _DEBUG )
				{
					Debug::getInstance()->addError('Create database error: '.$e);
				}
			}
		}
		return '';
    }

	public static function db_insert( $dbDsnCache, array $getObjectVars, $exec = true, $tableName = null )
    {
		if ( $tableName === null )
		{
			$tableName = stripslashes(get_called_class());
		}
		
		
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
			elseif ( gettype($value) == 'array' )
			{
				$sqlTemp = '';
				foreach ($value as $key2 => $val2)
				{
					$sqlTemp .= 'INSERT INTO `'.$tableName.'_array` VALUES ( '.(int)$getObjectVars['_id'];
					$sqlTemp .= ', \''.addslashes($key2).'\', \''.addslashes($val2).'\'';
					$sqlTemp .= ' );';
				}
				if ( $sqlTemp != '' ) $sqls[] = $sqlTemp;
			}
		}
		$sqls[0] .= ' );';
		$sql = implode('', $sqls);
		
		/*echo "\n-->"
			. $sql
			. "<--\n";*/
		
		if ( $exec )
		{
			try
			{
				$db = new \PDO($dbDsnCache, _DB_USER, _DB_PASS, _DB_OPTIONS );
				//$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
				$db->exec($sql);
				$db = null;
				return $sql;
			}
			catch(PDOException $e)
			{
				if ( _DEBUG )
				{
					Debug::getInstance()->addError('Insert database error: '.$e);
				}
			}
		}
		
		return $sql;
    }

    /**
     * Create a new object by a saved object.
     * A saved object can by generate by the method getSave().
     * 
     * @param array $saveDatas		Datas generated by a save method of this class
     * @return self					New Saver with the news values
     */
    /*public static function create( array $saveDatas )
    {
		$c = get_called_class();
		$element = new $c;
		$element->update( $saveDatas );
		return $element;
    }*/

    /**
     * Update the object with a saved object.
     * A saved object can by generate by the method getSave().
     * 
     * @param array $saveDatas		Datas generated by a save method of this class
     * @return self					Saver with the news values
     */
    //abstract public function update( array $saveDatas );

    /**
     * Return a string for instantiate the same data
     * 
     * @param type $array			Associative array $data to transform
     * @return string				Text usable to create a new same object
     */
    /*protected static function getStrConstructor( $data )
    {
		$output = 'array(';
		$first = true;
		foreach ($data as $key => $value)
		{
			if ( !$first ) $output .= ',';

			$output .= '"'.$key.'"=>';
			if ( gettype ($value) === "array" ) 
			{
				$output	.= self::getStrConstructor($value);
			}
			else if ( gettype($value) === "object" )
			{
			if ( get_parent_class($value) === "Saver" )
			{
				$output .= $value->getSave();
			}
			else
			{
				$output .= self::getStrConstructor( $value );
			}
			}
			elseif( gettype ($value) === "string" )
			{
				$output	.= self::escQuot($value);
			}
			elseif( gettype ($value) === "integer" || gettype ($value) === "double" )
			{
				$output	.= $value;
			}
			elseif( gettype ($value) === "boolean" )
			{
				$output	.= ($value)?'true':'false';
			}
			else
			{
				$output	.= self::escQuot($value);
			}

			if ( $first )
			{
				$first = false;
			}
		}
		$output .= ')';
		return $output;
    }*/

    /**
     * Escape the double quotes
     * 
     * @param type $text		Text to escape
     * @return type				Text escaped
     */
	/*private static function escQuot( $text )
	{
		return '"' . str_replace('"', '\"', $text ) .'"';
	}*/
	
}
