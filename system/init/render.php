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

if ( _DEBUG )
{
	include_once _SYSTEM_DIRECTORY.'helpers/system/Debug.php';
	Debug::getInstance();
	if (!ini_get('display_errors')) { ini_set('display_errors', '1'); }
	error_reporting(E_ALL);
}

include _SYSTEM_DIRECTORY.'init/checkDir.php';

if ( _CACHE )
{
	
	include_once _SYSTEM_DIRECTORY.'data/SqlQuery.php';
	include_once _SYSTEM_DIRECTORY.'data/DataBase.php';
	include_once _SYSTEM_DIRECTORY.'helpers/system/Cache.php';
	include_once _SYSTEM_DIRECTORY.'helpers/system/UrlUtil.php';
	$cache = new Cache(_DB_DSN_CACHE);
	
	$urlStr = UrlUtil::urlPageToStr( UrlUtil::getNavigatorRelUrl() );
	
	if( $cache->isWrited( $urlStr ) )
	{
		$cache->echoSaved( $urlStr );
		if ( _DEBUG )
		{
			//echo '<!--'.Debug::getInstance()->getTimes('load cache time').'-->';
			Debug::getInstance()->dispatchErrors();
		}
	}
	else
	{
		include_once _SYSTEM_DIRECTORY.'init/import.php';
		include_once _SYSTEM_DIRECTORY.'init/loadPages.php';
		include_once _SYSTEM_DIRECTORY.'init/buildPage.php';
		
		$page = General::getInstance()->getCurrentPage();
		
		if ( $cache->isPageCachable( $page ) )
		{
			//$cache->startSave();
			//$content = $page->render();
			//$cache->stopSave();
			//$content = \Flea::getBuildUtil()->replaceFleaVars( $content, $page );
			$cache->setContent( $page->render() ); // $content
			$cache->writeCache( $urlStr, $page->getPhpHeader() );
			
			if ( _DEBUG )
			{
				Debug::getInstance()->addTimeMark('write cache');
			}
		}
		
		echo $page->render();
		
		if ( _DEBUG )
		{
			//echo '<!--'.Debug::getInstance()->getTimes('render page time').'-->';
			Debug::getInstance()->dispatchErrors();
		}
	}
	
	exit();
}

include_once _SYSTEM_DIRECTORY.'init/import.php';
include_once _SYSTEM_DIRECTORY.'init/loadPages.php';
include_once _SYSTEM_DIRECTORY.'init/buildPage.php';
$page = General::getInstance()->getCurrentPage();
echo $page->render();

if ( _DEBUG )
{
	//echo '<!--'.Debug::getInstance()->getTimes('execute PHP time').'-->';
	Debug::getInstance()->dispatchErrors();
}
