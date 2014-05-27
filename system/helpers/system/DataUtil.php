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
 * Storage datas without SQL
 *
 * @author Namide
 */
class DataUtil
{
	protected $_dir = '';
	/**
	 * Directory to save the files
	 * 
	 * @return string		Directory to save the files
	 */
	public function getDir() { return $this->_dir; }
	
	/**
	 * Initialise the "connection" with the directory of data files
	 * 
	 * @param type $dir		Directory to save the files
	 */
	public function __construct( $dir )
	{
		$this->_dir = $dir;
	}
	
	/**
	 * Add a file in the datas
	 * 
	 * @param string $key		Key of the data to save (same that the name of a file)
	 * @param string $content	Content of the data to save
	 */
	public function add( $key, &$content )
	{
		if( _DEBUG && $this->has($key) )
		{
			Debug::getInstance()->addError( 'The content [dir:'.$this->_dir.', key:'.$key.'] already exist' );
		}
		FileUtil::writeFile( $content, $this->_dir.$key );
	}
	
	/**
	 * Add a JSON file in the datas
	 * 
	 * @param string $key		Key of the data saved (same that the name)
	 * @param array $content	Content of the data saved
	 */
	public function addJson( $key, array &$content )
	{
		$json = json_encode($content);
		$this->add( $key, $json );
	}
	
	/**
	 * Update a file in the datas
	 * 
	 * @param string $key			Key of the data saved (same that the name)
	 * @param string $content		New content of the data
	 */
	public function update( $key, &$content )
	{
		if ( _DEBUG && $this->has($key) )
		{
			Debug::getInstance()->addError( 'The content [dir:'.$this->_dir.', key:'.$key.'] don\'t exist' );
		}
		$type = $this->get($key);
		$this->delete($key);
		$this->add($key, $content, $type);
	}
	
	/**
	 * Update a JSON file in the datas
	 * 
	 * @param string $key		Key of the data saved (same that the name)
	 * @param array $content	New content of the data
	 */
	public function updateJson( $key, array &$content )
	{
		$json = json_encode($content);
		update( $key, $json );
	}
	
	/**
	 * Get the content of a file in the datas
	 * 
	 * @param string $key		Key of the data saved (same that the name)
	 * @return string			Content of the data
	 */
	public function get( $key )
	{
		if ( _DEBUG && !$this->has($key) )
		{
			Debug::getInstance()->addError( 'The content [dir:'.$this->_dir.', key:'.$key.'] don\'t exist' );
		}
		return file_get_contents( $this->_dir.$key );
	}
	
	/**
	 * Get the content of a JSON file in the datas
	 * 
	 * @param string $key		Key of the data saved (same that the name)
	 * @return array			Content of the data
	 */
	public function getJson( $key )
	{
		$content = $this->get($key);
		return json_decode( $content, true );
	}
	
	/**
	 * Test if the file exist in the datas
	 * 
	 * @param string $key		Key of the data saved (same that the name)
	 * @return bool				true if the data exist, otherwise false
	 */
	public function has( $key )
	{
		return file_exists( $this->_dir.$key );
	}
	
	/**
	 * Delete a file in the datas
	 * 
	 * @param string $key		Key of the files
	 */
	public function delete( $key )
	{
		if ( $this->has($key) ) { unlink( $this->_dir.$key ); }
	}
	
	/**
	 * Echo the content of a file in the datas
	 * 
	 * @param string $key		Key of the data saved (same that the name)
	 */
	public function render( $key )
	{
		$ext = strtolower( substr( strrchr( $key, '.' ), 1 ) );
		switch ($ext)
		{
			case "xml":
				header('Content-Type: application/xml;');
				break;
			//default: $ctype="application/force-download";
		}
		
		readfile( $this->_dir.$key );
	}
	
}
