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
 * List of pages in the website.
 * 
 * @author Namide
 */
class PageList
{
	private static $_INSTANCE = null;
	
	public static $LOAD_INIT = 0;
	public static $LOAD_LIST = 1;
	
    /**
	 * List of pages.
	 * By default this query return all the pages without the non-visible pages.
	 * To change this you must use a SqlQuery with an other where defined.
	 * Exemple :
	 * $query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
	 * $query->setWhere('_visible = 1 AND _visible = 0');
	 * $pages = PageList::getInstance()->getAll( $query );
	 * 
	 * @param SqlQuery $query		Query for the request
	 * @return array				All the Pages
	 */
	public function getAll( SqlQuery $query = null )
	{
		if ( $query === null )
			$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
		else
			$query->setType ( SqlQuery::$TYPE_SELECT );
		
		if ( $query->getWhere() == '' )
			$query->setWhere('_visible = 1');
		
		if ( $query->getSelect() == '' )
			$query->setSelect('*');
		
		if ( $query->getFrom() == '' )
			$query->setFrom('`'.DataBase::objectToTableName( Page::getEmptyPage() ).'`');
		
		if ( $query->getOrderBy() == '' )
			$query->setOrderBy('_date DESC');
		
		$pages = array();
		foreach ( DataBase::getInstance( _DB_DSN_PAGE )->fetchAll($query) as $row )
		{
			$page = new Page();
			$page->setByObjectVars($row);
			$pages[$page->getId()] = $page;
		}
		
		return $pages;
	}
	
	/**
	 * Get the page and use the list of the page.
	 * You can use the values used in the DataList of the Page (tags, contents...)
	 * Example :
	 * $query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
	 * $query->setWhere('_lang = \'en\' AND page_prop = \'_tags\' AND value = \'post\' AND _visible = 1');
	 * $pages = PageList::getInstance()->getByList ( $query );
	 * 
	 * @param SqlQuery $query		Query for the request
	 * @return Page					List the Pages corresponding of the request
	 */
	public function getByList( SqlQuery $query )
	{
		$table_page = DataBase::objectToTableName( Page::getEmptyPage() );
		$table_list = $table_page.'_array';
		
		$pages = array();
		
		$query->setType( SqlQuery::$TYPE_SELECT );
		$query->setSelect('*');
		$query->setFrom('`'.$table_page.'` '
						. 'LEFT JOIN `'.$table_list.'` '
						. 'ON '.$table_page.'._id = '.$table_list.'.page_id');
		
		if ( $query->getOrderBy() == '' )
			$query->setOrderBy('_date DESC');
		
		foreach ( DataBase::getInstance( _DB_DSN_PAGE )->fetchAll($query) as $row )
		{
			$page = new Page();
			$page->setByObjectVars($row);
			
			$pages[$page->getId()] = $page;
		}
		
		return $pages;
	}
	
	/**
	 * Update the current page.
	 * Used to build the body with the "build file" (ex: en-build.php).
	 * 
	 * @param Page $page	Page to update
	 * @return Page			Same page updated
	 */
	public function buildPage( &$page )
	{
		if( $page->getBuildFile() === '' )
		{
			return $page;
		}
		
		ob_start();
		$page = $this->initPage( $page, $page->getBuildFile() );
		$page->setHtmlBody( ob_get_clean() );
		
		return $page;
	}
	
	/**
	 * List of pages in the language $lang
	 * 
	 * @param string $lang	Language
	 * @return array		All the page for this language
	 */
	public function getAllByLang( $lang )
	{
		$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
		$query->setWhere('_lang = \''.$lang.'\' AND _visible = 1');
		return $this->getAll( $query );
	}
	
	/**
	 * You must use the static method PageList::getInstance();
	 */
	final protected function __construct() { }
	
	/**
	 * Return all the pages with the name $name (all langues)
	 * 
	 * @param string $name		Name of the pages
	 * @return array			List of the pages with the name $name
	 */
	public function getAllByName( $name )
	{
		$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
		$query->setWhere('_name = \''.$name.'\' AND _visible = 1');
		return $this->getAll( $query );
	}
	
	/**
	 * Return a list of pages with the tag
	 * 
	 * @param string $tag		Tag for the page
	 * @param string $lang		Language of the page
	 * @return array			List of the pages
	 */
	public function getByTag( $tag, $lang )
    {
		$query = SqlQuery::getTemp();
		$query->setWhere(' _lang = \''.$lang.'\' AND page_prop = \'_tags\' AND value = \''.$tag.'\' AND _visible = 1');
		return $this->getByList ( $query );
    }
	
