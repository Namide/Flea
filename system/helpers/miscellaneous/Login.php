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

class LoginTableName
{
	public static $TABLE_NAME_DATAS = 'login_datas';
	public static $TABLE_NAME_USERS = 'login_users';
	public static $TABLE_NAME_LOGS = 'login_logs';
}

class User
{
	public static $ROLE_BASIC = 1;
	public static $ROLE_ADMIN = 2;
	
	private $_db;
	
	protected $_email;
	public function getEmail() { return $this->_email; }
	
	protected $_token;
	public function getToken() { return $this->_token; }
	
	protected $_role;
	public function getRole() { return $this->_role; }
	
	public function __construct( $db )
	{
		$this->_db = $db;
	}
	
	public function initUser( $email, $token, $role = 1 )
	{
		$this->_email = $email;
		$this->_token = $token;
		$this->_role = $role;
	}
	
	private $_datas;
	/**
	 * A data is a pair with key and value.
	 * You can't add 2 datas with same label.
	 * 
	 * @return DataList 
	 */
	public function getDatas()
	{
		if ( $this->_datas === null )
		{
			$this->_datas = new DataList(true);
			
			if ( DataBase::getInstance( _DB_DSN_CONTENT )->exist(LoginTableName::$TABLE_NAME_DATAS) )
			{
				$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
				//$where = 'user_email = \''.$this->getEmail().'\'';
				$where = array( 'user_email'=>$this->getEmail() );
				$query->initSelect('key, value', '`'.LoginTableName::$TABLE_NAME_DATAS.'`', $where);
				foreach ( $this->_db->fetchAll($query) as $row )
				{
					$content = BuildUtil::getInstance ()->replaceFleaVars ($row['value'], $this);
					$this->_datas->add($content, $row['key']);
				}
			}
		}
		return $this->_datas;
	}
	
}

/**
 * 
 * @author Namide
 */
class Login
{
	private static $_INSTANCE = array();
	
	private static $_HASH_ALGO = 'whirlpool';
	
	private static $_IS_SESSION_STARTED = false;
	
	/**
	 * @var DataBase
	 */
	private $_db;
	
	
	private $_user = null;
	
	public function isConnected()
	{
		if( isset( $_SESSION['login_token'] ) )
		{
			throw new \Exception('todo optimise binds DataBase and add user in DB test');
			/*if ( $this->_user == null )
			{
				$keys = array( 'token' );
				$signs = array( '=' );
				$values = array( $_SESSION['login_token'] );

				$query = SqlQuery::getTemp();
				$query->initSelectValues('*', LoginTableName::$TABLE_NAME_USERS, $keys, $signs, $values );
				$rows = $this->_db->fetchAll($query);
				if ( count( $rows ) < 1 )
				{
					return false;
				}
				$this->_db->count($query);
				
			}*/
			return true;
		}
		return false;
	}
	
	/**
	 * 
	 * @return User
	 */
	public function getUserConnected()
	{
		if ( !$this->isConnected() )
		{
			if ( _DEBUG ) Debug::getInstance()->addError ('User is\'n connected');
			return false;
		}
		
		if ( $this->_user == null )
		{
			/*$keys = array( 'token' );
			$signs = array( '=' );
			$values = array( $_SESSION['login_token'] );*/

			$query = SqlQuery::getTemp();
			$where = array( 'token'=>$_SESSION['login_token'] );
			$query->initSelect('*', LoginTableName::$TABLE_NAME_USERS, $where, $signs );
			$rows = $this->_db->fetchAll($query);
			if ( count( $rows ) < 1 )
			{
				return false;
			}

			$this->_user = new User( $this->_db );
			$this->_user->initUser( $rows[0]['email'], $rows[0]['token'], $rows[0]['role'] );
		}
		
		return $this->_user;
	}
	
	public function passEncrypt( $realPass, $email )
	{
		return hash( self::$_HASH_ALGO, $realPass.$email );
	}
	
