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
 * Used for write and read files
 *
 * @author Namide
 */
class Cache {

	//private $_rootDir;
	private $_content;
	private $_db;
	private $_tableName;

	//private $_dataUtil;

	/**
	 * Directory for write the file(s)
	 * 
	 * @param string $rootDir	Root directory
	 */
	function __construct($dbDsnCache/* $rootDir = null */, $tableName = 'pages_cached') {
		$this->_db = DataBase::getInstance($dbDsnCache);
		$this->_tableName = $tableName;
		$this->_content = '';

		if (!$this->_db->exist($tableName)) {
			//$sql = 'CREATE TABLE `'.$tableName.'` ( url TEXT, header TEXT, content TEXT );';
			$sql = SqlQuery::getTemp(SqlQuery::$TYPE_CREATE);
			$tables = array('url' => 'TEXT', 'header' => 'TEXT', 'content' => 'TEXT', 'gzip' => 'TEXT');
			$sql->initCreate($tableName, $tables);
			$this->_db->execute($sql);

			$fileTemplate = _SYSTEM_DIRECTORY . 'helpers/system/autoCacheTemplate.php';
			$fileNew = 'cache.php';
			copy($fileTemplate, $fileNew);
		}
	}

	/**
	 * Test if the file is already writed
	 * 
	 * @param string $fileName	Name of the file
	 * @return bool				true if the file is writted, false if error has occured
	 */
	public function isWrited($strUrl) {
		$query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$query->initCount($this->_tableName, array('url' => $strUrl), array('LIKE'));
		return $this->_db->count($query) > 0;
	}

	/**
	 * Write the file in the directory
	 * 
	 * @param type $fileName	Name of the file
	 * @param type $content		Content of the file (optional)
	 */
	public function writeCache($url, $header = '', &$content = null) {
		if ($content === null) {
			$content = $this->_content;
		}

		$obj = array();
		$obj['url'] = $url;
		$obj['header'] = $header;
		$obj['content'] = &$content;
		$obj['gzip'] = gzencode($content, 9);

		$query = SqlQuery::getTemp(SqlQuery::$TYPE_INSERT);
		$query->initInsertValues($this->_tableName, $obj);
		$this->_db->execute($query);
	}

	/**
	 * Echo the file (with the function readfile)
	 */
	public function echoSaved($url) {
		$query = SqlQuery::getTemp(SqlQuery::$TYPE_SELECT);
		$where = array('url' => $url);
		$sign = array('LIKE');
		$query->initSelect('*', '`' . $this->_tableName . '`', $where, $sign);
		$row = $this->_db->fetchAll($query);
		if ($row > 0) {
			if ($row[0]['header'] != '') {
				header($row[0]['header']);
			}

			// GZIP
			if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
				$gzip = $row[0]['gzip'];
				header('Content-Encoding: gzip');
				header('Content-Length: ' . strlen($gzip));
				echo $gzip;
			} else {
				echo $row[0]['content'];
			}
		} elseif (_DEBUG) {
			Debug::getInstance()->addError('The URL ' . $url . ' don\'t exist in the cache data base');
		}
	}

	/**
	 * Test if the page is cachable.
	 * A page is cachable if :
	 * - the maximum of cached page not reached
	 * - the propertie "cachable" of the page is true
	 * 
	 * @param Page $page	Page to test
	 * @return boolean		true if the page is cachable, false if the page is uncachable
	 */
	public function isPageCachable(Page &$page) {
		if ($this->getNumFilesSaved() < _MAX_PAGE_CACHE) {
			return $page->getCachable();
		}
		return false;
	}

	/**
	 * Start to save the communication (echo...)
	 */
	public function startSave() {
		ob_start();
	}

	/**
	 * Get the content saved
	 * 
	 * @return string		Content saved
	 */
	public function getContent() {
		return $this->_content;
	}

	/**
	 * Set the content
	 * 
	 * @param string $content		Change the content
	 */
	public function setContent($content) {
		$this->_content = $content;
	}

	/**
	 * Stop to save the communication (echo...)
	 * 
	 * @return string		Content saved
	 */
	public function stopSave() {
		$content = ob_get_contents();
		ob_end_clean();

		$this->_content = $content;
		return $content;
	}

	/**
	 * Num of files saved
	 * 
	 * @param string $cacheDirectory		Directory of the files
	 * @return int							Number of files cached
	 */
	public function getNumFilesSaved() {
		$query = SqlQuery::getTemp();
		$query->initCount($this->_tableName);
		return $this->_db->count($query);
	}

}
