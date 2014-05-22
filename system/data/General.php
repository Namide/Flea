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
 * All the general data changeable (current page, state...)
 *
 * @author Namide
 */
class General
{
	protected static $_INSTANCE;

	protected $_pagesInitialised = false;
	/**
	 * All page initialised
	 * 
	 * @return bool
	 */
	public function getPagesInitialised() { return $this->_pagesInitialised; }
	
	protected $_pageBuilded = false;
	/**
	 * Current page build
	 * 
	 * @return bool
	 */
	public function getPagesBuilded() { return $this->_pageBuilded; }
	
	protected $_currentPageName;
	/**
	 * Name of the current page
	 * 
	 * @return string
	 */
	public function getCurrentPageName()
	{
		if ( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			trigger_error( 'You can\'t access to the current page name if the pages isn\'tinitialised', E_USER_ERROR );
		}
		return $this->_currentPageName;
	}
	
	protected $_currentPage;
	/**
	 * Current page
	 * 
	 * @return Page
	 */
	public function getCurrentPage()
	{
		if ( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			trigger_error( 'You can\'t access to the current page if the pages isn\'tinitialised', E_USER_ERROR );
		}
		return $this->_currentPage;
	}
	
	protected $_currentLang;
	/**
	 * Current language
	 * 
	 * @return string
	 */
	public function getCurrentLang()
	{
		if ( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			trigger_error( 'You can\'t access to the current language if the pages isn\'tinitialised', E_USER_ERROR );
		}
		return $this->_currentLang;
	}
	
	protected $_currentGetUrl;
	/**
	 * Current $_GET
	 * 
	 * @return array
	 */
	public function getCurrentGetUrl()
	{
		if ( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			trigger_error( 'You can\'t access to the current language if the pages isn\'tinitialised', E_USER_ERROR );
		}
		return $this->_currentGetUrl;
	}
	
	protected $_currentPageUrl;
	/**
	 * Current page URL
	 * 
	 * @return string
	 */
	public function getCurrentPageUrl()
	{
		if ( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			trigger_error( 'You can\'t access to the current language if the pages isn\'tinitialised', E_USER_ERROR );
		}
		return $this->_currentPageUrl;
	}
	
	/**
	 * Change the current page
	 * 
	 * @param Page $page
	 */
	public function setCurrentPage( &$page )
	{
		$this->_currentPage = $page;
		$this->_currentLang = $page->getLang();
		$this->_currentPageName = $page->getName();
		$this->_pagesInitialised = true;
	}
	
	/**
	 * Change the current URL
	 * 
	 * @param Page $page
	 */
	public function setCurrentUrl( $pageUrl, array $getUrl = null )
	{
		if ( $getUrl === null )
		{
			$getUrl = array();
		}
		
		$this->_currentPageUrl = $pageUrl;
		$this->_currentGetUrl = $getUrl;
	}
	
	
	protected function __construct() { }
	protected function __clone() { }

	/**
	 * Get the instance of General
	 * 
	 * @return General
	 */
	public static function getInstance()
	{
		if ( !isset(self::$_INSTANCE) )
		{
			self::$_INSTANCE = new self();
		}

		return self::$_INSTANCE;
	}
}
