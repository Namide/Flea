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


function testString( $a, $b )
{
	if ( $a === $b ) return '<span class="passed">true</span>';
	return '<span class="error">false</span>';
}

function writeTest( $method, $resul )
{
	global $class;
	echo '
			<tr>
				<td>'.$class.'</td><td>'.$method.'()</td><td>'.$resul.'</td>
			</tr>';
}

?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Unit test</title>
	<style type="text/css">
	.error { color: red; }
	.passed { color: green; }
	</style>
</head>

<body>
	
	
	
	<table>
		<tbody>
			<tr>
				<th>Class</th>
				<th>method</th>
				<td>passed</td>
			</tr>
			<?php
	
				
				include_once 'config.php';
				include_once _SYSTEM_DIRECTORY.'data/list/LangList.php';
				include_once _SYSTEM_DIRECTORY.'data/Element.php';
				$element = new \Flea\Element();
				$class = 'Element';
				$element->addTags( array('a:b','bb','yohé\glitch', 2) );
				writeTest( 'addTags', testString( $element->hasTag('yohé\glitch'), true ) );
				$element->addTag( 'hum' );
				writeTest( 'addTag', testString( $element->hasTag('yohé\glitch'), true ) );
				writeTest( 'hasTag', testString( $element->hasTag('hum'), true ) );
				$element->addTag( 'hum' );
				writeTest( 'hasTag', testString( $element->hasTag('hum'), true ) );
				writeTest( 'hasTags', testString( $element->hasTags(array('hum', 'yohé\glitch', 2)), true ) );
				writeTest( 'hasTags', testString( $element->hasTags(array('no')), false ) );
				
			?>
			<tr>
				<th>Version</th>
				<td><span itemprop="version">beta</span></td>
			</tr>
			<tr>
				<th>Plate-forme</th>
				<td>en ligne (Facebook / navigateurs)</td>
			</tr>
			<tr>
				<th>Genre</th>
				<td><span itemprop="genre">arcade</span>, <span itemprop="genre">plate-forme</span></td>
			</tr>
			<tr>
				<th>Développement et graphisme</th>
				<td>Damien Doussaud (<a href="http://namide.com" rel="author">Namide</a>)</td>
			</tr>
			<tr>
				<th>Langage</th>
				<td><a href="http://haxe.org/" target="_blank">Haxe</a></td>
			</tr>
			<tr>
				<th>Testeurs</th>
				<td><span itemprop="contributor">SMX</span>, <span itemprop="contributor">jems Kalagan</span>, <span itemprop="contributor">Greg</span></td>
			</tr>
			<tr>
				<th>Contexte</th>
				<td>Jeu créé en 50 heures<br />(sans compter les interactions avec Facebook).</td>
			</tr>
			<tr>
				<th>Lien</th>
				<td><a href="http://apps.facebook.com/tetrominos-killer/" class="button" itemprop="url" target="_blank">Tetrominos killer</a></td>
			</tr>
		</tbody>
	</table>
	
</body>
</html>

