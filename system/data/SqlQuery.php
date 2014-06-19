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

/**
 *
 * @author Namide
 */
class SqlQuery
{
	public static $TYPE_CREATE = 1;
	public static $TYPE_READ = 2;
	public static $TYPE_UPDATE = 3;
	public static $TYPE_INSERT = 4;
	public static $TYPE_DELETE = 5;
	
	protected static $_TEMP = null;
	public static function getTemp()
	{
		if ( self::$_TEMP === null ) { self::$_TEMP = new SqlQuery(); }
		return self::$_TEMP;
	}
	
	protected $_type;
	public function getType() { return $this->_type; }
	public function setType( $type ) { $this->_type = $type; }
	
	protected $_binds;
	public function getBinds() { return $this->_binds; }
	public function setBinds( array $binds ) { $this->_binds = $this->_binds + $binds; }
	public function addBind( $key, $value ) { $this->_binds[$key] = $value; }
	
	
	// CREATE
	protected $_create;
	public function setCreate( $create )
	{
		$this->_type = self::$TYPE_CREATE;
		$this->_create = $create;
	}
	
	// READ
	protected $_select;
	public function setSelect( $select )
	{
		$this->_type = self::$TYPE_READ;
		$this->_select = $select;
	}
	
	protected $_where;
	public function setWhere( $where ) { $this->_where = $where; }
	
	protected $_from;
	public function setFrom( $from ) { $this->_from = $from; }
	
	protected $_groupBy;
	public function setGroupBy( $groupBy ) { $this->_groupBy = $groupBy; }
	
	protected $_having;
	public function setHaving( $having ) { $this->_having = $having; }
	
	protected $_orderBy;
	public function setOrderBy( $orderBy ) { $this->_orderBy = $orderBy; }
	
	protected $_limit;
	public function setLimit( $limit ) { $this->_limit = $limit; }
	
	// INSERT
	protected $_insert;
	public function setInsert( $insert )
	{
		$this->_type = self::$TYPE_INSERT;
		$this->_insert = $insert;
	}
	
	protected $_into;
	public function setInto( $into ) { $this->_into = $into; }

	protected $_values;
	public function setValues( $values ) { $this->_values = $values; }

	// UPDATE
	protected $_update;
	public function setUpdate( $update )
	{
		$this->_type = self::$TYPE_UPDATE;
		$this->_update = $update;
	}
	
	protected $_set;
	public function setSet( $set ) { $this->_set = $set; }
	
	// DELETE
	protected $_delete;
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
		$this->_into = '';
		$this->_values = '';
		$this->_delete = '';
		$this->_binds = array();
	}
	
	public function initSelect( $select, $from, $where = '', $orderBy = '', $limit = '' )
	{
		$this->_type = self::$TYPE_READ;
		$this->_select = $select;
		$this->_from = $from;
		$this->_where = $where;
		$this->_orderBy = $orderBy;
		$this->_limit = $limit;
	}
	
	public function initInsertValues( $insert, $into, $values = '', array $binds = array() )
	{
		$this->_type = self::$TYPE_INSERT;
		$this->_insert = $insert;
		$this->_into = $into;
		$this->_values = $values;
		$this->_binds = $binds;
	}
	
	public function initInsertSet( $insert, $into, $set = '', array $binds = array() )
	{
		$this->_type = self::$TYPE_INSERT;
		$this->_insert = $insert;
		$this->_into = $into;
		$this->_set = $set;
		$this->_binds = $binds;
	}
	
	public function getRequest()
	{
		switch ($this->_type)
		{
			case self::$TYPE_CREATE:
				return $this->getRequestCreate();
				break;
				
			case self::$TYPE_READ:
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
			Flea\Debug::getInstance()->addError('For a TYPE_READ SQL query You must init the var "from"');
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
			if($this->_into == '')
			{
				Flea\Debug::getInstance()->addError('For a TYPE_INSERT SQL query You must init the var "into"');
			}
		}
		$request = 'INSERT ' . $this->_insert;
		$request .= ' INTO ' . $this->_into;
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
