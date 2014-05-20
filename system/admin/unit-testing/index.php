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

function resultTest( $valid )
{
	if ( $valid ) return '<span class="passed">true</span>';
	return '<span class="error">false</span>';
}
function testString( $a, $b )
{
	return resultTest( $a === $b );
}
function testArray( $a, $b )
{
	return resultTest(count( array_diff($a, $b) ) < 1);
}
function testObject( $a, $b )
{
	return resultTest( $a == $b ); 
}

function writeTest( $method, $resul )
{
	global $class;
	echo '
			<tr>
				<td>'.$class.'</td><td>'.$method.'()</td><td>'.$resul.'</td>
			</tr>';
}
function writeClass()
{
	global $class;
	echo '
			<tr>
				<th colspan="3" align="center">'.$class.'</th>
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
				<th>state</th>
			</tr>
			<?php
	
				include_once 'config.php';
				include_once _SYSTEM_DIRECTORY.'init/import.php';
				
				
				$class = 'LangList';
				writeClass();
				$lang = \Flea\LangList::getInstance();
				$lang->addDefaultLang('uk');
				writeTest( 'addDefaultLang', testString( $lang->hasLang('uk'), true ) );
				writeTest( 'getLangByNavigator', testString($lang->getLangByNavigator(), 'uk') );
				$lang->addLang('fr');
				writeTest( 'getLangByNavigator', testString($lang->getLangByNavigator(), 'fr') );
				writeTest( 'getList', testArray($lang->getList(), array('all', 'uk', 'fr')) );
				
				
				$class = 'Element';
				writeClass();
				$element = new \Flea\Element();
				$element->addTags( array('a:b','bb','yohé\glitch', 2) );
				writeTest( 'addTags', testString( $element->hasTag('yohé\glitch'), true ) );
				$element->removeTag( 'bb' );
				writeTest( 'removeTag', testArray( $element->getTags(), array('a:b','yohé\glitch', 2) ) );
				$element->addTag( 'hum' );
				writeTest( 'addTag', testString( $element->hasTag('yohé\glitch'), true ) );
				writeTest( 'hasTag', testString( $element->hasTag('hum'), true ) );
				$element->addTag( 'hum' );
				writeTest( 'hasTag', testString( $element->hasTag('hum'), true ) );
				writeTest( 'hasTags', testString( $element->hasTags(array('hum', 'yohé\glitch', 2)), true ) );
				writeTest( 'hasTags', testString( $element->hasTags(array('no')), false ) );
				$element->setId('the id!');
				writeTest( 'setId', testString( $element->getId(), 'the id!' ) );
				$element->setLang('uk');
				writeTest( 'setLang', testString( $element->getLang(), 'uk' ) );
				$element->setType('wall');
				writeTest( 'setLang', testString( $element->getType(), 'wall' ) );
				$save = $element->getSave();
				$element2 = new \Flea\Element();
				eval( '$element2 = '.$save.';');
				writeTest( 'getSave', testObject( $element, $element2 ) );
				writeTest( 'create', testObject( $element, $element2 ) );
				$element->removeTags();
				writeTest( 'removeTags', testArray( $element->getTags(), array() ) );
				
				
				$class = 'ElementList';
				writeClass();
				$elementList = Flea\ElementList::getInstance();
				$elementList->addElement($element);
				writeTest( 'addElement', testObject( $elementList->getElements()[0], $element ) );
				$element2->setLang('fr');
				$elementList->addElement($element2);
				writeTest( 'getElementsByLang', testObject( $elementList->getElementsByLang('fr')[0], $element2 ) );
				
			?>
			
		</tbody>
	</table>
	
</body>
</html>

