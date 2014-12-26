<?php

namespace Flea\admin;

$login = \Flea::getLogin('sqlite:'._CONTENT_DIRECTORY.'flea-admin/flea-admin-login.sqlite');
	
if ( !$login->hasUsersInList() || !$login->isConnected() )
{
	echo '<script>window.location.href = "' ,
			\Flea::getBuildUtil()->getAbsUrl('flea-admin/login') ,
			'";</script>';
	exit;
}
