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
 * Requests data helper.
 * Use this class to create SQL requests.
 * 
 * @author Namide
 */
class SqlQuery
{
	/**
	 * Flag for a create request
	 * 
	 * @var int 
	 */
	public static $TYPE_CREATE = 1;
	
	/**
	 * Flag for a select request
	 * 
	 * @var int 
	 */
	public static $TYPE_SELECT = 2;
	
	/**
	 * Flag for an update request
	 * 
	 * @var int 
	 */
	public static $TYPE_UPDATE = 3;
	
	/**
	 * Flag for a insert request
	 * 
	 * @var int 
	 */
	public static $TYPE_INSERT = 4;
	
	/**
	 * Flag for a delete request
	 * 
	 * @var int 
	 */
	public static $TYPE_DELETE = 5;
	
	/**
	 * Flag for a multiple insert request
	 * 
	 * @var int 
	 */
	public static $TYPE_MULTI_INSERT = 6;
	
	//private static $_SALTZ = 0;
	
	private static $_TEMP = null;
	
	/**
	 * Create a temporary request object.
	 * Used to reduce memory (avoid to create other request objects)
	 * 
	 * @return self		SqlQuery object
	 */
	public static function getTemp( $type = 0 )
	{
		if ( self::$_TEMP === null )
		{
			self::$_TEMP = new SqlQuery();
		}
		else
		{
			self::$_TEMP->clean( $type );
		}
		return self::$_TEMP;
	}
	
	/////////////////////////////
	//
	//			TYPE
	//
	/////////////////////////////
	
	private $_type;
	
	/**
	 * Type of the request :
	 * - self::$TYPE_CREATE 
	 * - self::$TYPE_SELECT 
	 * - self::$TYPE_UPDATE 
	 * - self::$TYPE_INSERT 
	 * - self::$TYPE_DELETE
	 * 
	 * @return int				Type of the request
	 */
	public function getType() { return $this->_type; }
	
	/**
	 * Type of the request :
	 * - self::$TYPE_CREATE 
	 * - self::$TYPE_SELECT 
	 * - self::$TYPE_UPDATE 
	 * - self::$TYPE_INSERT 
	 * - self::$TYPE_DELETE
	 * 
	 * @param string $type		Type of the request
	 */
	public function setType( $type ) { $this->_type = $type; }
	
	/////////////////////////////
	//
	//			BINDS
	//
	/////////////////////////////
	
	private $_binds;
	
	/**
	 * Used in PDO to securise datas injected in the data base
	 * 
	 * @return array	Binds (like binds in PDO object)
	 */
	public function getBinds() { return $this->_binds; }
	
	/**
	 * Used in PDO to securise datas injected in the data base
	 * 
	 * @param array $binds		List of binds in associative array
	 */
	public function setBinds( array $binds ) { $this->_binds = $this->_binds + $binds; }
	
	/**
	 * Add a data in the binging list.
	 * 
	 * @param string $key				Label of the data
	 * @param string $value			value of the data
	 * @param string $pdoParamType	Type of the data (in PDO format)
	 */
	public function addBind( $key, $value, $pdoParamType )
	{
		$this->_binds[] = array($key, $value, $pdoParamType);
	}
	
	/////////////////////////////
	//
	//			CREATE
	//
	/////////////////////////////
	
	private $_create;
	
	/**
	 * SQL CREATE.
	 * like 'TABLE table_name ( row1 datas_type, row2 datas_type )'
	 * 
	 * @return string
	 */
	public function getCreate() { return $this->_create; }
	
	/**
	 * SQL CREATE.
	 * example: $create = 'TABLE table_name ( row1 datas_type, row2 datas_type )'
	 * 
	 * @param string $create
	 */
	public function setCreate( $create )
	{
		$this->_type = self::$TYPE_CREATE;
		$this->_create = $create;
	}
	
	/////////////////////////////
	//
	//			READ
	//
	/////////////////////////////
	
	private $_select;
	
	/**
	 * SQL SELECT.
	 * like 'row_name FROM table_name'
	 * 
	 * @return string
	 */
	public function getSelect() { return $this->_select; }
	
	/**
	 * SQL SELECT.
	 * example: $select = 'row_name FROM table_name'
	 * 
	 * @param string $select
	 */
	public function setSelect( $select )
	{
		$this->_type = self::$TYPE_SELECT;
		$this->_select = $select;
	}
	
	private $_where;
	
	/**
	 * SQL WHERE.
	 * like 'row_name = row_value'
	 * 
	 * @return string
	 */
	public function getWhere() { return $this->_where; }
	
	/**
	 * SQL WHERE.
	 * example $where = 'row_name = row_value'
	 * 
	 * @param string $where
	 */
	public function setWhere( $where ) { $this->_where = $where; }
	
	private $_from;
	
	/**
	 * SQL FROM.
	 * like 'table_name'
	 * 
	 * @return string
	 */
	public function getFrom() { return $this->_from; }
	
	/**
	 * SQL FROM.
	 * example $from = 'table_name'
	 * 
	 * @param string $from
	 */
	public function setFrom( $from ) { $this->_from = $from; }
	
	private $_groupBy;
	
