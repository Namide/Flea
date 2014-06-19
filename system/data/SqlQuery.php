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
	 * @return SqlQuery
	 */
	public static function getTemp( $type = 0 )
	{
		if ( self::$_TEMP === null ) { self::$_TEMP = new SqlQuery(); }
		else self::$_TEMP->clean( $type );
		return self::$_TEMP;
	}
	
	protected $_type;
	public function getType() { return $this->_type; }
	public function setType( $type ) { $this->_type = $type; }
	
	protected $_binds;
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
	
	public function initSelect( $select, $from, $where = '', $orderBy = '', $limit = '' )
	{
		$this->_type = self::$TYPE_SELECT;
		$this->_select = $select;
		$this->_from = $from;
		$this->_where = $where;
		$this->_orderBy = $orderBy;
		$this->_limit = $limit;
	}
	
	public function initInsertValues( $insert, array $values = array() )
	{
		$this->_type = self::$TYPE_INSERT;
		$this->_insert = $insert;
		
		$this->_values = '';
		$first = true;
		foreach ( $values as $key => $value )
		{
			if ( gettype($value) == 'boolean' )
			{
				$this->_values .= ( ($first)?':':', :' ).$key;
				$this->_binds[] = array( ':'.$key, (($value)?'1':'0'), \PDO::PARAM_BOOL );
				$first = false;
			}
			elseif ( gettype($value) == 'integer' )
			{
				$this->_values .= ( ($first)?':':', :' ).$key;
				$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_INT );
				$first = false;
			}
			elseif ( gettype($value) == 'double' )
			{
				$this->_values .= ( ($first)?':':', :' ).$key;
				$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				$first = false;
			}
			elseif ( gettype($value) == 'string' )
			{
				$this->_values .= ( ($first)?':':', :' ).$key;
				$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				$first = false;
			}
		}
	}
	
	public function initInsertSet( $insert, $set = '', array $binds = array() )
	{
		$this->_type = self::$TYPE_INSERT;
		$this->_insert = $insert;
		$this->_set = $set;
		$this->_binds = $binds;
	}
	
	public function initCreate( $createTable, array $getObjectVars )
	{
		$this->_type = self::$TYPE_CREATE;
		$this->_create = 'TABLE `'.$createTable.'` (';
		
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
					Flea\Debug::getInstance()->addError('No type declared for this SQL request');
				}
		}
		
		return '';
	}
	
	protected function getRequestCreate()
	{
		if(_DEBUG && $this->_create == '')
		{
			Flea\Debug::getInstance()->addError('For a TYPE_CREATE SQL query You must init the var "create"');
		}
		return 'CREATE '.$this->_create;
	}
	
	protected function getRequestRead()
	{
		if($this->_select == '')
		{
			$this->_select = '*';
		}
		if(_DEBUG && $this->_from == '')
		{
			Flea\Debug::getInstance()->addError('For a TYPE_SELECT SQL query You must init the var "from"');
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
	
	protected function getRequestInsert()
	{
		if(_DEBUG)
		{
			if($this->_insert == '')
			{
				Flea\Debug::getInstance()->addError('For a TYPE_INSERT SQL query You must init the var "insert"');
			}
		}
		$request = 'INSERT ' . $this->_insert;
		if($this->_values != '') { $request .= ' VALUES (' . $this->_values . ')'; }
		if($this->_set != '') { $request .= ' SET ' . $this->_groupBy; }
		if($this->_select != '') { $request .= ' SELECT ' . $this->_select; }
		return $request;
	}
	
	protected function getRequestUpdate()
	{
		if(_DEBUG)
		{
			if($this->_update == '')
			{
				Flea\Debug::getInstance()->addError('For a TYPE_UPDATE SQL query You must init the var "update"');
			}
			if($this->_set == '')
			{
				Flea\Debug::getInstance()->addError('For a TYPE_UPDATE SQL query You must init the var "set"');
			}
		}
		$request = 'UPDATE ' . $this->_update;
		if($this->_set != '') { $request .= ' SET ' . $this->_set; }
		if($this->_where != '') { $request .= ' WHERE ' . $this->_where; }
		if($this->_orderBy != '') { $request .= ' ORDER BY ' . $this->_orderBy; }
		if($this->_limit != '') { $request .= ' LIMIT ' . $this->_limit; }
		return $request;
	}
	
	protected function getRequestDelete()
	{
		if(_DEBUG)
		{
			if($this->_delete == '')
			{
				Flea\Debug::getInstance()->addError('For a TYPE_DELETE SQL query You must init the var "delete"');
			}
			if($this->_from == '')
			{
				Flea\Debug::getInstance()->addError('For a TYPE_DELETE SQL query You must init the var "from"');
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
