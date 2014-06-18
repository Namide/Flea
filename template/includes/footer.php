<?php

function getFooter()
{
    $page = \Flea\General::getInstance()->getCurrentPage();
	$lang = \Flea\General::getInstance()->getCurrentLang();
    
    $output = '<ul>';
    
    if( $lang == 'all' )
    {
    	$output .= '<li><a href="'.\Flea\BuildUtil::getInstance()->getAbsUrlByNameLang( 'home', 'en' ).'">en</a></li>';
    	$output .= '<li><a href="'.\Flea\BuildUtil::getInstance()->getAbsUrlByNameLang( 'home', 'fr' ).'">fr</a></li>';
    }
    else if ( $lang == 'fr' )
    {
    	$output .= '<li><a href="'.\Flea\BuildUtil::getInstance()->getAbsUrlByNameLang( $page->getName(), 'en' ).'">en</a></li>';
    }
    else
    {
		$output .= '<li><a href="'.\Flea\BuildUtil::getInstance()->getAbsUrlByNameLang( $page->getName(), 'fr' ).'">fr</a></li>';
    }
    
    $output .= '</ul>';
    return $output;
}