	public function getUserList( $dataKey = null, $dataValue = null )
	{
		if (	!$this->isConnected() ||
				$this->getUserConnected()->getRole() != User::$ROLE_ADMIN )
		{
			return false;
		}
		
		$list = array();
		
		$tnu = LoginTableName::$TABLE_NAME_USERS;
		$tnd = LoginTableName::$TABLE_NAME_DATAS;
		
		$query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query->initSelect( 'email, role', $tnu );
		if ( $dataKey !== null && $dataValue !== null )
		{
			$query->setFrom('`'.$tnu.'` '
						. 'LEFT JOIN '.$tnd.' '
						. 'ON '.$tnu.'.email = '.$tnd.'.user_email');
			$query->setWhere( $tnd.'.key = \''.$dataKey.'\' AND '.$tnd.'.value = \''.$dataValue.'\'' );
		}
		$where = '';
		foreach ($this->_db->fetchAll($query) as $user)
		{
			$list[$user['email']] = array();
			$list[$user['email']]['role'] = $user['role'];
			$list[$user['email']]['datas'] = array();
			$where .= ($where == '') ? 'user_email = \''.$user['email'].'\'' : ' OR user_email = \''.$user['email'].'\'';
		}
		
		$query2 = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query2->initSelect( 'user_email, key, value', $tnd);
		$query2->setWhere($where);
		foreach ($this->_db->fetchAll($query2) as $value)
		{
			$email = $value['user_email'];
			if ( isset($list[$email]) )
			{
				$list[$email]['datas'][$value['key']] = $value['value'];
			}
		}
		
		return $list;
	}

	public function addUser( $email, $pass, $role = 1 )
	{
		if (	!$this->isConnected() ||
				$this->getUserConnected()->getRole() != User::$ROLE_ADMIN )
		{
			return false;
		}
		
		$query = SqlQuery::getTemp( SqlQuery::$TYPE_INSERT );
		$values = array();
		$values['email'] = $email;
		$values['pass'] = $this->passEncrypt($pass, $email);
		$values['role'] = $role;
		$values['token'] = $this->generateToken();
		$insert = 'INTO `'.LoginTableName::$TABLE_NAME_USERS.'`';
		$query->initInsertValues($insert, $values);
		$this->_db->execute($query);
		
		return true;
	}
	
	public function addDataToUser( $userEmail, $dataKey, $dataValue )
	{
		if (	!$this->isConnected() ||
				!(
					$this->getUserConnected()->getRole() == User::$ROLE_ADMIN ||
					$this->getUserConnected()->getEmail() == $userEmail
				)
			)
		{
			return false;
		}
		
		if ( DataBase::getInstance( _DB_DSN_CONTENT )->exist(LoginTableName::$TABLE_NAME_DATAS) )
		{
			$query = SqlQuery::getTemp( SqlQuery::$TYPE_INSERT );
			$values = array();
			$values['user_email'] = $userEmail;
			$values['key'] = $dataKey;
			$values['value'] = $dataValue;
			$insert = 'INTO `'.LoginTableName::$TABLE_NAME_DATAS.'`';
			$query->initInsertValues($insert, $values);
			
			return $this->_db->execute($query);
		}
		
	}
	
