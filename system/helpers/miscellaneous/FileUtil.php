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
 * Utils to write directories or files
 *
 * @author Namide
 */
class FileUtil {

	/**
	 * Writes the content in a file.
	 * If the directory doesn't exist, it's automatically created.
	 * 
	 * @param string &$content		Content of the file
	 * @param string $fileName		Name of the file
	 */
	public static function writeFile(&$content, $fileName) {
		self::writeDirOfFile($fileName);
		file_put_contents($fileName, $content, LOCK_EX);
	}

	/**
	 * Writes recursively the directories of a files if it doesn't exist
	 * 
	 * @param string $fileName		Name of the file
	 */
	public static function writeDirOfFile($fileName) {
		$dir = explode('/', $fileName);
		array_pop($dir);
		self::writeDir(implode($dir, '/'));
	}

	/**
	 * Writes a directory if it doesn't exist.
	 * It works recursively.
	 * 
	 * @param string $dirName		Directory to write
	 */
	public static function writeDir($dirName) {
		$path = explode('/', $dirName);

		$dirName = '';
		while (count($path) > 0) {
			$dirName .= $path[0] . '/';
			if (!file_exists($dirName)) {
				mkdir($dirName, 0777);
			}
			array_shift($path);
		}
	}

	/**
	 * Return the content of your CSS file with absolute URL for your pictures
	 * 
	 * @param string $cssFile	Path of your CSS file	
	 * @return string			Content of your CSS
	 */
	public static function getCssContentWithAbsUrl($cssFile) {
		$content = file_get_contents($cssFile);
		$regex = '/url\(([\'"]?.[^\'"]*\.(png|jpg|jpeg|gif)[\'"]?)\)/i';
		preg_match_all($regex, $content, $links);
		for ($i = 0; $i < count($links[1]); $i++) {
			$newUrlCss = self::getAbsPathFromFile($cssFile, $links[1][$i]);
			$content = str_replace($links[1][$i], $newUrlCss, $content);
		}
		return $content;
	}

	/**
	 * Use $rootPath to create an absolute URL for $relPath.
	 * Can be use to change an URL in a CSS file.
	 * 
	 * @param string $rootPath		Absolute URL of the container file like "http://domain.com/css/style.css"
	 * @param string $relPath			Relative URL of the file like "../img/picture.jpg"
	 * @return string					Absolute URL of the file like "http://domain.com/img/picture.jpg"
	 */
	public static function getAbsPathFromFile($rootPath, $relPath) {
		$root = explode('/', $rootPath);
		$rel = explode('/', $relPath);

		//if ( is_file($rootPath) )
		array_pop($root);

		while ($rel[0] == '..') {
			array_pop($root);
			array_shift($rel);
		}
		$final = array_merge($root, $rel);

		return implode('/', $final);
	}

	/**
	 * Writes a directory with .htaccess (deny from all) if it doesn't exist.
	 * It works recursively.
	 * 
	 * @param string $dirName		Directory of the .htaccess
	 */
	public static function writeProtectedDir($dirName) {
		FileUtil::writeDir($dirName);

		if (!file_exists($dirName . '.htaccess')) {
			$htaccess = fopen($dirName . '.htaccess', "w");
			$htaccessContent = 'deny from all';
			fwrite($htaccess, $htaccessContent);
			fclose($htaccess);
		}
	}

