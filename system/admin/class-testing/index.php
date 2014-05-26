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

function resultTest( $valid )
{
	if ( $valid ) { return '<span class="passed">true</span>'; }
	return '<span class="error">false</span>';
}
function testString( $a, $b )
{
	return resultTest( $a === $b );
}
function testArray( $a, $b )
{
	return resultTest( sameArray( $a, $b ) );
}
function sameArray( $a, $b )
{
	foreach($a as $key => $value)
    {
        if(is_array($value))
        {
              if( !isset($b[$key]) )
              {
                  return false;
              }
              elseif(!is_array($b[$key]))
              {
                  return false;
              }
              else
              {
                  return sameArray($value, $b[$key]);
              }
          }
          elseif(!isset($b[$key]) || $b[$key] != $value)
          {
              return false;
          }
    }
    return true; 
}
function testObject( $a, $b )
{
	return resultTest( $a == $b ); 
}

function writeTest( $method, $resul )
{
	global $class;
	echo '<tr><td>'.$class.'</td><td>'.$method.'()</td><td>'.$resul.'</td>', writeTime() , '</tr>' , "\n";
}

function writeTime()
{
	global $time;
	if ( $time == null ) $time = microtime(true);
	
	$dt = microtime(true) - $time;
	$color = ($dt > 0.001);
	$totalTime = number_format( $dt, 3);
	$time = microtime(true);
	
	echo '<td><em ',( ($color)?'class="error"':'') ,'>'.$totalTime.'s</em></td>', "\n";
}

function writeClass( $className, $t = true )
{
	global $class;
	$class = $className;
	echo '<tr><th colspan="4" align="center">'.$className.'</th></tr>', "\n";
}

?>

<table>
	<tbody>
		<tr>
			<th>Class</th>
			<th>method</th>
			<th>state</th>
			<th>time</th>
		</tr>
		<?php

			writeClass('include', false);


			include_once 'config.php';
			include_once _SYSTEM_DIRECTORY.'init/import.php';
			include_once _SYSTEM_DIRECTORY.'helpers/miscellaneous/FileUtil.php';
			include_once _SYSTEM_DIRECTORY.'helpers/system/DataUtil.php';
			include_once _SYSTEM_DIRECTORY.'helpers/system/Cache.php';
			
			writeClass('LangList');
			$lang = \Flea\LangList::getInstance();
			$lang->addDefault('uk');
			writeTest( 'addDefaultLang', testString( $lang->has('uk'), true ) );
			writeTest( 'getLangByNavigator', testString($lang->getLangByNavigator(), 'uk') );
			$lang->add('fr');
			writeTest( 'getLangByNavigator', testString($lang->getLangByNavigator(), 'fr') );
			writeTest( 'getList', testArray($lang->getList(), array('all', 'uk', 'fr')) );

			writeClass('Element');
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
			$element->setName('the id!');
			writeTest( 'setId', testString( $element->getName(), 'the id!' ) );
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


			writeClass('ElementList');
			$elementList = \Flea\ElementList::getInstance();
			$elementList->add($element);
			writeTest( 'add', testObject( $elementList->getAll()[0], $element ) );
			$element2->setLang('fr');
			$element2->setName('whohoo-as\' /');
			$elementList->add($element2);
			writeTest( 'getElementsByLang', testObject( $elementList->getByLang('fr')[0], $element2 ) );
			writeTest( 'getById', testObject( $elementList->getByName('whohoo-as\' /', 'fr'), $element2 ) );

			
			writeClass('DataUtil');
			$datas = new \Flea\DataUtil(_TEMP_DIRECTORY);
			$temp = 'yohé!';
			$key = 'test-01';
			if ( $datas->has($key) ) $datas->delete ($key);
			$datas->add( 'test-01', $temp );
			writeTest( 'add', testString( $datas->has($key), true ) );
			writeTest( 'has', testString( $datas->has($key), true ) );
			if ( $datas->has($key) ) $datas->delete ($key);
			writeTest( 'delete', testString( $datas->has($key), false ) );
			$temp = array( 'a'=>array(5,1,2), 'f'=>3 );
			$datas->addJson($key, $temp);
			writeTest( 'addJson', testString( $datas->has($key), true ) );
			$temp2 = $datas->getJson($key, $temp);
			writeTest( 'getJson', testString( $temp, $temp2 ) );
			$temp2 = $datas->delete($key);
			
			writeClass('-');
			
		?>

	</tbody>
</table>
