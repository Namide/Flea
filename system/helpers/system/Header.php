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
 * Used for write and read files
 *
 * @author Namide
 */
class Header
{
	private static $_INSTANCE;
	
	public function getHeaderOfPage($format)
	{
		switch ($format)
		{
			case Page::$FORMAT_HTML:
				return 'Content-Type: text/html';
				break;
			case Page::$FORMAT_CSS:
				return 'Content-Type: text/css';
				break;
			case Page::$FORMAT_JS:
				return 'Content-Type: application/javascript';
				break;
			case Page::$FORMAT_XML:
				return 'Content-Type: text/xml';
				break;
			case Page::$FORMAT_JSON:
				return 'Content-Type: application/json';
				break;
			case Page::$FORMAT_PDF:
				return 'Content-Type: application/pdf';
				break;
			case Page::$FORMAT_ZIP:
				return 'Content-Type: application/zip';
				break;
		}
		return '';
	}
	
	public function appliHeaderOfPage(Page &$page)
	{
		if ( $page->getPhpHeader() != '' )
		{
			header( $page->getPhpHeader() );
		}
		
		$this->appliHeaderByFormat( $page->getFormat() );
	}
	
	public function appliHeaderByFormat( $format )
	{
		$headForm = $this->getHeaderOfPage( $format );
		if ( $headForm != '' )
		{
			header( $headForm );
		}
	}
	
	
	final private function __construct() { }
	
	final private function __clone() { }
	
	/**
	 * Instance of the Header
	 * 
	 * @return self		Instance of the Header
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
