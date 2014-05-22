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
	 * @param string $pageId
	 * @param string $lang
	 * @return Page
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
	 * @param string $url
	 * @return string
	 */
    public function getAbsUrlByPageUrl( $url, array $gets = null, $explicitGet = true )
    {
		$relUrl = UrlUtil::getInstance()->getRelUrlByPageUrl( $url, $gets, $explicitGet );
		
		return _ROOT_URL.$relUrl;
    }
    
	/**
	 * Absolute URL for a page
	 * 
	 * @param string $pageName
	 * @param string $lang
	 * @param array $getUrl
	 * @return string
	 */
	public function getAbsUrlByIdLang( $pageName, $lang, array $getUrl = null )
    {
		$PageList = PageList::getInstance();
		$page = $PageList->getByName($pageName, $lang);
		$relUrl = UrlUtil::getInstance()->getRelUrlByIdLang($page, $lang, $getUrl);
		
        return _ROOT_URL.$relUrl;
    }
	
	public function reset()
	{
		$pageList = PageList::getInstance();
		$general = General::getInstance();
        if ( _DEBUG && !$general->getPagesInitialised() )
		{
			trigger_error( 'All pages must be initialised after use BuildUtil class', E_USER_ERROR );
		}
		
        $pageUrl = $general->getCurrentPageUrl();
        
        $page = $pageList->getPageByUrl( $pageUrl );
        //$this->_page = $page;
        //$this->_language = $page->getLanguage();
		$general->setCurrentPage($page);
	}
    
	/**
	 * Absolute URL for a page
	 * 
	 * @param string $pageName
	 * @return string
	 */
    public function getAbsUrl( $pageName )
    {
		$lang = $this->getLang();
        //return PageUtils::getAbsoluteUrl($idPage, $lang);
		return $this->getAbsUrlByIdLang($pageName, $lang);
    }
	
	/**
	 * 
	 * @param string $idPage
	 * @param string $tagBefore
	 * @param string $tagAfter
	 * @return string
	 */
	public function getLink( $idPage, $tagBefore = '', $tagAfter = '', $argsInA = '' )
	{
		$lang = General::getInstance()->getCurrentLang();
		$pageList = PageList::getInstance();
		$page = $pageList->getPage( $idPage, $lang );
		return '<a href="'.$this->urlPageToAbsUrl( $page->getUrl() ).'"'.$argsInA.'>'.$tagBefore.$page->getTitle().$tagAfter.'</a>';
	}
	
	
	/**
	 * 
	 * @param string $text
	 * @param Page $page
	 * @return Page
	 */
	public function render( $text, Page &$page = null )
    {
		if ( $page !== null )
		{
			$replacePage = preg_replace('/\{\{pathCurrentPage:(.*?)\}\}/', $page->getAbsoluteUrl('$1'), $text);
		}
		$replacePage = preg_replace('/\{\{urlPageToAbsoluteUrl:(.*?)\}\}/', $this->urlPageToAbsUrl('$1'), $replacePage);
        $replacePage = preg_replace('/\{\{pathTemplate:(.*?)\}\}/', $this->getTemplateAbsUrl('$1'), $replacePage);
		$replacePage = preg_replace('/\{\{pathContent:(.*?)\}\}/', $this->getContentAbsUrl('$1'), $replacePage);

		$pageList = PageList::getInstance();
		if ( General::getInstance()->getPagesInitialised() && $page !== null )
		{
			$replacePage = preg_replace_callback( '/\{\{idPageToAbsoluteUrl:(.*?)\}\}/', function ($matches) use($page)
			{
				$lang = $page->getLang();
				return InitUtil::getInstance()->getAbsUrlByIdLang( $matches[1], $lang );
			}, $replacePage );
		}

        return $replacePage;
    }
}
