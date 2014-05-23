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
class Cache
{
	private $_rootDir;
	private $_content;
	
	/**
	 * Directory for write the file(s)
	 * 
	 * @param string $rootDir
	 */
	function __construct( $rootDir = null )
	{
		if ( $rootDir === null )
		{
			$rootDir = _CACHE_DIRECTORY;
		}
		
		if ( substr( $rootDir, -1, 1 ) != '/' ) { $rootDir .= '/'; }
		$this->_rootDir = $rootDir;
		$this->_content = '';
		/*$this->initFile = _CACHE_DIRECTORY.'init/start.php';
		include_once _SYSTEM_DIRECTORY.'helpers/UrlUtil.php';
		$this->pageFile = UrlUtil::getURICacheID();
		$path = explode( "/", $this->pageFile );
		if ( count( explode( ".", array_pop($path) ) ) < 2 )
		{
			$this->pageFile .= '/index.html';
		}*/
    }
	
	/**
	 * Test if the file is already writed
	 * 
	 * @param string $fileName
	 * @return bool
	 */
	public function isWrited( $fileName )
	{
		return file_exists( $this->_rootDir.$fileName );
	}
	
	/**
	 * Echo the file (with the function readfile)
	 */
	public function echoSaved( $fileName )
	{
		$file_extension = strtolower( substr( strrchr( $fileName ,"." ), 1 ) );

		switch ($file_extension)
		{
			case "xml":
				header('Content-Type: application/xml;');
				break;
			//default: $ctype="application/force-download";
		}
		
		readfile( $this->_rootDir.$fileName );
	}
	
	/**
	 * Test if the page is cachable.
	 * A page is cachable if :
	 * - the maximum of cached page not reached
	 * - the propertie "cachable" of the page is true
	 * 
	 * @param Page $page
	 * @return boolean
	 */
	public function isPageCachable( Page &$page )
	{
		if ( self::getNumFilesSaved( $this->_rootDir ) < _MAX_PAGE_CACHE )
		{
			return $page->getCachable();
		}
		return false;
	}
	
	/**
	 * Start to save the communication (echo...)
	 */
	public function startSave()
	{
		if( !file_exists(_CACHE_DIRECTORY) )
		{
			mkdir( _CACHE_DIRECTORY, 0777 );
		}
		
		if( !file_exists(_CACHE_DIRECTORY.'.htaccess') )
		{
			$htaccess = fopen( _CACHE_DIRECTORY.'.htaccess' , "w" );
			$htaccessContent = 'deny from all
<Files ../index.php>
allow from all
</Files>';
			fwrite($htaccess, $htaccessContent);
			fclose($htaccess); 
		}
		ob_start();
	}
	
	/**
	 * Get the content saved
	 * 
	 * @return string
	 */
	public function getSaved()
	{
		return $this->_content;
	}
	
	/**
	 * Set the content
	 * 
	 * @param string $content
	 */
	public function setSaved( $content )
	{
		$this->_content = $content;
	}
	
	/**
	 * Stop to save the communication (echo...)
	 * 
	 * @return string
	 */
	public function stopSave()
	{
		$content = ob_get_contents();
		ob_end_clean();
		
		$this->_content = $content;
		return $content;
	}
	
	/**
	 * 
	 * @param string $newContent
	 * @param string $file
	 */
	/*public function writesCache( &$content, $fileName )
	{
		$page = BuildUtil::getInstance()->getCurrentPage();
		if ( $page->getCachable() )
		{
			if ( count( explode( ".", $file ) ) < 2 )
			{
				if ( substr($file, -1, 1) !== '/' ) $file .= '/';
				$file .= 'index.html';
			}
			
			FileUtil::writeFile($pageContent, $file);
			//$this->writesCacheFile( $pageContent, $file );
		}
	}*/
	
	
	/*public function cacheInit()
	{
		include_once _SYSTEM_DIRECTORY.'core/ElementList.php';
		
		$content = '<?php'."\r\n";
		$content .= LanguageList::getInstance()->getSave().';'."\r\n";
		$content .= 'include_once "'._SYSTEM_DIRECTORY.'core/ElementList.php";'."\r\n";
		$content .= ElementList::getInstance()->getSave().';'."\r\n";
		$content .= PageList::getInstance()->getSave().';'."\r\n";
		
		$this->writesCacheFile( $content, $this->initFile );
	}*/

	/*public function isInitCached()
	{
		if ( !file_exists($this->initFile) ) return false;
		include_once $this->initFile;
		return true;
	}*/

	
	
	/**
	 * Num of files saved
	 * 
	 * @param string $cacheDirectory
	 * @return int
	 */
	public static function getNumFilesSaved( $cacheDirectory )
	{
		$dir = $cacheDirectory;
		if ( substr($dir, -1, 1) === '/' ) { $dir = substr($dir, 0, -1); }
		return self::getNumFilesRecurs($dir);
	}
	
	private static function getNumFilesRecurs( $dir )
	{
		$num = 0;
		if ( !file_exists($dir) ) return $num;
		
		$MyDirectory = opendir($dir);
		while ( $Entry = @readdir($MyDirectory) )
		{
			if ( is_dir($dir.'/'.$Entry) && $Entry != '.' && $Entry != '..' )
			{
				$num += self::getNumFilesRecurs($dir.'/'.$Entry);
			}
			elseif ( substr($Entry, 0, 1) != '.' ) 
			{
				$num++;
			}
		}
		closedir($MyDirectory);
		
		return $num;
	}
}
