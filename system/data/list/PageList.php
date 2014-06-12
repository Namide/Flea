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
	 * @return string	Default page
	 */
	public function getDefaultPageName() { return $this->_default; }
	
	protected $_error404;
	/**
	 * The name of the error 404 page
	 * 
	 * @return string	Error 404 page
	 */
	public function getError404PageName() { return $this->_error404; }
	
	/**
	 * Edit the page to convert in error page
	 * 
	 * @param Page &$page	Same page updated
	 */
	private function makeError404Page( &$page )
	{
		$page->setVisible( false );
		$page->setCachable( false );
		$page->setPhpHeader( 'HTTP/1.0 404 Not Found' );
		return $page;
	}
    
	/**
	 * Add all pages of a directory
	 * 
	 * @param type $dir				Root directory
	 * @param type $fileDirRel		Relative directory (for the recursivity)	
	 */
	function addPagesByDir( $dir, $fileDirRel = '' )
	{
		if ( !file_exists($dir) ) { return; }

		$dirOpen = opendir($dir);
		while($file = @readdir($dirOpen))
		{
			if ($file == "." || $file == "..") { continue; }

			if( is_dir($dir.'/'.$file) )
			{
				$this->addPagesByDir( $dir.'/'.$file.'/', $fileDirRel.'/'.$file );
				$this->createPage( (($fileDirRel != '')?$fileDirRel.'/':'').$file );
			}
		}
		closedir($dirOpen);
	}
	
	/*public function db_exist( $dsn, $childClass = null )
	{
		if ( $childClass === null )
		{
			$childClass = 'Element';
		}
		return $childClass::db_exist( $dsn );
	}*/
	
	/*public function db_create( $dsn, $childClass = null )
	{
		if ( $childClass === null )
		{
			$childClass = 'Element';
		}
		
		$element = new Element();
		$objectVars = $element->getObjectVars();
		$childClass::db_create( $dsn, $objectVars, true );
	}*/
	
	/*public function db_save( $dsn, $childClass = null )
	{
		if ( _DEBUG && !$this->db_exist() )
		{
			Debug::getInstance()->addError('You must create after save data base');
		}
		if ( $childClass === null )
		{
			$childClass = 'Element';
		}
		
		$sql = '';
		foreach ($this->_elements as $key => $value) 
		{
			$sql .= $childClass::db_insert($dsn, $value->getObjectVars(), false);
		}
		
		//print_r($sql);
		
		$db = new \PDO( $dsn, _DB_USER, _DB_PASS, _DB_OPTIONS );
		$db->exec($sql);
		$db = null;
	}*/
	
	/*public function db_pagesExist()
	{
		return Page::db_exist(_DB_DSN_PAGES);
	}*/
	
	/*public function db_savePages()
	{
		if ( !$this->db_pagesExist() )
		{
			$emptyPage = new Page();
			$datas = $emptyPage->getObjectVars();
			
			print_r( Page::db_create(_DB_DSN_PAGES, $datas, true) );
		}
		
		$sql = '';
		foreach ($this->_elements as $key => $value) 
		{
			$sql .= Page::db_insert(_DB_DSN_PAGES, $value->getObjectVars(), false);
		}
		
		print_r($sql);
		
		$db = new \PDO(_DB_DSN_PAGES, _DB_USER, _DB_PASS, _DB_OPTIONS );
		$db->exec($sql);
		$db = null;
		
	}*/
	
	/**
	 * Add all the pages (by languages) in the folder
	 * 
	 * @param string $folderName	Name of the folder thats contain the page
	 * @return array				List of the pages generated (differents languages)
	 */
	public function createPage( $folderName )
    {
        $pages = array();
        
        $langList = LangList::getInstance();
        $langs = $langList->getList();
        
        foreach ( $langs as $lang )
        {
            $filename = _CONTENT_DIRECTORY.$folderName.'/'.$lang.'-init.php';
            
            if( file_exists ( $filename ) )
            {
				$page = new Page();
				$page->setLang( $lang );
				$page->setName( $folderName );
				
				$this->initPage($page, $filename);
				
				
				$buildFile = _CONTENT_DIRECTORY.$folderName.'/'.$lang.'-build.php';
				if( file_exists ( $buildFile ) )
				{
					$page->setBuildFile($buildFile);
				}
				
				$url = $page->getPageUrl();
				parent::add( $page, $url );
				
				foreach ($page->getAdditionalPageUrls() as $url)
				{
					parent::add( $page, $url );
				}
				
				array_push( $pages, $page );
            }
			
        }
        
		return $pages;
    }
	
	private function initPage( Page &$page, $filename )
    {
		include $filename;
		
		$pageUrlTemp = $page->getPageUrl();
		if ( _DEBUG && empty($pageUrlTemp) && !isset($url) )
		{
			Debug::getInstance()->addError( 'The initialisation of a page must to have an URL' );
		}
		
		if ( isset($url) )				{ $page->setPageUrl($url) ; }
		if ( isset($addUrl) )			{ $page->addAdditionalPageUrl($addUrl) ; }
		if ( isset($addUrls) )			{ $page->addAdditionalPageUrls($addUrls) ; }
		if ( isset($template) )			{ $page->setTemplate($template) ; }
		
		if ( isset($visible) )			{ $page->setVisible($visible) ; }
		if ( isset($cachable) )			{ $page->setCachable($cachable) ; }
		
		if ( isset($getEnabled) )		{ $page->setGetEnabled($getEnabled) ; }
		if ( isset($getExplicit) )		{ $page->setGetExplicit($getExplicit) ; }
		if ( isset($date) )				{ $page->setDate($date) ; }
		
		if ( isset($htmlBody) )			{ $page->setHtmlBody($htmlBody) ; }
		if ( isset($htmlDescription) )	{ $page->setHtmlDescription($htmlDescription) ; }
		if ( isset($htmlHeader) )		{ $page->setHtmlHeader($htmlHeader) ; }
		if ( isset($htmlTitle) )		{ $page->setHtmlTitle($htmlTitle) ; }
				
		if ( isset($phpHeader) )		{ $page->setPhpHeader($phpHeader) ; }
		
		if ( isset($tags) )				{ $page->addTags($tags) ; }
		if ( isset($tag) )				{ $page->addTag($tag) ; }
		if ( isset($contents) )			{ $page->addContents($contents) ; }
		
		if ( isset($type) )	
		{
			$page->setType($type);
			if ( $type == 'default' )	{ $this->_default = $page->getName(); }
			if ( $type == 'error404' )
			{
				$this->_error404 = $page->getName();
				$page->setVisible( false );
				$page->setCachable( false );
				$page->setPhpHeader( 'HTTP/1.0 404 Not Found' );
			}
		}
		
        return $page;
    }
	
	/**
	 * Update the current page.
	 * Used to build the body with the "build file" (ex: en-build.php).
	 * 
	 * @param Page $page	Page to update
	 * @return Page			Same page updated
	 */
	public function updatePage( &$page )
	{
		if( $page->getBuildFile() == '' )
		{
			return $page;
		}
		
		ob_start();
		$page = $this->initPage( $page, $page->getBuildFile() );
		$page->setHtmlBody( ob_get_clean() );
		
		return $page;
	}

	/**
	 * Get the page by relative URL
	 * 
	 * @param string $relURL	Relative URL
	 * @return Page				Corresponding page
	 */
    public function getByUrl( $relURL )
    {
		if( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			Debug::getInstance()->addError( 'All pages must be initialised after use getByUrl()' );
		}
		
		$tableName = stripslashes(get_called_class());
		
		
		try
		{
			$page = null;
			$db = new \PDO(_DB_DSN_PAGES, _DB_USER, _DB_PASS, _DB_OPTIONS );
			
			
			// EXIST
			$sql = 'SELECT COUNT(*) FROM `'.$tableName.'` WHERE _url LIKE \''.$relURL.'\' LIMIT 1;';
			$res = $db->query($sql);
			if ( $res && $res->fetchColumn() > 0 )
			{
				$sql = 'SELECT * FROM `'.$tableName.'` WHERE _url LIKE \''.$relURL.'\' LIMIT 1;';
				foreach  ($db->query($sql) as $row)
				{
					$page = new Page();
					$page->update($row);
				}
				if( $page != null ) { return $page; }
			}
			
			
			// EXIST WITHOUT "/" AT THE END
			if ( strlen($relURL) > 0 && $relURL[strlen($relURL)-1] === '/' )
			{
				$urlTemp = substr( $relURL, 0, strlen($relURL)-1 );
				$sql = 'SELECT COUNT(*) FROM `'.$tableName.'` WHERE _url LIKE \''.$urlTemp.'\' LIMIT 1;';
				$res = $db->query($sql);
				if ( $res && $res->fetchColumn() > 0 )
				{
					$sql = 'SELECT * FROM `'.$tableName.'` WHERE _url LIKE \''.$urlTemp.'\' LIMIT 1;';
					foreach  ($db->query($sql) as $row)
					{
						$page = new Page();
						$page->update($row);
					}
					if( $page != null ) { return $page; }
				}
			}
			
			$lang = LangList::getInstance()->getLangByNavigator();
			
			// IS DEFAULT PAGE
			$sql =  'SELECT COUNT(*) FROM `'.$tableName.'` WHERE _type = \'default\' AND _lang = \''.$lang.'\' LIMIT 1;';
			$res = $db->query($sql);
			if ( $res && $res->fetchColumn() > 0 )
			{
				$sql =  'SELECT * FROM `'.$tableName.'` WHERE _type = \'default\' AND _lang = \''.$lang.'\' LIMIT 1;';
				foreach  ($db->query($sql) as $row)
				{
					$page = new Page();
					$page->update($row);
				}
				if( $page != null ) { return $page; }
			}
			
			// IS DYNAMIC PAGE
			$sql = 'SELECT COUNT(*) FROM `'.$tableName.'` WHERE _url% LIKE \''.$relURL.'\' AND _lang = \''.$lang.'\' LIMIT 1;';
			$res = $db->query($sql);
			if ( $res && $res->fetchColumn() > 0 )
			{
				$sql = 'SELECT * FROM `'.$tableName.'` WHERE _url% LIKE \''.$relURL.'\' AND _lang = \''.$lang.'\' LIMIT 1;';
				$bestScore = 0;
				$page = null;
				foreach ($db->query($sql) as $row)
				{
					$pageTemp = new Page();
					$pageTemp->update($row);
					
					$scoreTemp = $pageTemp->comparePageUrl($relURL);
					if ( $scoreTemp > $bestScore )
					{
						$bestScore = $scoreTemp;
						$page = $pageTemp;
					}
				}
				if( $page != null ) { return $page; }
			}
			
			// IS ERROR 404
			$sql =  'SELECT COUNT(*) FROM `'.$tableName.'` WHERE _type = \'error404\' AND _lang = \''.$lang.'\' LIMIT 1;';
			$res = $db->query($sql);
			if ( $res && $res->fetchColumn() > 0 )
			{
				$sql =  'SELECT * FROM `'.$tableName.'` WHERE _type = \'error404\' AND _lang = \''.$lang.'\' LIMIT 1;';
				foreach  ($db->query($sql) as $row)
				{
					$page = new Page();
					$page->update($row);
				}
				if( $page != null ) { return $page; }
			}
			
			
		}
		catch (PDOException $e)
		{
			if ( _DEBUG ) Debug::getInstance ()->addError ('Database error: '.$e);
		}
		/*if ( $this->hasKey($relURL) )
		{
			return parent::getByKey($relURL);
		}*/
		
		/*if ( strlen($relURL) > 0 && $relURL[strlen($relURL)-1] === '/' )
		{
			$urlTemp = substr( $relURL, 0, strlen($relURL)-1 );
			if ( $this->hasKey($urlTemp) )
			{
				return parent::getByKey($urlTemp);
			}
		}*/
		
		
		//$lang = LangList::getInstance()->getLangByNavigator();
		
		// IS DEFAULT PAGE
		/*$lang = LangList::getInstance()->getLangByNavigator();
		if( ($relURL === '' || $relURL === '/') && !empty($this->_default) )
        {
			return $this->getDefaultPage($lang);
        }*/
		
		// IS DYNAMIC PAGE
		/*$bestScore = 0;
		$page = null;
		foreach ( $this->_elements as $pageTemp )
		{
			$scoreTemp = $pageTemp->comparePageUrl($relURL);
			if ( $scoreTemp > $bestScore )
			{
				$bestScore = $scoreTemp;
				$page = $pageTemp;
			}
		}
		if ( $page != null ) { return $page; }*/
		
        // IS ERROR 404
		/*if ( !empty( $this->_error404 ) )
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
		}*/
        
        // CREATE PAGE ERROR 404
			$page = new Page();
			$page->setHtmlHeader( '<title>Error 404 - Not found</title>
					<meta name="robots" content="noindex,nofollow" />
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' );
			$page->setHtmlBody( '<h1>Error 404 - Not found</h1>' );
			$this->makeError404Page($page);
			return $page;
        //
    }
	
	/**
	 * Default page for the lang
	 * 
	 * @param string $lang	Language
	 * @return Page			default page
	 */
    public function getDefaultPage( $lang )
    {
        $name = $this->_default;
        
        foreach ( $this->_elements as $page )
        {
			if (	$page->getName() === $name &&
					$page->getLang() === $lang )
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
	 * @param string $lang	Language
	 * @return array		List of the visible pages
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
	 * @param string $name	Name of the page
	 * @param string $lang	Language
	 * @return Page			Page corresponding
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
	 * @param string $url	Relative URL
	 * @return boolean		URL exist
	 */
	public function hasUrl( $url )
    {
		return $this->hasKey($url);
    }
	
	/**
	 * Get the language from the URL
	 * 
	 * @param string $url	Relative URL
	 * @return string		Language
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
	 * @return string	The save text
	 */
	/*public function getSave()
	{
		return $this->constructSave( get_object_vars($this) );
	}*/
	
	/**
	 * Update the object with a saved object.
	 * A saved object can by generate by the method getSave().
	 * 
	 * @param array $saveDatas	Datas generated by a save method of this class
	 * @return Page				PageList with the news values
	 */
	/*public function update( array $saveDatas )
	{
		if ( count( $saveDatas ) > 0 )
		{
			foreach ( $saveDatas as $varLabel => $varValue )
			{
				$this->$varLabel = $varValue;
			}
		}
		return $this;
	}*/
	
}
