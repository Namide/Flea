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
 * Flags for the names of the table used by the Login class.
 * 
 * @author Namide
 */
class LoginTableName
{
	/**
	 * Name of the table of the datas of users.
	 * Datas are optionnal and unlimited.
	 * @var string 
	 */
	public static $TABLE_NAME_DATAS = 'login_datas';
	
	/**
	 * Name of the table of users (email, password...).
	 * @var string
	 */
	public static $TABLE_NAME_USERS = 'login_users';
	
	/**
	 * Name of the table of logs of users.
	 * @var string 
	 */
	public static $TABLE_NAME_LOGS = 'login_logs';
}

/**
 * User's variables used by the Login class.
 * 
 * @author Namide
 */
class LoginUser
{
	/**
	 * Role of the user. Basic user is authorized to less informations.
	 * 
	 * @var int
	 */
	public static $ROLE_BASIC = 1;
	
	/**
	 * Role of the user. Admin is authorized to more informations.
	 * 
	 * @var int
	 */
	public static $ROLE_ADMIN = 2;
	
	private $_db;
	
	private $_email;
	
	/**
	 * Email of the user
	 * 
	 * @return string
	 */
	public function getEmail() { return $this->_email; }
	
	private $_token;
	
	/**
	 * Token is used for security
	 * 
	 * @return string
	 */
	public function getToken() { return $this->_token; }
	
	private $_role;
	
	/**
	 * Role of the user: LoginUser::$ROLE_BASIC or LoginUser::$ROLE_ADMIN
	 * 
	 * @return int
	 */
	public function getRole() { return $this->_role; }
	
	/**
	 * Init the user.
	 * 
	 * @param DataBase $db		DataBase used to this user
	 */
	public function __construct( $db )
	{
		$this->_db = $db;
	}
	
	/**
	 * Initialize user's datas
	 * 
	 * @param type $email	Email of the user	
	 * @param type $token	Token of the current conection of the user
	 * @param type $role	Role of the user
	 */
	public function init( $email, $token, $role = 1 )
	{
		$this->_email = $email;
		$this->_token = $token;
		$this->_role = $role;
	}
	
	private $_datas;
	
	/**
	 * A data is a pair with key and value.
	 * 
	 * @return DataList		Data of the user
	 */
	public function getDatas()
	{
		if ( $this->_datas === null )
		{
			$this->_datas = new DataList(true);
			
			$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
			$where = array( 'user_email'=>$this->getEmail() );
			$query->initSelect('key, value', '`'.LoginTableName::$TABLE_NAME_DATAS.'`', $where);
			
			foreach ( $this->_db->fetchAll($query) as $row )
			{
				$this->_datas->add($row['value'], $row['key']);
			}
			
		}
		return $this->_datas;
	}
	
}