	/**
	 * SQL GROUP BY.
	 * like 'column_name'
	 * 
	 * @return string
	 */
	public function getGroupBy() { return $this->_groupBy; }
	
	/**
	 * SQL GROUP BY.
	 * example $groupBy = 'column_name'
	 * 
	 * @param string $groupBy
	 */
	public function setGroupBy( $groupBy ) { $this->_groupBy = $groupBy; }
	
	private $_having;
	
	/**
	 * SQL HAVING.
	 * like 'SUM(cost) > 40'
	 * 
	 * @return string
	 */
	public function getHaving() { return $this->_having; }
	
	/**
	 * SQL HAVING.
	 * example: $having = 'SUM(cost) > 40'
	 * 
	 * @param string $having
	 */
	public function setHaving( $having ) { $this->_having = $having; }
	
	private $_orderBy;
	
	/**
	 * SQL ORDER BY.
	 * Like 'column1'
	 * 
	 * @return string
	 */
	public function getOrderBy() { return $this->_orderBy; }
	
	/**
	 * SQL ORDER BY.
	 * Example: $orderBy = 'column1'
	 * 
	 * @param string $orderBy
	 */
	public function setOrderBy( $orderBy ) { $this->_orderBy = $orderBy; }
	
	private $_limit;
	
	/**
	 * SQL LIMIT.
	 * Like '10'
	 * 
	 * @return string
	 */
	public function getLimit() { return $this->_limit; }
	
	/**
	 * SQL LIMIT.
	 * Example: $limit = '10'
	 * 
	 * @param string $limit
	 */
	public function setLimit( $limit ) { $this->_limit = $limit; }
	
	/////////////////////////////
	//
	//			INSERT
	//
	/////////////////////////////
	
	private $_insert;
	
	/**
	 * SQL INSERT.
	 * Like 'table_name'
	 * 
	 * @return string
	 */
	public function getInsert() { return $this->_insert; }
	
	/**
	 * SQL INSERT.
	 * Example: $insert = 'table_name'
	 * 
	 * @param string $insert
	 */
	public function setInsert( $insert )
	{
		$this->_type = self::$TYPE_INSERT;
		$this->_insert = $insert;
	}
	
	private $_values;
	
	/**
	 * SQL VALUES.
	 * Like '("val1", "val2")'
	 * 
	 * @return type
	 */
	public function getValues() { return $this->_values; }
	
	/**
	 * SQL VALUES.
	 * Example: $values = '("val1", "val2")'
	 * 
	 * @param string $values
	 */
	public function setValues( $values ) { $this->_values = $values; }

	/////////////////////////////
	//
	//			UPDATE
	//
	/////////////////////////////
	
	private $_update;
	
	/**
	 * SQL UPDATE.
	 * Like 'table_name'
	 * 
	 * @return string
	 */
	public function getUpdate() { return $this->_update; }
	
	/**
	 * SQL UPDATE.
	 * example: $update = 'table_name'
	 * 
	 * @param string $update
	 */
	public function setUpdate( $update )
	{
		$this->_type = self::$TYPE_UPDATE;
		$this->_update = $update;
	}
	
	private $_set;
	
	/**
	 * SQL SET.
	 * Like 'name_column_1 = "value"'
	 * 
	 * @return string
	 */
	public function getSet() { return $this->_set; }
	
	/**
	 * SQL SET.
	 * example: $set = 'name_column_1 = "value"'
	 * 
	 * @param string $set
	 */
	public function setSet( $set ) { $this->_set = $set; }
	
	/////////////////////////////
	//
	//			DELETE
	//
	/////////////////////////////
	
	private $_delete;
	
	/**
	 * SQL DELETE.
	 * Like '*'
	 * 
	 * @return string
	 */
	public function getDelete() { return $this->_delete; }
	
	/**
	 * SQL DELETE.
	 * Example: $delete = '*'
	 * 
	 * @param string $delete
	 */
	public function setDelete( $delete )
	{
		$this->_type = self::$TYPE_DELETE;
		$this->_delete = $delete;
	}
	
	/**
	 * Initialize the query with the type :
	 * - SqlQuery::$TYPE_CREATE 
	 * - SqlQuery::$TYPE_SELECT 
	 * - SqlQuery::$TYPE_UPDATE 
	 * - SqlQuery::$TYPE_INSERT 
	 * - SqlQuery::$TYPE_DELETE
	 * 
	 * @param int $type		Type of the query
	 */
	public function __construct( $type = 0 )
	{
		$this->clean( $type );
	}
	
	/**
	 * Clean datas in your object
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
	 * Initialize you SqlQuery with a self::$TYPE_MULTI_INSERT request.
	 * 
	 * @param string $tableName		Name of the table		
	 * @param array $keys			List of keys for values
	 * @param array $values			Bidimentionnal array with datas
	 */
	public function initMultiInsertValues( $tableName, array $keys, array $values )
	{
		$this->_type = self::$TYPE_MULTI_INSERT;
		$this->_insert = 'INTO `' . $tableName . '` (' . implode(', ', $keys) . ')';
		
		$this->_values = array();
		foreach ($values as $line)
		{
			$tmp = array();
			foreach ($line as $row)
			{
				$tmp[] = '?';
			}
			$this->_values[] = implode(', ', $tmp);
		}
		$this->_values = implode('), (', $this->_values);
		
		$this->_binds = $values;
	}
	
