<!DOCTYPE html>
<html lang="<?php echo TemplateUtils::getInstance()->getLanguage(); ?>">

<head>

    <meta charset="UTF-8" />
	<title><?php echo TemplateUtils::getInstance()->getCurrentPage()->getTitle(); ?> - FWK</title>
    <meta name="description" content="<?php echo TemplateUtils::getInstance()->getCurrentPage()->getDescription(); ?>" />
	
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <meta name="author" content="Damien Doussaud, Namide" />

    <link rel="Shortcut Icon" type="image/x-icon" href="<?php echo PageUtils::getTemplateAbsoluteUrl( 'img/favicon.ico' ); ?>" /> 
    
    <meta name="viewport" content="width=device-width; height=device-height; maximum-scale=1.4; initial-scale=1.0; user-scalable=yes" />
	
    <?php echo $page->getHeader(); ?>
    
    
    <style type="text/css">
    
		body,td,th
		{
			font-family: Arial, sans-serif;
			font-size: 12px;
		}
		
		h1
		{
			color:#F07;
			text-transform: uppercase;
			font-size:24px;
		}
		
		table tr:nth-child(odd)
		{
			background-color:#EEE;
		}
		table td { padding:8px; }
		table th { padding:16px 8px; }
		table { border-spacing: 0; }
		
	</style>
    
</head>

<body>
    
    
	<?php
		echo $page->getBody();
	?>
</body>

</html>