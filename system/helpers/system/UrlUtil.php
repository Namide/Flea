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
    private static $_arg = '_u';
	//public function getPageGetArg() { return self::$_arg; }
	
	private $_basePageUrl;
	
    final private function __construct()
    {
    	$this->reset();
    }

	/**
	 * Reload the URL.
	 * This state compose the global GET and computes the currentPage
	 * 
	 */
	public function reset()
	{
		if( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			Debug::getInstance()->addError( 'All pages must be initialised after use UrlUtil class' );
		}
		$this->_basePageUrl = 'index.php?'.self::$_arg.'=';
		
		$navigatorGets = self::getNavigatorGets();
		$relURL = '';
		$pageGet = array();
		foreach ($navigatorGets as $key => $value)
		{
			if ( $key === self::$_arg ) { $relURL = $value; }
			else { $pageGet[$key] = $value; }
		}
		
		$page = PageList::getInstance()->getByUrl( $relURL );
		$pageUrl = $this->dynamicPageUrlToPageUrl( $page, $relURL, $pageGet );
		
		General::getInstance()->setCurrentUrl($pageUrl, $pageGet);
		General::getInstance()->setCurrentPage($page);
	}
	
	private static function getNavigatorGets()
	{
		$navigatorGets = array();
		
		foreach ($_GET as $key => $value)
		{
			$navigatorGets[$key] = filter_input( INPUT_GET, $key, FILTER_SANITIZE_STRING );
		}
		
		//$navigatorGets = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
		//$navigatorGets = self::getCleanGets( $navigatorGets );
		
		return $navigatorGets;
	}

	/**
	 * Get first relative URL, this URL does not represent a Page
	 * 
	 * @return string
	 */
	public static function getNavigatorRelUrl()
	{
		$navigatorGets = self::getNavigatorGets();
		$relURL = '';
		$pageGet = array();
		foreach ($navigatorGets as $key => $value)
		{
			if ( $key === self::$_arg ) { $relURL = $value; }
			else { $pageGet[$key] = $value; }
		}
		foreach ($pageGet as $key => $value)
		{
			$relURL .= '/'.$key.'/'.$value;
		}
		
		return $relURL;
	}
	
	private function dynamicPageUrlToPageUrl(Page &$page, $relUrl, array &$pageGet)
	{
		$pageUrl = $page->getPageUrl();
		
		if( substr($relUrl, 0, strlen($pageUrl)) == $pageUrl &&
			$page->getGetEnabled() )
		{
			$restUrl = substr($relUrl, strlen($pageUrl)+1);
			$this->explodeDynamicUrlToGet( $restUrl, $pageGet, $page->getGetExplicit() );
		}
		return $pageUrl;
	}
	
	private function explodeDynamicUrlToGet( $restUrl, array &$pageGet, $isExplicit )
	{
		$getTemp = explode( '/', $restUrl );print_r($getTemp);
		if ( $isExplicit )
		{
			$l = count( $getTemp ) - 1;
			
			for ( $i = 0; $i<$l; $i+=2 )
			{
				$pageGet[$getTemp[$i]] = $getTemp[$i+1];
			}
		}
		else
		{
			$pageGet = array_merge( $pageGet, $getTemp );
		}
	}
	
	private static function getCleanGets( &$gets )
	{
		/*$getsStr = implode('&', $gets);
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
		}*/
		
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
		$page = PageList::getInstance()->getByName( $page->getName(), $lang );
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
        if ( _DEBUG )
		{
			Debug::getInstance()->addError( 'You can\'t clone a singleton' );
		}
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