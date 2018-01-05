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

namespace Flea;

/**
 * Creates and saves all the pages in database
 *
 * @author Namide
 */
class PageListCreate {

	private static $_INSTANCE = null;

	/**
	 * Add all pages of a directory
	 * 
	 * @param type $dir				Root directory
	 */
	/* public function addPagesByDir( $dir )
	  {
	  $listOfPages = $this->addPageByDirRecurs( $dir, '' );
	  $this->db_insertPages($listOfPages);
	  } */

	/**
	 * Add all pages with the CSV
	 * 
	 * @param type $dir				Root directory
	 */
	public function addPagesByCSV($dir) {
		//$listOfPages = $this->addPageByDirRecurs( $dir, '' );


		$csvName = $dir . 'pagelist.csv';
		$csvFile = file($csvName);
		$num = -1;
		$head = [];
		$pageList = [];
		foreach ($csvFile as &$line) {

			if ($num < 0) {
				$head = array_map('trim', str_getcsv($line));
			} else {

				$page = new Page();

				$csvStr = str_getcsv($line);
				foreach ($csvStr as $id => &$row) {
					$row = trim($row);

					switch ($head[$id]) {
						case 'path': $page->setName($row);
							break;
						case 'lang': $page->setLang($row);
							break;
						case 'url': $page->setPageUrl($row);
							break;
						case 'template': $page->setTemplate($row);
							break;
						case 'visible': $page->setVisible((bool) $row);
							break;
						case 'cachable': $page->setCachable((bool) $row);
							break;
						case 'type': $page->setType($row);
							break;
						case 'get.enable': $page->setGetEnabled((bool) $row);
							break;
						case 'get.explicit': $page->setGetExplicit((bool) $row);
							break;
						case 'header': $page->setPhpHeader($row);
							break;
						case 'date': $page->setDate($row);
							break;
                                                case 'format': if (!empty($row)) { $page->setFormat($row); }
							break;

						case 'tags':

							if ($row !== '') {
								$aTemp = array_map('trim', explode(';', $row));
								$page->getTags()->addMultiple($aTemp);
								break;
							}

						case 'metas':

							if ($row !== '') {
								$aTemp = explode(';', $row);
								foreach ($aTemp as &$pair) {
									$aTemp2 = explode(':', $pair);
									if (count($aTemp2) > 1) {
										$page->getMetas()->add(trim($aTemp2[1]), trim($aTemp2[0]));
									} else if (_DEBUG) {
										Debug::getInstance()->addError('The meta "' . $pair . '" must be a pair key:value in the CSV ' . $csvName);
									}
								}
							}

							break;

						case '301':

							if ($row !== '') {
								$aTemp = array_map('trim', explode(';', $row));
								$page->getUrl301()->addMultiple($aTemp);
							}
							break;

						default:

							$aTemp = array_map('trim', explode(':', $head[$id]));
							if ($aTemp[0] === 'meta' && count($aTemp) > 1) {
								$page->getMetas()->add($row, $aTemp[1]);
							} else if (_DEBUG) {
								Debug::getInstance()->addError('label "' . $head[$id] . '" is not correct in the CSV ' . $csvName);
							}
					}
				}

				$build = $dir . $page->getName() . '/' . $page->getLang() . '.php';
				if (file_exists($build))
					$page->setBuildFile($build);

				$pageList[] = $page;
			}
			$num++;
		}


		$this->db_insertPages($pageList);
	}

	/**
	 * Add 301 redirections and other commands
	 * 
	 * @param string $file				File with commands
	 */
	public function commands($file) {
		$listOfPages = array();

		include $file;
		if (isset($redirect301)) {
			foreach ($redirect301 as $oldURL => $newURL) {
				$page = new Page();
				$page->setType(Page::$TYPE_REDIRECT301);
				$page->setPageUrl($oldURL);
				$page->setName('redirect301');
				//$page->setHtmlBody( $newURL );
				$page->setPhpHeader('Location: ' . $newURL);

				$page->setVisible(false);
				$page->setCachable(false);

				array_push($listOfPages, $page);
			}
		}

		$this->db_insertPages($listOfPages);
	}

