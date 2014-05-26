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
 * Description of BuildUtil
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
	public function getAbsUrlByIdLang( $pageName, $lang, array $getUrl = null )
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
        if ( _DEBUG && !$general->getPagesInitialised() )
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
		
		return $this->getAbsUrlByIdLang($pageName, $lang);
    }
	
	/**
	 * Simple method to create a link.
	 * Ex:
	 * <code>
	 * - $pageName = 'home';
	 * - $tagBefore = '<em>';
	 * - $tagAfter = '</em>';
	 * - $attInA = ' class="link-home blue"';
	 * =>  <a href="http://flea.namide.com/en/home" class="link-home blue">Home page<em></em></a>
	 * </code>
	 * 
	 * @param string $pageName		Name of the page to linked
	 * @param string $tagBefore		Tag before the title of the page (in the tag <a></a>)
	 * @param string $tagAfter		Tag after the title of the page (in the tag <a></a>)
	 * @param string $attInA		Additionnal attribute to the tag <a></a>
	 * @return string				HTML link generated	
	 */
	public function getLink( $pageName, $tagBefore = '', $tagAfter = '', $attInA = '' )
	{
		$lang = General::getInstance()->getCurrentLang();
		$pageList = PageList::getInstance();
		$page = $pageList->getPage( $idPage, $lang );
		return '<a href="'.$this->urlPageToAbsUrl( $page->getUrl() ).'" '.$attInA.'>'.$tagBefore.$page->getTitle().$tagAfter.'</a>';
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
	 * - {{pageNameToAbsUrl:page-name}}			=> $buildUtil->getAbsUrlByIdLang( ‘page-name', $currentLanguage );	
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
		$currentPage = $general->getCurrentPage();
		
		$replacePage = str_replace('{{lang}}', $general->getCurrentLang(), $replacePage);
		$replacePage = str_replace('{{pageContentPath}}', _ROOT_URL._CONTENT_DIRECTORY.$currentPage->getName(), $replacePage);

		$replacePage = str_replace('{{title}}', $currentPage->getHtmlTitle(), $replacePage);
		$replacePage = str_replace('{{header}}', $currentPage->getHtmlHeader(), $replacePage);
		$replacePage = str_replace('{{body}}', $currentPage->getHtmlBody(), $replacePage);
		$replacePage = str_replace('{{description}}', $currentPage->getHtmlDescription(), $replacePage);
		
			
		if ( General::getInstance()->getPagesInitialised() && $page !== null )
		{
			$replacePage = preg_replace_callback( '/\{\{content:(.*?)\}\}/', function ($matches) use($page)
			{
				$currentPage = General::getInstance()->getCurrentPage();
				return $currentPage->getContent($matches[1]);
			}, $replacePage );
		
			$replacePage = preg_replace_callback( '/\{\{pageNameToAbsUrl:(.*?)\}\}/', function ($matches) use($page)
			{
				$lang = $page->getLang();
				return BuildUtil::getInstance()->getAbsUrlByIdLang( $matches[1], $lang );
			}, $replacePage );
		}

        return $replacePage;
    }
}
