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
class DataList
{
	public static $_EMPTY = null;
	
	protected $_datas;
	protected $_isAssoc;
	
	public function __construct( $isAssociative = false )
	{
		$this->_datas = array();
		$this->_isAssoc = $isAssociative;
	}
	
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
	
	public function update( $val, $key )
	{
		$this->_datas[$key] = $val;
	}
	
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
	
	public function getKey( $val )
	{
		return array_search( $val, $this->_datas );
	}


	public function addMultiple( array $values )
	{
		foreach ($values as $key => $val)
        {
			$this->add( $val, $key );
        }
	}
	
	public function getValue( $key )
	{
		return $this->_datas[$key];
	}
	
	public function getArray()
	{
		return $this->_datas;
	}
	
	public function setByArray( $array )
	{
		$this->_datas = $array;
		$this->_isAssoc = array_keys($array) !== range(0, count($array) - 1);
	}
	
	public function hasValue( $val )
	{
		return in_array( $val, $this->_tags );
	}
	
	public function hasKey( $key )
	{
		return array_key_exists( $key, $this->_tags );
	}
	
	public function length()
	{
		return count($this->_datas);
	}
	
	/**
	 * 
	 * @param boolean $isAssociative
	 * @return DataList
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
