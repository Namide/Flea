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
 * All the datas of a page
 *
 * @author namide.com
 */
class Page
{
	public static $TYPE_DEFAULT = 'default';
	public static $TYPE_ERROR404 = 'error404';
	public static $TYPE_REDIRECT301 = 'redirect301';
	
	public static $_EMPTY = null;
	
	private $_id;
	/**
	 * ID of the page.
	 * The ID is unique, it's composed by the name and the language of the page.
	 * 
	 * @return string		ID of the page
	 */
	public function getId() { return $this->_id; }
	private function updateId()
	{
		$this->_id = $this->_name.','.$this->_lang;
	}
	
	private $_name;
	/**
	 * Name of the Page.
	 * Like an ID, but an page has the same ID for differents languages
	 * 
	 * @param string $name	Name of the Page
	 */
    public function setName( $name ) { $this->_name = $name; $this->updateId(); }
	/**
	 * Name of the Page
	 * 
	 * @return string	Name of the Page
	 */
    public function getName() { return $this->_name; }
	
	private $_cover;
	/**
	 * Cover URL of the Page.
	 * 
	 * @param string $src	URL of the cover
	 */
    public function setCover( $src ) { $this->_cover = $src; }
	/**
	 * Cover URL of the Page
	 * 
	 * @return string	URL of the cover
	 */
    public function getCover() { return $this->_cover; }
	
	private $_lang;
	/**
	 * Language of the Page (fr, en, ko...)
	 * The list of languages is in the LangList.php
	 * 
	 * @param string $lang	Language
	 */
    public function setLang( $lang )
	{
		if ( _DEBUG && !LangList::getInstance()->has($lang) )
		{
			Debug::getInstance()->addError( 'The Language '.$lang.' don\'t exist');
		}
		$this->_lang = $lang;
		$this->updateId();
	}
	/**
	 * Language of the Page (fr, en, ko...)
	 * The list of languages is in the LangList.php
	 * 
	 * @return string	Language
	 */
    public function getLang() { return $this->_lang; }
	
	private $_date;
	/**
	 * Date of the Page, no set format
	 * 
	 * @param string $date	Date
	 */
    public function setDate( $date )
	{
		$this->_date = $date;
	}
	/**
	 * Date of the Page
	 * 
	 * @return string	Date
	 */
    public function getDate() { return $this->_date; }
	
	private $_type;
	/**
	 * Type of the page.
	 * The page can be :
	 * - Page::$TYPE_DEFAULT, 
	 * - Page::$TYPE_ERROR404, 
	 * - '', 
	 * 
	 * @param string $type	Type of the page
	 */
    public function setType( $type )
	{
		if (	_DEBUG &&
				$type !== '' &&
				$type !== Page::$TYPE_DEFAULT &&
				$type !== Page::$TYPE_ERROR404 &&
				$type !== Page::$TYPE_REDIRECT301 )
		{
			Debug::getInstance()->addError('The type: '.$type.' don\'t exist');
		}
		$this->_type = $type;
	}
	/**
	 * Type of the page
	 * 
	 * @return string		Type of the page
	 */
    public function getType() { return $this->_type; }
	
	private $_tags;
	/**
	 * Tags of the page.
	 * You can use tags for search a list of pages.
	 * 
	 * @return DataList		List of tags
	 */
	public function getTags()
	{
		if ( $this->_tags === null )
		{
			$this->_tags = new DataList(false);
			
			$table_page = DataBase::objectToTableName( $this );
			if ( DataBase::getInstance( _DB_DSN_PAGE )->exist($table_page) )
			{
				$table_list = $table_page.'_array';

				$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
				$where = array( 'page_id'=>$this->getId(), 'page_prop'=>'_tags' );
				$query->initSelect( 'value', '`'.$table_list.'`', $where );
				
				$rows = DataBase::getInstance( _DB_DSN_PAGE )->fetchAll($query);
				foreach ( $rows as $row )
				{
					$this->_tags->add( $row['value'] );
				}
			}
		}
		
		return $this->_tags;
	}
	
