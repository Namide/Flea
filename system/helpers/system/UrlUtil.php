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

	/**
	 * Label of the variable GET that content the URL.
	 * If you change it, change to the root .htaccess
	 * 
	 * @return string
	 */
    private $_arg = '_u';
	//public function getPageGetArg() { return self::$_arg; }
	
	private $_basePageUrl;
	
    final private function __construct()
    {
    	$this->reset();
    }

	/**
	 * Reload the URL
	 */
	public function reset()
	{
		if( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			trigger_error( 'All pages must be initialised after use UrlUtil class', E_USER_ERROR );
		}
		$this->_basePageUrl = 'index.php?'.$this->_arg.'=';
		
		$gets = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
		$gets = $this->getCleanGets( $gets );
		
		$pageURL = '';
		$pageGET = array();
		
		$general = General::getInstance();
		
		foreach ($array as $key => $value)
		{
			if ( $key === self::$_arg )
			{
				$pageURL = $value;
			}
			else
			{
				$pageGET[$key] = $general;
			}
		}
		
		$this->fixCurrentUrl($pageURL, $pageGET);
		
		
		
	}
	
	private function fixCurrentUrl( &$pageURL, &$pageGET )
	{
		$page = null;
		$lang = null;
		
		$pageList = PageList::getInstance();
		$allPages = $pageList->getAll();
		if ( !array_key_exists($pageURL, $allPages) )
		{
			$bestPage = null;
			$bestScore = -1;
			
			foreach ( $allPages as $pageTemp )
			{
				$score = $pageTemp->comparePageUrl($pageURL);
				
				if ( $score > $bestScore )
				{
					$bestScore = $score;
					$bestPage = $pageTemp;
				}
			}
			
			if ( $bestPage === null )
			{
				$lang = LangList::getInstance()->getLangByNavigator();
				$page = $pageList->getByName($pageList->getError404PageName(), $lang);
			}
			else
			{
				$page = $bestPage;
				$lang = $page->getLang();
						
				$this->explodeUrlToGet($page, $pageURL, $pageGET);
			}
		}
		
		General::getInstance()->setCurrentUrl($pageURL, $pageGET);
	}
	
	private function explodeUrlToGet( Page &$page, &$pageURL, array &$pageGET )
	{
		$url = $page->getPageUrl();
		$rest = substr( $pageURL, strlen($url) );
		if ( $page->getGetExplicit() )
		{
			$getTemp = explode( '/', $rest );
			foreach ($getTemp as $key => $value)
			{
				array_push($pageGET, $value);
			}
		}
		else
		{
			$get = explode( '/', $rest );
			array_merge( $pageGET, $get );
		}
	}
	
	private function getCleanGets( &$gets )
	{
		$getsStr = implode('&', $gets);
		$getsStr = str_replace('?', '&', $getsStr);
		$getsStr = str_replace('&&', '&', $getsStr);
		
		$getsExpl = explode('&', $getsStr);
		
		$gets = array();
		foreach ($getsExpl as $getStr)
		{
			$get = explode( '=', $getStr );
			if ( count($get) === 2 )
			{
				$gets[$get[0]] = $get[1];
			}
		}
		
		return $gets;
	}
	
	/**
	 * Relative URL of the page
	 * 
	 * @param \Flea\Page $page
	 * @param string $lang
	 * @param array $getUrl
	 * @return string
	 */
	public function getRelUrlByIdLang( \Flea\Page &$page, $lang, array $getUrl = null )
    {
		$page = $PageList->getByName( $idPage, $lang );
		$url = $this->getRelUrlByPageUrl( $page->getPageUrl(), $getUrl, $page->getGetExplicit() );
		
		return $url;
    }
	
	/**
	 * PageUrl to relative URL
	 * 
	 * @param string $pageUrl
	 * @param array $getUrl
	 * @param bool $explicitGet
	 * @return string
	 */
	public function getRelUrlByPageUrl( $pageUrl, array $getUrl = null, $explicitGet = true )
	{
		if ( $getUrl === null ) { $getUrl = array(); }
		
		$url = '';
		if ( !_URL_REWRITING )
		{
			$url .= $this->_basePageUrl;
		}
		$url .= $pageUrl;
		
		if ( count($getUrl)>0 )
		{
			foreach ($getUrl as $key => $value)
			{
				if ( _URL_REWRITING && $explicitGet )
				{
					$url .= '/'.urlencode($key).'/'.urlencode($value);
				}
				elseif ( _URL_REWRITING && !$explicitGet )
				{
					$url .= '/'.urlencode($value);
				}
				else
				{
					$url .= '&'.urlencode($key).'='.urlencode($value);
				}
			}
		}
		
		return $url;
	}


	/**
	 * Converts an URL of page to a string
	 * 
	 * @return string
	 */
	public static function urlPageToStr( $pageUrl, $getUrl = null )
	{
		if ( $getUrl === null )	{ $getUrl = array(); }
		
		$urlStr = $pageUrl.'&'.implode('&', $getUrl);
		
		$invalid = array( /*'/'=>'-',*/ '\\'=>'-', ':'=>'-', /*'?'=>'-',*/ '"'=>'-', '*'=>'-', '<'=>'-', '>'=>'-', '|'=>'-' );
		$urlStr = str_replace(array_keys($invalid), array_values($invalid), htmlentities( $urlStr ) );

		$invalid = array( '&'=>'/', '?'=>'/' );
		$urlStr = str_replace(array_keys($invalid), array_values($invalid), htmlentities( $urlStr ) );

		return $urlStr;
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
            self::$_INSTANCE = new self();
        }
 
        return self::$_INSTANCE;
    }
}
