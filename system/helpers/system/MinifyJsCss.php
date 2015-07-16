<?php

/*
 * The MIT License
 *
 * Copyright 2015 Damien.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Flea;

/**
 * Used for write and read files
 *
 * @author Namide
 */
class MinifyJsCss
{
	private static $_INSTANCE;
	
	public function process( $pageContent )
	{
		$bu = BuildUtil::getInstance();

		$cssList = array();
		$cssListHash = array();
		$cssListTag = array();
		$jsHead = '';
		$jsHeadHash = '';
		$jsHeadTag;
		$jsBody = '';
		$jsBodyHash = '';
		$jsBodyTag;
		
		
		// Parse the google code website into a DOM
		//$html = file_get_dom('http://code.google.com/');
		$html = str_get_dom($pageContent);

		foreach($html('link[type="text/css"]') as $element)
		{
			$del = true;
			if ( !isset($cssList[$element->media]) )
			{
				$cssList[$element->media] = '';
				$cssListHash[$element->media] = '';
				
				$cssListTag[$element->media] = $element;
				$del = false;
			}


			if ( $element->hasAttribute('href') )
			{
				$cssList[$element->media] .= file_get_contents( $bu->getPageUrlByAbsUrl($element->href) );
				$cssListHash[$element->media] .= $element->href;
			}
			else
			{
				$cssList[$element->media] .= $element->getInnerText();
				$cssListHash[$element->media] .= $element->getInnerText();
			}

			if ( $del )
				$element->delete();
		}

		foreach($html('head script') as $element)
		{
			$del = true;
			if( $jsHead == '' )
			{
				$jsHeadTag = $element;
				$del = false;
			}
			
			if ( $element->hasAttribute('src') )
			{
				$jsHead .= file_get_contents($bu->getPageUrlByAbsUrl($element->src)).';';
				$jsHeadHash .= $element->src;
			}
			else
			{
				$jsHead .= $element->getInnerText().';';
				$jsHeadHash .= $element->getInnerText();
			}

			if ( $del )
				$element->delete();
		}

		foreach($html('body script') as $element)
		{
			$del = true;
			if( $jsBody == '' )
			{
				$jsBodyTag = $element;
				$del = false;
			}
			
			if ( $element->hasAttribute("src") )
			{
				$jsBody .= file_get_contents($bu->getPageUrlByAbsUrl($element->src)).';';
				$jsBodyHash .= $element->src;
			}
			else
			{
				$jsBody .= $element->getInnerText().';';
				$jsBodyHash .= $element->getInnerText();
			}

			if ( $del )
				$element->delete();
		}

		


		/*$jsHeadTemp = '';
		foreach ($jsHead as $code)
			$jsHeadTemp .= $code.';';*/

		/*$jsBodyTemp = '';
		foreach ($jsBody as $code)
			$jsBodyTemp .= $code.';';*/

		// Minify js
		$jSqueeze = new \JSqueeze();
		
		

		// Hash js and css for print
		foreach ($cssListHash as &$cssHash)
			$cssHash = 'css/'.md5($cssHash).'.css';

		$jsHeadHash = 'js/'.md5($jsHeadHash).'.js';
		$jsBodyHash = 'js/'.md5($jsBodyHash).'.js';

		
		// Save files
		$uu = UrlUtil::getInstance();
		$cache = new Cache(_DB_DSN_CACHE);
		
		
		// WRITE HEAD JS
		if ( $jsHead != '' )
		{
			$src = $bu->getAbsUrlByPageUrl($jsHeadHash);
			$jsHeadTag->src = $src;
			$jsHeadTag->type = 'text/javascript';
			$jsHeadTag->setInnerText('');
			if ( !$cache->isWrited( $jsHeadHash ) )
			{
				$jsHead = $jSqueeze->squeeze($jsHead, true, false);
				$cache->writeCache( $jsHeadHash, 'Content-Type: application/javascript', $jsHead );
			}
		}
		
			
		
		
		// WRITE BODY JS
		if ( $jsBody != '' )
		{
			$src = $bu->getAbsUrlByPageUrl($jsBodyHash);
			$jsBodyTag->src = $src;
			$jsBodyTag->type = 'text/javascript';
			$jsBodyTag->setInnerText('');
			if ( !$cache->isWrited( $jsBodyHash ) )
			{
				$jsBody = $jSqueeze->squeeze($jsBody, true, false);
				$cache->writeCache( $jsBodyHash, 'Content-Type: application/javascript', $jsBody );
			}
		}
		
		
		// WRITE CSS
		foreach ($cssList as $media => $content)
		{
			$src = $bu->getAbsUrlByPageUrl($cssListHash[$media]);
			$cssListTag[$media]->href = $src;
			
			if ( !$cache->isWrited( $cssListHash[$media] ) )
			{
				// Minify CSS
				$str = $cssList[$media];
				$str = preg_replace( '#\s+#', ' ', $str );
				$str = preg_replace( '#/\*.*?\*/#s', '', $str );
				$str = str_replace( '; ', ';', $str );
				$str = str_replace( ': ', ':', $str );
				$str = str_replace( ' {', '{', $str );
				$str = str_replace( '{ ', '{', $str );
				$str = str_replace( ', ', ',', $str );
				$str = str_replace( '} ', '}', $str );
				$str = str_replace( ';}', '}', $str );
				$str = trim( $str );
				
				$cache->writeCache( $cssListHash[$media], 'Content-type: text/css', $str );
			}
		}
		
		return $html;
	}
	
	
	final private function __construct() { }
	
	final private function __clone() { }
	
	/**
	 * Instance of the MinifyJsCss
	 * 
	 * @return self		Instance of the MinifyJsCss
	 */
    final public static function getInstance()
    {
        if( !isset( self::$_INSTANCE ) )
        {
            self::$_INSTANCE = new self();
        }
 
        return self::$_INSTANCE;
    }
}
