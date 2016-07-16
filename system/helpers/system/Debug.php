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
 * Emmiting debuging messages, warnings and errors
 *
 * @author Namide
 */
class Debug {

	private static $_INSTANCE;
	private $_errorList;
	private $_timer;
	private $_totalTime;
	private $_timerList;
	private $_errorBackNum = 10;
	private $_errorJsAlert = false;
	private $_errorEcho = true;
	private $_errorFile = true;
	private $_errorFileName = 'errors.log';

	/**
	 * Number of functions called before the error.
	 * Default is 10.
	 * 
	 * @param int $num		Maximum of caller saved
	 */
	public function setErrorBackNum($num) {
		$this->_errorBackNum = $num;
	}

	/**
	 * Number of functions called before the error.
	 * 
	 * @return int			Maximum of caller saved
	 */
	public function getErrorBackNum() {
		return $this->_errorBackNum;
	}

	/**
	 * Force the JavaScript alert() to trace errors.
	 * 
	 * @param bool $errorJsAlert		Force it
	 */
	public function setErrorJsAlert($errorJsAlert) {
		$this->_errorJsAlert = $errorJsAlert;
	}

	/**
	 * Errors traced with the JavaScript function: alert()
	 * 
	 * @return type						Is it
	 */
	public function getErrorJsAlert() {
		return $this->_errorJsAlert;
	}

	/**
	 * Force the php echo to trace errors in the page.
	 * 
	 * @param bool $errorEcho		Force it
	 */
	public function setErrorEcho($errorEcho) {
		$this->_errorEcho = $errorEcho;
	}

	/**
	 * Errors traced with the php function: echo
	 * 
	 * @return type						Is it
	 */
	public function getErrorEcho() {
		return $this->_errorEcho;
	}

	private $_errorJsLog = true;

	/**
	 * Force the JavaScript console.log() to output errors.
	 * 
	 * @param bool $errorEcho		Force it
	 */
	public function setErrorJsLog($errorJsLog) {
		$this->_errorJsLog = $errorJsLog;
	}

	/**
	 * Errors traced with the JavaScript function: console.log()
	 * 
	 * @return type						Is it
	 */
	public function getErrorJsLog() {
		return $this->_errorJsLog;
	}

	/**
	 * Save an error message
	 * 
	 * @param string $msg		Error message (information about the error)
	 */
	public function addError($msg) {
		$error = $msg . "\n" . $this->getDebugBacktrace(1, $this->_errorBackNum);
		array_push($this->_errorList, $error);
	}

	/**
	 * Dispatch all errors messages.
	 * Echo in the HTML (PHP), in alert() (JavaScript) or console.log() (JavaScript)
	 */
	public function dispatchErrors() {
		if (count($this->_errorList) > 0) {
			if ($this->_errorFile) {
				include_once _SYSTEM_DIRECTORY . 'helpers/miscellaneous/FileUtil.php';

				if (!file_exists(_CACHE_DIRECTORY)) {
					FileUtil::writeProtectedDir(_CACHE_DIRECTORY);
				}

				$fileName = _CACHE_DIRECTORY . $this->_errorFileName;
				$file = fopen($fileName, 'a+');

				$page = General::getInstance()->getCurrentPage();
				fputs($file, "\r\n");
				fputs($file, "\r\n");
				fputs($file, "url: " . $page->getPageUrl());
				fputs($file, "\r\n");
				fputs($file, "id: " . $page->getId());
				fputs($file, "\r\n");

				foreach ($this->_errorList as $errorStr) {
					//foreach (explode('\n', $errorStr) as $single) {
					fputs($file, $errorStr);
					fputs($file, "\r\n");
					//}
				}
				//$datas .= implode( "\n", $this->_errorList );


				fclose($file);
			}

			echo '<script>/*Errors*/';
			if ($this->_errorJsAlert) {
				echo 'alert("' . $this->delDoubleQuotes(implode('\n', $this->_errorList)) . '");';
			}
			if ($this->_errorJsLog) {
				echo 'console.log("' . $this->delDoubleQuotes(implode('\n', $this->_errorList)) . '");';
			}
			echo '</script>';

			if ($this->_errorEcho) {
				echo $this->getErrorsHtml();
			}
		}
	}

	/**
	 * Get the list of errors with html tags.
	 * 
	 * @return string
	 */
	public function getErrorsHtml() {
		return $this->addHtmlReturns(implode('<br>', $this->_errorList));
	}

	/**
	 * Add a time marker
	 * 
	 * @param type $msg		Description of the marker
	 */
	public function addTimeMark($msg) {
		$dt = microtime(true) - $this->_timer;
		$this->_timer += $dt;
		$this->_totalTime += $dt;
		$this->_timerList[] = array('dt' => $dt, 'msg' => $msg);
	}

	/**
	 * Get all the markers and the total time
	 * 
	 * @param string $msg		Message for the total time
	 * @return string			All datas
	 */
	public function getTimes($msg) {
		$this->_timerList[] = array('dt' => $this->_totalTime, 'msg' => 'total time: ' . $msg);

		$output = '';
		foreach ($this->_timerList as $value) {
			$output .= $value['msg'] . ': ' . number_format($value['dt'], 3) . 's' . "\n";
		}
		return $output;
	}

	private function addHtmlReturns($str) {
		return str_replace('\n', '<br>', $str);
	}

	private function delDoubleQuotes($str) {
		$str = str_replace(array('"', '\\'), array('\"', '\\\\'), $str);
		$str = str_replace(array('\\\n'), array('\n'), $str);
		return $str;
	}

	/**
	 * Clear all datas without total timer.
	 */
	public function clear() {
		$this->_errorList = array();
		$this->_timerList = array();
	}

	final private function __construct() {
		$this->clear();
		$this->_timer = microtime(true);
		$this->_totalTime = 0;
	}

	/**
	 * Unclonable
	 */
	final private function __clone() {
		if (_DEBUG) {
			Debug::getInstance()->addError('You can\'t clone a singleton');
		}
	}

	/**
	 * Instance of the Debug object
	 * 
	 * @return self		Instance of the object Debug
	 */
	final public static function getInstance() {
		if (!isset(self::$_INSTANCE)) {
			self::$_INSTANCE = new self();
		}

		return self::$_INSTANCE;
	}

	/**
	 * Get the path of errors
	 * 
	 * @param int $traces_to_ignore		First trace (1 if you would escape the first function)
	 * @param int $max_trace			Maximum of recover functions
	 * @return string					Resume of path error
	 */
	protected function getDebugBacktrace($traces_to_ignore = 1, $max_trace = 1) {
		$traces = debug_backtrace();
		$ret = array();
		foreach ($traces as $i => $call) {
			if ($i < $traces_to_ignore || $i > $traces_to_ignore + $max_trace - 1) {
				continue;
			}

			//$str .= $object.$call['function'].'('.implode(', ', $call['args']).') ';
			if (isset($call['file']) && isset($call['line'])) {
				$str = '';
				if ($max_trace > 1) {
					$str .= '	#' . str_pad($i - $traces_to_ignore, 3, ' ');
				}

				$str .= '	' . $call['file'] . ':' . $call['line'];
				$ret[] = $str;
			}
		}
		return implode("\n", $ret);
	}

}
