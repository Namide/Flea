
<h1>Login page</h1>

Contenu !

<?php

include_once _SYSTEM_DIRECTORY.'helpers/miscellaneous/Login.php';
$login = \Flea\Login::getInstance('sqlite:'._CONTENT_DIRECTORY.'login.sqlite');
	
?>

<table>
	<tr>
		<td>Add User</td>
		<td><?= addUser($login); ?></td>
	</tr>
	<tr>
		<td>Connect</td>
		<td><?= connectUser($login); ?></td>
	</tr>
	
	<tr>
		<td>Disconnect</td>
		<td><?= disconnectUser($login); ?></td>
	</tr>
	
	<tr>
		<td>Is connected</td>
		<td><?= ( $login->isConnected() ) ? ( $login->getUserConnected()->getEmail() ) : 'false'; ?></td>
	</tr>
	
	<tr>
		<td>User list</td>
		<td><?= getUserList( $login ); ?></td>
	</tr>
	
</table>

<?php

function addUser( \Flea\Login $login )
{
	$urlUtil = Flea\UrlUtil::getInstance();
	$gen = \Flea\General::getInstance();
	$post = $gen->getCurrentPostUrl();
	if (	isset($post['addUserEmail']) &&
			isset($post['addUserPass']) )
	{
		$login->addUser( $post['addUserEmail'], $post['addUserPass'] );
	}
	
	$currentUrl = Flea\BuildUtil::getInstance()->getAbsUrl( $gen->getCurrentPage()->getName() );
	
	$form = '<form method="post" action="'.$currentUrl.'">
				<input class="field" type="text" name="addUserEmail" placeholder="E-mail" value="" required="required" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"/>
				<input class="field" type="password" name="addUserPass" placeholder="Password" value="" required="required"/>
				<input type="submit" name="add" value="add">
			</form>';
	return $form;
}

function connectUser( \Flea\Login $login )
{
	$urlUtil = Flea\UrlUtil::getInstance();
	$gen = \Flea\General::getInstance();
	$post = $gen->getCurrentPostUrl();
	if (	isset($post['connectUserEmail']) &&
			isset($post['connectUserPass']) )
	{
		$login->connect( $post['connectUserEmail'], $post['connectUserPass'] );
	}
	
	$currentUrl = Flea\BuildUtil::getInstance()->getAbsUrl( $gen->getCurrentPage()->getName() );
	
	$form = '<form method="post" action="'.$currentUrl.'">
				<input class="field" type="text" name="connectUserEmail" placeholder="E-mail" value="" required="required"/>
				<input class="field" type="password" name="connectUserPass" placeholder="Password" value="" required="required"/>
				<input type="submit" name="connect" value="connect">
			</form>';
	return $form;
}

	
function getUserList( \Flea\Login $login )
{
	$list = $login->getUserList('group', 'CD');
	$output = '';
	if ( is_array($list) )
	{
		foreach ($list as $userMail => $userDatas)
		{
			$output .= $userMail.'<br>-role: '.$userDatas['role'].'<br>';
			foreach ($userDatas['datas'] as $dataKey => $dataValue )
			{
				$output .= '-'.$dataKey.': '.$dataValue.'<br>';
			}
		}
	}
	return $output;
}
	
function disconnectUser( \Flea\Login $login )
{
	$urlUtil = Flea\UrlUtil::getInstance();
	$gen = \Flea\General::getInstance();
	$post = $gen->getCurrentPostUrl();
	if ( isset($post['disconnectUser']) )
	{
		$login->disconnect();
	}
	
	$currentUrl = Flea\BuildUtil::getInstance()->getAbsUrl( $gen->getCurrentPage()->getName() );
	
	$form = '<form method="post" action="'.$currentUrl.'">
				<input type="hidden" name="disconnectUser" value="1">
				<input type="submit" name="disconnect" value="disconnect">
			</form>';
	return $form;
}

?>