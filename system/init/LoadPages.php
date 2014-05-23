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

$lang = LangList::getInstance();
include_once _CONTENT_DIRECTORY.'init-lang.php';

$pageList = PageList::getInstance();
$langs = $lang->getList();
addPagesRecurs( _CONTENT_DIRECTORY, $lang, $pageList );
//include_once _CONTENT_DIRECTORY.'pages.php';
//$pageList->go();
//UrlUtil::getInstance();
General::getInstance()->setPagesInitialised(true);

// HELPERS FOR TEMPLATES
//include_once _SYSTEM_DIRECTORY.'helpers/BuildUtil.php';
BuildUtil::getInstance();

function addPagesRecurs( $dir, &$langs, PageList &$pageList )
{
	if ( !file_exists($dir) )
	{
		return;
	}
	
	$dirOpen = opendir($dir);
    while($file = @readdir($dirOpen))
    {
		if ($file == "." || $file == "..") continue;

        if( is_dir($dir.'/'.$file) )
        {
            addPagesRecurs( $dir.'/'.$file.'/', $langs, $pageList );
			$pageList->createPage( $dir.'/'.$file );
        }
    }
    closedir($dirOpen);
}
