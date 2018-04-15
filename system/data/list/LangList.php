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
 * List of languages used in the website.
 *
 * @author Namide
 */
class LangList {

	private static $_INSTANCE;
	private $_default;
	private $_langs;

	/**
	 * List of languages used in the Website
	 * 
	 * @return array	List of languages
	 */
	public function getList() {
		return $this->_langs;
	}

	final private function __construct() {
		$this->_langs = array();
		$this->addDefault('all');
	}

	/**
	 * This language is used if the user don't choose a language
	 * 
	 * @param string $lang	Default language
	 */
	public function addDefault($lang) {
		$this->_default = $lang;
		$this->add($lang);
	}

	/**
	 * Language defined in default language
	 * 
	 * @return string	Default language
	 */
	public function getDefault() {
		return $this->_default;
	}

	/**
	 * Add a language in the Website
	 * 
	 * @param string $lang	New language
	 */
	public function add($lang) {
		if (_DEBUG && $this->has($lang)) {
			Debug::getInstance()->addError('LangList->addLang() ' . $lang . ' already exist');
		}
		array_push($this->_langs, $lang);
	}

	/**
	 * Test if Lang already pushed
	 * 
	 * @param string $lang	Language
	 * @return boolean		Has this language in the LangList Object
	 */
	public function has($lang) {
		return in_array($lang, $this->_langs);
	}

	/**
	 * Recovered the language with the navigator global variable
	 * of, if isn't in list, the default language
	 * 
	 * @return string	The language of the current navigator (in 2 chars)
	 */
	public function getLangByNavigator() {
		//$acceptedLanguages = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING);
		$acceptedLanguages = filter_var($_SERVER['HTTP_ACCEPT_LANGUAGE'], FILTER_SANITIZE_STRING);
		$langList = explode(',', $acceptedLanguages);
		$langLower = strtolower(substr(chop($langList[0]), 0, 2));

		if ($this->has($langLower)) {
			return $langLower;
		}

		return $this->_default;
	}

	/**
	 * Unclonable
	 */
	final private function __clone() {
		if (_DEBUG) {
			Debug::getInstance()->addError('You can\'t clone a singleton');
		}
	}

	/**
	 * Instance of the langListObject
	 * 
	 * @return self		LangList instancied
	 */
	final public static function getInstance() {
		if (!isset(self::$_INSTANCE)) {
			self::$_INSTANCE = new self;
		}

		return self::$_INSTANCE;
	}

}