	private $_contents;
	/**
	 * A content is a pair with key and value.
	 * You can't add 2 contents with same label.
	 * 
	 * @return DataList 
	 */
	public function getContents()
	{
		if ( $this->_contents === null )
		{
			$this->_contents = new DataList(true);
			
			$table_page = DataBase::objectToTableName( $this );
			if ( DataBase::getInstance( _DB_DSN_PAGE )->exist($table_page) )
			{
				$table_list = $table_page.'_array';

				$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
				$where = array( 'page_id'=>$this->getId(), 'page_prop'=>'_contents' );
				$query->initSelect( 'key, value', '`'.$table_list.'`', $where );
				$rows = DataBase::getInstance( _DB_DSN_PAGE )->fetchAll($query);
				foreach ( $rows as $row )
				{
					$content = BuildUtil::getInstance ()->replaceFleaVars ( $row['value'], $this );
					$this->_contents->add($content, $row['key']);
				}
			}
			
			
		}
		return $this->_contents;
	}
	
	protected $_phpHeader;
	/**
	 * Set phpHeader (for header() function)
	 * 
	 * @param string $phpHeader		PHP header
	 */
    public function setPhpHeader( $phpHeader ) { $this->_phpHeader = $phpHeader; }
	/**
	 * phpHeader if the page has a special format (XML...)
	 * 
	 * @return string	PHP header
	 */
    public function getPhpHeader() { return $this->_phpHeader; }
	
    protected $_visible;
	/**
	 * Set the visibility of the page
	 * 
	 * @param boolean $visible	Visibility of the page
	 */
    public function setVisible( $visible ) { $this->_visible = $visible; }
	/**
	 * If the page is visible (sitemap.xml...)
	 * 
	 * @return boolean	Visibility of the page
	 */
    public function getVisible() { return $this->_visible; }

    protected $_getEnabled;
	/**
	 * Active or unactive the GET method
	 * 
	 * @param boolean $enabled		Global variables GET enabled
	 */
    public function setGetEnabled( $enabled ) { $this->_getEnabled = $enabled; }
	/**
	 * If the GET is activated the page can have several URL with the same base
	 * 
	 * @return boolean		Global variables GET enabled
	 */
    public function getGetEnabled() { return $this->_getEnabled; }

	protected $_getExplicit;
	/**
	 * Active or unactive the explicit GET
	 * 
	 * @param boolean $explicit		Global variables GET explicit or not
	 */
    public function setGetExplicit( $explicit ) { $this->_getExplicit = $explicit; }
	/**
	 * If the GET is explicit the URL contains the labels of values.
	 * URL: www.flea.namide.com/games
	 * GET: array( 'page'=>2, 'tag'=>'RTS' );
	 * ( explicit ) www.flea.namide.com/games/page/2/tag/RTS
	 * ( !explicit ) www.flea.namide.com/games/2/RTS
	 * 
	 * @return boolean		Global variables GET explicit or not
	 */
    public function getGetExplicit() { return $this->_getExplicit; }

	
    protected $_cachable;
	/**
	 * set the cachability of the page
	 * 
	 * @param boolean $cachable		Is the page cachable or not
	 */
	public function setCachable( $cachable ) { $this->_cachable = $cachable; }
	/**
	 * Cachability of the page.
	 * It's recomended to don't cache a page with POST datas.
	 * 
	 * @return boolean		Is the page cachable or not
	 */
    public function getCachable() { return $this->_cachable; }

    protected $_url;
	/**
	 * Set the URL of the page (without Root and GET)
	 * 
	 * @param string $url		URL of the page
	 */
    public function setPageUrl( $url ) { $this->_url = $url; }
	/**
	 * URL of the page (without Root and GET)
	 * 
	 * @return string	URL of the page
	 */
    public function getPageUrl() { return $this->_url; }
	
