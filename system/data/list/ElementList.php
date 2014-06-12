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
	 * @return array All the elements
	 */
	public function getAll() { return $this->_elements; }
	
	/**
	 * List of elements in the language
	 * 
	 * @param string $lang	Language
	 * @return array		All the page for this language
	 */
	public function getAllByLang( $lang )
	{
		$elements = array();
		
		foreach ( $this->_elements as $element )
        {
			if( $element->getLang() === $lang )
			{
				$elements[] = $element;
			}
        }
		
		return $elements;
	}
	
	final protected function __construct()
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
	 * @param \Flea\Element $element	Element to add
	 * @param string $key				Key of the element (like an ID in database)
	 */
	public function add( Element &$element, $key = null )
    {
        $lang = $element->getLang();
		if( _DEBUG && $this->has( $element, null ) )
		{
			Debug::getInstance()->addError( 'The element ['.$element.'] already exist' );
		}
		
		if ( $key === null )
		{
			array_push($this->_elements, $element);
		}
		elseif ( _DEBUG && isset($this->_elements[$key]) )
		{
			Debug::getInstance()->addError( 'The list has the same key ['.$key.'] for the element ['.$element->getName().']' );
		}
		else
		{
			$this->_elements[$key] = $element;
		}
    }
	
	/**
	 * Sort the elements by their dates
	 */
	public function sortByDate()
	{
		usort($this->_elements, 'cmpDate');
	}
	
	private function cmpDate($a, $b)
	{
		if ($a == $b) {	return 0; }
		return ($a < $b) ? -1 : 1;
	}

	/**
	 * Return the elements with this ID (all langues)
	 * 
	 * @param string $name	Name of the elements
	 * @return array		List of the elements with the name
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
			Debug::getInstance()->addError( 'The Element ['.$name.'] don\'t exist' );
		}
		
		return $elements;
	}
	
	/**
	 * Return the element with this ID
	 * 
	 * @param string $name	Name of the elements
	 * @param string $lang	Language of the elements
	 * @return Element		Element			
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
			Debug::getInstance()->addError( 'The Element ['.$name.'] don\'t exist in the language '.$lang );
		}
	}
	
	/**
	 * Return a list of element with the tag
	 * 
	 * @param string $tag	Tag for the element
	 * @param string $lang	Language of the element
	 * @return array		List of the elements
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
	 * @param array $tags	List of tags (withouts keys)
	 * @param string $lang	Language of the elements
	 * @return array		List of elements
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
	 * @param array $tags	List of tags
	 * @param string $lang	Language of the elements
	 * @return array		List of elements
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
	 * @param string $lang	Language of the elements
	 * @return array		List of elements
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
	 * @param string $name	Name of the element
	 * @param string $lang	Language of the element
	 * @return boolean		Exist
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
	 * @param string $key	Key of the element
	 * @return bool			Exist	
	 */
	public function hasKey($key)
	{
		return array_key_exists($key, $this->_elements);
	}
	
	/**
	 * Return the element for this key
	 * 
	 * @param string $key	Key of the elemment
	 * @return Element		List of elements
	 */
    public function getByKey( $key )
    {
		if ( _DEBUG && !$this->hasKey($key) )
		{
			Debug::getInstance()->addError( 'The key ['.$key.'] don\'t exist in this list' );
		}
		
		return $this->_elements[$key];
    }
	
    final public function __clone()
    {
		if ( _DEBUG )
		{
			Debug::getInstance()->addError( 'You can\'t clone a singleton' );
		}
    }
 
	/**
	 * Instance of the list
	 * 
	 * @return static	Instance of the object ElementList
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
	
	/*public function db_exist( $dsn, Element $elementExample )
	{
		if ( $childClass === null )
		{
			$childClass = 'Element';
		}
		return gettype($elementExample)::db_exist( $dsn );
	}*/
	
	/*public function db_create( $dsn, Element $elementExample )
	{
		if ( $childClass === null )
		{
			$childClass = 'Element';
		}
		
		$element = new Element();
		$objectVars = $element->getObjectVars();
		call_user_func( $elementExample . '::db_create', $dsn, $objectVars, true );
		//$childClass::db_create( $dsn, $objectVars, true );
	}*/
	
	public function db_save( $dsn )
	{
		if ( _DEBUG && !$this->db_exist( $dsn, stripslashes( get_called_class() ) ) )
		{
			Debug::getInstance()->addError('You must create after save data base');
		}
		
		$sql = '';
		foreach ($this->_elements as $key => $value) 
		{
			
			
			$sql .= Saver::db_insert($dsn, $value->getObjectVars(), false, stripslashes(get_called_class()) );
			
			//$sql .= call_user_func( get_called_class().'::db_insert', $dsn, $value->getObjectVars(), false );
			//$sql .= call_user_func( get_called_class().'::db_insert', $dsn, $value->getObjectVars(), false );
			//$sql .= $childClass::db_insert($dsn, $value->getObjectVars(), false);
		}
		
		echo "-- ElementList->db_save()\n"
			. $sql
			. "\n --";
		
		try
		{
			$db = new \PDO( $dsn, _DB_USER, _DB_PASS, _DB_OPTIONS );
			$db->exec($sql);
			$db = null;
		}
		catch (PDOException $e)
		{
			if ( _DEBUG ) 
			{
				Debug::getInstance()->addError( 'Save database error: '.$e->getMessage() );
			}
		}
		
	}
	
	/**
	 * Get a script for create the same object
	 * 
	 * @return string	String to generated the same element
	 */
	/*public function getSave()
	{
		return $this->constructSave( get_object_vars($this) );
	}*/
	
	/**
	 * Update the object with a saved object.
	 * A saved object can by generate by the method getSave().
	 * 
	 * @param array $saveDatas	Datas generated by a save method of this class
	 * @return static			ElementList with the news values
	 */
	/*public function update( array $saveDatas )
	{
		if ( count( $saveDatas ) > 0 )
		{
			foreach ( $saveDatas as $varLabel => $varValue )
			{
				$this->$varLabel = $varValue;
			}
		}
		return $this;
	}*/
}
