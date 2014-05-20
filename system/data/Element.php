<?php

/*
 * The MIT License
 *
 * Copyright 2014 Damien Doussaud (namide.com)
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
 * Contains datas, easy to use with ElementList
 *
 * @author Damien Doussaud (namide.com)
 */
class Element extends Saver
{
	
	private $_lang;
	/**
	 * Language of the Element.
	 * The list of language is in the LangList.php
	 * 
	 * @param string $lang
	 */
    public function setLang( $lang )
	{
		if ( _DEBUG && !LangList::getInstance()->hasLang($lang) )
		{
			trigger_error( 'The Language '.$lang.' don\'t exist' , E_USER_ERROR);
		}
		$this->_lang = $lang;
	}
	/**
	 * Language of the Element.
	 * The list of language is in the LangList.php
	 * 
	 * @return string
	 */
    public function getLang() { return $this->_lang; }

    private $_id;
	/**
	 * ID of the Element
	 * 
	 * @param string $id
	 */
    public function setId( $id ) { $this->_id = $id; }
	/**
	 * ID of the Element
	 * 
	 * @return string
	 */
    public function getId() { return $this->_id; }
	
	private $_type;
	/**
	 * Type of the element
	 * 
	 * @param string $type
	 */
    public function setType( $type ) { $this->_type = $type; }
	/**
	 * Type of the element
	 * 
	 * @return string
	 */
    public function getType() { return $this->_type; }
	
   
    private $_tags;
	/**
	 * Add a tag to the element.
	 * In example you can use tags for search a list of elements.
	 * 
	 * @param string $tag
	 */
    public function addTag( $tag )
	{
		$this->_tags[] = $tag;
	}
	
	/**
	 * Add a list of tags to the element.
	 * In example you can use tags for search a list of elements.
	 * 
	 * @param array $tags
	 */
    public function addTags( $tags )
    {
        foreach ( $tags as $tag )
        {
            $this->addTag( $tag );
        }
    }
	
	/**
	 * Search if the element has a tag
	 * 
	 * @param string $tag
	 * @return boolean
	 */
    public function hasTag( $tag )
    {
        return in_array( $tag, $this->_tags );
    }
	
	/**
	 * Search if the element has a list of tag.
	 * To return true the element must to have all the tags of the list.
	 * 
	 * @param array $tag
	 * @return boolean
	 */
    public function hasTags( $tags )
    {
		foreach ($tags as $tag)
		{
			if ( !$this->hasTag($tag) )
			{
				return false;
			}
		}
		return true;
    }
	
	/**
	 * List of the tag's element
	 * 
	 * @return array
	 */
	public function getTags()
    {
        return $this->_tags;
    }
	
	/**
	 * Remove all the tags
	 * 
	 * @return array
	 */
	public function removeTags()
    {
        $this->_tags = array();
    }
	
	/**
	 * Remove all the tags
	 * 
	 * @return array
	 */
	public function removeTag( $tag )
    {
        if ( $this->hasTag($tag) )
		{
			$key = array_search($tag, $this->_tags);
			array_splice($this->_tags, $key, 1);
		}
    }
	
	private $_contents;
    /**
	 * A content is a data with label and value.
	 * You can't add 2 contents with same label.
	 * 
	 * @param string $label
	 * @param string $value
	 */
	public function addContent( $label, $value )
	{
		if ( _DEBUG && $this->hasContent($label) )
		{
			trigger_error( 'This content already exist: '.$label.' ('.$this->_id.', '.$this->_lang.')' , E_USER_ERROR);
		}
		$this->_contents[$label] = $value;
	}
	
	/**
	 * Add a list of contents
	 * 
	 * @param array $arrayOfContentByLabel
	 */
    public function addContents( $arrayOfContentByLabel )
    {
        foreach ( $arrayOfContentByLabel as $label => $content )
        {
            $this->addContent( $label, $content );
        }
    }
	
	/**
	 * Test if the content already exist
	 * 
	 * @param string $label
	 * @return boolean
	 */
    public function hasContent( $label )
    {
		return array_key_exists( $label, $this->_contents );
    }
	
	/**
	 * Return the value of the content
	 * 
	 * @param string $label
	 * @return string
	 */
	public function getContent( $label )
    {
        return $this->_contents[ $label ];
    }
	
	/**
	 * Return the list of the element's contents
	 * 
	 * @return string
	 */
	public function getContents()
    {
        return $this->_contents;
    }
	
	/**
	 * Create an element
	 * 
	 * @param type $lang
	 */
	public function __construct( $lang = null )
    {
        if ( $lang === null )
		{
			$lang = LangList::getInstance()->getDefaultLang();
		}
		$this->setLang( $lang );
		$this->_contents = array();
		$this->_tags = array();
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
