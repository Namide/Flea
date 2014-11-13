<?php

namespace Flea\admin;

$login = \Flea\Helper::getLogin('sqlite:'._CONTENT_DIRECTORY.'flea-admin-login.sqlite');
	
if ( !$login->hasUsersInList() )
{
	echo '<h1>Register</h1>';
	echo $login->getLoginFormHelper()->getFormRegisterUser( 2 );
}
else if ( !$login->isConnected() )
{
	echo '<h1>Login</h1>';
	echo $login->getLoginFormHelper()->getFormConnectUser();
}
else
{
	echo $login->getLoginFormHelper()->getFormDisconnectUser();
	echo '<h1>User list</h1>';
	echo $login->getLoginFormHelper()->getUserList();
}

include_once _SYSTEM_DIRECTORY.'init/import.php';
if ( _DEBUG )
{
	echo '<strong>'.\Flea\Debug::getInstance()->getTimes('').'</strong><br><br>';
	\Flea\Debug::getInstance()->dispatchErrors();
}
