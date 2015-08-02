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
class MinifyJsCssHtml
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
		
		foreach($html('style') as $element)
		{
			$del = true;
			if ( !isset($cssList['screen']) )
			{
				$cssList['screen'] = '';
				$cssListHash['screen'] = '';
				
				$cssListTag['screen'] = $element;
				$del = false;
			}

			$cssList['screen'] .= $element->getInnerText();
			$cssListHash['screen'] .= $element->getInnerText();
			
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

		
		// Hash js and css for print
		foreach ($cssListHash as &$cssHash)
			$cssHash = 'css/'.md5($cssHash).'.css';

		$jsHeadHash = 'js/'.md5($jsHeadHash).'.js';
		$jsBodyHash = 'js/'.md5($jsBodyHash).'.js';

		
		// Save files
		$uu = UrlUtil::getInstance();
		$cache = new Cache(_DB_DSN_CACHE);
		
		// Minify js
		if ( _MINIFY_ENABLE_JS )
			$jSqueeze = new \JSqueeze();
		
		// WRITE HEAD JS
		if ( $jsHead != '' )
		{
			$src = $bu->getAbsUrlByPageUrl($jsHeadHash);
			$jsHeadTag->src = $src;
			$jsHeadTag->type = 'text/javascript';
			$jsHeadTag->setInnerText('');
			if ( !$cache->isWrited( $jsHeadHash ) )
			{
				if ( _MINIFY_ENABLE_JS )
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
				if ( _MINIFY_ENABLE_JS )
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
				
				if ( _MINIFY_ENABLE_CSS )
				{
					
				
					//$str = preg_replace( '#\s+#', ' ', $str );
					//$str = preg_replace( '#/\*.*?\*/#s', '', $str );
					/*$str = str_replace( '; ', ';', $str );
					$str = str_replace( ': ', ':', $str );
					$str = str_replace( ' {', '{', $str );
					$str = str_replace( '{ ', '{', $str );
					$str = str_replace( ', ', ',', $str );
					$str = str_replace( '} ', '}', $str );
					$str = str_replace( ';}', '}', $str );
					$str = trim( $str );*/

					$str = str_replace(array("\r","\n"), '', $str);
					$str = preg_replace('`([^*/])\/\*([^*]|[*](?!/)){5,}\*\/([^*/])`Us', '$1$3', $str);
					$str = preg_replace('`\s*({|}|,|:|;)\s*`', '$1', $str);
					$str = str_replace(';}', '}', $str);
					$str = preg_replace('`(?=|})[^{}]+{}`', '', $str);
					$str = preg_replace('`[\s]+`', ' ', $str);
				
				}
				
				$cache->writeCache( $cssListHash[$media], 'Content-type: text/css', $str );
			}
		}
		
		if ( _MINIFY_ENABLE_HTML )
		{
			// bug utf-8
			\HTML_Formatter::minify_html($html);
		}
				
		/*$search = array(
			'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
			'/[^\S ]+\</s',  // strip whitespaces before tags, except space
			'/(\s)+/s'       // shorten multiple whitespace sequences
		);
		$replace = array(
			'>',
			'<',
			'\\1'
		);
		$html = preg_replace($search, $replace, $html);*/
		
		return $html;
	}
	
	
	final private function __construct() { }
	
	final private function __clone() { }
	
	/**
	 * Instance of the MinifyJsCssHtml
	 * 
	 * @return self		Instance of the MinifyJsCssHtml
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
