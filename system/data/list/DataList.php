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
 * Replace Array, effective to save in data base
 * 
 * @author Namide
 */
class DataList
{
	public static $_EMPTY = null;
	
	protected $_datas;
	protected $_isAssoc;
	
	/**
	 * Construct the DataList in associative array or not
	 * 
	 * @param boolean $isAssociative
	 */
	public function __construct( $isAssociative = false )
	{
		$this->_datas = array();
		$this->_isAssoc = $isAssociative;
	}
	
	/**
	 * Add a value in this DataList.
	 * If DataList is not associative, don't add the key.
	 * 
	 * @param type $val		Value of the data
	 * @param type $key		Key of the data (for a non-associative array)
	 */
	public function add( $val, $key = null )
	{
		if ( $this->_isAssoc )
		{
			if ( _DEBUG && $key === null ) 
			{
				Debug::getInstance()->addError('Associative DataList must have a key');
			}
			elseif( _DEBUG && $this->hasKey($key) )
			{
				Debug::getInstance()->addError('The pair ['.$key.'=>'.$val.'] already exist');
			}
				
			$this->_datas[$key] = $val;
		}
		else
		{
			$this->_datas[] = $val;
		}
	}
	
	/**
	 * Replace a value for a key.
	 * If this is a non-assiciative array,
	 * you must use an unsigned integer for the key.
	 * 
	 * @param type $val		New value
	 * @param type $key		String for an associative array, otherwise unsigned integer
	 */
	public function update( $val, $key )
	{
		$this->_datas[$key] = $val;
	}
	
	/**
	 * Remove a data in the DataList.
	 * Key is not required.
	 * 
	 * @param type $val		Value to remove
	 * @param type $key		Key of the value (not required)
	 * @return boolean		Value is removed
	 */
	public function remove( $val, $key = null )
	{
		if ( !$this->hasValue($val) )
		{
			return false;
		}
		
		if($key === null)
		{
			$key = $this->getKey ($val);
			if ( $this->_isAssoc ) 
			{
				unset( $this->_datas[$key] );
			}
			else
			{
				array_splice($this->_datas, $key, 1);
			}
		}
		return true;
	}
	
	/**
	 * Get the key of the value
	 * 
	 * @param type $val		Value to search
	 * @return type			Key of the value
	 */
	public function getKey( $val )
	{
		return array_search( $val, $this->_datas );
	}

	/**
	 * Add multiples datas
	 * 
	 * @param array $values		Use an associative array if the DataList is associative
	 */
	public function addMultiple( array $values )
	{
		foreach ($values as $key => $val)
        {
			$this->add( $val, $key );
        }
	}
	
	/**
	 * Get value by his key
	 * 
	 * @param type $key		Key of the value
	 * @return type			Value of the key
	 */
	public function getValue( $key )
	{
		if ( !$this->hasKey($key) && _DEBUG )
		{
			Debug::getInstance()->addError('The key:'.$key.' don\'t exist');
		}
		if ( !$this->hasKey($key) )
		{
			return "";
		}
		return $this->_datas[$key];
	}
	
	/**
	 * Obtains the array (with all the keys and values)
	 * 
	 * @return type		Array of the DataList
	 */
	public function getArray()
	{
		return $this->_datas;
	}
	
	/**
	 * Change the content by a new array.
	 * 
	 * @param type $array		New array
	 */
	public function setByArray( $array )
	{
		$this->_datas = $array;
		$this->_isAssoc = array_keys($array) !== range(0, count($array) - 1);
	}
	
	/**
	 * Check if the value is in the array
	 * 
	 * @param type $val		Value to check
	 * @return boolean		Is in array
	 */
	public function hasValue( $val )
	{
		return in_array( $val, $this->_datas );
	}
	
	/**
	 * Check if the key is in the array
	 * 
	 * @param type $key		Key to check
	 * @return boolean		Is in array
	 */
	public function hasKey( $key )
	{
		return array_key_exists( $key, $this->_datas );
	}
	
	/**
	 * Length of the DataList
	 * 
	 * @return int		Length of the DataList (unsigned integer)
	 */
	public function length()
	{
		return count($this->_datas);
	}
	
	/**
	 * Get an empty DataList to avoid the construction.
	 * You must use it temporarily, because a new call to this function void the previous DataList
	 * 
	 * @param boolean $isAssociative		Precise if the new DataList is associative
	 * @return DataList						New DataList
	 */
	public static function getEmptyDataList( $isAssociative = false )
	{
		if ( DataList::$_EMPTY === null )
		{
			DataList::$_EMPTY = new DataList( $isAssociative );
		}
		DataList::$_EMPTY->_isAssoc = $isAssociative;
		return DataList::$_EMPTY;
	}
}
