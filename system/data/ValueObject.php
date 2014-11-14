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
 * Datas used to the return of a function.
 * With this object you can detect errors and you can list their informations.
 *
 * @author namide.com
 */
class ValueObject
{
	/**
	 * If true, an error has occurred.
	 * Check the error list to find your error.
	 * 
	 * @var bool	If false, check the return in the propertie content
	 */
	public $error = false;
	
	/**
	 * All the errors who have occurred.
	 * 
	 * @var array	List of the errors
	 */
	public $errorList;
	
	/**
	 * Return of the method.
	 * 
	 * @var type	Before recover this one, check the errors
	 */
	public $content;
	
	/**
	 * Construct the reponse of a function with this object.
	 * 
	 * @param type $content		Return of the function
	 * @param type $error		True if an error has occurred
	 */
	public function __construct( $content, $error = false, $errorList = null )
    {
		$this->error = $error;
		$this->content = $content;
		if ( $errorList !== null )
		{
			$this->errorList = $errorList;
		}
		else
		{
			$this->errorList = array();
		}
    }
}
