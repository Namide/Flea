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

include _CONTENT_DIRECTORY.'initBegin.php';

$lang = LangList::getInstance();
include _CONTENT_DIRECTORY.'initLang.php';

$pageList = PageList::getInstance();
$langs = $lang->getList();
addPagesRecurs( _CONTENT_DIRECTORY, $lang, $pageList, '' );
General::getInstance()->setPagesInitialised(true);

UrlUtil::getInstance();
BuildUtil::getInstance();

function addPagesRecurs( $dir, &$langs, PageList &$pageList, $fileDirRel )
{
	if ( !file_exists($dir) ) { return; }
	
	$dirOpen = opendir($dir);
    while($file = @readdir($dirOpen))
    {
		if ($file == "." || $file == "..") { continue; }

        if( is_dir($dir.'/'.$file) )
        {
            addPagesRecurs( $dir.'/'.$file.'/', $langs, $pageList, $fileDirRel.'/'.$file );
			$pageList->createPage( (($fileDirRel != '')?$fileDirRel.'/':'').$file );
        }
    }
    closedir($dirOpen);
}
