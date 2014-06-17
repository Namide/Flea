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
class PageList
{
	private static $_INSTANCE = null;
	
	public static $LOAD_INIT = 0;
	public static $LOAD_LIST = 1;
	
    /**
	 * List of elements
	 * 
	 * @return array All the elements
	 */
	public function getAll( $query = null, $flagLoad = 0 )
	{
		$sql = 'SELECT';
		if ( $flagLoad == PageList::$LOAD_INIT )
		{
			//'SELECT name, color, calories FROM fruit ORDER BY name'
			$first = true;
			foreach (Page::getEmptyPage()->getObjectVars() as $key => $value)
			{
				if ( gettype($value) != 'array' && gettype($value) != 'object' )
				{
					if ( !$first ) { $sql .= ' ,'; }
					$sql .= ' '.$key;
					$first = false;
				}
			}
		}
		elseif ( $flagLoad == PageList::$LOAD_INIT | PageList::$LOAD_LIST )
		{
			$sql .= ' *';
		}
		else
		{
			$sql .= ' *';
		}

		/*
		SELECT *
		FROM table
		WHERE condition
		GROUP BY expression
		HAVING condition
		{ UNION | INTERSECT | EXCEPT }
		ORDER BY expression
		LIMIT count
		OFFSET start
		 */
		
		$sql .= ' FROM `'.DataBase::objectToTableName( Page::getEmptyPage() ).'`';
		if ( $query !== null )
		{
			$sql .= ' '.$query.';';
		}
		else
		{
			$sql .= ' ORDER BY _date;';
		}
		
		//$query .= ' LIMIT';
		
		$pages = array();
		foreach ( DataBase::getInstance( _DB_DSN_PAGES )->fetchAll($sql) as $row )
		{
			$page = new Page();
			$page->setByObjectVars($row);
			if ( ($flagLoad & PageList::$LOAD_LIST) > 0 )
			{
				$page = $this->addListToPage($page);
			}
			
			$pages[$page->getId()] = $page;
		}
		
		return $pages;
	}
	
	public function getByList( $where, $flagLoad = 0 )
	{
		$table_page = DataBase::objectToTableName( Page::getEmptyPage() );
		$table_list = $table_page.'_array';
		
		$query = 'SELECT * FROM `'.$table_page.'` INNER JOIN '.$table_list.' ON '.$table_page.'._id = '.$table_list.'.page_id';
		if ( $where !== null ) $query .= ' WHERE '.$where;
		$query .= ' ORDER BY _date;';
		//$query .= ' LIMIT';
		
		$pages = array();
		foreach ( DataBase::getInstance( _DB_DSN_PAGES )->fetchAll($query) as $row )
		{
			$page = new Page();
			$page->setByObjectVars($row);
			
			if ( ($flagLoad & PageList::$LOAD_LIST) > 0 )
			{
				$page = $this->addListToPage($page);
			}
			
			$pages[$page->getId()] = $page;
		}
		
		return $pages;
	}
	
	public function addListToPage( Page &$page )
	{		
		$table_page = DataBase::objectToTableName( Page::getEmptyPage() );
		$table_list = $table_page.'_array';
		
		$query = 'SELECT * FROM `'.$table_list.'` WHERE page_id = \''.$page->getId().'\'';
		
		foreach ( DataBase::getInstance( _DB_DSN_PAGES )->fetchAll($query) as $row )
		{
			$page->addToList( $row['page_prop'], $row['value'], $row['key'] );
		}
		return $page;
	}


	/**
	 * List of elements in the language
	 * 
	 * @param string $lang	Language
	 * @return array		All the page for this language
	 */
	public function getAllByLang( $lang, $flagLoad = 0 )
	{
		return $this->getAll( 'WHERE _lang = \''.$lang.'\' ORDER BY _date', $flagLoad );
	}
	
	final protected function __construct() { }
	
	/**
	 * Return the elements with this ID (all langues)
	 * 
	 * @param string $name	Name of the elements
	 * @return array		List of the elements with the name
	 */
	public function getAllByName( $name, $flagLoad = 0 )
	{
		return $this->getAll( 'WHERE _name = \''.$name.'\' ORDER BY _date', $flagLoad );
	}
	
