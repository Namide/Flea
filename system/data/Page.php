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
class Page extends Element
{
	
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
	 * @param boolean $visible		Global variables GET explicit or not
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
	
	protected $_additionalUrl;
	/**
	 * Add an additionnal URL for this page (without Root and GET)
	 * 
	 * @param string $url		Additional URL of the page
	 */
    public function addAdditionalPageUrl( $url ) { $this->_additionalUrl[] = $url; }
	/**
	 * Add a list of additionnals URLs for this page (without Root and GET)
	 * 
	 * @param string $url		Additionals URLs of this page
	 */
    public function addAdditionalPageUrls( array $urls )
	{
		foreach ( $urls as $url )
        {
            $this->addAdditionalPageUrl( $url );
        }
	}
	/**
	 * Get additionals URLs of this page
	 * 
	 * @return string			Additional URL of this page
	 */
    public function getAdditionalPageUrls() { return $this->_additionalUrl; }
	
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
		if ( $this->_phpHeader != '' )
		{
			header( $this->_phpHeader );
		}

		if ( $this->_template != '' )
		{
			ob_start();
			include _TEMPLATE_DIRECTORY.$this->_template.'.php';
			$content = ob_get_clean();
			echo BuildUtil::getInstance()->replaceFleaVars( $content, $this );
		}
		else
		{
			echo '<!doctype html>';
			echo '<html><head>' , BuildUtil::getInstance()->replaceFleaVars( $this->_htmlHeader, $this );
			if ( $this->_htmlTitle != '' )
			{
				echo '<title>', BuildUtil::getInstance()->replaceFleaVars( $this->_htmlTitle, $this ), '</title>';
			}
			if ( $this->_htmlDescription != '' )
			{
				echo '<meta name="description" content="', BuildUtil::getInstance()->replaceFleaVars( $this->_htmlDescription, $this ), '"/>';
			}
			echo '</head><body>' , BuildUtil::getInstance()->replaceFleaVars( $this->_htmlBody, $this );
			echo '</body></html>';
		}
	}

	/**
	 * A page object contain all the datas of an HTML page
	 * 
	 * @param type $name		Name of the page
	 * @param type $lang		Language of the page
	 */
	public function __construct( $name = '', $lang = null )
    {
		parent::__construct($name, $lang);
		
        $this->_visible = true;
        $this->_getEnabled = false;
		$this->_getExplicit = true;
		$this->_cachable = true;
		$this->_additionalUrl = array();
		
		$this->_url = '';
		
		$this->_htmlTitle = $name;
		$this->_htmlDescription = $name;
		$this->_htmlHeader = '';
		$this->_htmlBody = '';
		
        $this->_template = '';
		
		$this->_phpHeader = '';
		$this->_buildFile = '';
    }
	
	public static function getEmptyPage()
	{
		return new Page();
	}
	
	public function getObjectVars()
	{
		return get_object_vars($this);
	}
	
	/**
	 * Get a script for create the same object
	 * 
	 * @return string		The save text
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
	 * @return self				Page with the news values
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
