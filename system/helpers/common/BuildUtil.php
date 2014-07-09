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
 * Utils to write pages and template.
 * This class cannot be used before the building page time.
 *
 * @author Namide
 */
class BuildUtil extends InitUtil
{
	/**
	 * Get the page with the pageName
	 * 
	 * @param string $pageName		Name of the page
	 * @param string $lang			Language of the page (if null it's the current language)
	 * @return Page					Instance of the Page
	 */
	public function getPage( $pageName, $lang = null )
	{
		if ( $lang === null )
		{
			$lang = General::getInstance()->getCurrentLang();
		}
		return PageList::getInstance()->getByName($pageName, $lang);
	}

	/**
	 * Get the absoulte URL of a page by his page URL
	 * 
	 * @param string $url		Page URL
	 * @return string			Absolute URL of the page
	 */
    public function getAbsUrlByPageUrl( $url, array $gets = null, $explicitGet = true )
    {
		$relUrl = UrlUtil::getInstance()->getRelUrlByPageUrl( $url, $gets, $explicitGet );
		
		return _ROOT_URL.$relUrl;
    }
    
	/**
	 * Absolute URL for a page
	 * 
	 * @param string $pageName		Name of the page
	 * @param string $lang			Language of the page
	 * @param array $getUrl			Array of GET of the page (optional)
	 * @return string				Absolute URL
	 */
	public function getAbsUrlByNameLang( $pageName, $lang, array $getUrl = null )
    {
		$PageList = PageList::getInstance();
		$page = $PageList->getByName($pageName, $lang);
		$relUrl = UrlUtil::getInstance()->getRelUrlByIdLang($page, $lang, $getUrl);
		
        return _ROOT_URL.$relUrl;
    }
	
	/**
	 * Reset the object (new evaluation of current page)
	 */
	public function reset()
	{
		$pageList = PageList::getInstance();
		$general = General::getInstance();
        if ( _DEBUG && !$general->isPagesInitialized() )
		{
			Debug::getInstance()->addError( 'All pages must be initialised after use BuildUtil class' );
		}
        $pageUrl = $general->getCurrentPageUrl();
        $page = $pageList->getByUrl( $pageUrl );
		$general->setCurrentPage($page);
	}
    
	/**
	 * Absolute URL for a page
	 * 
	 * @param string $pageName	Name of the page
	 * @return string			Absolute URL of the page		
	 */
    public function getAbsUrl( $pageName )
    {
		$lang = General::getInstance()->getCurrentLang();
		
		return $this->getAbsUrlByNameLang($pageName, $lang);
    }
	
	public function getContentOfPage(	$contentKey, Page &$page, $replaceFleaVars = false )
	{
		if ( $page->getContents()->length() < 1 )
		{
			PageList::getInstance()->addListToPage($page);
		}
		
		if( !$page->getContents()->hasKey( $contentKey ) ) 
		{
			if( _DEBUG )
			{
				Debug::getInstance()->addError('The content "'.$contentKey.'" '
				. ' don\'t exist for the page ['.$page->getId().']' );
			}
			return '';
		}

		$content = $page->getContents()->getValue($contentKey);
		if ( $replaceFleaVars )
		{
			return $this->replaceFleaVars($content, $page);
		}
		
		return $content;
	}
	
	/**
	 * Format the text by converting the Flea variables to real datas.
	 * List of Flea variables :
	 * - {{rootPath}}			=> URL of the root
	 * - {{templatePath}}		=> URL of the template directory
	 * - {{contentPath}}		=> URL of the content directory
	 * - {{pageContentPath}}	=> URL of the page in the content directory
	 * - {{lang}}				=> current language
	 * - {{title}}				=> title of the current page
	 * - {{header}}				=> HTML header of the current page
	 * - {{body}}				=> HTML body of the current page
	 * - {{description}}		=> HTML description of the current page
	 * - {{content:additionnal-label-content}}	=> $currentPage->getContent('additionnal-label-content');
	 * - {{pageNameToAbsUrl:page-name}}			=> $buildUtil->getAbsUrlByNameLang( ‘page-name', $currentLanguage );	
	 * 
	 * @param string $text		Original text
	 * @param Page $page		Current page
	 * @return string			Formated text				
	 */
	public function replaceFleaVars( $text, Page &$page = null )
    {
		$replacePage = str_replace('{{rootPath}}', _ROOT_URL, $text);
		$replacePage = str_replace('{{templatePath}}', _ROOT_URL._TEMPLATE_DIRECTORY, $replacePage);
		$replacePage = str_replace('{{contentPath}}', _ROOT_URL._CONTENT_DIRECTORY, $replacePage);
		
		$general = General::getInstance();
		
		$replacePage = str_replace('{{lang}}', $general->getCurrentLang(), $replacePage);
		
		if ( $page === null )
		{
			$page = $general->getCurrentPage();
		}
		
		$replacePage = str_replace('{{pageContentPath}}', _ROOT_URL._CONTENT_DIRECTORY.$page->getName(), $replacePage);

		$replacePage = str_replace('{{title}}', $page->getHtmlTitle(), $replacePage);
		$replacePage = str_replace('{{header}}', $page->getHtmlHeader(), $replacePage);
		$replacePage = str_replace('{{body}}', $page->getHtmlBody(), $replacePage);
		$replacePage = str_replace('{{description}}', $page->getHtmlDescription(), $replacePage);
		
			
		if ( /*General::getInstance()->isPagesInitialized() &&*/ $page !== null )
		{
			$replacePage = preg_replace_callback( '/\{\{content:(.*?)\}\}/', function ($matches) use($page)
			{
				// update the lists if they are empty
				if ( $page->getContents()->length() < 1 )
				{
					PageList::getInstance()->addListToPage($page);
				}
				
				if( !$page->getContents()->hasKey($matches[1]) ) 
				{
					if( _DEBUG )
					{
						Debug::getInstance()->addError('The FleaVar {{content:'.$matches[1].'}}'
						. ' don\'t exist for the page ['.$page->getId().']' );
					}
					return '';
				}
				
				return $page->getContents()->getValue($matches[1]);
				
			}, $replacePage );
		
			$replacePage = preg_replace_callback( '/\{\{pageNameToAbsUrl:(.*?)\}\}/', function ($matches) use($page)
			{
				$lang = $page->getLang();
				
				if ( !PageList::getInstance()->has($matches[1], $lang) )
				{
					if( _DEBUG )
					{
						Debug::getInstance()->addError('The page {{pageNameToAbsUrl:'.$matches[1].'}}'
						. ' don\'t exist for the language ['.$lang.']' );
					}
					return '';
				}
				
				return BuildUtil::getInstance()->getAbsUrlByNameLang( $matches[1], $lang );
			}, $replacePage );
		}

        return $replacePage;
    }
}
