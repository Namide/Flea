<?php

$url = 'error404';

$template = 'flea-basic/default';

$visible = false;	
$cachable = true;

$getEnabled = false;
$date = '2014-05-01';

$htmlBody = '<h1>Error 404</h1><p>Page not found</p>';
$htmlDescription = 'Page not found';
$htmlTitle = 'Error 404';

$type = Flea\Page::$TYPE_ERROR404;
$phpHeader = 'HTTP/1.0 404 Not Found';
