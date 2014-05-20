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
    public function setUrl( $url ) { $this->_url = $url; }
	/**
	 * URL of the page
	 * 
	 * @return string
	 */
    public function getUrl() { return $this->_url; }

    protected $_lang;
	/**
	 * 
	 * @param string $lang
	 */
    public function setLang( $lang ) { $this->_lang = $lang; }
	/**
	 * 
	 * @return string
	 */
    public function getLang() { return $this->_lang; }

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
	
??????
	
	protected $_requests;
	/**
	 * 
	 * @param RequestPage $requestPage
	 * @param string $content
	 */
	public function addRequest( &$requestPage )
	{
		if ( _DEBUG && $this->hasRequest( $requestPage->getUrl() ) )
		{
			trigger_error( 'This request already exist: '.$requestPage->getUrl().' ('.$this->_id.', '.$this->_lang.')', E_USER_ERROR );
		}
		$this->_requests[$requestPage->getUrl()] = $requestPage;
	}
	
	/**
	 * 
	 * @param string $url
	 * @return boolean
	 */
    public function hasRequest( $url )
    {
        return array_key_exists( $url, $this->_requests );
    }
		
	/**
	 * 
	 * @param string $url
	 * @return RequestPage
	 */
	public function getRequest( $url )
    {
		if ( !$this->hasRequest($url) )
		{
			trigger_error( 'This request don\'t exist: '.$url.' ('.$this->_id.', '.$this->_lang.')', E_USER_ERROR );
		}
        return $this->_requests[ $url ];
    }
	
	/**
	 * 
	 * @return array
	 */
	public function getRequests()
    {
        return $this->_requests;
    }
	
	
	protected $_contents;
    /**
	 * 
	 * @param string $label
	 * @param string $value
	 */
	public function addContent( $label, $value )
	{
		if ( $this->hasContent($label) )
		{
			trigger_error( 'This content already exist: '.$label.' ('.$this->id.', '.$this->language.')', E_USER_ERROR);
		}
		$this->_contents[$label] = $value;
	}
	
	/**
	 * 
	 * @param array $arrayOfContentByLabel
	 */
    public function addContents( $arrayOfContentByLabel )
    {
        foreach ( $arrayOfContentByLabel as $label => $content )
        {
            $this->addContent( $label, $content );
        }
    }
	
	/**
	 * 
	 * @param string $label
	 * @return boolean
	 */
    public function hasContent( $label )
    {
		return array_key_exists( $label, $this->_contents );
    }
	
	/**
	 * Content with mustache's process
	 * 
	 * @param string $label
	 * @return string
	 */
	public function getContentFinal( $label )
    {
        return InitUtil::getInstance()->mustache($this->_contents[ $label ], $this);
    }
	
	/**
	 * Content without mustache's process
	 * 
	 * @param type $label
	 * @return type
	 */
	public function getContent( $label )
    {
		return $this->_contents[ $label ];
	}
	
	/**
	 * Contents (in array of string) with mustache's process
	 * 
	 * @return string
	 */
	public function getContentsFinal()
    {
		$contents = array();
		foreach ($this->_contents as $label => $content)
		{
			$contents[$label] = InitUtil::getInstance()->mustache($content, $this) ;
		}
        return $contents;
    }
	/**
	 * Contents (in array of string) without mustache's process
	 * 
	 * @return array
	 */
	public function getContents()
    {
		return $this->_contents;
    }
	
	/**
	 * 
	 * @param string $file
	 * @return string
	 */
	public function getAbsoluteUrl( $file )
    {
        return _ROOT_URL._CONTENT_DIRECTORY.$this->getId().'/'.$file;
    }
    
	
	/**
	 * 
	 * @return string
	 */
	public function getSave()
	{
		$obj = get_object_vars($this);
		$output = 'Page::update(new Page("'.$this->_id.'"),';
		$output .= SaveUtil::arrayToStrConstructor($obj);
		$output .= ')';
		
		return $output;
	}
	
	/**
	 * 
	 * @param Page $page
	 * @param array $save
	 * @return Page
	 */
	public static function update( &$page, $save )
	{
		foreach ($save as $key => $value)
		{
			$page->$key = $value;
		}
		return $page;
	}
	
	
	public function __construct( $id )
    {
        $this->_id = $id;
        $this->_tags = array();
		$this->_contents = array();
		$this->_requests = array();
        $this->_visible = true;
        
        // DEFAULT
        $this->_linkTitle = $id;
        $this->_htmlTitle = $id;
		$this->_htmlDescription = $id;
        //$this->_template = 'default';
		$this->_file2 = '';
		$this->_cachable = true;
		$this->_template = '';
		$this->_phpHeader = '';
    }
}
