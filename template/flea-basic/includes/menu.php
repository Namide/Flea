<?php

function getMenu() {
	//$templateUtils = Flea::getBuildUtil();
	$lang = Flea::getGeneral()->getCurrentLang();

	if ($lang == 'all')
		return '';

	$pageList = Flea::getPageList();

	$output = '<ul>';
	foreach ($pageList->getAllByLang($lang) as $pageTemp) {
		$output .= '<li><a hreflang="' . $pageTemp->getLang() . '" '
				. 'href="{{pageNameToAbsUrl:' . $pageTemp->getName() . '}}">';
		$output .= $pageTemp->getMetas('title') . '</a></li>';
	}
	foreach ($pageList->getAllByLang('all') as $pageTemp) {
		$output .= '<li><a href="{{rootPath}}' . $pageTemp->getName() . '">';
		$output .= $pageTemp->getMetas('title') . '</a></li>';
	}
	$output .= '</ul>';

	return $output;
}
