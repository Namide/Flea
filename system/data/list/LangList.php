<?php

/*
 * The MIT License
 *
 * Copyright 2014 Damien Doussaud (namide.com).
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
 * Description of LangList
 *
 * @author Namide
 */
class LangList
{
	private static $_INSTANCE;
	
	private $_defaultLang;
    
    private $_langs;
	/**
	 * List of languages used in the Website
	 * 
	 * @return array
	 */
    public function getList() { return $this->_langs; }
	
    final private function __construct()
    {
		$this->_langs = array();
        $this->addDefaultLang('all');
    }
    
	/**
	 * This language is used if the user don't choose a language
	 * 
	 * @param string $lang
	 */
    public function addDefaultLang( $lang )
    {
		$this->_defaultLang = $lang;
        $this->addLang( $lang );
    }
    
	/**
	 * Language defined in default language
	 * 
	 * @return string
	 */
	public function getDefaultLang()
    {
        return $this->_defaultLang;
    }
	
	/**
	 * Add a language in the Website
	 * 
	 * @param string $lang
	 */
    public function addLang( $lang )
    {
		if ( _DEBUG && $this->hasLang($lang) )
		{
			trigger_error( 'LangList->addLang() '.$lang.' already exist' , E_USER_ERROR);
		}
        array_push( $this->_langs, $lang );
    }
    
	/**
	 * Test if Lang already pushed
	 * 
	 * @param string $lang
	 * @return boolean
	 */
	public function hasLang( $lang )
	{
		return in_array($lang, $this->_langs);
	}
	
	/**
	 * Recovered the language with the navigator global variable
	 * of, if isn't in list, the default language
	 * 
	 * @return string
	 */
    public function getLangByNavigator()
    {
		$acceptedLanguages = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING);
        $langList = explode( ',', $acceptedLanguages );
        $langLower = strtolower( substr( chop( $langList[0] ), 0, 2 ) );

		if ( $this->hasLang($langLower) )
		{
			return $langLower;
		}
		
        return $this->_defaultLang;
    }
    
	/**
	 * Unclonable
	 */
    final public function __clone()
    {
		if ( _DEBUG )
		{
			trigger_error( 'You can\'t clone.', E_USER_ERROR);
		}
    }
	
	/**
	 * Instance of the langListObject
	 * 
	 * @return LangList
	 */
    final public static function getInstance()
    {
        if( !isset( self::$_INSTANCE ) )
        {
            self::$_INSTANCE = new LangList();
        }
 
        return self::$_INSTANCE;
    }
}
