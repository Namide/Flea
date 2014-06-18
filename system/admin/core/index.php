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

namespace Flea\admin;

function adminHeader()
{
	$dir = _SYSTEM_DIRECTORY.'admin/';
	$files = array_diff( scandir($dir), array('.','..','core') );
	echo '<ul>';
	foreach ($files as $file)
	{
		if ( is_dir($dir.'/'.$file) && file_exists($dir.'/'.$file.'/index.php') )
		{
			echo '<li><a href="admin.php?page='.$file.'">'.$file.'</a></li>';
		}
	}
	echo '</ul>';
}

function adminBody()
{
	if (	isset( $_GET['page'] ) &&
			file_exists( _SYSTEM_DIRECTORY.'admin/'.$_GET['page'].'/index.php' ) )
	{
		include _SYSTEM_DIRECTORY.'admin/'.$_GET['page'].'/index.php';
	}
}

?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Admin</title>
	<style type="text/css">
		
		.error { color: red; }
		.passed { color: green; }
		strong { font-weight: bold; }
		
		body
		{
			font-family: Arial, sans-serif;
			font-size: 12px;
			color: #444;
			font-weight: lighter;
		}
		body>header
		{
			position:fixed;
			top:0;
			left:0;
			right:0;
			padding: 16px 16px 0 16px;
			background-color: rgba( 127, 0, 255, 0.8 );
			box-shadow: 0 4px 8px rgba(0, 0, 50, 0.25); 
		}
		body>header h1 { text-transform: uppercase; font-weight: lighter; }
		body>header h1, body>header nav, body>header ul, body>header li, body>header a, body>header p
		{
			display:inline-block;
			color: #FFF;
			margin-left: 8px;
			text-decoration: none;
		}
		body>header nav { float: right; }
		body>section
		{
			margin-top:64px;
			padding:16px;
		}

		p { text-align: left; }

		table h1, table h2, table h3, table h4, table h5, table h6 {  margin: 0; }
		
		h1 { font-size: 30px; color:#000; margin: 0; text-transform: uppercase; font-weight: lighter; }
		h2 { font-size: 20px; color:#000; margin: 30px 0 10px 0; text-transform: uppercase; font-weight: lighter; }
		h3 { font-size: 18px; color:#000; margin: 18px 0 10px 0; text-transform: uppercase; font-weight: lighter; }
		h4 { font-size: 16px; color:#000; margin: 15px 0 10px 0; text-transform: uppercase; font-weight: lighter; }
		h5 { font-size: 14px; color:#000; margin: 12px 0 10px 0; text-transform: uppercase; font-weight: lighter; }
		h6 { font-size: 12px; color:#000; margin: 10px 0 10px 0; text-transform: uppercase; font-weight: lighter; }

		table tr:nth-child(odd) { background-color:#EEE; 	}
		table tr:nth-child(even) { background-color:#FFF; 	}
		table td { padding:8px; }
		table th { padding:16px 8px; }
		table { border-spacing: 0; float:left; margin: 0 32px 32px 0; }
		
		ul, li { margin:0; padding:0; }
		ul { padding-left:10px; }
	</style>
</head>

<body>
	
	<header>
		<h1></h1>
		<nav><?=\flea\admin\adminHeader()?></nav>		
	</header>
	
	<section>
		<?=\flea\admin\adminBody()?>
	</section>
	
	<?php
		include_once _SYSTEM_DIRECTORY.'init/import.php';
		if ( _DEBUG )
		{
			echo '<strong>'.\Flea\Debug::getInstance()->getTimes('').'</strong><br><br>';
			\Flea\Debug::getInstance()->dispatchErrors();
		}
	?>
	
</body>
</html>