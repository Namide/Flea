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
 * Description of PageList
 *
 * @author Namide
 */
class PageList extends ElementList
{
	
	protected $_default;
	/**
	 * The name of the default page
	 * 
	 * @return string
	 */
	public function getDefaultPageName() { return $this->_default; }
	
	protected $_error404;
	/**
	 * The name of the error 404 page
	 * 
	 * @return string
	 */
	public function getError404PageName() { return $this->_error404; }
	
	/**
	 * Add a default page (you can only have 1 by language)
	 * 
	 * @param string $name
	 */
	public function addDefault( $name )
    {
		$this->_default = $name;
        $this->add($name);
    }

	/**
	 * Add an error 404 page (you can only have 1 by language)
	 * 
	 * @param string $name
	 */
    public function addError404( $name )
    {
        $this->_error404 = $name;
		foreach ( $this->addPage($name) as $page)
		{
			$this->makeError404Page( $page );
		}
    }
	
	/**
	 * Edit the page to convert in error page
	 * 
	 * @param Page &$page
	 */
	private function makeError404Page( &$page )
	{
		$page->setVisible( false );
		$page->setCachable( false );
		$page->setPhpHeader( 'HTTP/1.0 404 Not Found' );
		return $page;
	}
    
	/**
	 * Add all the pages (by languages) in the folder
	 * 
	 * @param string $folderName
	 * @return array
	 */
	public function add( $folderName )
    {
        $pages = array();
        
        $langList = LangList::getInstance();
        $langs = $langList->getList();
        
        foreach ( $langs as $lang )
        {
            $filename = _CONTENT_DIRECTORY.$folderName.'/'.$lang.'-init.php';
            
            if( file_exists ( $filename ) )
            {
				include $filename;
				
				if ( _DEBUG && !isset($page) )
				{
					trigger_error( 'The page ['.$filename.'] is don\'t declared', E_USER_ERROR );
					$page = new Page();
				}
				
				$page->setLang( $lang );
				$page->setName( $folderName );
				
				$buildFile = _CONTENT_DIRECTORY.$folderName.'/'.$lang.'-build.php';
				if( file_exists ( $buildFile ) )
				{
					$page->setBuildFile($buildFile);
				}
				
				$url = $page->getPageUrl();
				parent::add( $page, $url );
				array_push( $pages, $page );
            }
        }
        
		return $pages;
    }
	
	/**
	 * Update the current page.
	 * Used to build the body with the "build file" (ex: en-build.php).
	 * 
	 * @param Page $page
	 * @return Page
	 */
	public function updatePage( &$page )
	{
		if( $page->getBuildFile() == '' )
		{
			return $page;
		}
		
		ob_start();
		$page = $this->initPage( $page, $page->getBuildFile() );
		$page->startBuild();
		$page->setBody( ob_get_clean() );
		
		return $page;
	}

	/**
	 * Get the page by URL
	 * 
	 * @param string $url
	 * @return Page
	 */
    public function getByUrl( $url )
    {
		// EXIST
		if ( $this->hasKey($url) )
		{
			return parent::getByKey($url);
		}
		
		// EXIST WITHOUT "/" AT THE END
		if ( $url[strlen($url)-1] === '/' )
		{
			$urlTemp = substr( $url, 0, strlen($url)-1 );
			if ( $this->hasKey($urlTemp) )
			{
				return parent::getByKey($urlTemp);
			}
		}
		
        // IS DEFAULT PAGE
		if( $url === '' || $url === '/' )
        {
			return $this->getDefaultPage();
        }
        
        // IS ERROR 404
		$lang = LangList::getInstance()->getLangByNavigator();
		if ( !empty( $this->_error404 ) )
		{
			foreach ( $this->_elements as $page )
			{
				$nameTemp = $page->getName();
				$langTemp = $page->getLang();
				if ( $nameTemp === $this->_error404 && $langTemp === $lang )
				{
					return $page;
				}
			}
		}
        
        // CREATE PAGE ERROR 404
			$page = new Page();
			$page->setHeader( '<title>Error 404 - Not found</title>
					<meta name="robots" content="noindex,nofollow" />
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' );
			$page->setBody( '<h1>Error 404 - Not found</h1>' );
			$this->makeError404Page($page);
			return $page;
        //
    }
	
	/**
	 * Default page for the lang
	 * 
	 * @param string $lang
	 * @return Page
	 */
    public function getDefaultPage( $lang )
    {
        $name = $this->_default;
        
        foreach ( $this->_elements as $page )
        {
			if (	$page->getName() === $name &&
					$page->getLanguage() === $lang )
            {
                return $page;
            }
        }
		
		/* IF THE LANGUAGE OF THE DEFAULT PAGE DON'T EXIST */
		foreach ( $this->_elements as $page )
        {
            if ( $page->getName() === $name )
            {
                return $page;
            }
        }
		
		/* IF THE DEFAULT PAGE DON'T EXIST */
		foreach ( $this->_elements as $page )
        {
            if ( $page->getLang() === $lang )
            {
                return $page;
            }
        }
		
		/* ELSE: RANDOM PAGE (FIRST IN THE LIST) */
		foreach ( $this->_elements as $page )
        {
            return $page;
        }
    }
    
	/**
	 * Get all pages visible (ex: for the sitemap.xml)
	 * 
	 * @param string $lang
	 * @return array
	 */
    public function getAllVisible( $lang )
    {
        $pages = array();
        foreach ( $this->_elements as $page )
        {
			if (	$page->getVisible() &&
					$page->getLanguage() === $lang )
            {
                array_push( $pages, $page );
            }
        }
        return $pages;
    }
	
	/**
	 * Return the page or, if it doesn't exist, the default page
	 * 
	 * @param string $name
	 * @param string $lang
	 * @return Page
	 */
    public function getByName( $name, $lang )
    {
		if ( $this->has($name, $lang) )
		{
			return parent::getByName($name, $lang);
		}
        return $this->getDefaultPage( $lang );
    }
	
	/**
	 * Try if the URL exist.
	 * Same that hasKey()
	 * 
	 * @param string $url
	 * @return boolean
	 */
	public function hasUrl( $url )
    {
		return $this->hasKey($url);
    }
	
	/**
	 * Get the language from the URL
	 * 
	 * @param string $url
	 * @return string
	 */
    private function getLangByUrl( $url )
    {
        if ( $this->hasUrl($url) )
        {
            $page = $this->_elements[$url];
            return $page->getLang();
        }
        
		return LangList::getInstance()->getLangByNavigator();
    }
	
	/**
	 * Get a script for create the same object
	 * 
	 * @return string
	 */
	public function getSave()
	{
		return $this->constructSave( get_object_vars($this) );
	}
	
	/**
	 * Update the object with a saved object.
	 * A saved object can by generate by the method getSave().
	 * 
	 * @param array $saveDatas
	 * @return self
	 */
	public function update( $saveDatas )
	{
		if ( count( $saveDatas ) > 0 )
		{
			foreach ( $saveDatas as $varLabel => $varValue )
			{
				$this->$varLabel = $varValue;
			}
		}
		return $this;
	}
	
}
