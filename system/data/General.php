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
	 * @return bool		true if the pages are initialised, otherwise false
	 */
	public function isPagesInitialized() { return $this->_pagesInitialised; }
	
	/**
	 * Database of the pages initialized
	 * 
	 * @return bool		true if the databases are initialised, otherwise false
	 */
	public function isDBInitialized()
	{
		return DataBase::getInstance( _DB_DSN_PAGE )->exist( DataBase::objectToTableName(Page::getEmptyPage() ) );
	}
	
	/**
	 * All page initialised
	 */
	public function initializesPages()
	{
		if (file_exists(_CONTENT_DIRECTORY.'initBegin.php') )
		{
			include _CONTENT_DIRECTORY.'initBegin.php';
		}

		if (file_exists(_CONTENT_DIRECTORY.'initLang.php') )
		{
			include _CONTENT_DIRECTORY.'initLang.php';
		}
		elseif ( _DEBUG )
		{
			Debug::getInstance()->addError( 'The file: '._CONTENT_DIRECTORY.'initLang.php don\'t exist' );
		}
		
		if ( !$this->isDBInitialized() )
		{
			include_once _SYSTEM_DIRECTORY.'data/list/PageListCreate.php';
			PageListCreate::getInstance()->addPagesByDir(_CONTENT_DIRECTORY);
			
			if (file_exists(_CONTENT_DIRECTORY.'initDB.php') )
			{
				PageListCreate::getInstance()->commands(_CONTENT_DIRECTORY.'initDB.php');
			}
		}
		
		$this->_pagesInitialised = true;
	}
	
	protected $_currentPageName;
	/**
	 * Name of the current page
	 * 
	 * @return string	Current page name
	 */
	public function getCurrentPageName()
	{
		if ( _DEBUG && !General::getInstance()->isPagesInitialized() )
		{
			Debug::getInstance()->addError( 'You can\'t access to the current '
				. 'page name if the pages isn\'tinitialised' );
		}
		return $this->_currentPageName;
	}
	
	protected $_currentPage;
	/**
	 * Current page
	 * 
	 * @return Page		Current page
	 */
	public function getCurrentPage()
	{
		if ( _DEBUG && !General::getInstance()->isPagesInitialized() )
		{
			Debug::getInstance()->addError( 'You can\'t access to the current '
				. 'page if the pages isn\'tinitialised' );
		}
		return $this->_currentPage;
	}
	
	protected $_currentLang;
	/**
	 * Current language
	 * 
	 * @return string		Current language
	 */
	public function getCurrentLang()
	{
		if ( _DEBUG && !General::getInstance()->isPagesInitialized() )
		{
			Debug::getInstance()->addError( 'You can\'t access to the current '
				. 'language if the pages isn\'tinitialised' );
		}
		return $this->_currentLang;
	}
	
	protected $_currentGetUrl;
	/**
	 * Current $_GET
	 * 
	 * @return array		GET variables
	 */
	public function getCurrentGetUrl()
	{
		if ( _DEBUG && !General::getInstance()->isPagesInitialized() )
		{
			Debug::getInstance()->addError( 'You can\'t access to the current '
				. 'global variable GET if the pages isn\'tinitialised' );
		}
		return $this->_currentGetUrl;
	}
	
	protected $_currentPostUrl = null;
	/**
	 * Current $_POST
	 * 
	 * @return array		POST variables
	 */
	public function getCurrentPostUrl()
	{
		if ( $this->_currentPostUrl === null )
		{
			$this->_currentPostUrl = array();
			foreach ( $_POST as $key => $value )
			{
				$this->_currentPostUrl[$key] = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
			}
		}
		
		return $this->_currentPostUrl;
	}
	
	protected $_currentPageUrl;
	/**
	 * Current page URL
	 * 
	 * @return string		Current page URL
	 */
	public function getCurrentPageUrl()
	{
		if ( _DEBUG && !General::getInstance()->isPagesInitialized() )
		{
			Debug::getInstance()->addError( 'You can\'t access to the current '
				. 'language if the pages isn\'tinitialised' );
		}
		return $this->_currentPageUrl;
	}
	
	/**
	 * Change the current page
	 * 
	 * @param Page $page	Current page
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
	 * @param type $pageUrl		URL of the page
	 * @param array $getUrl		List of the global variables GET
	 * @param array $postUrl	List of the global variables POST
	 */
	public function setCurrentUrl( $pageUrl,
									array $getUrl = null,
									array $postUrl = null )
	{
		if ( $getUrl === null )
		{
			$getUrl = array();
		}
		if ( $postUrl === null )
		{
			$postUrl = array();
		}
		
		$this->_currentPageUrl = $pageUrl;
		$this->_currentGetUrl = $getUrl;
	}
	
	/**
	 * You can't construct a singleton
	 */
	protected function __construct() { }
	
	/**
	 * You can't clone a singleton
	 */
	protected function __clone() { }

	/**
	 * Get the instance of General
	 * 
	 * @return General		Object instancied
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
