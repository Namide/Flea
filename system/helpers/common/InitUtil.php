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
    
    final protected function __construct()
    {
        $this->reset();
    }
	
    protected function reset() { }
	
	/**
	 * Get an absolute URL for a file at the root of the website
	 * 
	 * @param string $file
	 * @return string
	 */
	public function getRootAbsUrl( $file )
    {
        return _ROOT_URL.$file;
    }
	
	/**
	 * Get an absolute URL for a file in the template directory
	 * 
	 * @param string $file
	 * @return string
	 */
    public function getTemplateAbsUrl( $file )
    {
       return _ROOT_URL._TEMPLATE_DIRECTORY.$file;
    }
    
	/**
	 * Get an absolute URL for a file in the content directory
	 * 
	 * @param string $file
	 * @return string
	 */
    public function getContentAbsUrl( $file )
    {
       return _ROOT_URL._CONTENT_DIRECTORY.$file;
    }
!!!!!!!!!!!!	
	/**
	 * Get the absoulte URL of a page by his page URL
	 * 
	 * @param string $url
	 * @return string
	 */
    public function getAbsUrlByPageUrl( $url, array $gets = null )
    {
	    return _ROOT_URL.( (!_URL_REWRITING) ? (UrlUtil::$BASE_PAGE_URL) : '' ).$url;
    }
    
	/**
	 * 
	 * @param string $idPage
	 * @param string $lang
	 * @return string
	 */
    public function getAbsUrlByIdLang( $idPage, $lang )
    {
		$pagesClass = PageList::getInstance();
		$page = $pagesClass->getPage( $idPage, $lang );
		
         return _ROOT_URL.( (!_URL_REWRITING) ? (UrlUtil::$BASE_PAGE_URL) : '' ).$page->getPageUrl();
    }
    
	/**
	 * 
	 * @param string $url
	 * @return string
	 */
    public function urlToLang( $url )
    {
        $pagesClass = PageList::getInstance();
        $pageClass = $pagesClass->getPageByUrl( $url );
        return $pageClass->getLanguage();
    }
	
	/**
	 * 
	 * @param string $text
	 * @param Page $page
	 * @return Page
	 */
	public function mustache( $text, &$page )
    {
		$replacePage = preg_replace('/\{\{pathCurrentPage:(.*?)\}\}/', $page->getAbsoluteUrl('$1'), $text);
		$replacePage = preg_replace('/\{\{urlPageToAbsoluteUrl:(.*?)\}\}/', $this->urlPageToAbsUrl('$1'), $replacePage);
        $replacePage = preg_replace('/\{\{pathTemplate:(.*?)\}\}/', $this->getTemplateAbsUrl('$1'), $replacePage);
		$replacePage = preg_replace('/\{\{pathContent:(.*?)\}\}/', $this->getContentAbsUrl('$1'), $replacePage);

		$pageList = PageList::getInstance();
		if ( $pageList->getInitialised() )
		{
			$replacePage = preg_replace_callback( '/\{\{idPageToAbsoluteUrl:(.*?)\}\}/', function ($matches) use($page)
			{
				$lang = $page->getLanguage();//$this->_language;//BuildUtil::getInstance()->getLang();
				return InitUtil::getInstance()->getAbsUrlByIdLang( $matches[1], $lang );
			}, $replacePage );
		}

        return $replacePage;
    }
	
	final public function __clone()
    {
        trigger_error( 'You can\'t clone.', E_USER_ERROR );
    }
	
	/**
	 * @return InitUtil
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
