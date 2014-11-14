<?php

function getMenu()
{
	$templateUtils = \Flea\BuildUtil::getInstance();
	$lang = \Flea\General::getInstance()->getCurrentLang();
	
	if ( $lang == 'all' ) return '';
	
	
	$pageList = \Flea\PageList::getInstance();

	$output = '<ul>';
	foreach( $pageList->getAllByLang( $lang ) as $pageTemp )
	{
		$output .= '<li><a href="{{pageNameToAbsUrl:'.$pageTemp->getName().'}}">';
		$output .= $pageTemp->getHtmlTitle().'</a></li>';
	}
	foreach( $pageList->getAllByLang( 'all' ) as $pageTemp )
	{
		$output .= '<li><a href="{{rootPath}}'.$pageTemp->getName().'">';
		$output .= $pageTemp->getHtmlTitle().'</a></li>';
	}
	$output .= '</ul>';
	
    return $output;
}
