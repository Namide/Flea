<article>
	<h1>Moteur de recherche</h1>
	<p>Tests.</p>

	<?php
	//print_r( Flea\General::getInstance()->getCurrentGetUrl() );
	?>

	<form action="{{idPageToAbsUrl:en/search}}" type="POST">

		<?php foreach ($_GET as $key => $value) { ?>
			<input type="hidden" name="<?= $key ?>" value="<?= $value ?>" />
		<?php } ?>

		<input type="text" name="s" placeholder="Votre recherche" >
		<input type="submit" value="Chercher">
	</form>

	<a href="http://google.fr/">test</a>

</article>