	public function connect( $email, $realPass )
	{
		$time = time();
		
		// anti-brute-force -->
			$query = SqlQuery::getTemp( SqlQuery::$TYPE_INSERT );
			$datas = array();
			//$datas['id'] = null;
			$datas['user_email'] = $email;
			$datas['time'] = $time;
			$datas['ip'] = $_SERVER["REMOTE_ADDR"];
			$query->initInsertValues( 'INTO `'.LoginTableName::$TABLE_NAME_LOGS.'`', $datas );
			$this->_db->execute($query);

			$query2 = SqlQuery::getTemp();
			$query2->initCount( LoginTableName::$TABLE_NAME_LOGS, array('user_email'=>$email, 'time'=>($time-2) ), array('=', '>') );
			if ( $this->_db->count($query2) > 1 ) return false;
		// <-- anti-brute-force
		
		$cryptedPass = $this->passEncrypt($realPass, $email);
		$where3 = array( 'email'=>$email, 'pass'=>$cryptedPass );
		$query3 = SqlQuery::getTemp();
		$query3->initSelect('*', LoginTableName::$TABLE_NAME_USERS, $where3 );
		$rows = $this->_db->fetchAll($query3);
		if ( count( $rows ) < 1 ) return false;
		
		$token = $this->generateToken();
		$query4 = SqlQuery::getTemp( SqlQuery::$TYPE_UPDATE );
		$query4->setUpdate( LoginTableName::$TABLE_NAME_USERS );
		$query4->setSet('token = \''.$token.'\'');
		$query4->setWhere( 'email = \''.$rows[0]['email'].'\'' );
		
		if( $this->_db->execute($query4) )
		{
			$_SESSION['login_token'] = $token;
		}
	}
	
	public function disconnect()
	{
		if ( $this->isConnected() )
		{
			$user = $this->getUserConnected();
			
			$token = $this->generateToken();
			$query = SqlQuery::getTemp( SqlQuery::$TYPE_UPDATE );
			$query->setUpdate( LoginTableName::$TABLE_NAME_USERS );
			$query->setSet('token = \''.$token.'\'');
			$query->setWhere( 'email = \''.$user->getEmail().'\'' );
			$this->_db->execute($query);
		}
			
		$this->_user = null;

		session_unset();
		session_destroy();
	}
	
	private function generateToken()
	{
		return md5( rand(0, 9999).microtime() );
	}
	
	private function create()
	{
		$req1 = SqlQuery::getTemp( SqlQuery::$TYPE_CREATE );
		$create1 = 'TABLE IF NOT EXISTS `'.LoginTableName::$TABLE_NAME_USERS.'` ( '
			. 'email TEXT UNIQUE, '
			. 'pass TEXT, '
			. 'role INT DEFAULT 0, '
			. 'token TEXT DEFAULT \''.$this->generateToken().'\' '
			. ');';
		$req1->setCreate($create1);
		$this->_db->execute( $req1 );

		$req2 = SqlQuery::getTemp( SqlQuery::$TYPE_CREATE );
		$create2 = 'TABLE IF NOT EXISTS `'.LoginTableName::$TABLE_NAME_LOGS.'` ( '
			. 'user_email TEXT, '
			. 'time INT, '
			. 'ip TEXT );';
		$req2->setCreate( $create2 );
		$this->_db->execute( $req2 );
		
		$req3 = SqlQuery::getTemp( SqlQuery::$TYPE_CREATE );
		$create3 = 'TABLE IF NOT EXISTS `'.LoginTableName::$TABLE_NAME_DATAS.'` ( '
			. 'user_email TEXT, '
			. 'key INT, '
			. 'value TEXT );';
		$req3->setCreate( $create3 );
		$this->_db->execute( $req3 );
	}
	
	private function isDBInitialized()
	{
		return $this->_db->exist( LoginTableName::$TABLE_NAME_USERS );
	}
	
	private function __construct( $dbDsn )
	{
		if ( !self::$_IS_SESSION_STARTED )
		{
			session_start();
			self::$_IS_SESSION_STARTED = true;
		}
		
		$this->_db = DataBase::getInstance($dbDsn);
		
		if ( !$this->isDBInitialized() )
		{
			$this->create();
		}
	}
	
	/**
	 * 
	 * @param string $dbDsn
	 * @return DataBase
	 */
	public static function getInstance( $dbDsn ) 
    {
		if(!isset(self::$_INSTANCE[$dbDsn]))
        {
            self::$_INSTANCE[$dbDsn] = new Login($dbDsn);
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
