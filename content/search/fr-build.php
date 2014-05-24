<article>
	<h1>Moteur de recherche</h1>
	<p>Tests.</p>
	
	<?php
		//include_once _SYSTEM_DIRECTORY.'helpers/SearchEngine.php';
		//$search = SearchEngine::getInstance();
	
		print_r( Flea\General::getInstance()->getCurrentGetUrl() );
	?>
	
	 <form action="{{idPageToAbsUrl:en/search}}" type="POST">
		
		<?php foreach ($_GET as $key => $value) { ?>
			<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
		<?php } ?>
		
		<input type="text" name="s" placeholder="Votre recherche" >
		<input type="submit" value="Chercher">
	</form> 
	
</article>
