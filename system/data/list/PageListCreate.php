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
 * Description of PageListCreate
 *
 * @author Namide
 */
class PageListCreate
{
	private static $_INSTANCE = null;
	
	/**
	 * Add all pages of a directory
	 * 
	 * @param type $dir				Root directory
	 * @param type $fileDirRel		Relative directory (for the recursivity)	
	 */
	public function addPagesByDir( $dir )
	{
		$listOfPages = $this->addPageByDirRecurs( $dir, '' );
		$this->db_insertPages($listOfPages);
	}
	
	protected function db_insertPages( array $list )
	{
		$tableName = DataBase::objectToTableName( Page::getEmptyPage() );
		
		$db = DataBase::getInstance(_DB_DSN_PAGES);
		
		/*$pageVars = Page::getEmptyPage()->getObjectVars();
		$db->create( $pageVars, $tableName, true);*/
		$request = SqlQuery::getTemp( SqlQuery::$TYPE_CREATE );
		$request->initCreate($tableName, Page::getEmptyPage()->getObjectVars() );
		$db->execute( $request );
		
		$request = SqlQuery::getTemp( SqlQuery::$TYPE_CREATE );
		$request->initCreate($tableName.'_array', array( 'page_id'=>'TEXT', 'page_prop'=>'TEXT', 'key'=>'TEXT', 'value'=>'TEXT') );
		/*$sql = 'CREATE TABLE `'.$tableName.'_array` ( page_id TEXT, page_prop TEXT, key TEXT, value TEXT );';*/
		$db->execute( $request );
		
		foreach ($list as $page) 
		{
			$pageVars = $page->getObjectVars();
			/*$db->insert( $pageVars, $tableName, true );*/
			
			$request = SqlQuery::getTemp( SqlQuery::$TYPE_INSERT );
			$request->initInsertValues( 'INTO `'.$tableName.'`', $pageVars);
			$db->execute($request);
			
			foreach ($pageVars as $key => $value)
			{
				if(	gettype($value) == 'array' )
				{
					foreach ($value as $key2 => $val2)
					{
						$obj = array();
						$obj['page_id'] = $pageVars['_id'];
						$obj['page_prop'] = $key;
						$obj['key'] = $key2;
						$obj['value'] = $val2;
						//$db->insert( $obj, $tableName.'_array', true);
						
						$request->clean( SqlQuery::$TYPE_INSERT );
						$request->initInsertValues( 'INTO `'.$tableName.'_array`', $obj );
						$db->execute($request);
					}
				}
			}
		}
	}
	
	
	protected function addPageByDirRecurs( $dir, $fileDirRel = '' )
	{
		$list = array();
		
		if ( !file_exists($dir) ) { return $list; }

		$dirOpen = opendir($dir);
		while($file = @readdir($dirOpen))
		{
			if( $file != "." &&
				$file != ".." &&
				is_dir($dir.'/'.$file) )
			{
				$list1 = $this->addPageByDirRecurs( $dir.'/'.$file.'/', $fileDirRel.'/'.$file );
				$list2 = $this->createPage( (($fileDirRel != '')?$fileDirRel.'/':'').$file );
				
				
				$list = array_merge($list, $list1, $list2);
			}
		}
		closedir($dirOpen);
		
		return $list;
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
		
		if ( _DEBUG && !isset($url) )
		{
			Debug::getInstance()->addError( 'The initialisation of a page must to have an URL' );
		}
		
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
	
	final protected function __construct() { }
	
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
        if(PageListCreate::$_INSTANCE === null)
        {
            PageListCreate::$_INSTANCE = new PageListCreate();
        }
        return PageListCreate::$_INSTANCE;
    }
}
