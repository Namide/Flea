<?php

$url = 'error404';
//$addUrl = 'fr/accueil';
//$addUrls = array('en/homepage/1', 'en/homepage/2'); 

$template = 'default';

$visible = false;	
$cachable = true;

$getEnabled = false;
$getExplicit = true;
$date = '2014-05-01';

$htmlBody = '<h1>Error 404</h1><p>Page not found</p>';
$htmlDescription = 'Page not found';
$htmlHeader = '';
$htmlTitle = 'Error';

$type = Flea\Page::$TYPE_ERROR404; // error404
$phpHeader = 'HTTP/1.0 404 Not Found';	

//$tags = array( 'basic', 'home' );			
//$tag				
//$contents = array( 'test01'=>'youhou ! ! !', 'test02'=>'hoé???', 'test02'=>'haha' );
