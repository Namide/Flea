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
 * An object DataBaseCRUD corresponds to a table,
 * with this method it is simple to interact with the table
 * 
 * @author Namide
 */
class DataBaseCRUD {

	private static $_INSTANCE = array();
	private $_table;
	private $_db;

	private function __construct($tableName) {
		$this->_table = $tableName;
		$this->_db = DataBase::getInstance(_DB_DSN_CONTENT);
	}

	/**
	 * Create the table from rows.
	 * 
	 * @param array $rows	List of rows of the table (keys for names, values for example)
	 * @return bool			true if successful, else without
	 */
	public function create(array $rows) {
		$request = SqlQuery::getTemp(SqlQuery::$TYPE_CREATE);
		$request->initCreate($this->_table, $rows);
		return $this->_db->execute($request);
	}

	/**
	 * Get lines from the database
	 * 
	 * @param array $whereList		filter in associative array
	 * @param array $whereSign		signs of the $whereList ('=', '<', 'LIKE'...)
	 * @param string $orderBy		sorting of the result data
	 * @param string $limit			count of results
	 * @return array				List of results
	 */
	public function read(array $whereList, array $whereSign = null, $orderBy = '', $limit = '') {
		$request = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$request->initSelect('*', $this->_table, $whereList, null, $orderBy, $limit);
		return $this->_db->fetchAll($request);
	}

	/**
	 * Insert a line in the table
	 * 
	 * @param array $values		Associative array with datas (keys for rows names)
	 * @return bool				true if successful, else without
	 */
	public function insert($values) {
		$request = SqlQuery::getTemp(SqlQuery::$TYPE_INSERT);
		$request->initInsertValues($this->_table, $values);
		return $this->_db->execute($request);
	}

	/**
	 * Update a line in the table
	 * 
	 * @param array $setList		Associative array with datas (keys for rows names)
	 * @param array $whereList		Filter in associative array
	 * @return type					true if successful, else without
	 */
	public function update(array $setList, array $whereList = null) {
		$request = SqlQuery::getTemp(SqlQuery::$TYPE_UPDATE);
		$request->initUpdateSet($this->_table, $setList, $whereList);
		return $this->_db->execute($request);
	}

	/**
	 * Delete line(s) in the table
	 * 
	 * @param array $whereList		Filter in associative array
	 * @return type					true if successful, else without
	 */
	public function delete(array $whereList) {
		$request = SqlQuery::getTemp(SqlQuery::$TYPE_DELETE);
		$request->initDelete($this->_table, $whereList);
		return $this->_db->execute($request);
	}

	/**
	 * Test if the table exist
	 * 
	 * @return boolean		Exist
	 */
	public function exist() {
		return $this->_db->exist($this->_table);
	}

	/**
	 * Get the instance of the object
	 * 
	 * @param string $tableName		Name of the table
	 * @return DataBaseCRUD			DataBaseCRUD for this table
	 */
	public static function getInstance($tableName) {
		if (!isset(self::$_INSTANCE[$tableName])) {
			self::$_INSTANCE[$tableName] = new DataBaseCRUD($tableName);
		}
		return self::$_INSTANCE[$tableName];
	}

	private function __clone() {
		
	}

}