	/**
	 * Add more values to an SqlQuery with the type self::$TYPE_INSERT.
	 * 
	 * @param array $values		Associative array with datas (keys for rows names)
	 */
	/*public function addMultiInsert( array $values )
	{
		if ($this->_values == '' && _DEBUG)
		{
			Debug::getInstance()->addError('You must to initialize the insertion query with values before adding mutiple insertions');
		}
		
		$this->_values .= '), (';
		$first = true;
		self::$_SALTZ++;
		foreach ( $values as $key => $value )
		{
			if ( gettype($value) == 'boolean' )
			{
				$this->_values .= ( ($first) ? ':' : ', :' ) . $key . self::$_SALTZ . '_';
				$this->_binds[] = array( ':' . $key . self::$_SALTZ . '_', ( ($value) ? '1' : '0'), \PDO::PARAM_BOOL );
				$first = false;
			}
			elseif ( gettype($value) == 'integer' )
			{
				$this->_values .= ( ($first) ? ':' : ', :' ) . $key . self::$_SALTZ . '_';
				$this->_binds[] = array( ':' . $key . self::$_SALTZ . '_', $value, \PDO::PARAM_INT );
				$first = false;
			}
			elseif ( gettype($value) == 'double' )
			{
				$this->_values .= ( ($first) ? ':' : ', :' ) . $key . self::$_SALTZ . '_';
				$this->_binds[] = array( ':' . $key . self::$_SALTZ . '_', $value, \PDO::PARAM_STR );
				$first = false;
			}
			elseif ( gettype($value) == 'string' )
			{
				$this->_values .= ( ($first) ? ':' : ', :' ) . $key . self::$_SALTZ . '_';
				$this->_binds[] = array( ':' . $key . self::$_SALTZ . '_', $value, \PDO::PARAM_STR );
				$first = false;
			}
		}
	}*/
	
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
		$this->_create .= ' )';
	}
	
	/**
	 * Initialize you SqlQuery with a self::$TYPE_DELETE request.
	 * 
	 * @param string $from			Name of the table		
	 * @param array $whereList		filter in associative array
	 */
	public function initDelete( $from, array $whereList )
	{
		$this->_type = self::$TYPE_DELETE;
		//$this->_delete = ($tableName == '') ? '' : 'FROM `'.$tableName.'`';
		$this->_from = $from;
		
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
				$output .= ( ($first)?'':$strGlue ).$key.' '.$sign.' :'.$key;
				if ( !array_key_exists(':'.$key, $this->_binds) )
				{
					$this->_binds[] = array( ':'.$key, (($value)?'1':'0'), \PDO::PARAM_BOOL );
				}
				$first = false;
			}
			elseif ( gettype($value) == 'integer' )
			{
				$output .= ( ($first)?'':$strGlue ).$key.' '.$sign.' :'.$key;
				if ( !array_key_exists(':'.$key, $this->_binds) )
				{
					$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_INT );
				}
				$first = false;
			}
			elseif ( gettype($value) == 'double' )
			{
				$output .= ( ($first)?'':$strGlue ).$key.' '.$sign.' :'.$key;
				if ( !array_key_exists(':'.$key, $this->_binds) )
				{
					$this->_binds[] = array( ':'.$key, $value, \PDO::PARAM_STR );
				}
				$first = false;
			}
			elseif ( gettype($value) == 'string' )
			{
				$output .= ( ($first)?'':$strGlue ).$key.' '.$sign.' :'.$key;
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
			
			case self::$TYPE_MULTI_INSERT:
				return $this->getRequestMuliInsert();
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
	private function getRequestCreate()
	{
		if(_DEBUG && $this->_create == '')
		{
			Debug::getInstance()->addError('For a TYPE_CREATE SQL query You must init the var "create"');
		}
		return 'CREATE ' . $this->_create . ';';
	}
	
	/**
	 * Generate the string request to select rows in a table
	 * 
	 * @return string		Request in SQL to select rows in a table
	 */
	private function getRequestRead()
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
		return $request . ';';
	}
	
	/**
	 * Generate the string request to insert lines in a table
	 * 
	 * @return string		Request in SQL to insert lines in a table
	 */
	private function getRequestInsert()
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
		return $request . ';';
	}
	
	private function getRequestMuliInsert()
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
		return $request . ';';
	}
	
	/**
	 * Generate the string request to update line in a table
	 * 
	 * @return string		Request in SQL to update line in a table
	 */
	private function getRequestUpdate()
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
		return $request . ';';
	}
	
	/**
	 * Generate the string request to delete lines
	 * 
	 * @return string		Generate the string request to delete lines
	 */
	private function getRequestDelete()
	{
		if(_DEBUG)
		{
			/*if($this->_delete == '')
			{
				Debug::getInstance()->addError('For a TYPE_DELETE SQL query You must init the var "delete"');
			}*/
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
		return $request.';';
	}
}
