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
 * All simple methods usable after pages building.
 * You can use it during the initialization state.
 *
 * @author Namide
 */
class InitUtil
{
	private static $_INSTANCES = array();
    
    final private function __construct()
    {
        $this->reset();
    }
	
	/**
	 * Reset the object
	 */
    protected function reset() { }
	
	/**
	 * Get an absolute URL for a file at the root of the website
	 * 
	 * @param string $file		Name of the file
	 * @return string			Absolute URL of the file
	 */
	public function getRootAbsUrl( $file )
    {
        return _ROOT_URL.$file;
    }
	
	/**
	 * Get an absolute URL for a file in the template directory
	 * 
	 * @param string $file		Name of the file
	 * @return string			Absolute URL of the file
	 */
    public function getTemplateAbsUrl( $file )
    {
       return _ROOT_URL._TEMPLATE_DIRECTORY.$file;
    }
    
	/**
	 * Get an absolute URL for a file in the content directory
	 * 
	 * @param string $file		Name of the file
	 * @return string			Absolute URL of the file
	 */
    public function getContentAbsUrl( $file )
    {
       return _ROOT_URL._CONTENT_DIRECTORY.$file;
    }
	
	final private function __clone()
    {
        if ( _DEBUG )
		{
			Debug::getInstance()->addError( 'You can\'t clone a singleton' );
		}
    }
	
	/**
	 * Instance of the object
	 * 
	 * @return static		Instance of the object InitUtil
	 */
    public static function getInstance()
    {
        $c = get_called_class();
 
        if(!isset(self::$_INSTANCES[$c]))
        {
            self::$_INSTANCES[$c] = new $c;
        }
 
        return self::$_INSTANCES[$c];
    }
}
