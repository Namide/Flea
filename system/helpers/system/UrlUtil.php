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
 * URL managment
 *
 * @author Namide
 */
class UrlUtil
{
	private static $_INSTANCE = null;

    private $_arg = '_u';
	/**
	 * Label of the variable GET that content the URL.
	 * If you change it, change to the root .htaccess
	 * 
	 * @return string
	 */
	public function getPageGetArg() { return self::$_arg; }
	
	public $_basePageUrl;
	
    final private function __construct()
    {
    	$this->reset();
    }
	
!!!!!!!!!!!	
	
	public function reset()
	{
		$this->_basePageUrl = 'index.php?'.$this->_arg.'=';
		
		$gets = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
		$gets = $this->getCleanGets( $gets );
		
		if( isset( $gets[self::$_arg] ) )
        {
			$this->url = $this->getCleanUrl();
        }
        // if no URL -> redirection to good url
        else
        {
			$path = $_SERVER['PHP_SELF'];
			$file = basename($path);
			if ( $file == 'index.php' )
			{
				$pages = PageList::getInstance();
				$page = $pages->getDefaultPage();
				$this->url = $page->getUrl();

				header( 'Location:'.InitUtil::getInstance()->urlPageToAbsUrl( $page->getUrl() ) );
				exit();
			}
        }
	}
	
	private function getCleanGets( &$gets )
	{
		$oldGet = $gets;
		
		foreach ($oldGet as $key1 => $value1)
		{
			$expl = explode( '?', $value1 );
			foreach ($expl as $key => $value)
			{
				
			}
		}
		
		
		$urlParts = explode('?', $totalUrl);
		$l = count( $urlParts );
		for( $i = 1; $i < $l; $i++ )
		{
			$microParts = explode('&', $urlParts[$i]);
			$l2 = count($microParts);
			for( $j = 0; $j < $l2; $j++ )
			{
				$this->addGet( $microParts[$j] );
			}
		}
		
		return htmlentities($urlParts[0]);//filter_input('INPUT_GET', 'page', 'FILTER_SANITIZE_URL');
	}
	
	private function addGet( $get )
	{
		$a = explode('=', $get);
		$_GET[$a[0]] = $a[1];
	}

	/**
	 * 
	 * @return string
	 */
	public static function getURICacheID( $pageUrl = -1 )
	{
		$invalid = array( /*'/'=>'-',*/ '\\'=>'-', ':'=>'-', '?'=>'-', '"'=>'-', '*'=>'-', '<'=>'-', '>'=>'-', '|'=>'-' );
		if( $pageUrl != -1 )
		{
			$url = str_replace(array_keys($invalid), array_values($invalid), htmlentities( $pageUrl ) );
		}
		elseif( isset( $_GET[self::$_arg] ) )
		{
			$url = str_replace(array_keys($invalid), array_values($invalid), htmlentities( $_GET[self::$_arg] ) );
		}
		else
		{
			$url = 'empty';
		}

		foreach ( $_GET as $key => $value )
		{
			if ( $key != self::$_arg ) 
			{
				$url .= '-'.$key.'-'.$value;
			}
		}

		return $url;
	}
	
	/**
	 * 
	 * @param string $arg
	 * @return boolean
	 */
	public static function hasGet( $label )
	{
		return isset( $_GET[$label] );
	}
	
	/**
	 * 
	 * @param string $arg
	 * @return string
	 */
	public static function getGet( $label )
	{
		if( self::hasGet( $label ) ) return htmlentities( $_GET[$label] );
		return NULL;
	}


	final public function __clone()
    {
        trigger_error( 'You can\'t clone.', E_USER_ERROR );
    }
 
	/**
	* @return UrlUtil
	*/
    final public static function getInstance()
    {
        if(!isset(self::$_INSTANCE))
        {
            self::$_INSTANCE = new self;
        }
 
        return self::$_INSTANCE;
    }
}
