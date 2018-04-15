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

$t = microtime(true);

if (_DEBUG) {
	include_once _SYSTEM_DIRECTORY . 'helpers/system/Debug.php';
	Debug::getInstance();
	if (!ini_get('display_errors')) {
		ini_set('display_errors', '1');
	}
	error_reporting(E_ALL);
}

include _SYSTEM_DIRECTORY . 'init/checkDir.php';

if (_CACHE) {

	include_once _SYSTEM_DIRECTORY . 'data/SqlQuery.php';
	include_once _SYSTEM_DIRECTORY . 'data/DataBase.php';
	include_once _SYSTEM_DIRECTORY . 'helpers/system/Cache.php';
	include_once _SYSTEM_DIRECTORY . 'helpers/system/UrlUtil.php';
	$cache = new Cache(_DB_DSN_CACHE);

	$urlStr = UrlUtil::urlPageToStr(UrlUtil::getNavigatorRelUrl());

	if ($cache->isWrited($urlStr)) {
		$cache->echoSaved($urlStr);
		if (_DEBUG) {
			Debug::getInstance()->dispatchErrors();
		}
	} else {
		include_once _SYSTEM_DIRECTORY . 'init/import.php';
		include_once _SYSTEM_DIRECTORY . 'init/loadPages.php';
		include_once _SYSTEM_DIRECTORY . 'init/buildPage.php';

		$page = General::getInstance()->getCurrentPage();
		$html = $page->render();

		if ($cache->isPageCachable($page)) {
			if (_GZIP_CSS_JS) {

				// GZIP ALL CSS AND JS FILES
				$regex = '#(href|src)="(' . _ROOT_URL . '[^"]+(\.js|\.css))"#i';
				preg_match_all($regex, $html, $out);
				for ($i = 0; $i < count($out[2]); $i++) {
					
					$url = $out[2][$i];
					if ( substr($url, 0, strlen(_ROOT_URL)) == _ROOT_URL )
						$url = './' . substr($url, strlen(_ROOT_URL));
					
					$newUrl = str_replace(_ROOT_URL, '', $url) . '.gz';
					if (!$cache->isWrited($newUrl)) {
						$type = substr(strrchr($url, '.'), 1);
						$head = ($type == 'css') ? 'Content-type: text/css' : 'Content-Type: application/javascript';

						// REPLACE REL URL BY ABS URL IN THE CSS FILE
						if ($type == 'css') {
							include_once _SYSTEM_DIRECTORY . 'helpers/miscellaneous/FileUtil.php';
							$content = FileUtil::getCssContentWithAbsUrl($url);
							$cache->writeCache($newUrl, $head, $content);
						} else {
							$content = file_get_contents($url);
							$cache->writeCache($newUrl, $head, $content);
						}
					}

					$absUrl = BuildUtil::getInstance()->getAbsUrlByPageUrl($newUrl);
					$html = str_replace($url, $absUrl, $html);
				}
			}

			$cache->writeCache($urlStr, $page->getPhpHeader(), $html);

			if (_DEBUG) {
				Debug::getInstance()->addTimeMark('write cache');
			}
		}

		echo $html;

		if (_DEBUG) {
			Debug::getInstance()->dispatchErrors();
		}
	}

	exit();
}

include_once _SYSTEM_DIRECTORY . 'init/import.php';
include_once _SYSTEM_DIRECTORY . 'init/loadPages.php';
include_once _SYSTEM_DIRECTORY . 'init/buildPage.php';
$page = General::getInstance()->getCurrentPage();
echo $page->render();

if (_DEBUG) {
	Debug::getInstance()->dispatchErrors();
}
