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
 * Requests data helper
 * 
 * @author Namide
 */
class SqlQuery
{
	public static $TYPE_CREATE = 1;
	public static $TYPE_SELECT = 2;
	public static $TYPE_UPDATE = 3;
	public static $TYPE_INSERT = 4;
	public static $TYPE_DELETE = 5;
	
	protected static $_TEMP = null;
	/**
	 * Create a temporary request object.
	 * Used to reduce memory (avoid to create other request objects)
	 * 
	 * @return self		SqlQuery object
	 */
	public static function getTemp( $type = 0 )
	{
		if ( self::$_TEMP === null ) { self::$_TEMP = new SqlQuery(); }
		else self::$_TEMP->clean( $type );
		return self::$_TEMP;
	}
	
	protected $_type;
	/**
	 * Type of the request :
	 * - self::$TYPE_CREATE 
	 * - self::$TYPE_SELECT 
	 * - self::$TYPE_UPDATE 
	 * - self::$TYPE_INSERT 
	 * - self::$TYPE_DELETE
	 * 
	 * @return int		Type of the request
	 */
	public function getType() { return $this->_type; }
	public function setType( $type ) { $this->_type = $type; }
	
	protected $_binds;
	/**
	 * Used in PDO to securise datas injected in the data base
	 * 
	 * @return array	Binds like binds in PDO object
	 */
	public function getBinds() { return $this->_binds; }
	public function setBinds( array $binds ) { $this->_binds = $this->_binds + $binds; }
	public function addBind( $key, $value, $pdoParamType )
	{
		$this->_binds[] = array($key, $value, $pdoParamType);
	}
	
	
	// CREATE
	protected $_create;
	public function getCreate() { return $this->_create; }
	public function setCreate( $create )
	{
		$this->_type = self::$TYPE_CREATE;
		$this->_create = $create;
	}
	
	// READ
	protected $_select;
	public function getSelect() { return $this->_select; }
	public function setSelect( $select )
	{
		$this->_type = self::$TYPE_SELECT;
		$this->_select = $select;
	}
	
	protected $_where;
	public function getWhere() { return $this->_where; }
	public function setWhere( $where ) { $this->_where = $where; }
	
	protected $_from;
	public function getFrom() { return $this->_from; }
	public function setFrom( $from ) { $this->_from = $from; }
	
	protected $_groupBy;
	public function getGroupBy() { return $this->_groupBy; }
	public function setGroupBy( $groupBy ) { $this->_groupBy = $groupBy; }
	
	protected $_having;
	public function getHaving() { return $this->_having; }
	public function setHaving( $having ) { $this->_having = $having; }
	
	protected $_orderBy;
	public function getOrderBy() { return $this->_orderBy; }
	public function setOrderBy( $orderBy ) { $this->_orderBy = $orderBy; }
	
	protected $_limit;
	public function getLimit() { return $this->_limit; }
	public function setLimit( $limit ) { $this->_limit = $limit; }
	
	// INSERT
	protected $_insert;
	public function getInsert() { return $this->_insert; }
	public function setInsert( $insert )
	{
		$this->_type = self::$TYPE_INSERT;
		$this->_insert = $insert;
	}
	
	protected $_values;
	public function getValues() { return $this->_values; }
	public function setValues( $values ) { $this->_values = $values; }

	// UPDATE
	protected $_update;
	public function getUpdate() { return $this->_update; }
	public function setUpdate( $update )
	{
		$this->_type = self::$TYPE_UPDATE;
		$this->_update = $update;
	}
	
	protected $_set;
	public function getSet() { return $this->_set; }
	public function setSet( $set ) { $this->_set = $set; }
	
	// DELETE
	protected $_delete;
	public function getDelete() { return $this->_delete; }
	public function setDelete( $delete )
	{
		$this->_type = self::$TYPE_UPDATE;
		$this->_delete = $delete;
	}
	
	public function __construct( $type = 0 )
	{
		$this->clean( $type );
	}
	
