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

include_once _SYSTEM_DIRECTORY.'data/Saver.php';
include_once _SYSTEM_DIRECTORY.'data/list/LangList.php';
include_once _SYSTEM_DIRECTORY.'data/Element.php';
include_once _SYSTEM_DIRECTORY.'data/list/ElementList.php';
include_once _SYSTEM_DIRECTORY.'data/Page.php';
include_once _SYSTEM_DIRECTORY.'data/list/PageList.php';

include_once _SYSTEM_DIRECTORY.'data/General.php';
include_once _SYSTEM_DIRECTORY.'helpers/system/UrlUtil.php';
include_once _SYSTEM_DIRECTORY.'helpers/common/InitUtil.php';
include_once _SYSTEM_DIRECTORY.'helpers/common/BuildUtil.php';

if ( _CACHE )
{
	include_once _SYSTEM_DIRECTORY.'helpers/miscellaneous/FileUtil.php';
	include_once _SYSTEM_DIRECTORY.'helpers/system/DataUtil.php';
	include_once _SYSTEM_DIRECTORY.'helpers/system/Cache.php';
}
