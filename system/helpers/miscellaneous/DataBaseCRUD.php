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
 * @author Namide
 */
class DataBaseCRUD
{
	private static $_INSTANCE = array();
	
	private $_table;
	private $_db;
	
	private function __construct( $tableName ) 
    {
		$this->_table = $tableName;
		$this->_db = DataBase::getInstance( _DB_DSN_CONTENT );
    }
	
	public function create( array $rows )
	{
		$request = SqlQuery::getTemp( SqlQuery::$TYPE_CREATE );
		$request->initCreate( $this->_table, $rows );
		return $this->_db->execute( $request );
	}

	public function read( array $whereList, array $whereSign = null, $orderBy = '', $limit = ''  )
	{
		$request = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
		$request->initSelect('*', $this->_table, $whereList, $signList, $orderBy, $limit);
		return $this->_db->fetchAll( $request );
	}
	
	public function insert( $values )
	{
		$request = SqlQuery::getTemp( SqlQuery::$TYPE_INSERT );
		$request->initInsertValues( $this->_table, $values );
		return $this->_db->execute( $request );
	}
	
	public function update( array $setList, array $whereList = null )
	{
		$request = SqlQuery::getTemp( SqlQuery::$TYPE_UPDATE );
		$request->initUpdateSet( $this->_table, $setList, $whereList);
		return $this->_db->execute( $request );
	}
	
	public function delete( array $whereList )
	{
		$request = SqlQuery::getTemp( SqlQuery::$TYPE_DELETE );
		$request->initDelete( $this->_table, $whereList );
		return $this->_db->execute( $request );
	}
	
	/**
	 * Test if the table exist
	 * 
	 * @return boolean		Exist
	 */
	public function exist()
	{
		return $this->_db->exist($this->_table);
	}
	
	/**
	 * Get the instance of the object
	 * 
	 * @param string $tableName		Name of the table
	 * @return DataBaseCRUD			DataBaseCRUD for this table
	 */
	public static function getInstance( $tableName ) 
    {
		if(!isset(self::$_INSTANCE[$tableName]))
        {
            self::$_INSTANCE[$tableName] = new DataBaseCRUD($tableName);
        }
        return self::$_INSTANCE[$tableName];
    }
	
	/**
	 * Can't clone a multiton
	 */
	final public function __clone()
    {
        if ( _DEBUG ) 
		{
			Debug::getInstance()->addError('You can\'t clone a multiton');
		}
    }
}