	/**
	 * Size of the directory in octets
	 * 
	 * @param string $dir		Directory to mesure
	 * @return float			Size of the directory in octet
	 */
	public static function getDirSize($dirName) {
		$size = 0;
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirName)) as $file) {
			$size += $file->getSize();
		}
		return $size;
	}

	/**
	 * Size of the directory in string with type (bytes, kilo-bytes...)
	 * 
	 * @param string $dirName				Directory to mesure
	 * @param int $round				Number to float
	 * @return string					Formated size of the directory
	 */
	public static function getFormatedSize($dirName, $round = 2) {
		$size = self::getDirSize($dirName);

		//Size must be bytes!
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		for ($i = 0; $size > 1024 && $i < count($sizes) - 1; $i++)
			$size /= 1024;

		return round($size, $round) . ' ' . $sizes[$i];
	}

	/**
	 * Delete a directory and his content
	 * 
	 * @param string $dirName		Directory to delete		
	 * @return int					true on success or false on failure			
	 */
	public static function delDirRecursively($dirName) {
		if (!file_exists($dirName)) {
			return 0;
		}

		$files = array_diff(scandir($dirName), array('.', '..'));
		foreach ($files as $file) {
			if (is_dir($dirName . '/' . $file)) {
				self::delDirRecursively($dirName . '/' . $file);
			} else {
				unlink($dirName . '/' . $file);
			}
		}

		return rmdir($dirName);
	}

	/**
	 * Delete file
	 * 
	 * @param type $file				Path of file to delete
	 * @param type $recursEmptyDir		Delete containers directories empty
	 * @return boolean					File successfull deleted
	 */
	public static function delFile($file, $recursEmptyDir = false) {
		if (!file_exists($file)) {
			return false;
		}
		unlink($file);

		if ($recursEmptyDir) {
			$dir = $file;
			do {
				$dir = explode('/', $dir);
				if (count($dir) < 1)
					return true;
				array_pop($dir);
				$dir = implode('/', $dir);
				self::delEmptyDirRecursively($dir);
			}
			while (self::isEmpty($dir));
		}

		return true;
	}

	/**
	 * Is the directory empty
	 * 
	 * @param type $dir			Directtory to test
	 * @return int				Is deleted	
	 */
	public static function isEmpty($dir) {
		if (!file_exists($dir)) {
			return false;
		}
		if (is_file($dir)) {
			return true;
		}

		$files = array_diff(scandir($dir), array('.', '..', '.DS_Store', 'Thumbs.db'));
		return count($files) < 1;
	}

	/**
	 * Delete all files and directories and return the number of file deleted
	 * 
	 * @param string $dirName		Directory to delete
	 * @return int					Number of files deleted (without directories)
	 */
	public static function delEmptyDirRecursively($dirName) {
		$numChilds = 0;

		if (!file_exists($dirName)) {
			return 0;
		}
		if (is_file($dirName)) {
			return 1;
		}

		$files = array_diff(scandir($dirName), array('.', '..', '.DS_Store', 'Thumbs.db'));
		foreach ($files as $file) {
			if (is_dir($dirName . '/' . $file)) {
				$numChilds += self::delEmptyDirRecursively($dirName . '/' . $file);
			} else {
				$numChilds++;
			}
		}

		if ($numChilds < 1) {
			rmdir($dirName);
		}

		return $numChilds;
	}

	/**
	 * Copy the recursivly the directory ($dir2copy) to the directory ($dir2paste)
	 * 
	 * @param string $dir2copy		Original directory
	 * @param string $dir2paste		New directory
	 */
	public static function copyDir($dir2copy, $dir2paste) {
		if (is_dir($dir2copy)) {
			if ($dh = opendir($dir2copy)) {
				while (($file = readdir($dh)) !== false) {
					if (!is_dir($dir2paste)) {
						mkdir($dir2paste, 0777);
					}

					if (is_dir($dir2copy . $file) && $file != '..' && $file != '.') {
						$this->copyDir($dir2copy . $file . '/', $dir2paste . $file . '/');
					} elseif ($file != '..' &&
							$file != '.') {
						copy($dir2copy . $file, $dir2paste . $file);
					}
				}

				closedir($dh);
			}
		}
	}

	/**
	 * Copy the directory ($dir2copy) to the directory ($dir2paste) for type.
	 * Ex for copy without php:
	 * copyDirWithoutType( 'original/dir', 'new/dir', array('php', 'php4', 'php5') );
	 * 
	 * @param string $dir2copy		Original directory
	 * @param string $dir2paste		New directory
	 * @param array $extentions		Exceptions list
	 */
	public static function copyDirWithoutType($dir2copy, $dir2paste, array $extentions = null) {
		if ($extentions === null) {
			$extentions = array();
		}

		if (is_dir($dir2copy)) {

			if ($dh = opendir($dir2copy)) {
				while (($file = readdir($dh)) !== false) {
					if (!is_dir($dir2paste)) {
						mkdir($dir2paste, 0777);
					}

					if (is_dir($dir2copy . $file) && $file != '..' && $file != '.') {
						self::copyDirWithoutPhpFiles($dir2copy . $file . '/', $dir2paste . $file . '/');
					} elseif ($file != '..' &&
							$file != '.') {
						$ok = true;
						foreach ($extentions as $ext) {
							$l = count($ext);

							if (strtolower(substr(strrchr($file, '.'), 1)) === $ext) {
								$ok = false;
							}
						}
						if ($ok) {
							copy($dir2copy . $file, $dir2paste . $file);
						}
					}
				}

				closedir($dh);
			}
		}
	}

}