	private function db_createPagesDB() {
		$tableName = DataBase::objectToTableName(Page::getEmptyPage());

		$db = DataBase::getInstance(_DB_DSN_PAGE);

		$request = SqlQuery::getTemp(SqlQuery::$TYPE_CREATE);
		$request->initCreate($tableName, Page::getEmptyPage()->getObjectVars());
		$db->execute($request);

		$request->clean(SqlQuery::$TYPE_CREATE);
		$request->initCreate($tableName . '_array', array('page_id' => 'TEXT', 'page_prop' => 'TEXT', 'key' => 'TEXT', 'value' => 'TEXT'));
		$db->execute($request);
	}

	private function db_insertPages(array $list) {
		$tableName = DataBase::objectToTableName(Page::getEmptyPage());
		$db = DataBase::getInstance(_DB_DSN_PAGE);

		if (!$db->exist($tableName)) {
			$this->db_createPagesDB();
		}

		$keys1 = array();
		$keys2 = array();
		$values1 = array();
		$values2 = array();
		$length1 = 1;
		$length2 = 1;

		//$req1 = new SqlQuery( SqlQuery::$TYPE_INSERT );
		//$req2 = new SqlQuery( SqlQuery::$TYPE_INSERT );

		foreach ($list as $page) {
			$allVars = $page->getObjectVars();
			$obj1 = array();
			//$db->execute($request);

			foreach ($allVars as $key => $value) {
				if (gettype($value) == 'array') {
					foreach ($value as $key2 => $val2) {
						$obj2 = array();
						$obj2['page_id'] = $allVars['_id'];
						$obj2['page_prop'] = $key;
						$obj2['key'] = $key2;
						$obj2['value'] = $val2;

						if (count($keys2) < 1) {
							$keys2 = array_keys($obj2);
							$length2 = count($keys2);
						} else if ((count($values2) + 1) * $length2 > 999) {
							$req = SqlQuery::getTemp(SqlQuery::$TYPE_MULTI_INSERT);
							$req->initMultiInsertValues($tableName . '_array', $keys2, $values2);
							$db->execute($req);
							$values2 = array();
						}

						$values2[] = array_values($obj2);

						/* $request->clean( SqlQuery::$TYPE_INSERT );
						  $request->initInsertValues( $tableName.'_array', $obj2 );
						  $db->execute($request); */
					}
				} elseif ($value !== null) {
					$obj1[$key] = $value;
				}
			}

			if (count($keys1) < 1) {
				$keys1 = array_keys($obj1);
				$length1 = count($keys1);
			} else if ((count($values1) + 1) * $length1 > 999) {
				$req = SqlQuery::getTemp(SqlQuery::$TYPE_MULTI_INSERT);
				$req->initMultiInsertValues($tableName, $keys1, $values1);
				$db->execute($req);
				$values1 = array();
			}
			$values1[] = array_values($obj1);
		}

		if (count($values1) > 0) {
			$req = SqlQuery::getTemp(SqlQuery::$TYPE_MULTI_INSERT);
			$req->initMultiInsertValues($tableName, $keys1, $values1);
			$db->execute($req);
		}

		if (count($values2) > 0) {
			$req->clean(SqlQuery::$TYPE_MULTI_INSERT);
			$req->initMultiInsertValues($tableName . '_array', $keys2, $values2);
			$db->execute($req);
		}
	}

	/**
	 * Add all pages of a directory recursivly
	 * 
	 * @param type $dir				Root directory
	 * @param type $fileDirRel		Relative directory (for the recursivity)	
	 * @return array				List of the pages added
	 */
	/* private function addPageByDirRecurs( $dir, $fileDirRel = '' )
	  {
	  $list = array();

	  if ( !file_exists($dir) ) { return $list; }

	  $dirOpen = opendir($dir);
	  while($file = @readdir($dirOpen))
	  {
	  if( $file != "." &&
	  $file != ".." &&
	  is_dir($dir.'/'.$file) )
	  {
	  $list1 = $this->addPageByDirRecurs( $dir.'/'.$file.'/', (($fileDirRel != '')?$fileDirRel.'/':'').$file );
	  $list2 = $this->createPage( (($fileDirRel != '')?$fileDirRel.'/':'').$file );


	  $list = array_merge($list, $list1, $list2);
	  }
	  }
	  closedir($dirOpen);

	  return $list;
	  } */

