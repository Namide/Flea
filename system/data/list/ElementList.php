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
	public function getElements() { return $this->_elements; }
	
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
    public function addElement( &$element )
    {
        $lang = $element->getLang();
		if( _DEBUG && $this->hasElement( $element, null ) )
		{
			trigger_error( 'The element ['.$element.'] already exist', E_USER_ERROR );
		}
		array_push($this->_elements, $element);
    }
	
	/**
	 * Return the element with this ID
	 * 
	 * @param string $id
	 * @param string $lang
	 * @param array $tags
	 * @return type
	 */
	public function getElementById( $id, $lang )
	{
		foreach ( $this->_elements as $element )
        {
			if( $element->getId() === $id &&
				$element->getLang() === $lang )
			{
				return $element;
			}
        }
		
		if ( _DEBUG )
		{
			trigger_error( 'The Element ['.$id.'] don\'t exist in the language '.$lang, E_USER_ERROR );
		}
	}
	
	/**
	 * Return a list of element with the tag
	 * 
	 * @param string $tag
	 * @param string $lang
	 * @return array
	 */
	public function getElementsByTag( $tag, $lang )
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
	public function getElementsWithOneOfTags( $tags, $lang )
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
	public function getElementsWithAllTags( $tags, $lang )
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
    public function getElementsByLang( $lang )
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
	 * @param string $id
	 * @param string $lang
	 * @return boolean
	 */
	public function hasElement( $id, $lang = null )
    {
		foreach ( $this->_elements as $element )
        {
            $idTemp = $element->getId();
            $langTemp = $element->getLang();
			
            if (	$idTemp === $id && ($langTemp === $lang || $lang === null ) )
            {
                return true;
            }
        }
        return false;
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
	public function update( $saveDatas )
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
