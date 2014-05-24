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

$timestart = microtime(true);
$initTime = 0;

if ( _DEBUG )
{
	if (!ini_get('display_errors')) { ini_set('display_errors', '1'); }
	error_reporting(E_ALL);
}

if ( _CACHE )
{
	include_once _SYSTEM_DIRECTORY.'helpers/system/Cache.php';
	$cache = new Cache( _CACHE_DIRECTORY.'pages/' );
	
	$fileName = UrlUtil::urlPageToStr( UrlUtil::getNavigatorRelUrl() );
	
	if( $cache->isWrite( $fileName ) )
	{
		$cache->echoSaved($fileName);
		if ( _DEBUG )
		{
			echo '<!-- load cache time: ', number_format( microtime(true) - $timestart , 3) , 'ms -->';
			Debug::getInstance()->dispatchErrors();
		}
		exit();
	}
	elseif( $cache->isCachable( $fileName ) )
	{
		
		include_once _SYSTEM_DIRECTORY.'init/import.php';
		include_once _SYSTEM_DIRECTORY.'init/loadPages.php';
		include_once _SYSTEM_DIRECTORY.'init/buildPage.php';
		
		$page = General::getInstance()->getCurrentPage();
		
		$cache->isCachable( $page );
		if ( $cache->isCachable( $page ) )
		{
			$cache->startSave();
				$page->render();
			$cache->stopSave();
			$content = BuildUtil::getInstance()->replaceFleaVars( $cache->getSaved(), $page );
			$cache->setSaved( $content );
			$cache->writesCache( $fileName );
			echo $cache->getSaved();


			if ( _DEBUG )
			{
				echo '<!-- execute PHP and write cache time: ', number_format( microtime(true) - $timestart , 3), 'ms -->';
				Debug::getInstance()->dispatchErrors();
			}
		}
		else
		{
			$page->render();
		}
		exit();
	}
}

include_once _SYSTEM_DIRECTORY.'init/import.php';
include_once _SYSTEM_DIRECTORY.'init/loadPages.php';
include_once _SYSTEM_DIRECTORY.'init/buildPage.php';
$page = General::getInstance()->getCurrentPage();
$page->render();

if ( _DEBUG )
{
	echo '<!-- execute PHP time: ', number_format( microtime(true) - $timestart , 3),'ms -->';
	Debug::getInstance()->dispatchErrors();
}