	protected $_additionalUrls;
	/**
	 * Additionnal(s) URL for this page (without Root and GET)
	 * 
	 * @return DataList
	 */
    public function getAdditionalUrls()
	{
		if ( $this->_additionalUrls === null )
		{
			$this->_additionalUrls = new DataList(false);
			
			$table_page = DataBase::objectToTableName( $this );
			if ( DataBase::getInstance( _DB_DSN_PAGE )->exist($table_page) )
			{
				$table_list = $table_page.'_array';

				$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
				$where = array( 'page_id'=>$this->getId(), 'page_prop'=>'_additionalUrls' );
				$query->initSelect( 'value', '`'.$table_list.'`', $where );
				$rows = DataBase::getInstance( _DB_DSN_PAGE )->fetchAll($query);
				foreach ( $rows as $row )
				{
					$this->_additionalUrls->add( $row['value'] );
				}
			}
		}
		
		return $this->_additionalUrls;
		
	}
	
	/**
	 * Compare the URL, if this page accept GET it can accept others URL.
	 * = -1 if the URL is different
	 * = +x to x = the sames caracters
	 * 
	 * @param string $pageUrl	URL of the page (without root, with GET)
	 * @return int				If they are differents: -1 or 0 , otherwise positive
	 */
	public function comparePageUrl( $pageUrl )
	{
		$thisLength = strlen($this->_url);
		if ( $this->_url == $pageUrl )	{ return $thisLength+1; }
		if ( !$this->_getEnabled )		{ return -1; }
		
		$otherLength = strlen($pageUrl);
		if ( $thisLength > $otherLength ) { return -1; }
		if ( $this->_url == substr($pageUrl, 0, $thisLength) ) { return $thisLength; }
		
		return 0;
	}
	
    protected $_htmlHeader;
	/**
	 * Add to the content header HTML (links CSS, links JS...)
	 * 
	 * @param string $header	HTML header
	 */
    public function setHtmlHeader( $header ) { $this->_htmlHeader = $header; }
	
	/**
	 * HTML content in the header
	 * 
	 * @return type		HTML header
	 */
	public function getHtmlHeader() { return $this->_htmlHeader; }
	
    protected $_htmlBody;
	/**
	 * Set the body of the page
	 * 
	 * @param string $body		Body of the page
	 */
    public function setHtmlBody( $body ) { $this->_htmlBody = $body; }
	
	/**
	 * Body content in the HTML page
	 * 
	 * @return type			Body of the page
	 */
	public function getHtmlBody() { return $this->_htmlBody; }

    protected $_htmlTitle;
	/**
	 * Set the title of the page
	 * 
	 * @param string $title		Title of the page
	 */
    public function setHtmlTitle( $title ) { $this->_htmlTitle = $title; }
	/**
	 * Title in the HTML page
	 * 
	 * @return string			Title of the page
	 */
    public function getHtmlTitle() { return $this->_htmlTitle; }

	protected $_htmlDescription;
	/**
	 * Set the description of the page
	 * 
	 * @param string $description		Desciption of the page
	 */
    public function setHtmlDescription( $description ) { $this->_htmlDescription = $description; }
	/**
	 * Description of the page (in the header)
	 * 
	 * @return string		Desciption of the page
	 */
    public function getHtmlDescription() { return $this->_htmlDescription; }
	
    protected $_template;
	/**
	 * Change the template of the page
	 * 
	 * @param string $template		Template of the page
	 */
    public function setTemplate( $template ) { $this->_template = $template; }
	/**
	 * Template used for the page
	 * 
	 * @return string		Template of the page
	 */
    public function getTemplate() { return $this->_template; }

	protected $_buildFile;
	/**
	 * Change the build file of the page
	 * 
	 * @param string $buildFile		Build file
	 */
    public function setBuildFile( $buildFile ) { $this->_buildFile = $buildFile; }
	/**
	 * Used in second time to update the content(s) of the page
	 * 
	 * @return string		Build file
	 */
    public function getBuildFile() { return $this->_buildFile; }
	