/**
 * Class to manipulate Users (connect, disconnect, add...)
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
	
	private $_loginFormHelper = null;
	
	private $_user = null;
	
	/**
	 * Test if this user is connected (with Session).
	 * 
	 * @return boolean		Is connected
	 */
	public function isConnected()
	{
		if( isset( $_SESSION['login_token'] ) )
		{
			if ( $this->_user === null )
			{
				$where = array( 'token'=>$_SESSION['login_token'] );
				$query = SqlQuery::getTemp( SqlQuery::$TYPE_SELECT );
				$query->initSelect('*', LoginTableName::$TABLE_NAME_USERS, $where );
				$rows = $this->_db->fetchAll($query);
				if ( count( $rows ) < 1 )
				{
					return false;
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * User connected.
	 * 
	 * @return ValueObject
	 */
	public function getUserConnected()
	{
		if ( !$this->isConnected() )
		{
			//if ( _DEBUG ) Debug::getInstance()->addError ('User is\'n connected');
			return new ValueObject(null, true, array('You are don\'t connected') );
		}
		
		if ( $this->_user == null )
		{
			$query = SqlQuery::getTemp();
			$where = array( 'token'=>$_SESSION['login_token'] );
			$query->initSelect('*', LoginTableName::$TABLE_NAME_USERS, $where );
			$rows = $this->_db->fetchAll($query);
			if ( count( $rows ) < 1 )
			{
				return new ValueObject(null, true, array('Connection data corrupted') );
			}

			$this->_user = new LoginUser( $this->_db );
			$this->_user->init( $rows[0]['email'], $rows[0]['token'], $rows[0]['role'] );
		}
		
		return new ValueObject($this->_user);;
	}
	
	/**
	 * An helper to make formularies of the login page.
	 * 
	 * @return LoginFormHelper
	 */
	public function getLoginFormHelper()
	{
		if ( $this->_loginFormHelper === null )
		{
			include_once _SYSTEM_DIRECTORY.'helpers/miscellaneous/LoginFormHelper.php';
			$this->_loginFormHelper = new LoginFormHelper($this);
		}
		return $this->_loginFormHelper;
	}
	
	/**
	 * Crypt the password.
	 * 
	 * @param string $realPass		Password not crypted
	 * @param string $email			Email of the user (for the saltz)
	 * @return string				Password crypted
	 */
	public function passEncrypt( $realPass, $email )
	{
		return hash( self::$_HASH_ALGO, $realPass.$email );
	}
	
	/**
	 * Test if the data base has 1 or more users.
	 * 
	 * @return bool		True is it has 1 or more users in the data base
	 */
	public function hasUsersInList()
	{
		$query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query->initSelect( 'COUNT(*)', LoginTableName::$TABLE_NAME_USERS );
		return $this->_db->count($query) > 0;
	}
	
	/**
	 * Found and return a ValueObject with the user
	 * (error if no user connected or email not found or
	 * if the user don't have authorizations).
	 * Work only if your are admin and connected
	 * 
	 * @param string $email		Mail of the user
	 * @return ValueObject		<code>getUserByEMail( $email )->content</code>;
	 */
	public function getUserByEMail( $email )
	{
		$userConnectedVO = $this->getUserConnected();
		if ( $userConnectedVO->error )
		{
			return new ValueObject( null, true, $userConnectedVO->errorList );
		}
		else if ( $userConnectedVO->content->getRole() != LoginUser::$ROLE_ADMIN )
		{
			return new ValueObject( null, true, array('Only admin can see others user with her email') );
		}
		
		$tnu = LoginTableName::$TABLE_NAME_USERS;
		
		$query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query->initSelect( 'email, role', $tnu, array('email'=>$email) );
		$rows = $this->_db->fetchAll($query);
		if ( count( $rows ) < 1 )
		{
			return new ValueObject( null, true, array('No users have this email') );
		}
		
		$user = new LoginUser( $this->_db );
		$user->init( $rows[0]['email'], 'null', $rows[0]['role'] );
		return new ValueObject($user);
	}
	
	/**
	 * List of the users.
	 * Only if your are admin and connected
	 * <br>Example of getUserList() in <code>en-build.php</code>
	 * <pre>
	 * $vo = $login->getUserList('group', 'admin');
	 * $list = $vo->content;
	 * $output = '';
	 * if ( !$vo->error )
	 * {
	 *   foreach ($list as $userMail => $userDatas)
	 *   {
	 *     $output .= $userMail.'<br>-role: '.$userDatas['role'].'<br>';
	 *     foreach ($userDatas['datas'] as $dataKey => $dataValue )
	 *     {
	 *       $output .= '-'.$dataKey.': '.$dataValue.'<br>';
	 *     }
	 *   }
	 * }
	 * else if ( count($vo->errorList)>0 )
	 * {
	 *   foreach ($vo->errorList as $errorInfo)
	 *   {
	 *     $output .= $errorInfo;
	 *   }
	 * }
	 * echo $output;
	 * </pre>
	 * 
	 * @param string $dataKey				Key for the filter (example: group)
	 * @param string $dataValue				Value for the filter (example: gamer)
	 * @return ValueObject					List of the users with $dataKey = $dataValue
	 */
	public function getUserList( $dataKey = null, $dataValue = null )
	{
		$list = array();
		
		$userConnectedVO = $this->getUserConnected();
		if ( $userConnectedVO->error )
		{
			return new ValueObject( $list, true, $userConnectedVO->errorList );
		}
		else if ( $userConnectedVO->content->getRole() != LoginUser::$ROLE_ADMIN )
		{
			return new ValueObject( $list, true, array('Only admin can see others user with her email') );
		}
		
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
		
		foreach ($this->_db->fetchAll($query) as $user)
		{
			$list[$user['email']] = new LoginUser( $this->_db );
			$list[$user['email']]->init( $user['email'], 'null', $user['role'] );
		}
		
		return new ValueObject($list);
	}
	
	/**
	 * Add a new user.
	 * Only if your are admin and connected.<br>
	 * <br>Example of addUser() in <code>en-build.php</code>
	 * <pre>
	 * $build = Flea::getBuildUtil();
	 * $gen = Flea::getGeneral();
	 * $post = $gen->getCurrentPostUrl();
	 * if (	isset($post['addUserEmail']) &&
	 *		isset($post['addUserPass']) )
	 * {
	 * 	$login->addUser( $post['addUserEmail'],
	 *				$post['addUserPass'] );
	 * }
	 * $currentUrl = $build->getAbsUrl( $gen->getCurrentPage()->getName() );
	 * $form = '<form method="post" action="'.$currentUrl.'">
	 * 		<input class="field" 
	 *				type="text"
	 *				name="addUserEmail"
	 *				placeholder="E-mail"
	 *				value=""
	 *				required="required"
	 *				pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"/>
	 * 		<input class="field"
	 *				type="password"
	 *				name="addUserPass"
	 *				placeholder="Password"
	 *				value=""
	 *				required="required"/>
	 * 		<input type="submit"
	 *				name="add"
	 *				value="add">
	 * 	</form>';
	 * echo $form;
	 * </pre>
	 * 
	 * @param type $email		Email of the new user
	 * @param type $realPass	Password of the new user
	 * @param type $role		Role of the new user
	 * @return ValueObject		Content is true if the user is added
	 */
	public function addUser( $email, $realPass, $role = 1 )
	{
		$userConnectedVO = $this->getUserConnected();
		if ( $userConnectedVO->error )
		{
			return new ValueObject( false, true, $userConnectedVO->errorList );
		}
		else if ( $userConnectedVO->content->getRole() != LoginUser::$ROLE_ADMIN )
		{
			return new ValueObject( false, true, array('Only admin can add a user.') );
		}
		
		return registerUser( $email, $realPass, $role );
	}
	
	/**
	 * Register a new user
	 * 
	 * @param type $email		Email of the new user
	 * @param type $realPass	Password of the new user
	 * @param type $role		Role of the new user
	 * @return ValueObject		ValueObject with datas : content = true if the user is added
	 */
	public function registerUser( $email, $realPass, $role = 1 )
	{
		$sameEmail = $this->getUserByEMail($email);
		if ( !$sameEmail->error && $sameEmail->content !== null )
		{
			return new ValueObject( false, true, array('An user with the same email already exist.') );
		}
		
		$query = SqlQuery::getTemp( SqlQuery::$TYPE_INSERT );
		$values = array();
		$values['email'] = $email;
		$values['pass'] = $this->passEncrypt($realPass, $email);
		$values['role'] = $role;
		$values['token'] = $this->generateToken();
		$insert = LoginTableName::$TABLE_NAME_USERS;
		$query->initInsertValues($insert, $values);
		$this->_db->execute($query);
		
		return new ValueObject( true );
	}
	
	/**
	 * Add informations to a user.
	 * Only if your are admin and connected
	 * 
	 * @param type $userEmail		Email of the user
	 * @param type $dataKey			Label of the data (example: group)
	 * @param type $dataValue		Value of the data (example: gamer)
	 * @return ValueObject			ValueObject, $vo->content = true if the data is added
	 */
	public function addDataToUser( $userEmail, $dataKey, $dataValue )
	{
		$userConnectedVO = $this->getUserConnected();
		if ( $userConnectedVO->error )
		{
			return new ValueObject( false, true, $userConnectedVO->errorList );
		}
		else if (	$userConnectedVO->content->getRole() != LoginUser::$ROLE_ADMIN ||
					$userConnectedVO->content->getEmail() == $userEmail	)
		{
			return new ValueObject( false, true, array('Only admin or the user can add data.') );
		}
				
		if ( $this->_db->exist(LoginTableName::$TABLE_NAME_DATAS) )
		{
			$query = SqlQuery::getTemp( SqlQuery::$TYPE_INSERT );
			$values = array();
			$values['user_email'] = $userEmail;
			$values['key'] = $dataKey;
			$values['value'] = $dataValue;
			$insert = LoginTableName::$TABLE_NAME_DATAS;
			$query->initInsertValues($insert, $values);
			
			$added = $this->_db->execute($query);
			if ( $added )
			{
				return new ValueObject( true );
			}
			else				
			{
				return new ValueObject( false, true, array('An error as occured in the data base.') );
			}
		}
		
		return new ValueObject( false, true, array('This table does not exist in the database') );
	}
	
	/**
	 * Connect the user.
	 * After this state a token will be storage in the session
	 * <br>Example of connect() in <code>en-build.php</code>
	 * <pre>
	 * $build = Flea::getBuildUtil();
	 * $gen = \Flea\General::getInstance();
	 * $post = $gen->getCurrentPostUrl();
	 * if (	isset($post['connectUserEmail']) &&
	 * 		isset($post['connectUserPass']) )
	 * {
	 * 	$login->connect( $post['connectUserEmail'], 
	 * 			$post['connectUserPass'] );
	 * }
	 * $currentUrl = $build->getAbsUrl( $gen->getCurrentPage()->getName() );
	 * $form = '<form method="post" action="'.$currentUrl.'">
	 * 		<input class="field"
	 * 			type="text" 
	 * 			name="connectUserEmail" 
	 * 			placeholder="E-mail" 
	 * 			value="" 
	 * 			required="required"/>
	 * 		<input class="field" 
	 * 			type="password" 
	 * 			name="connectUserPass" 
	 * 			placeholder="Password" 
	 * 			value="" required="required"/>
	 * 		<input type="submit" 
	 * 			name="connect" 
	 * 			value="connect">
	 * 		</form>';
	 * echo $form;
	 * </pre>
	 * 
	 * @param string $email			Email of the user
	 * @param string $realPass		Password of the user
	 * @return ValueObject			It is connected
	 */
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
			$query->initInsertValues( LoginTableName::$TABLE_NAME_LOGS, $datas );
			$this->_db->execute($query);

			$query2 = SqlQuery::getTemp();
			$query2->initCount( LoginTableName::$TABLE_NAME_LOGS, array('user_email'=>$email, 'time'=>($time-2) ), array('=', '>') );
			if ( $this->_db->count($query2) > 1 )
			{
				return new ValueObject( false, true, array('The server has detected an attack by brute forcing.') );
			}
		// <-- anti-brute-force
		
		$cryptedPass = $this->passEncrypt($realPass, $email);
		$where3 = array( 'email'=>$email, 'pass'=>$cryptedPass );
		$query3 = SqlQuery::getTemp();
		$query3->initSelect('*', LoginTableName::$TABLE_NAME_USERS, $where3 );
		$rows = $this->_db->fetchAll($query3);
		if ( count( $rows ) < 1 ) 
		{
			return new ValueObject( false, true, array('No password matches this email.') );
		}
		
		$token = $this->generateToken();
		$query4 = SqlQuery::getTemp( SqlQuery::$TYPE_UPDATE );
		$query4->setUpdate( LoginTableName::$TABLE_NAME_USERS );
		$query4->setSet('token = \''.$token.'\'');
		$query4->setWhere( 'email = \''.$rows[0]['email'].'\'' );
		
		if( $this->_db->execute($query4) )
		{
			$_SESSION['login_token'] = $token;
			return new ValueObject( true );
		}
		else
		{
			return new ValueObject( false, true, array('An error in the data base has occurred.') );
		}
	}
	
	/**
	 * Disconnect the current connected user.
	 * <br>Example of connect() in <code>en-build.php</code>
	 * <pre>
	 * $buil = Flea::getBuildUtil();
	 * $gen = \Flea\General::getInstance();
	 * $post = $gen->getCurrentPostUrl();
	 * if ( isset($post['disconnectUser']) )
	 * {
	 *   $login->disconnect();
	 * }
	 * $currentUrl = $buil->getAbsUrl( $gen->getCurrentPage()->getName() );
	 * $form = '<form method="post" action="'.$currentUrl.'">
	 *			<input type="hidden" name="disconnectUser" value="1">
	 *			<input type="submit" name="disconnect" value="disconnect">
	 *		</form>';
	 * echo $form;
	 * </pre>
	 * 
	 * @return boolean
	 */
	public function disconnect()
	{
		$vo = $this->getUserConnected();
		if ( !$vo->error )
		{
			$user = $vo->content;
			
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
		return true;
	}
	
	private function generateToken()
	{
		return md5( rand(0, 9999).microtime() );
	}
	
	/**
	 * Create the table of the users.
	 */
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
	
	/**
	 * Test if the table of the user exist.
	 * 
	 * @return Bool			True if the table exist 
	 */
	private function isDbInitialized()
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
		
		if ( !$this->isDbInitialized() )
		{
			$this->create();
		}
	}
	
	/**
	 * Get the Login manager
	 * 
	 * @param string $dbDsn		Data Source Name of the data base
	 * @return Login			Login corresponding at the data base
	 */
	public static function getInstance( $dbDsn ) 
    {
		if(!isset(self::$_INSTANCE[$dbDsn]))
        {
            self::$_INSTANCE[$dbDsn] = new Login($dbDsn);
        }
        return self::$_INSTANCE[$dbDsn];
    }
	
	final private function __clone()
    {
        if ( _DEBUG ) 
		{
			Debug::getInstance()->addError('You can\'t clone a multiton');
		}
    }
}