	/**
	 * Return a list of element with the tag
	 * 
	 * @param string $tag	Tag for the element
	 * @param string $lang	Language of the element
	 * @return array		List of the elements
	 */
	public function getByTag( $tag, $lang, $flagLoad = 0 )
    {
		$where = ' _lang = \''.$lang.'\' AND _tag = \''.$tag.'\'';
		return $this->getByList ( $where, $flagLoad );
    }
	
	/**
	 * Return a list of element with each element has at least one of your tags
	 * 
	 * @param array $tags	List of tags (withouts keys)
	 * @param string $lang	Language of the elements
	 * @return array		List of elements
	 */
	public function getWithOneOfTags( array $tags, $lang, $flagLoad )
    {
		$where = ' _lang = \''.$lang.'\' AND (';
		
		$first = true;
		foreach ($tags as $tag)
		{
			if ( !$first ) $where .= ' OR';
			$where .= ' _tags = \''.$tag.'\'';
			$first = false;
		}
		$where .= ')';
		
		return $this->getByList ( $where, $flagLoad );
    }
    
	/**
	 * Return a list of element with each element has each of tags
	 * 
	 * @param array $tags	List of tags
	 * @param string $lang	Language of the elements
	 * @return array		List of elements
	 */
	public function getWithAllTags( array $tags, $lang, $flagLoad = 0 )
    {
		$where = ' _lang = \''.$lang.'\' AND ';
		
		$first = true;
		foreach ($tags as $tag)
		{
			if ( !$first ) $where .= ' AND';
			$where .= ' _tags = \''.$tag.'\'';
			$first = false;
		}
		
		return $this->getByList ( $where, $flagLoad );
    }
	
	/**
	 * Return a list of element for a language
	 * 
	 * @param string $lang	Language of the elements
	 * @return array		List of elements
	 */
    public function getByLang( $lang, $flagLoad = 0 )
    {
        return $this->getAll( 'WHERE _lang = \''.$lang.'\' ORDER BY _date', $flagLoad );
    }
	