	/**
	 * Echo the page (with template and Flea variables {{...}} transformed
	 */
	public function render()
	{
		if ( _DEBUG && $this->_template != '' &&
			!file_exists(_TEMPLATE_DIRECTORY.$this->_template.'.php') )
		{
			Debug::getInstance()->addError( 'The template "'._TEMPLATE_DIRECTORY.$this->_template.'.php don\'t exist": page:'.$this->_id );
		}
		
		if ( $this->_template != '' && file_exists(_TEMPLATE_DIRECTORY.$this->_template.'.php') )
		{
			if ( $this->_phpHeader != '' )
			{
				header( $this->_phpHeader );
			}
		
			ob_start();
			include _TEMPLATE_DIRECTORY.$this->_template.'.php';
			$content = ob_get_clean();
			echo BuildUtil::getInstance()->replaceFleaVars( $content, $this );
		}
		else
		{
			$this->renderWithoutTemplate();
		}
	}
	
	/**
	 * Echo the page with Flea variables {{...}} transformed but without template
	 */
	public function renderWithoutTemplate()
	{
		if ( $this->_phpHeader != '' )
		{
			header( $this->_phpHeader );
		}
		
		if ( $this->_type === Page::$TYPE_REDIRECT301 )
		{
			
			$absNewURL = BuildUtil::getInstance()->replaceFleaVars( $this->_htmlBody, $this );
			header( 'Status: 301 Moved Permanently' );
			header( 'Location: '.$absNewURL );
			exit;
		}
		
		
		echo '<!doctype html><html><head><meta charset="UTF-8" />';
		echo BuildUtil::getInstance()->replaceFleaVars( $this->_htmlHeader, $this );
		if ( $this->_htmlTitle != '' )
		{
			echo '<title>', BuildUtil::getInstance()->replaceFleaVars( $this->_htmlTitle, $this ), '</title>';
		}
		if ( $this->_htmlDescription != '' )
		{
			echo '<meta name="description" content="', BuildUtil::getInstance()->replaceFleaVars( $this->_htmlDescription, $this ), '"/>';
		}
		echo '</head><body>' , BuildUtil::getInstance()->replaceFleaVars( $this->_htmlBody, $this ), '</body></html>';
	}

	/**
	 * A page object contain all the datas of an HTML page
	 * 
	 * @param type $name		Name of the page
	 * @param type $lang		Language of the page
	 */
	public function __construct( $name = '', $lang = null )
    {
		if ( $lang === null )
		{
			$lang = LangList::getInstance()->getDefault();
		}
		$this->setLang( $lang );
		$this->setName( $name );
		$this->_contents = null;
		$this->_tags = null;
		
		$this->_type = '';
		$this->_date = '';
		
        $this->_visible = true;
        $this->_getEnabled = false;
		$this->_getExplicit = true;
		$this->_cachable = true;
		$this->_additionalUrls = null;
		
		$this->_url = '';
		
		$this->_htmlTitle = $name;
		$this->_htmlDescription = $name;
		$this->_htmlHeader = '';
		$this->_htmlBody = '';
		
		$this->_cover = '';
        $this->_template = '';
		
		$this->_phpHeader = '';
		$this->_buildFile = '';
    }
	
	/**
	 * Get an empty page to use temporarily.
	 * 
	 * @return Page		Empty page
	 */
	public static function getEmptyPage()
	{
		if ( Page::$_EMPTY === null ) Page::$_EMPTY = new Page();
		return Page::$_EMPTY;
	}
	
	/**
	 * Get an associative array with all the unstatic properties of the page
	 * (public, private and protected).
	 * The DataList properties are converted in an array.
	 * 
	 * @return array		Associative array with all the properties		
	 */
	public function getObjectVars()
	{
		$obj = get_object_vars($this);
		foreach ($obj as $key => $value)
		{
			if( gettype($value) == 'object' &&
				get_class($value) == get_class( DataList::getEmptyDataList() ) )
			{
				$obj[$key] = $value->getArray();
			}
		}
		
		return $obj;
	}
	
	/**
	 * Set all this Page with an object_vars
	 * 
	 * @param array $obj	Object_vars (associative array)
	 */
	public function setByObjectVars( $obj )
	{
		foreach ($obj as $key => $value)
		{
			if ( gettype($value) == 'array' )
			{
				$this->$key = new DataList();
				$this->$key->setByArray( $value );
			}
			else
			{
				$this->$key = $value;
			}
		}
	}
}
