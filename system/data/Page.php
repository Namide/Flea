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
	 * set phpHeader
	 * 
	 * @param string $phpHeader
	 */
    public function setPhpHeader( $phpHeader ) { $this->_phpHeader = $phpHeader; }
	/**
	 * phpHeader if the page has a special format (XML...)
	 * 
	 * @return string
	 */
    public function getPhpHeader() { return $this->_phpHeader; }
	
    protected $_visible;
	/**
	 * set the visibility of the page
	 * 
	 * @param boolean $visible
	 */
    public function setVisible( $visible ) { $this->_visible = $visible; }
	/**
	 * If the page is visible (sitemap.xml...)
	 * 
	 * @return boolean
	 */
    public function getVisible() { return $this->_visible; }

    protected $_getEnabled;
	/**
	 * Active or unactive the GET method
	 * 
	 * @param boolean $visible
	 */
    public function setGetEnabled( $enabled ) { $this->_getEnabled = $enabled; }
	/**
	 * If the GET is activated the page can have several URL with the same base
	 * 
	 * @return boolean
	 */
    public function getGetEnabled() { return $this->_getEnabled; }

	protected $_getExplicit;
	/**
	 * Active or unactive the explicit GET
	 * 
	 * @param boolean $visible
	 */
    public function setGetExplicit( $explicit ) { $this->_getExplicit = $explicit; }
	/**
	 * If the GET is explicit the URL contains the labels of values.
	 * URL: www.flea.namide.com/games
	 * GET: array( 'page'=>2, 'tag'=>'RTS' );
	 * ( explicit ) www.flea.namide.com/games/page/2/tag/RTS
	 * ( !explicit ) www.flea.namide.com/games/2/RTS
	 * 
	 * @return boolean
	 */
    public function getGetExplicit() { return $this->_getExplicit; }

	
    protected $_cachable;
	/**
	 * set the chability of the page
	 * 
	 * @param boolean $cachable
	 */
	public function setCachable( $cachable ) { $this->_cachable = $cachable; }
	/**
	 * Cachability of the page.
	 * It's recomended to don't cache a page with POST datas.
	 * 
	 * @return boolean
	 */
    public function getCachable() { return $this->_cachable; }

    protected $_url;
	/**
	 * Set the URL of the page
	 * 
	 * @param string $url
	 */
    public function setPageUrl( $url ) { $this->_url = $url; }
	/**
	 * URL of the page
	 * 
	 * @return string
	 */
    public function getPageUrl() { return $this->_url; }

	/**
	 * Compare the URL, if this page accept GET it can accept others URL.
	 * = -1 if the URL is different
	 * = +x to x = the sames caracters
	 * 
	 * @param string $pageUrl
	 * @return int
	 */
	public function comparePageUrl( $pageUrl )
	{
		$thisLength = strlen($this->_url);
		if ( $this->_url == $pageUrl )
		{
			return $thisLength+1;
		}
		
		if ( !$this->_getEnabled )
		{
			return -1;
		}
		
		$otherLength = strlen($pageUrl);
		if ( $thisLength > $otherLength )
		{
			return -1;
		}
		
		if ( $this->_url == substr($pageUrl, 0, $thisLength) )
		{
			return $thisLength;
		}
		
		return 0;
	}
	
    protected $_HtmlHeader;
	/**
	 * Set the header php
	 * 
	 * @param string $header
	 */
    public function setHtmlHeader( $header ) { $this->_HtmlHeader = $header; }
	
	/**
	 * HTML content in the header
	 * 
	 * @return type
	 */
	public function getHtmlHeader() { return $this->_HtmlHeader; }
	
    protected $_htmlBody;
	/**
	 * Set the body of the page
	 * 
	 * @param string $body
	 */
    public function setHtmlBody( $body ) { $this->_htmlBody = $body; }
	
	/**
	 * Body content in the HTML page
	 * 
	 * @return type
	 */
	public function getHtmlBody() { return $this->_htmlBody; }

    protected $_htmlTitle;
	/**
	 * Set the title of the page
	 * 
	 * @param string $title
	 */
    public function setHtmlTitle( $title ) { $this->_htmlTitle = $title; }
	/**
	 * Title in the HTML page
	 * 
	 * @return string
	 */
    public function getHtmlTitle() { return $this->_htmlTitle; }

	protected $_htmlDescription;
	/**
	 * Set the description of the page
	 * 
	 * @param string $description
	 */
    public function setHtmlDescription( $description ) { $this->_htmlDescription = $description; }
	/**
	 * Description of the page (in the header)
	 * 
	 * @return string
	 */
    public function getHtmlDescription() { return $this->_htmlDescription; }
	
    protected $_template;
	/**
	 * Change the template of the page
	 * 
	 * @param string $template
	 */
    public function setTemplate( $template ) { $this->_template = $template; }
	/**
	 * Template used for the page
	 * 
	 * @return string
	 */
    public function getTemplate() { return $this->_template; }

	protected $_buildFile;
	/**
	 * Change the build file of the page
	 * 
	 * @param string $buildFile
	 */
    public function setBuildFile( $buildFile ) { $this->_buildFile = $buildFile; }
	/**
	 * Used in second time to update the content(s) of the page
	 * 
	 * @return string
	 */
    public function getBuildFile() { return $this->_buildFile; }
	
	public function __construct( $name = '', $lang = null )
    {
		parent::__construct($lang);
		
        $this->_visible = true;
        $this->_getEnabled = false;
		$this->_getExplicit = true;
		$this->_cachable = true;
		
		$this->_url = '';
		
		$this->_htmlTitle = $name;
		$this->_htmlDescription = $name;
		$this->_HtmlHeader = '';
		$this->_htmlBody = '';
		
        $this->_file2 = '';
		$this->_template = '';
		
		$this->_phpHeader = '';
		$this->_buildFile = '';
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