	/**
	 * Test if the element with this ID and this language exist
	 * 
	 * @param string $name	Name of the element
	 * @param string $lang	Language of the element
	 * @return boolean		Exist
	 */
	public function has( $name, $lang = null )
    {
		$tableName = DataBase::objectToTableName( Page::getEmptyPage() );
		
		$where = '_name = \''.$name.'\'';
		if ( $lang !== null ) { $where .= ' AND _lang = \''.$lang.'\''; }
		
		return ( DataBase::getInstance( _DB_DSN_PAGES )->count( $tableName, $where ) > 0);
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
	 * Update the current page.
	 * Used to build the body with the "build file" (ex: en-build.php).
	 * 
	 * @param Page $page	Page to update
	 * @return Page			Same page updated
	 */
	public function buildPage( &$page )
	{
		//return $this->getAll( 'WHERE _lang = \''.$lang.'\' ORDER BY _date', $flagLoad );
		$pages = $this->getAll('WHERE _id = \''.$page->getId().'\'', self::$LOAD_INIT | self::$LOAD_LIST );
		
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
	 * Get the page by relative URL
	 * 
	 * @param string $relURL	Relative URL
	 * @return Page				Corresponding page
	 */
    public function getByUrl( $relURL, $flagLoad = 0 )
    {
		if( _DEBUG && !General::getInstance()->getPagesInitialised() )
		{
			Debug::getInstance()->addError( 'All pages must be initialised after use getByUrl()' );
		}
		
		$tableName = stripslashes(get_called_class());
		
		
		// EXIST
		/*$pages = $this->getAll( 'WHERE _url LIKE \''.$relURL.'\' ORDER BY _date', $flagLoad);
		if ( count($pages) > 0 ) return current ($pages);*/
		$pages = $this->getByList( '_url LIKE \''.$relURL.'\' OR (page_prop = \'_additionalUrls\' AND value = \''.$relURL.'\')' );
		if ( count($pages) > 0 ) return current ($pages);
		

		// EXIST WITHOUT "/" AT THE END
		if ( strlen($relURL) > 0 && $relURL[strlen($relURL)-1] === '/' )
		{
			$urlTemp = substr( $relURL, 0, strlen($relURL)-1 );
			$pages = $this->getAll( 'WHERE _url LIKE \''.$urlTemp.'\' ORDER BY _date', $flagLoad);
			if ( count($pages) > 0 ) return current ($pages);
		}


		$lang = LangList::getInstance()->getLangByNavigator();

		// IS DYNAMIC PAGE
		$pages = $this->getAll( 'WHERE _url LIKE \''.$relURL.'\' AND _lang = \''.$lang.'\' ORDER BY _date', $flagLoad);
		if ( count($pages) > 0 )
		{
			$bestScore = 0;
			$page = null;
			foreach ($pages as $pageTemp)
			{
				$scoreTemp = $pageTemp->comparePageUrl($relURL);
				if ( $scoreTemp > $bestScore )
				{
					$bestScore = $scoreTemp;
					$page = $pageTemp;
				}
			}
			if( $page != null ) { return $page; }
		}

		if ( _DEBUG )
		{
			Debug::getInstance()->addError('The URL "'.$relURL.'" don\'t exist');
		}
		
		return $this->getDefaultPage($lang, $flagLoad);
    }
	
	/**
	 * Default page for the lang
	 * 
	 * @param string $lang	Language
	 * @return Page			default page
	 */
    public function getDefaultPage( $lang, $flagLoad = 0 )
    {
        $pages = $this->getAll( 'WHERE _type = \''.Page::$TYPE_DEFAULT.'\' AND _lang = \''.$lang.'\' ORDER BY _date LIMIT 1', $flagLoad);
		if ( count($pages) > 0 ) return current($pages);
		
		/* IF THE LANGUAGE OF THE DEFAULT PAGE DON'T EXIST */
		$pages = $this->getAll( 'WHERE _type = \''.Page::$TYPE_DEFAULT.'\' ORDER BY _date LIMIT 1', $flagLoad);
		if ( count($pages) > 0 ) return current($pages);
		
		/* IF THE DEFAULT PAGE DON'T EXIST */
		$pages = $this->getAll( 'WHERE _lang = \''.$lang.'\' ORDER BY _date LIMIT 1', $flagLoad);
		if ( count($pages) > 0 ) return current($pages);
		
		$pages = $this->getAll( 'WHERE _type = \''.Page::$TYPE_ERROR404.'\' AND _lang = \''.$lang.'\' ORDER BY _date LIMIT 1', $flagLoad);
		if ( count($pages) > 0 ) return current($pages);

		$pages = $this->getAll( 'WHERE _type = \''.Page::$TYPE_ERROR404.'\' ORDER BY _date LIMIT 1', $flagLoad);
		if ( count($pages) > 0 ) return current($pages);

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
	 * Get all pages visible (ex: for the sitemap.xml)
	 * 
	 * @param string $lang	Language
	 * @return array		List of the visible pages
	 */
    public function getAllVisible( $lang, $flagLoad = 0 )
    {
		$pages = $this->getAll( 'WHERE _visible = 1 AND _lang = \''.$lang.'\' ORDER BY _date', $flagLoad);
		return $pages;
    }
	
	/**
	 * Return the page or, if it doesn't exist, the default page
	 * 
	 * @param string $name	Name of the page
	 * @param string $lang	Language
	 * @return Page			Page corresponding
	 */
    public function getByName( $name, $lang, $flagLoad = 0 )
    {
		$pages = $this->getAll( 'WHERE _name = \''.$name.'\' AND _lang = \''.$lang.'\' ORDER BY _date LIMIT 1', $flagLoad);
		if ( count($pages) > 0 ) return current ($pages);
		
		if ( _DEBUG )
		{
			Debug::getInstance()->addError('The page '.$name.','.$lang.' don\'t exist');
		}
		
        return $this->getDefaultPage( $lang, $flagLoad );
    }
	
	/**
	 * Try if the URL exist.
	 * Same that hasKey()
	 * 
	 * @param string $url	Relative URL
	 * @return boolean		URL exist
	 */
	public function hasUrl( $url, $flagLoad = 0 )
    {
		$pages = $this->getAll( 'WHERE _url = \''.$url.'\' LIMIT 1', $flagLoad );
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
        $pages = $this->getAll( 'WHERE _url = \''.$url.'\' LIMIT 1', PageList::$LOAD_INIT );
		if ( count($pages) > 0 ) return current ($pages)->getLang();
		
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
	 * Instance of the list
	 * 
	 * @return static	Instance of the object ElementList
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
