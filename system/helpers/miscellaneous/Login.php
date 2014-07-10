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

define( '_DB_LOGIN', 'sqlite:'._CONTENT_DIRECTORY.'login.sqlite' );

class User
{
	protected $_id;
	public function getId() { return $this->_id; }
	
	protected $_email;
	public function getEmail() { return $this->_email; }
	
	protected $_token;
	public function getToken() { return $this->_token; }
	
	public function __construct( $id, $email, $token )
	{
		$this->_id = $id;
		$this->_email = $email;
		$this->_token = $token;
	}
}

/**
 * 
 * @author Namide
 */
class Login
{
	private static $_INSTANCE = array();
	private $_db;
	
	public function isConnected()
	{
		
	}
	
	public function getUser()
	{
		if ( !$this->isConnected() )
		{
			if ( _DEBUG ) Debug::getInstance()->addError ('User is\'n connectet');
			return false;
		}
	}
	
	public function connect( $email, $pass )
	{
		
	}
	
	public function disconnect()
	{
		
	}
	
	private function __construct( $dbDsn )
	{
		session_start();
		$this->_db = DataBase::getInstance($dbDsn);
	}
	
	/**
	 * 
	 * @param string $dbDsnCache
	 * @return DataBase
	 */
	public static function getInstance( $dbDsn ) 
    {
		if(!isset(self::$_INSTANCE[$dbDsn]))
        {
            self::$_INSTANCE[$dbDsn] = new DataBase($dbDsn);
        }
        return self::$_INSTANCE[$dbDsn];
    }
	
	final public function __clone()
    {
        if ( _DEBUG ) 
		{
			Debug::getInstance()->addError('You can\'t clone a multiton');
		}
    }
}
