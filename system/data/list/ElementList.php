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
 * Contains Element, easy to sort datas
 *
 * @author Namide
 */
class ElementList extends Saver
{
	private static $_instances = array();
	
    protected $_elements;
	/**
	 * List of elements
	 * 
	 * @return array
	 */
	public function getAll() { return $this->_elements; }
	
	final private function __construct()
    {
        $this->reset();
	}
	
	/**
	 * Clear all the list
	 */
	public function reset() { $this->_elements = array(); }

	/**
	 * Add an element in the list
	 * 
	 * @param Element $element
	 */
    public function add( Element &$element, $key = null )
    {
        $lang = $element->getLang();
		if( _DEBUG && $this->has( $element, null ) )
		{
			trigger_error( 'The element ['.$element.'] already exist', E_USER_ERROR );
		}
		
		if ( $key === null )
		{
			array_push($this->_elements, $element);
		}
		elseif ( _DEBUG && isset($this->_elements[$key]) )
		{
			trigger_error( 'The list has the same key ['.$key.'] for the element ['.$element.']', E_USER_ERROR );
		}
		else
		{
			$this->_elements[$key] = $element;
		}
    }
	
	/**
	 * Return the elements with this ID (all langues)
	 * 
	 * @param string $name
	 * @param string $lang
	 * @param array $tags
	 * @return array
	 */
	public function getAllByName( $name )
	{
		$elements = array();
		foreach ( $this->_elements as $element )
        {
			if( $element->getName() === $name )
			{
				array_push($elements, $element);
			}
        }
		
		if ( _DEBUG && count( $elements ) < 1 )
		{
			trigger_error( 'The Element ['.$name.'] don\'t exist', E_USER_ERROR );
		}
		
		return $elements;
	}
	
	/**
	 * Return the element with this ID
	 * 
	 * @param string $name
	 * @param string $lang
	 * @param array $tags
	 * @return type
	 */
	public function getByName( $name, $lang )
	{
		foreach ( $this->_elements as $element )
        {
			if( $element->getName() === $name &&
				$element->getLang() === $lang )
			{
				return $element;
			}
        }
		
		if ( _DEBUG )
		{
			trigger_error( 'The Element ['.$name.'] don\'t exist in the language '.$lang, E_USER_ERROR );
		}
	}
	
	/**
	 * Return a list of element with the tag
	 * 
	 * @param string $tag
	 * @param string $lang
	 * @return array
	 */
	public function getByTag( $tag, $lang )
    {
		$elements = array();
		foreach ( $this->_elements as $element )
        {
			if( (	$element->getLang() === $lang) &&
					$element->hasTag($tag) )
			{
				array_push( $elements, $element );
			}
        }
		return $elements;
    }
	
	/**
	 * Return a list of element with each element has at least one of your tags
	 * 
	 * @param array $tags
	 * @param string $lang
	 * @return array
	 */
	public function getWithOneOfTags( array $tags, $lang )
    {
		$elements = array();
        foreach ( $this->_elements as $element )
        {
            foreach ( $tags as $category )
            {
                if( $element->getLang() == $lang &&
					$element->hasTag($category) )
                {
                    array_push( $elements, $element );
                    break 1;
                }
            }
        }
        return $elements;
    }
    
	/**
	 * Return a list of element with each element has each of tags
	 * 
	 * @param array $tags
	 * @param string $lang
	 * @return array
	 */
	public function getWithAllTags( array $tags, $lang )
    {
		$elements = array();
        foreach ( $this->_elements as $element )
        {
            if( $element->getLang() == $lang &&
				$element->hasTags($tags) )
			{
				array_push( $elements, $element );
			}
            
        }
        return $elements;
    }
	
	/**
	 * Return a list of element for a language
	 * 
	 * @param string $lang
	 * @return array
	 */
    public function getByLang( $lang )
    {
        $elements = array();
        foreach ( $this->_elements as $element )
        {
            $langTemp = $element->getLang();
            if ( $langTemp == $lang )
            {
                array_push( $elements, $element );
            }
        }
        return $elements;
    }
	
	/**
	 * Test if the element with this ID and this language exist
	 * 
	 * @param string $name
	 * @param string $lang
	 * @return boolean
	 */
	public function has( $name, $lang = null )
    {
		foreach ( $this->_elements as $element )
        {
            $nameTemp = $element->getName();
            $langTemp = $element->getLang();
			
            if (	$nameTemp === $name && ($langTemp === $lang || $lang === null ) )
            {
                return true;
            }
        }
        return false;
    }
	
	/**
	 * Test if the element at this key exist
	 * 
	 * @param type $key
	 * @return type
	 */
	public function hasKey($key)
	{
		return array_key_exists($key, $this->_elements);
	}
	
	/**
	 * Return the element for this key
	 * 
	 * @param string $url
	 * @return Page
	 */
    public function getByKey( $key )
    {
		if ( _DEBUG && !$this->hasKey($key) )
		{
			trigger_error( 'The key ['.$key.'] don\'t exist in this list', E_USER_ERROR );
		}
		
		return $this->_elements[$key];
    }
	
	
	
    final public function __clone()
    {
        trigger_error( 'You can\'t clone.', E_USER_ERROR );
    }
 
	/**
	 * 
	 * @return ElementList
	 */
    final public static function getInstance()
    {
        $c = get_called_class();
        if(!isset(self::$_instances[$c]))
        {
            self::$_instances[$c] = new $c;
        }
        return self::$_instances[$c];
    }
	
	/**
	 * Get a script for create the same object
	 * 
	 * @return string
	 */
	public function getSave()
	{
		return $this->constructSave( get_object_vars($this) );
	}
	
	/**
	 * Update the object with a saved object.
	 * A saved object can by generate by the method getSave().
	 * 
	 * @param array $saveDatas
	 * @return self
	 */
	public function update( array $saveDatas )
	{
		if ( count( $saveDatas ) > 0 )
		{
			foreach ( $saveDatas as $varLabel => $varValue )
			{
				$this->$varLabel = $varValue;
			}
		}
		return $this;
	}
}
