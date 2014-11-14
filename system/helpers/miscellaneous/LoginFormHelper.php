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
 * Helper class for formularies of login's pages
 * 
 * @author Namide
 */
class LoginFormHelper
{
	private $_login;
	
	/**
	 * To use this class you must to push the login corresponding
	 * @param Login $login
	 */
	public function __construct( $login )
	{
		$this->_login = $login;
	}
	
	/**
	 * Test if the POST variables add a user and return a form to add a user.
	 * 
	 * @return string
	 */
	public function getFormRegisterUser( $role = 1 )
	{
		$build = \Flea\Helper::getBuildUtil();
		$gen = \Flea\Helper::getGeneral();
		$post = $gen->getCurrentPostUrl();
		$error = '';
		if (	isset($post['addUserEmail']) &&
				isset($post['addUserPass']) )
		{
			$vo = $this->_login->registerUser( $post['addUserEmail'], $post['addUserPass'], $role );
			if( $vo->content )
			{
				$currentUrl = $build->getAbsUrl( $gen->getCurrentPage()->getName() );
				header('Location: '.$currentUrl);
			}
			else if ( count($vo->errorList) > 0 )
			{
				$error .= '<strong class="error">';
				foreach ($vo->errorList as $e)
				{
					$error .= $e . ' ';
				}
				$error .= '</strong>';
			}
		}

		$currentUrl = $build->getAbsUrl( $gen->getCurrentPage()->getName() );
		$form = '<form method="post" action="'.$currentUrl.'">
					'.$error.'
					<input class="field" type="text" name="addUserEmail" placeholder="E-mail" value="" required="required" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"/>
					<input class="field" type="password" name="addUserPass" placeholder="Password" value="" required="required"/>
					<input type="submit" name="add" value="add">
				</form>';
		return $form;
	}
	
	/**
	 * Test if the POST variables add a user and return a form to add a user.
	 * 
	 * @return string
	 */
	public function getFormAddUser()
	{
		$build = \Flea\Helper::getBuildUtil();
		$gen = \Flea\Helper::getGeneral();
		$post = $gen->getCurrentPostUrl();
		$error = '';
		if (	isset($post['addUserEmail']) &&
				isset($post['addUserPass']) )
		{
			$vo = $this->_login->addUser( $post['addUserEmail'], $post['addUserPass'] );
			if( $vo->content )
			{
				$currentUrl = $build->getAbsUrl( $gen->getCurrentPage()->getName() );
				header('Location: '.$currentUrl);
			}
			else if ( count($vo->errorList) > 0 )
			{
				$error .= '<strong class="error">';
				foreach ($vo->errorList as $e)
				{
					$error .= $e . ' ';
				}
				$error .= '</strong>';
			}
		}

		$currentUrl = $build->getAbsUrl( $gen->getCurrentPage()->getName() );
		$form = '<form method="post" action="'.$currentUrl.'">
					'.$error.'
					<input class="field" type="text" name="addUserEmail" placeholder="E-mail" value="" required="required" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"/>
					<input class="field" type="password" name="addUserPass" placeholder="Password" value="" required="required"/>
					<input type="submit" name="add" value="add">
				</form>';
		return $form;
	}
	
	/**
	 * Test if the POST variables connect a user and return a form to connect a user.
	 *
	 * @return string
	 */
	public function getFormConnectUser()
	{
		$build = \Flea\Helper::getBuildUtil();
		$gen = \Flea\General::getInstance();
		$post = $gen->getCurrentPostUrl();
		$error = '';
		if (	isset($post['connectUserEmail']) &&
				isset($post['connectUserPass']) )
		{
			$vo = $this->_login->connect( $post['connectUserEmail'], $post['connectUserPass'] );
			if( $vo->content )
			{
				//return '<script>location.reload();</script>';
				$currentUrl = $build->getAbsUrl( $gen->getCurrentPage()->getName() );
				header('Location: '.$currentUrl);
			}
			else if ( count($vo->errorList) > 0 )
			{
				$error .= '<strong class="error">';
				foreach ($vo->errorList as $e)
				{
					$error .= $e . ' ';
				}
				$error .= '</strong>';
			}
			
		}

		$currentUrl = $build->getAbsUrl( $gen->getCurrentPage()->getName() );

		$form = '<form method="post" action="'.$currentUrl.'">
					'.$error.'
					<input class="field" type="text" name="connectUserEmail" placeholder="E-mail" value="" required="required"/>
					<input class="field" type="password" name="connectUserPass" placeholder="Password" value="" required="required"/>
					<input type="submit" name="connect" value="connect">
				</form>';
		return $form;
	}
	
	/**
	 * Test if the POST variables disconnect a user and return a form to disconnect a user.
	 * 
	 * @return string
	 */
	public function getFormDisconnectUser()
	{
		$buil = \Flea\Helper::getBuildUtil();
		$gen = \Flea\General::getInstance();
		$post = $gen->getCurrentPostUrl();
		if ( isset($post['disconnectUser']) )
		{
			if( $this->_login->disconnect() )
			{
				//return '<script>location.reload();</script>';
				$currentUrl = $buil->getAbsUrl( $gen->getCurrentPage()->getName() );
				header('Location: '.$currentUrl);
			}
		}

		$currentUrl = $buil->getAbsUrl( $gen->getCurrentPage()->getName() );
		$form = '<form method="post" action="'.$currentUrl.'">
					<input type="hidden" name="disconnectUser" value="1">
					<input type="submit" name="disconnect" value="disconnect">
				</form>';
		return $form;
	}
	
	/**
	 * Return a list of the user corresponding of the pair key:value
	 * 
	 * @param string $key		A key to filter like 'group' (optional)
	 * @param string $value		A value to filter like 'gamer' (optional)
	 * @return string			The list of users corresponding to the pair
	 */
	public function getUserList( $key = null, $value = null )
	{
		$vo = $this->_login->getUserList( $key, $value );
		$error = '';
		if ( $vo->error && count($vo->errorList) > 0 )
		{
			$error .= '<strong class="error">';
			foreach ($vo->errorList as $e)
			{
				$error .= $e . ' ';
			}
			$error .= '</strong>';
		}

		$list = $vo->content;
		$output = $error.'<ul>';
		if ( is_array($list) )
		{
			foreach ($list as $userMail => $userDatas)
			{
				$output .= '<li>'.$userMail;
				$output .= '<ul>role: '.$userDatas->getRole();
				foreach ($userDatas->getDatas() as $dataKey => $dataValue )
				{
					$output .= '<li>'.$dataKey.': '.$dataValue.'</li>';
				}
				$output .= '</ul>';
				$output .= '</li>';
			}
		}
		$output .= '</ul>';
		return $output;
	}
	
}