	/**
	 * Return a list of pages with each page has at least one of your tags
	 * 
	 * @param array $tags		List of tags (withouts keys)
	 * @param string $lang		Language of the elements
	 * @return array			List of elements
	 */
	public function getWithOneOfTags( array $tags, $lang )
    {
		$where = '_lang = \''.$lang.'\' AND '
			. 'page_prop = \'_tags\' AND '
			. '_visible = 1 AND (';
		
		$first = true;
		foreach ($tags as $tag)
		{
			if ( !$first ) $where .= ' OR';
			$where .= ' value = \''.$tag.'\'';
			$first = false;
		}
		$where .= ')';
		
		$query = SqlQuery::getTemp();
		$query->setWhere($where);
		return $this->getByList ( $query );
    }
    
	/**
	 * Return a list of pages with each pages has each of tags
	 * 
	 * @param array $tags		List of tags
	 * @param string $lang		Language of the pages
	 * @return array			List of pages
	 */
	public function getWithAllTags( array $tags, $lang )
    {
		$allPages = $this->getWithOneOfTags( $tags, $lang );
		$goodPages = array();
		
		foreach ( $allPages as $page )
		{
			$ok = true;
			foreach( $tags as $tag )
			{
				if ( !$page->getTags()->hasValue($tag) )
				{
					$ok = false;
				}
			}
			if( $ok )
			{
				$goodPages[] = $page;
			}
		}
		
		return $goodPages;
    }
	
	/**
	 * Return a list of pages for a language
	 * 
	 * @param string $lang	Language of the pages
	 * @return array		List of pages
	 */
    public function getByLang( $lang )
    {
		$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
		$query->setWhere('_lang = \''.$lang.'\' AND _visible = 1');
        return $this->getAll( $query );
    }
	
	/**
	 * Test if the page with this ID and this language exist
	 * 
	 * @param string $name		Name of the page
	 * @param string $lang		Language of the page
	 * @return boolean			Exist
	 */
	public function has( $name, $lang = null )
    {
		$tableName = DataBase::objectToTableName( Page::getEmptyPage() );
		
		$query = SqlQuery::getTemp();
		$query->initCount($tableName);
		$where = '_name = \''.$name.'\' AND _visible = 1';
		if ( $lang !== null ) { $where .= ' AND _lang = \''.$lang.'\''; }
		$query->setWhere($where);
		
		return ( DataBase::getInstance( _DB_DSN_PAGE )->count( $query ) > 0);
    }
	
	
	
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
	 * Add all the pages (by languages) in the folder
	 * 
	 * @param string $folderName	Name of the folder thats contain the page
	 * @return array				List of the pages generated (differents languages)
	 */
	protected function createPage( $folderName )
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
				
