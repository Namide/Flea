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
	 * @return string
	 */
	public function getDir() { return $this->_dir; }
	
	/**
	 * Initialise the "connection" with the directory of data files
	 * 
	 * @param type $dir
	 * @param type $name
	 */
	public function __construct( $dir )
	{
		$this->_dir = $dir;
	}
	
	/**
	 * Add a file in the datas
	 * 
	 * @param string $key
	 * @param string $content
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
	 * @param string $key
	 * @param array $content
	 */
	public function addJson( $key, array &$content )
	{
		$json = json_encode($content);
		$this->add( $key, $json );
	}
	
	/**
	 * Update a file in the datas
	 * 
	 * @param string $key
	 * @param string $content
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
	 * @param string $key
	 * @param array $content
	 */
	public function updateJson( $key, array &$content )
	{
		$json = json_encode($content);
		update( $key, $json );
	}
	
	/**
	 * Get the content of a file in the datas
	 * 
	 * @param string $key
	 * @return string
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
	 * @param string $key
	 * @return array
	 */
	public function getJson( $key )
	{
		$content = $this->get($key);
		return json_decode( $content, true );
	}
	
	/**
	 * Test if the file exist in the datas
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function has( $key )
	{
		return file_exists( $this->_dir.$key );
	}
	
	/**
	 * Delete a file in the datas
	 * 
	 * @param string $key
	 */
	public function delete( $key )
	{
		if ( $this->has($key) ) { unlink( $this->_dir.$key ); }
	}
	
	/**
	 * Echo the content of a file in the datas
	 * 
	 * @param string $key
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
