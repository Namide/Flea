<?php

/*
 * The MIT License
 *
 * Copyright 2014 damien.
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
 * Helper for tags.
 * You can use this class for create tags ( a, img, breadcrump )
 *
 * @author damien
 */
class TagUtil
{
	
	private function __construct() { }
	
	/**
	 * Simple method to create a link.
	 * Ex:
	 * - $pageName = 'home';
	 * - $tagBefore = '<em>';
	 * - $tagAfter = '</em>';
	 * - $attInA = ' class="link-home blue"';
	 * =>  <a href="http://flea.namide.com/en/home" class="link-home blue">Home page<em></em></a>
	 * 
	 * @param string $pageName		Name of the page to linked
	 * @param string $tagBefore		Tag before the title of the page (in the tag <a></a>)
	 * @param string $tagAfter		Tag after the title of the page (in the tag <a></a>)
	 * @param string $attInA		Additionnal attribute to the tag <a></a>
	 * @return string				HTML link generated	
	 */
	public static function getLink( $pageName, $tagBefore = '', $tagAfter = '', $attInA = '' )
	{
		$lang = General::getInstance()->getCurrentLang();
		$pageList = PageList::getInstance();
		$page = $pageList->getByName( $pageName, $lang );
		$buildUtil = BuildUtil::getInstance();
		return '<a href="'.$buildUtil->getAbsUrlByPageUrl( $page->getPageUrl() ).'" '.$attInA.' hreflang="'.$page->getLang().'">'.$tagBefore.$page->getHtmlTitle().$tagAfter.'</a>';
	}
	
	/**
	 * Simple method to create an img
	 * 
	 * @param type $fileName	Name of the image to include
	 * @param type $alt			Alternative content of the tag <img/>
	 * @param type $attInImg	Additionnal attribute to the tag <img/>
	 * @return type				Tag img with : alt, width, height and $attInImg
	 */
	public static function getImg( $fileName, $alt = '', $attInImg = '' )
	{
		if ( !is_file($fileName) )
		{
			return '<img src="'.$fileName.'" alt="'.$alt.'" '.$attInImg.'/>';
		}
		list( $width, $height, $type, $attr ) = getimagesize($filename);
		
		$img = '<img src="'.$fileName.'" width="'.$width.'" height="'.$height.'"';
		if ( $alt != '' ) $img.= ' alt="'.$alt.'"';
		if ( $attInImg != '' ) $img.= ' '.$attInImg;
		$img .= '/>';
		return  $img;
	}
	
	/**
	 * Simple method to get breadcrump of the current page.
	 * It have microdatas.
	 * 
	 * @param Page $currentPage		Current page (optional if the pages are initialized)
	 * @param string $delimiter		String between the links
	 * @return string				Tag of the breadcrump
	 */
	public static function getBreadcrump( Page $currentPage = null, $delimiter = ' > ' )
	{
		if ( $currentPage === null )
		{
			if ( _DEBUG && !General::getInstance()->isPagesInitialized() )
			{
				Debug::getInstance()->addError( 'All pages must be initialised after use TagUtil::getBreadcrump( $argument ) method without argument' );
			}
			$currentPage = General::getInstance()->getCurrentPage();
		}
		
		$path = explode('/', $currentPage->getPageUrl() ) ;
		$numParentsPages = count($path);
		$output = '';
		
		if ( $numParentsPages > 1 )
		{
			$output = '<nav class="breadcrumb"><ul>';
			foreach ($path as $l => $url)
			{
				$url = $path[0];
				for( $i = 1; $i <= $l ; $i++ )
				{
					$url .= '/'.$path[$i];
				}
				$temp = '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
				$temp .= '<a href="'.BuildUtil::getInstance()->getAbsUrlByPageUrl($url).'" itemprop="url">';
				$temp .= '<span itemprop="title">'.PageList::getInstance()->getByUrl($url)->getHtmlTitle().'</span>';
				$temp .= '</a></li>';
				$output .= ( $l > 0 ) ? $delimiter : '';
				$output .= $temp;
			}
			$output .= '</ul></nav>';
		}
		return $output;
	}
	
}