	/**
	 * Add all the pages (by languages) in the folder
	 * 
	 * @param string $folderName	Name of the folder thats contain the page
	 * @return array				List of the pages generated (differents languages)
	 */
	/* private function createPage( $folderName )
	  {
	  $pages = array();

	  $langList = LangList::getInstance();
	  $langs = $langList->getList();

	  foreach ( $langs as $lang )
	  {
	  $filename = _CONTENT_DIRECTORY.$folderName.'/'.$lang.'-init.php';

	  if( file_exists ( $filename ) )
	  {
	  $page = new Page();
	  $page->setLang( $lang );
	  $page->setName( $folderName );

	  $this->initPage($page, $filename);


	  $buildFile = _CONTENT_DIRECTORY.$folderName.'/'.$lang.'-build.php';
	  if( file_exists ( $buildFile ) )
	  {
	  $page->setBuildFile($buildFile);
	  }

	  array_push( $pages, $page );
	  }

	  }

	  return $pages;
	  } */

	/* private function initPage( Page &$page, $filename )
	  {
	  include $filename;

	  if ( _DEBUG && !isset($url) )
	  {
	  Debug::getInstance()->addError( 'The initialisation of a page must to have an URL' );
	  }

	  if ( isset($type) )
	  {
	  $page->setType($type);
	  if ( $type == Page::$TYPE_ERROR404 )
	  {
	  $this->_error404 = $page->getName();
	  $page->setVisible( false );
	  $page->setCachable( false );
	  $page->setPhpHeader( 'HTTP/1.0 404 Not Found' );
	  }
	  }

	  if ( isset($url) )				{ $page->setPageUrl($url) ; }
	  if ( isset($addUrl) )			{ $page->getAdditionalUrls()->add($addUrl); }
	  if ( isset($addUrls) )			{ $page->getAdditionalUrls()->addMultiple($addUrls); }
	  if ( isset($template) )			{ $page->setTemplate($template) ; }

	  if ( isset($visible) )			{ $page->setVisible($visible) ; }
	  if ( isset($cachable) )			{ $page->setCachable($cachable) ; }

	  if ( isset($getEnabled) )		{ $page->setGetEnabled($getEnabled) ; }
	  if ( isset($getExplicit) )		{ $page->setGetExplicit($getExplicit) ; }
	  if ( isset($date) )				{ $page->setDate($date) ; }

	  if ( isset($htmlBody) )			{ $page->setHtmlBody($htmlBody) ; }
	  if ( isset($htmlDescription) )	{ $page->setHtmlDescription($htmlDescription) ; }
	  if ( isset($htmlHeader) )		{ $page->setHtmlHeader($htmlHeader) ; }
	  if ( isset($htmlTitle) )		{ $page->setHtmlTitle($htmlTitle) ; }
	  if ( isset($cover) )			{ $page->setCover($cover) ; }

	  if ( isset($phpHeader) )		{ $page->setPhpHeader($phpHeader) ; }
	  if ( isset($format) )			{ $page->setFormat($format) ; }

	  if ( isset($tags) )				{ $page->getTags()->addMultiple($tags) ; }
	  if ( isset($tag) )				{ $page->getTags()->add($tag) ; }
	  if ( isset($contents) )			{ $page->getContents()->addMultiple($contents); }

	  return $page;
	  } */

	final private function __construct() {
		
	}

	final private function __clone() {
		if (_DEBUG) {
			Debug::getInstance()->addError('You can\'t clone a singleton');
		}
	}

	/**
	 * Instance of the PageList
	 * 
	 * @return static	Instance of the object PageListCreate
	 */
	final public static function getInstance() {
		if (PageListCreate::$_INSTANCE === null) {
			PageListCreate::$_INSTANCE = new PageListCreate();
		}
		return PageListCreate::$_INSTANCE;
	}

}
