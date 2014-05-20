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
 * Description of Saver
 *
 * @author Namide
 */
abstract class Saver
{
	/**
	 * Get a script for create the same object
	 * 
	 * @return string
	 */
	abstract public function getSave();

	/**
	 * Called by getSaver()
	 * 
	 * @param type $getObjectVars
	 * @return string
	 */
	protected function constructSave( $getObjectVars )
	{
		$c = get_called_class();
		$output = $c.'::create(';
		$output .= self::getStrConstructor($getObjectVars);
		$output .= ')';
		
		return $output;
	}
	
	/**
	 * Create a new object by a saved object.
	 * A saved object can by generate by the method getSave().
	 * 
	 * @param array $saveDatas
	 * @return self
	 */
	public static function create( $saveDatas )
	{
		$c = get_called_class();
		$element = new $c;
		$element->update( $saveDatas );
		return $element;
	}
	
	/**
	 * Update the object with a saved object.
	 * A saved object can by generate by the method getSave().
	 * 
	 * @param array $saveDatas
	 * @return self
	 */
	abstract public function update( $saveDatas );
	
	/**
	 * Return a string for instantiate the same data
	 * 
	 * @param type $array
	 * @return string
	 */
	protected static function getStrConstructor( $data )
	{
		$output = 'array(';
		$first = true;
		foreach ($data as $key => $value)
		{
			if ( !$first ) $output .= ',';
			
			$output .= '"'.$key.'"=>';
			if ( gettype ($value) === "array" ) 
			{
				$output	.= self::getStrConstructor($value);
			}
			else if ( gettype($value) === "object" )
			{
				if ( get_parent_class($value) === "Saver" )
				{
					$output .= $value->getSave();
				}
				else
				{
					$output .= self::getStrConstructor( $value );
				}
			}
			elseif( gettype ($value) === "string" )
			{
				$output	.= self::escQuot($value);
			}
			elseif( gettype ($value) === "integer" || gettype ($value) === "double" )
			{
				$output	.= $value;
			}
			elseif( gettype ($value) === "boolean" )
			{
				$output	.= ($value)?'true':'false';
			}
			else
			{
				$output	.= self::escQuot($value);
			}
			
			if ( $first )
			{
				$first = false;
			}
		}
		$output .= ')';
		return $output;
	}
	
	
	private static function escQuot( $text )
	{
		return '"' . str_replace('"', '\"', $text ) .'"';
	}
}
