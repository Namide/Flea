<?php
if( _DEBUG )
{
	\Flea\Debug::getInstance()->setErrorBackNum(0);
}

function getLine( $num, $url, $dir, $lang, $errors, $links )
{
	return '<tr><td>' . $num. '</td>'
	. '<td>'. $url. '</td>'
	. '<td>'. $dir. '</td>'
	. '<td>'. $lang. '</td>'
	. '<td class="error">'. $errors. '</td>'
	. '<td>'. $links. '</td>'
	. '<td id="seo'.$num.'"></td></tr>';
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
					include_once _SYSTEM_DIRECTORY.'helpers/system/Cache.php';
				
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
		//\Flea\General::getInstance()->initializesPages();
		$pages = \Flea\PageList::getInstance()->getAll( $request );
		$i = 0;
		$seoList = '[';
		
		foreach ($pages as $id => $pageTemp)
		{
			
			if ( $pageTemp->getType() != \Flea\Page::$TYPE_REDIRECT301 &&
				 $pageTemp->getFormat() == \Flea\Page::$FORMAT_HTML &&
				 !$pageTemp->getTags()->hasValue('flea-admin') )
			{
				
				if( $i > 0 ) { $seoList .= ', '; }

				if( _DEBUG )
				{
					\Flea\Debug::getInstance()->clear();
				}
				
				$i++;
				$body = $pageTemp->render(false);
				$absURL = Flea::getBuildUtil()->getAbsUrlByNameLang($pageTemp->getName(), $pageTemp->getLang() );
				
				$url = '<a href="' . $absURL . '">' . $pageTemp->getPageUrl() . '</a>';
				
				$errors = 'debug mode disable';
				if(_DEBUG)
				{
					$errors = \Flea\Debug::getInstance()->getErrorsHtml();
				}
				if ( $errors == '' )
				{
					$errors = '<strong class="passed">Passed</strong>';
					
				}
				
				$linksString = '';
				$links = array();
				$regex = "\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))";
				preg_match_all("%$regex%s", $body, $links);
				$links = $links[0];
				foreach ($links as $link)
				{
					$linksString .= '<a href="' . $link. '" class="checkURL">'. $link. '</a><br>';
				}
				
				echo getLine(	$i,
								$url,
								$pageTemp->getName(),
								$pageTemp->getLang(),
								$errors,
								$linksString );
				
				$seoList .= '{ url:"' . $absURL;
				$seoList .= '", id:"seo'.$i.'" }';
				
			}
			
		}
		
		if( _DEBUG )
		{
			\Flea\Debug::getInstance()->clear();
		}
		
		$seoList .= ']';
		
		?>
		
	</tbody>
</table>




<!-- SCRIPTS JS -->

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>

<script type="text/javascript" src="{{pageContentPath}}js/linkChecker.js"></script>
<script type="text/javascript" src="{{pageContentPath}}js/seoTest.js"></script>

<script type="text/javascript" >
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

<?php

if ( _DEBUG )
{
	echo '<strong>'.\Flea\Debug::getInstance()->getTimes('').'</strong><br><br>';
	\Flea\Debug::getInstance()->dispatchErrors();
}
?>