	/**
	 * Clean the datas in your object
	 * 
	 * @param int		Type of the next SqlQuery
	 */
	public function clean( $type = 0 )
	{
		$this->_type = $type;
		$this->_create = '';
		$this->_select = '';
		$this->_from = '';
		$this->_where = '';
		$this->_groupBy = '';
		$this->_having = '';
		$this->_orderBy = '';
		$this->_limit = '';
		$this->_update = '';
		$this->_set = '';
		$this->_insert = '';
		$this->_values = '';
		$this->_delete = '';
		$this->_binds = array();
	}
	
	/**
	 * Initialize you SqlQuery with a self::$TYPE_SELECT request
	 * 
	 * @param string	$select			results column names
	 * @param string	$from			tables on which door the order 
	 * @param array		$whereList		filter in associative array
	 * @param array		$whereSigns		signs of the $whereList ('=', '<', 'LIKE'...)
	 * @param string	$orderBy		sorting of the result data
	 * @param string	$limit			count of results
	 */
	public function initSelect( $select, $from, array $whereList = null, array $whereSigns = null, $orderBy = '', $limit = '' )
	{
		$this->_type = self::$TYPE_SELECT;
		$this->_select = $select;
		$this->_from = $from;
		if ( $whereList !== null )
		{
			$this->_where = $this->getStrFromBinding($whereList, ' AND ', $whereSigns );
		}
		$this->_orderBy = $orderBy;
		$this->_limit = $limit;
	}
	
	/**
	 * Initialize you SqlQuery with a self::$TYPE_SELECT request to count lines.
	 * 
	 * @param string	$from			tables on which door the order 
	 * @param array		$whereList		filter in associative array
	 * @param array		$signList		signs of the $whereList ('=', '<', 'LIKE'...)
	 */
	public function initCount( $from, array $whereList = null, array $signList = null )
	{
		$this->_type = self::$TYPE_SELECT;
		$this->_select = 'COUNT(*)';
		$this->_from = $from;
		if ( $whereList !== null )
		{
			$this->_where = $this->getStrFromBinding( $whereList, ' AND ', $signList );
		}
	}
	
