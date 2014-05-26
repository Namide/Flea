<?php

function getMenu()
{
	$templateUtils = Flea\BuildUtil::getInstance();
	$lang = \Flea\General::getInstance()->getCurrentLang();
	
	if ( $lang == 'all' ) return '';
	
    
	$pageList = \Flea\PageList::getInstance();

	$output = '<ul>';
	foreach( $pageList->getAllByLang( $lang ) as $pageTemp )
	{
		$output .= '<li><a href="'.\Flea\BuildUtil::getInstance()->getAbsUrlByPageUrl($pageTemp->getPageUrl()).'">';
		$output .= $pageTemp->getHtmlTitle().'</a></li>';
	}
	$output .= '</ul>';

    return $output;
}
