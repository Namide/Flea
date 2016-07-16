<!DOCTYPE html>
<html lang="{{lang}}">

	<head>

		<meta charset="utf-8">
		<title>{{meta:title}} - Flea</title>
		<meta name="description" content="{{meta:description}}" />


		<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
		<meta name="viewport" content="width=device-width; height=device-height; maximum-scale=1.4; initial-scale=1.0; user-scalable=yes" />

		<meta name="author" content="Namide" />

		<link rel="stylesheet" media="screen" type="text/css" href="<?= Flea::getBuildUtil()->getTemplateAbsUrl('flea-basic/css/default.css') ?>" />
		<link rel="stylesheet" media="print" type="text/css" href="<?= Flea::getBuildUtil()->getTemplateAbsUrl('flea-basic/css/print.css') ?>" />
		<link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>

		<link rel="icon" type="image/png" href="<?= Flea::getBuildUtil()->getTemplateAbsUrl('flea-basic/img/favicon.png') ?>" /> 

	</head>

	<?php
	$lang = Flea::getGeneral()->getCurrentLang();
	?>

	<body>

		<header>
			<h1>
				<a href="<?= Flea::getBuildUtil()->getAbsUrlByPage(Flea::getPageList()->getDefaultPage($lang)); ?>">Flea</a>
			</h1>
			<p>Lightweight PHP framework</p>

			<nav>
				<?php
				$tag = 'main-pages';
				$list = Flea::getPageList()->getByTag($tag, $lang);
				echo Flea::getTagUtil()->getLinkList($list, 'title');
				?>
			</nav>
			<nav><?= Flea::getTagUtil()->getBreadcrump('title') ?></nav>
		</header>

		<div id="content">

			{{body}}

		</div>

		<footer>
			<?= Flea::getTagUtil()->getOtherLanguages(); ?>
		</footer>

	</body>

</html>