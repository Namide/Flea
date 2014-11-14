<?php

	include_once _TEMPLATE_DIRECTORY.'includes/menu.php';
	
?><!DOCTYPE html>
<html lang="{{lang}}">

<head>

    <meta charset="utf-8">
	<title>{{title}} - Flea</title>
    <meta name="description" content="{{description}}" />
	
	
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <meta name="viewport" content="width=device-width; height=device-height; maximum-scale=1.4; initial-scale=1.0; user-scalable=yes" />
	
	<meta name="author" content="Namide" />

	<link rel="stylesheet" media="screen" type="text/css" href="<?= Flea\InitUtil::getInstance()->getTemplateAbsUrl( 'css/default.css' ) ?>" />
    <link rel="stylesheet" media="print" type="text/css" href="<?= Flea\InitUtil::getInstance()->getTemplateAbsUrl( 'css/print.css' ) ?>" />
	
    <link rel="icon" type="image/png" href="<?= Flea\InitUtil::getInstance()->getTemplateAbsUrl( 'img/favicon.png' ) ?>" /> 
    
    {{header}}
    
</head>

<body>
    
    <header>
        <h1><a href="<?= Flea\Helper::getBuildUtil()->getAbsUrl( 'home' ) ?>">Flea</a></h1>
        <nav>
            <?php echo getMenu(); ?>
        </nav>
    </header>
    
    <div id="content">
    
        {{body}}
		
    </div>
    
    <footer>
        <?= \Flea\Helper::getTagUtil()->getOtherLanguages(); ?>
    </footer>
	
</body>

</html>