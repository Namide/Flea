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

if( _DEBUG )
{
	\Flea\Debug::getInstance()->setErrorBackNum(0);
}

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
			<td><?= (_URL_REWRITING) ? '<strong class="passed">true</strong>'
					: '<strong class="error">false</strong>' ?></td>
		</tr>
		<tr>
			<th>Debug mode</th>
			<td><?= (_DEBUG) ? '<strong class="error">true</strong>'
					: '<strong class="passed">false</strong>' ?></td>
		</tr>
		<tr>
			<th>Cache activated</th>
			<td><?= (_CACHE) ? '<strong class="passed">true</strong>'
					: '<strong class="error">false</strong>' ?></td>
		</tr>
		<tr>
			<th>SQL</th>
			<td>
				<?php
					echo 'Available drivers:<ul>';
					foreach( \PDO::getAvailableDrivers() as $driver)
					{
						echo '<li>'.$driver.'</li>';
					}
					echo '</ul>';

					if ( _DB_DSN_PAGE !== null )
					{
						try
						{
							$pdo = new \PDO( _DB_DSN_PAGE, _DB_USER, _DB_PASS, _DB_OPTIONS );
						}
						catch( \PDOException $e )
						{
							echo '<strong class="error">Connection to PDO error: ', $e->getMessage(), '</strong>';
						}
					}
				?>
			</td>
		</tr>
		<tr>
			<th>Pages cached</th>
			<td>
				<?php
					$cache = new \Flea\Cache(_DB_DSN_CACHE);
					$pagesCachedNum = $cache->getNumFilesSaved();
					
					echo '<strong class="' ,
							( $pagesCachedNum >= _MAX_PAGE_CACHE ||
							( $pagesCachedNum / _MAX_PAGE_CACHE > 0.95 )
							? 'error' : 'passed' ).'">'
							, $pagesCachedNum .' / '._MAX_PAGE_CACHE
							, '</strong>';
				?></td>
		</tr>
		
	</tbody>
</table>

<table>
	<tbody>
		
		<tr>
			<th colspan="7">
				<h2>Pages - details</h2>
			</th>
		</tr>
		<tr>
			<th>num</th>
			<th>url</th>
			<th>directory</th>
			<th>lang</th>
			<th>content errors</th>
			<th>content link(s) <div id="link-checker"><button onclick="processor.go();">check</button></div></th>
			<th>SEO <div id="seo-test"><button onclick="seoTest.start('seo-test');">start</button></div></th>
		</tr>
		
		<?php
		
		$request = \Flea\SqlQuery::getTemp( \Flea\SqlQuery::$TYPE_SELECT );
		$request->setWhere('_visible > -1 OR _visible < 0');
		\Flea\General::getInstance()->initializesPages();
		$pages = \Flea\PageList::getInstance()->getAll( $request );
		$i = 0;
		$seoList = '[';
		
		foreach ($pages as $id => $page)
		{
			if( $i > 0 ) { $seoList .= ', '; }
			
			\Flea\General::getInstance()->setCurrentPage($page);
			
			$absURL = \Flea\BuildUtil::getInstance()->getAbsUrlByNameLang($page->getName(), $page->getLang() );
			
			//$page = new Page();
			echo '<tr><td>' , ++$i , '</td>';
			echo '<td><a href="' , $absURL , '">' 
				, $page->getPageUrl() , '</a></td>';
			echo '<td>' , $page->getName() , '</td>';
			echo '<td>' , $page->getLang() , '</td>';
			if(_DEBUG)
			{
				echo '<td>' , \Flea\Debug::getInstance()->dispatchErrors() , '</td>';
			}
			else
			{
				echo '<td><strong class="error">debug mode disable</strong></td>';
			}
			echo '<td>';
			
			$links = array();
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
			echo '<td id="seo'.$i.'">' , '</td></tr>';
			
				$seoList .= '{ url:"' . $absURL;
				$seoList .= '", id:"seo'.$i.'" }';
			
		}
		
		$seoList .= ']';
		
		?>
		
	</tbody>
</table>




<!-- SCRIPTS JS -->

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>
<script type="text/javascript"><?php include _SYSTEM_DIRECTORY.'admin/board/js/linkChecker.js'; ?></script>

<script type="text/javascript" >
	<?php include _SYSTEM_DIRECTORY.'admin/board/js/seoTest.js'; ?>
	var seoTest = new SeoTest( <?php echo $seoList; ?> );
</script>

<script>

	var errorsNum = 0;

	var processor = new LinkChecker.LinkProcessor( document.querySelectorAll("a.checkURL") );
	processor.on(	
					LinkChecker.events.started,
					function(numberOfLinks)
					{
						$("div#link-checker").html("Links to check: " + numberOfLinks)
					}
				);


	processor.on(
					LinkChecker.events.checked,
					function(link)
					{
						$("div#link-checker").html( "Internal link checked: " + $(link.elem).html() );

						if(link.broken)
						{
							$(link.elem).addClass("broken-link").css(
							{
								color: "red"
							});

							errorsNum++;
						}
						else
						{
							$(link.elem).css(
							{
								color: "green"
							});
						}
					}
				);
	processor.on(LinkChecker.events.completed, function(link) {
		var char = ( errorsNum < 1 ) ? "<strong style=\"color:green;\" >All urls ok</strong>" : "<strong style=\"color:red;\" >" + errorsNum + " links broken</strong>";
		$("div#link-checker").html( char );
	});
	//processor.go();				

</script>