				array_push( $pages, $page );
            }
			
        }
        
		return $pages;
    }
	
	private function initPage( Page &$page, $filename )
    {
		include $filename;
		
		if ( isset($type) )	
		{
			$page->setType($type);
			if ( $type == Page::$TYPE_ERROR404 )
			{
				$this->_error404 = $page->getName();
				$page->setVisible( false );
				$page->setCachable( false );
				$page->setPhpHeader( 'HTTP/1.0 404 Not Found' );
			}
		}
		
		if ( isset($url) )				{ $page->setPageUrl($url) ; }
		if ( isset($addUrl) )			{ $page->getAdditionalUrls()->add($addUrl); }
		if ( isset($addUrls) )			{ $page->getAdditionalUrls()->addMultiple($addUrls); }
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
		
		if ( isset($tags) )				{ $page->getTags()->addMultiple($tags) ; }
		if ( isset($tag) )				{ $page->getTags()->add($tag) ; }
		if ( isset($contents) )			{ $page->getContents()->addMultiple($contents); }
		
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
		if( _DEBUG && !General::getInstance()->isPagesInitialized() )
		{
			Debug::getInstance()->addError( 'All pages must be initialised after use getByUrl()' );
		}
		
		
		// EXIST	
		$query = SqlQuery::getTemp();
		$query->setWhere( '_url LIKE \''.$relURL.'\' OR (page_prop = \'_additionalUrls\' AND value = \''.$relURL.'\')' );
		$pages1 = $this->getByList( $query );
		if ( count($pages1) > 0 ) return current ($pages1);
		
		
		// EXIST WITHOUT "/" AT THE END
		if ( strlen($relURL) > 0 && $relURL[strlen($relURL)-1] === '/' )
		{
			$urlTemp = substr( $relURL, 0, strlen($relURL)-1 );
			
			$query->clean(SqlQuery::$TYPE_SELECT);
			$query->setWhere('_url LIKE \''.$urlTemp.'\'');
			$pages = $this->getAll( $query );
			if ( count($pages) > 0 ) return current ($pages);
		}

		
		$lang = LangList::getInstance()->getLangByNavigator();

		// IS DYNAMIC PAGE
		$query->clean(SqlQuery::$TYPE_SELECT);
		$query->setSelect('_id');
		$query->setFrom('`'.DataBase::objectToTableName( Page::getEmptyPage() ).'`');
		$query->setWhere('SUBSTR( \''.$relURL.'\', 0, LENGTH(_url)+1 ) LIKE _url AND _getEnabled = 1');
		$query->setOrderBy('LENGTH(_url) DESC');
		
		$pages2 = DataBase::getInstance(_DB_DSN_PAGE)->fetchAll($query);
		if ( count($pages2) > 0 )
		{
			foreach ($pages2 as $pageTemp)
			{
				$query->clean();
				$query->setWhere('_id LIKE \''.$pageTemp['_id'].'\'');
				$pagesTemp = $this->getByList( $query );
				if ( count($pagesTemp) > 0 )
				{
					return current ($pagesTemp);
				}
			}
		}
		
		if ( _DEBUG )
		{
			Debug::getInstance()->addError('The URL "'.$relURL.'" don\'t exist');
		}
		
		return $this->getError404Page( $lang );
    }
	
	/**
	 * Get the error 404 page if it's defined.
	 * Otherwise return a standart error 404 page.
	 * 
	 * @param string $lang		Language of the page
	 * @return Page				Error 404 page
	 */
	public function getError404Page( $lang )
	{
		$query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query->setLimit(1);
		$query->setWhere('_type = \''.Page::$TYPE_ERROR404.'\' AND _lang = \''.$lang.'\'');
		$pages1 = $this->getAll( $query );
		if ( count($pages1) > 0 ) { return current($pages1); }
		
		$query->setWhere('_type = \''.Page::$TYPE_ERROR404.'\'');
		$pages2 = $this->getAll( $query );
		if ( count($pages2) > 0 ) { return current($pages2); }

		// CREATE PAGE ERROR 404
			$page = new Page();
			$page->setHtmlHeader( '<title>Error 404 - Not found</title>
					<meta charset="UTF-8" />
					<meta name="robots" content="noindex,nofollow" />
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' );
			$page->setHtmlBody( '<h1>Error 404 - Not found</h1>' );
			$this->makeError404Page($page);
			return $page;
        // END OF CREATE PAGE ERROR 404
	}
	
	/**
	 * Default page for the language
	 * 
	 * @param string $lang		Language of the default page
	 * @return Page				Default page
	 */
    public function getDefaultPage( $lang )
    {
        $query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query->setLimit(1);
		
		$query->setWhere('_type = \''.Page::$TYPE_DEFAULT.'\' AND _lang = \''.$lang.'\'');
		$pages1 = $this->getAll( $query );
		if ( count($pages1) > 0 ) { return current($pages1); }
		
		/* IF THE LANGUAGE OF THE DEFAULT PAGE DON'T EXIST */
		$query->setWhere('_type = \''.Page::$TYPE_DEFAULT.'\'');
		$pages2 = $this->getAll( $query );
		if ( count($pages2) > 0 ) { return current($pages2); }
		
		/* IF DEFAULT PAGE DON'T EXIST */
		return $this->getError404Page( $lang );
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
		$query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query->setWhere('_name = \''.$name.'\' AND _lang = \''.$lang.'\'');
		$query->setLimit(1);
		$pages = $this->getAll( $query );
		
		if ( count($pages) > 0 ) { return current ($pages); }
		
		if ( _DEBUG )
		{
			Debug::getInstance()->addError('The page '.$name.','.$lang.' don\'t exist');
		}
		
        return $this->getDefaultPage( $lang );
    }
	
	/**
	 * Try if the URL exist.
	 * 
	 * @param string $url	Relative URL
	 * @return boolean		URL exist
	 */
	public function hasUrl( $url )
    {
		$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
		$query->setWhere( '_url LIKE \''.$url.'\' OR (page_prop = \'_additionalUrls\' AND value = \''.$url.'\')' );
		$query->setLimit(1);
		$pages = $this->getByList( $query );
		return ( count($pages) > 0 );
    }
	
	/**
	 * Get the language from the URL
	 * 
	 * @param string $url	Relative URL
	 * @return string		Language
	 */
    private function getLangByUrl( $url )
    {
		$query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query->setWhere( '_url LIKE \''.$url.'\' OR (page_prop = \'_additionalUrls\' AND value = \''.$url.'\')' );
		$query->setLimit(1);
		$pages = $this->getByList( $query );
		if ( count($pages) > 0 ) { return current ($pages)->getLang(); }
		
		return LangList::getInstance()->getLangByNavigator();
    }
	
	final public function __clone()
    {
		if ( _DEBUG )
		{
			Debug::getInstance()->addError( 'You can\'t clone a singleton' );
		}
    }
 
	/**
	 * Instance of the PageList
	 * 
	 * @return static	Instance of the object PageList
	 */
    final public static function getInstance()
    {
        if(PageList::$_INSTANCE === null)
        {
            PageList::$_INSTANCE = new PageList();
        }
        return PageList::$_INSTANCE;
    }
	
}