	/**
	 * Initialize you SqlQuery with a self::$TYPE_INSERT request.
	 * 
	 * @param string $tableName		Name of the table		
	 * @param array $values			Associative array with datas (keys for rows names)
	 */
	public function initInsertValues( $tableName, array $values = array() )
	{
		$this->_type = self::$TYPE_INSERT;
		$this->_insert = 'INTO `'.$tableName.'` (';
		
		$this->_values = '';
		$first = true;
		foreach ( $values as $key => $value )
		{
			if ( gettype($value) == 'boolean' )
			{
				$this->_insert .= ( ($first)?'':', ' ).$key;
				$this->_values .= ( ($first)?':':', :' ).$key;
				$this->_binds[] = array( ':'.$key, (($value)?'1':'0'), \PDO::PARAM_BOOL );
				$first = false;
			}
			elseif ( gettype($value) == 'integer' )
			{
				$this->_insert .= ( ($first)?'':', ' ).$key;
				$this->_values .= ( ($first)?':':', :' ).$key;
				$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_INT );
				$first = false;
			}
			elseif ( gettype($value) == 'double' )
			{
				$this->_insert .= ( ($first)?'':', ' ).$key;
				$this->_values .= ( ($first)?':':', :' ).$key;
				$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				$first = false;
			}
			elseif ( gettype($value) == 'string' )
			{
				$this->_insert .= ( ($first)?'':', ' ).$key;
				$this->_values .= ( ($first)?':':', :' ).$key;
				$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				$first = false;
			}
		}
		$this->_insert .= ')';
	}
	
	/**
	 * Initialize you SqlQuery with a self::$TYPE_UPDATE request.
	 * 
	 * @param string $tableName		Name of the table		
	 * @param array $setList		Associative array with datas (keys for rows names)
	 * @param array $whereList		filter in associative array
	 */
	public function initUpdateSet( $tableName, array $setList, array $whereList = null )
	{
		$this->_type = self::$TYPE_UPDATE;
		$this->_update = '`'.$tableName.'`';
		$this->_set = $this->getStrFromBinding( $setList, ', ' );
		if ( $whereList !== null )
		{
			$this->_where = $this->getStrFromBinding( $whereList, ' AND ' );
		}
	}

	/**
	 * Initialize you SqlQuery with a self::$TYPE_UPDATE request.
	 * 
	 * @param string $tableName			Name of the table		
	 * @param array $getObjectVars		List of rows of the table (keys for names, values for example)
	 */
	public function initCreate( $tableName, array $getObjectVars )
	{
		$this->_type = self::$TYPE_CREATE;
		$this->_create = 'TABLE `'.$tableName.'` (';
		
		$first = true;
		foreach ( $getObjectVars as $key => $value )
		{
			if ( gettype($value) == "boolean" )
			{
				$this->_create .= ( ($first)?'':', ' ).$key.' BOOLEAN';
				if ( $first ) $first = false;
			}
			elseif ( gettype($value) == "integer" )
			{
				$this->_create .= ( ($first)?'':', ' ).$key.' INT';
				if ( $first ) $first = false;
			}
			elseif ( gettype($value) == "double" )
			{
				$this->_create .= ( ($first)?'':', ' ).$key.' DOUBLE';
				if ( $first ) $first = false;
			}
			elseif ( gettype($value) == "string" )
			{
				$this->_create .= ( ($first)?'':', ' ).$key.' TEXT';
				if ( $first ) $first = false;
			}
		}
		$this->_create .= ' );';
	}
	
	/**
	 * Initialize you SqlQuery with a self::$TYPE_DELETE request.
	 * 
	 * @param string $tableName			Name of the table		
	 * @param array $whereList			filter in associative array
	 */
	public function initDelete( $tableName, array $whereList )
	{
		$this->_type = self::$TYPE_DELETE;
		$this->_delete = 'FROM `'.$tableName.'`';
		
		if ( $whereList !== null )
			$this->_where = $this->getStrFromBinding( $whereList, ' AND ' );
	}
	
	private function getStrFromBinding( array $valueList /* associative array */, $strGlue = ', ', array $signList = null )
	{
		
		$values = array();
		$signs = array();
		$keys = array();
		$i = 0;
		foreach ($valueList as $key => $value)
		{
			$values[$i] = $value;
			$keys[$i] = $key;
			
			if ( $signList === null || !isset($signList[$i]) )
				$signs[$i] = '=';
			else
				$signs[$i] = $signList[$i];
			
			$i++;
		}
		
		$output = '';
		
		$first = true;
		$l = count($values);
		for( $i = 0; $i < $l; $i++ )
		{
			$key = $keys[$i];
			$sign = $signs[$i];
			$value = $values[$i];
			
			if ( gettype($value) == 'boolean' )
			{
				$output .= ( ($first)?' ':$strGlue ).$key.' '.$sign.' :'.$key;
				if ( !array_key_exists(':'.$key, $this->_binds) )
				{
					$this->_binds[] = array( ':'.$key, (($value)?'1':'0'), \PDO::PARAM_BOOL );
				}
				$first = false;
			}
			elseif ( gettype($value) == 'integer' )
			{
				$output .= ( ($first)?' ':$strGlue ).$key.' '.$sign.' :'.$key;
				if ( !array_key_exists(':'.$key, $this->_binds) )
				{
					$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_INT );
				}
				$first = false;
			}
			elseif ( gettype($value) == 'double' )
			{
				$output .= ( ($first)?' ':$strGlue ).$key.' '.$sign.' :'.$key;
				if ( !array_key_exists(':'.$key, $this->_binds) )
				{
					$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				}
				$first = false;
			}
			elseif ( gettype($value) == 'string' )
			{
				$output .= ( ($first)?' ':$strGlue ).$key.' '.$sign.' :'.$key;
				if ( !array_key_exists(':'.$key, $this->_binds) )
				{
					$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				}
				$first = false;
			}
		}
		
		return $output;
	}
	
	/**
	 * Generate the string request
	 * 
	 * @return string		Request in SQL
	 */
	public function getRequest()
	{
		switch ($this->_type)
		{
			case self::$TYPE_CREATE:
				return $this->getRequestCreate();
				break;
				
			case self::$TYPE_SELECT:
				return $this->getRequestRead();
				break;
			
			case self::$TYPE_INSERT:
				return $this->getRequestInsert();
				break;				
				
			case self::$TYPE_UPDATE:
				return $this->getRequestUpdate();
				break;
			
			case self::$TYPE_DELETE:
				return $this->getRequestDelete();
				break;
			
			default :
				if( _DEBUG && $this->_type == 0 )
				{
					Debug::getInstance()->addError('No type declared for this SQL request');
				}
		}
		
		return '';
	}
	
	/**
	 * Generate the string request to create a table
	 * 
	 * @return string		Request in SQL to create a table
	 */
	protected function getRequestCreate()
	{
		if(_DEBUG && $this->_create == '')
		{
			Debug::getInstance()->addError('For a TYPE_CREATE SQL query You must init the var "create"');
		}
		return 'CREATE '.$this->_create;
	}
	
	/**
	 * Generate the string request to select rows in a table
	 * 
	 * @return string		Request in SQL to select rows in a table
	 */
	protected function getRequestRead()
	{
		if($this->_select == '')
		{
			$this->_select = '*';
		}
		if(_DEBUG && $this->_from == '')
		{
			Debug::getInstance()->addError('For a TYPE_SELECT SQL query You must init the var "from"');
		}
		$request = 'SELECT ' . $this->_select;
		$request .= ' FROM ' . $this->_from;
		if($this->_where != '') { $request .= ' WHERE ' . $this->_where; }
		if($this->_groupBy != '') { $request .= ' GROUP BY ' . $this->_groupBy; }
		if($this->_having != '') { $request .= ' HAVING ' . $this->_having; }
		if($this->_orderBy != '') { $request .= ' ORDER BY ' . $this->_orderBy; }
		if($this->_limit != '') { $request .= ' LIMIT ' . $this->_limit; }
		return $request;
	}
	
	/**
	 * Generate the string request to insert lines in a table
	 * 
	 * @return string		Request in SQL to insert lines in a table
	 */
	protected function getRequestInsert()
	{
		if(_DEBUG)
		{
			if($this->_insert == '')
			{
				Debug::getInstance()->addError('For a TYPE_INSERT SQL query You must init the var "insert"');
			}
		}
		$request = 'INSERT ' . $this->_insert;
		if($this->_values != '') { $request .= ' VALUES (' . $this->_values . ')'; }
		if($this->_set != '') { $request .= ' SET ' . $this->_groupBy; }
		if($this->_select != '') { $request .= ' SELECT ' . $this->_select; }
		return $request;
	}
	
	/**
	 * Generate the string request to update line in a table
	 * 
	 * @return string		Request in SQL to update line in a table
	 */
	protected function getRequestUpdate()
	{
		if(_DEBUG)
		{
			if($this->_update == '')
			{
				Debug::getInstance()->addError('For a TYPE_UPDATE SQL query You must init the var "update"');
			}
			if($this->_set == '')
			{
				Debug::getInstance()->addError('For a TYPE_UPDATE SQL query You must init the var "set"');
			}
		}
		$request = 'UPDATE ' . $this->_update;
		if($this->_set != '') { $request .= ' SET ' . $this->_set; }
		if($this->_where != '') { $request .= ' WHERE ' . $this->_where; }
		if($this->_orderBy != '') { $request .= ' ORDER BY ' . $this->_orderBy; }
		if($this->_limit != '') { $request .= ' LIMIT ' . $this->_limit; }
		return $request;
	}
	
	/**
	 * Generate the string request to delete lines
	 * 
	 * @return string		Generate the string request to delete lines
	 */
	protected function getRequestDelete()
	{
		if(_DEBUG)
		{
			if($this->_delete == '')
			{
				Debug::getInstance()->addError('For a TYPE_DELETE SQL query You must init the var "delete"');
			}
			if($this->_from == '')
			{
				Debug::getInstance()->addError('For a TYPE_DELETE SQL query You must init the var "from"');
			}
		}
		$request = 'DELETE ' . $this->_delete;
		$request .= ' FROM ' . $this->_from;
		if($this->_where != '') { $request .= ' WHERE ' . $this->_where; }
		if($this->_orderBy != '') { $request .= ' ORDER BY ' . $this->_orderBy; }
		if($this->_limit != '') { $request .= ' LIMIT ' . $this->_limit; }
		return $request;
	}
}
