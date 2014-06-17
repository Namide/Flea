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

namespace Flea\admin;

include_once _SYSTEM_DIRECTORY.'data/DataBase.php';
include_once _SYSTEM_DIRECTORY.'helpers/system/Cache.php';
include_once _SYSTEM_DIRECTORY.'init/import.php';
		
?>

<table>
	<tbody>
		
		<tr>
			<th colspan="2">
				<h2>Parameters</h2>
			</th>
		</tr>
		<tr>
			<th>URL rewriting activated</th>
			<td><?= (_URL_REWRITING) ? '<strong class="passed">true</strong>' : '<strong class="error">false</strong>' ?></td>
		</tr>
		<tr>
			<th>Debug mode</th>
			<td><?= (_DEBUG) ? '<strong class="error">true</strong>' : '<strong class="passed">false</strong>' ?></td>
		</tr>
		<tr>
			<th>Cache activated</th>
			<td><?= (_CACHE) ? '<strong class="passed">true</strong>' : '<strong class="error">false</strong>' ?></td>
		</tr>
		<tr>
			<th>Pages cached</th>
			<td>
				<?php
					$cache = new \Flea\Cache(_DB_DSN_CACHE);
					$pagesCachedNum = $cache->getNumFilesSaved();
					
					echo '<strong class="'.( ( $pagesCachedNum / _MAX_PAGE_CACHE > 0.95 ) ? 'error' : 'passed' ).'">'
						, $pagesCachedNum .' / '._MAX_PAGE_CACHE
						, '</strong>';
					(_URL_REWRITING) ? '<strong class="passed">true</strong>' : '<strong class="error">false</strong>'
					
				?></td>
		</tr>
		
	</tbody>
</table>

<table>
	<tbody>
		
		<tr>
			<th colspan="6">
				<h2>Pages - details</h2>
			</th>
		</tr>
		<tr>
			<th>num</th>
			<th>url</th>
			<th>directory</th>
			<th>lang</th>
			<th>link(s)</th>
			<th>SEO</th>
		</tr>
		
		<?php
		
		$request = null;
		$pages = \Flea\PageList::getInstance()->getAll( $request, \Flea\PageList::$LOAD_INIT | \Flea\PageList::$LOAD_LIST );
		$i = 0;
		foreach ($pages as $id => $page)
		{
			//$page = new Page();
			echo '<tr><td>' , $i++ , '</td>';
			echo '<td>' , $page->getPageUrl() , '</td>';
			echo '<td>' , $page->getName() , '</td>';
			echo '<td>' , $page->getLang() , '</td>';
			echo '<td>';
			
			$links = array();
			\Flea\General::getInstance()->setPagesInitialised(true);
			\Flea\PageList::getInstance()->buildPage($page);
			$body = $page->getHtmlBody();
			foreach ( $page->getContents()->getArray() as $content )
			{
				$body .= $content;
			}
			$body = \Flea\BuildUtil::getInstance()->replaceFleaVars( $body, $page );
			$regex = "\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))";
			preg_match_all("%$regex%s", $body, $links);
			$links = $links[0];
			foreach ($links as $link)
			{
				echo '<a href="' , $link, '" class="checkURL">', $link, '</a><br>';
			}
			
			echo '</td>';
			echo '<td>' , 1 , '</td></tr>';
			
		}
		
		?>
		
	</tbody>
</table>
