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
 * Emmiting debuging messages, warnings and errors
 *
 * @author Namide
 */
class Debug
{
	private static $_INSTANCE;
	
	private $_errorList;
	
	/**
	 * Save an error message
	 * 
	 * @param string $msg
	 */
	public function addError( $msg )
	{
		array_push( $this->_errorList, $msg );
	}
	
	/**
	 * Dispatch all errors messages
	 * 
	 * @param string $msg
	 */
	public function dispatchErrors()
	{
		if ( count($this->_errorList) > 0 )
		{
			echo '<script>alert('.implode('\n', $this->_errorList).')</script>';
		}
	}
	
	final private function __construct()
    {
		$this->_errorList = array();
    }
	
	/**
	 * Unclonable
	 */
    final public function __clone()
    {
		if ( _DEBUG )
		{
			trigger_error( 'You can\'t clone.', E_USER_ERROR);
		}
    }
	
	/**
	 * Instance of the langListObject
	 * 
	 * @return LangList
	 */
    final public static function getInstance()
    {
        if( !isset( self::$_INSTANCE ) )
        {
            self::$_INSTANCE = new self();
        }
 
        return self::$_INSTANCE;
    }
	
}
