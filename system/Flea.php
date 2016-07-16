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

//namespace Flea;

/**
 * Shortcuts to all main helpers and class of the framework
 *
 * @author Namide
 */
class Flea {

	private function __construct() {
		
	}

	private static $_PAGE_LIST = false;

	/**
	 * List of pages in the website
	 * 
	 * @return PageList
	 */
	public static function getPageList() {
		if (!Flea::$_PAGE_LIST) {
			include_once _SYSTEM_DIRECTORY . 'data/list/PageList.php';
			Flea::$_PAGE_LIST = true;
		}
		return \Flea\PageList::getInstance();
	}

	private static $_BUILD_UTIL = false;

	/**
	 * Utils to write pages and template.
	 * This class cannot be used before the building page time.
	 * 
	 * @return BuildUtil
	 */
	public static function getBuildUtil() {
		if (!Flea::$_BUILD_UTIL) {
			include_once _SYSTEM_DIRECTORY . 'helpers/common/BuildUtil.php';
			Flea::$_BUILD_UTIL = true;
		}
		return \Flea\BuildUtil::getInstance();
	}

	private static $_INIT_UTIL = false;

	/**
	 * All simple methods usable after pages building.
	 * You can use it during the initialization state.
	 * 
	 * @return InitUtil
	 */
	public static function getInitUtil() {
		if (!Flea::$_INIT_UTIL) {
			include_once _SYSTEM_DIRECTORY . 'helpers/common/InitUtil.php';
			Flea::$_INIT_UTIL = true;
		}
		return \Flea\InitUtil::getInstance();
	}

	private static $_URL_UTIL = false;

	/**
	 * URL managment
	 * 
	 * @return UrlUtil
	 */
	public static function getUrlUtil() {
		if (!Flea::$_URL_UTIL) {
			include_once _SYSTEM_DIRECTORY . 'helpers/system/UrlUtil.php';
			Flea::$_URL_UTIL = true;
		}
		return \Flea\UrlUtil::getInstance();
	}

	private static $_TAG_UTIL = false;

	/**
	 * Helper for tags.
	 * You can use this class for create tags ( a, img, breadcrump )
	 * 
	 * @return TagUtil
	 */
	public static function getTagUtil() {
		if (!Flea::$_TAG_UTIL) {
			include_once _SYSTEM_DIRECTORY . 'helpers/miscellaneous/TagUtil.php';
			Flea::$_TAG_UTIL = true;
		}
		return \Flea\TagUtil::getInstance();
	}

	private static $_GENERAL = false;

	/**
	 * All the general data changeable (current page, state...)
	 * 
	 * @return General
	 */
	public static function getGeneral() {
		if (!Flea::$_GENERAL) {
			include_once _SYSTEM_DIRECTORY . 'data/General.php';
			Flea::$_GENERAL = true;
		}
		return \Flea\General::getInstance();
	}

	private static $_LOGIN = false;

	/**
	 * Login manager
	 * 
	 * @param type $dbDsn		Data Source Name of the data base used for this Login
	 * @return Login
	 */
	public static function getLogin($dbDsn) {
		if (!Flea::$_LOGIN) {
			include_once _SYSTEM_DIRECTORY . 'helpers/miscellaneous/Login.php';
			Flea::$_LOGIN = true;
		}
		return \Flea\Login::getInstance($dbDsn);
	}

	private static $_HEADER = false;

	/**
	 * Utils to write php header of a page.
	 * 
	 * @return BuildUtil
	 */
	public static function getHeader() {
		if (!Flea::$_HEADER) {
			include_once _SYSTEM_DIRECTORY . 'helpers/system/Header.php';
			Flea::$_HEADER = true;
		}
		return \Flea\Header::getInstance();
	}

}
