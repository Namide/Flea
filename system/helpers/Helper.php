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
 * Shortcuts to all main helpers
 *
 * @author Namide
 */
class Helper
{
	
	private function __construct() { }
	
	private static $_PAGE_LIST;
	/**
	 * List of pages in the website
	 * 
	 * @return PageList
	 */
	public static function getPageList()
	{
		if ( Helper::$_PAGE_LIST === null )
		{
			include_once _SYSTEM_DIRECTORY.'data/list/PageList.php';
			Helper::$_PAGE_LIST = PageList::getInstance();
		}
		return Helper::$_PAGE_LIST;
	}
	
	private static $_BUILD_UTIL;
	/**
	 * Utils to write pages and template.
	 * This class cannot be used before the building page time.
	 * 
	 * @return BuildUtil
	 */
	public static function getBuildUtil()
	{
		if ( Helper::$_BUILD_UTIL === null )
		{
			include_once _SYSTEM_DIRECTORY.'helpers/common/BuildUtil.php';
			Helper::$_BUILD_UTIL = BuildUtil::getInstance();
		}
		return Helper::$_BUILD_UTIL;
	}
	
	private static $_INIT_UTIL;
	/**
	 * All simple methods usable after pages building.
	 * You can use it during the initialization state.
	 * 
	 * @return InitUtil
	 */
	public static function getInitUtil()
	{
		if ( Helper::$_INIT_UTIL === null )
		{
			include_once _SYSTEM_DIRECTORY.'helpers/common/InitUtil.php';
			Helper::$_INIT_UTIL = InitUtil::getInstance();
		}
		return Helper::$_INIT_UTIL;
	}
	
	private static $_URL_UTIL;
	/**
	 * URL managment
	 * 
	 * @return UrlUtil
	 */
	public static function getUrlUtil()
	{
		if ( Helper::$_URL_UTIL === null )
		{
			include_once _SYSTEM_DIRECTORY.'helpers/system/UrlUtil.php';
			Helper::$_URL_UTIL = UrlUtil::getInstance();
		}
		return Helper::$_URL_UTIL;
	}
	
	private static $_TAG_UTIL;
	/**
	 * Helper for tags.
	 * You can use this class for create tags ( a, img, breadcrump )
	 * 
	 * @return TagUtil
	 */
	public static function getTagUtil()
	{
		if ( Helper::$_TAG_UTIL === null )
		{
			include_once _SYSTEM_DIRECTORY.'helpers/miscellaneous/TagUtil.php';
			Helper::$_TAG_UTIL = TagUtil::getInstance();
		}
		return Helper::$_TAG_UTIL;
	}
	
	private static $_GENERAL;
	/**
	 *  All the general data changeable (current page, state...)
	 * 
	 * @return General
	 */
	public static function getGeneral()
	{
		if ( Helper::$_GENERAL === null )
		{
			include_once _SYSTEM_DIRECTORY.'data/General.php';
			Helper::$_GENERAL = General::getInstance();
		}
		return Helper::$_GENERAL;
	}
}
