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
class FileUtil
{
	/**
	 * Writes the content in a file.
	 * If the directory doesn't exist, it's automatically created.
	 * 
	 * @param string &$content		Content of the file
	 * @param string $fileName		Name of the file
	 */
	public static function writeFile( &$content, $fileName )
	{
		self::writeDirOfFile( $fileName );
		file_put_contents( $fileName, $content, LOCK_EX );
	}
	
	/**
	 * Writes recursively the directories of a files if it doesn't exist
	 * 
	 * @param string $fileName		Name of the file
	 */
	public static function writeDirOfFile( $fileName )
	{
		$dir = explode( '/', $fileName );
		array_pop( $dir );
		self::writeDir( implode($dir, '/') );
	}
	
	/**
	 * Writes a directory if it doesn't exist.
	 * It works recursively.
	 * 
	 * @param string $dirName		Directory to write
	 */
	public static function writeDir( $dirName )
	{
		$path = explode( '/', $dirName );
		
		$dirName = '';
		while ( count($path) > 0 )
		{
			$dirName .= $path[0].'/';
			if ( !file_exists($dirName) )
			{
				mkdir( $dirName, 0777 );
			}
			array_shift($path);
		}
		
	}
	
	/**
	 * Size of the directory in octets
	 * 
	 * @param string $dir		Directory to mesure
	 * @return float			Size of the directory in octet
	 */
	public static function getDirSize($dirName)
	{
		$size = 0;
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirName)) as $file)
		{
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
	public static function getFormatedSize( $dirName, $round = 2 )
	{
		$size = self::getDirSize($dirName);
		
		//Size must be bytes!
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;

		return round($size,$round).' '.$sizes[$i];
	}
	
	/**
	 * Delete a directory and his content
	 * 
	 * @param string $dirName		Directory to delete		
	 * @return int					true on success or false on failure			
	 */
	public static function delDirRecursively( $dirName )
	{
		if ( !file_exists($dirName) ) { return 0; }

		$files = array_diff( scandir($dirName), array('.','..') );
		foreach ($files as $file)
		{
			if (is_dir($dirName.'/'.$file))
			{
				self::delDirRecursively($dirName.'/'.$file);
			}
			else
			{
				unlink($dirName.'/'.$file);
			}
		}
		
		return rmdir($dirName);
	}
	
	/**
	 * Delete all files and directories and return the number of file deleted
	 * 
	 * @param string $dirName		Directory to delete
	 * @return int					Number of files deleted (without directories)
	 */
	public static function delEmptyDirRecursively( $dirName )
	{
		$numChilds = 0;

		if ( !file_exists($dirName) )	{ return 0; }
		if ( is_file($dirName) )		{ return 1; }

		$files = array_diff( scandir($dirName), array( '.', '..', '.DS_Store', 'Thumbs.db' ) );
		foreach ($files as $file)
		{
			if (is_dir($dirName.'/'.$file))
			{
				$numChilds += self::delEmptyDirRecursively($dirName.'/'.$file);
			}
			else
			{
				$numChilds++;
			}
		}

		if ( $numChilds < 1 )
		{
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
	public static function copyDir( $dir2copy, $dir2paste )
	{
		if ( is_dir($dir2copy) )
		{

			if ( $dh = opendir($dir2copy) )
			{     
				while ( ($file = readdir($dh)) !== false )
				{
					if ( !is_dir($dir2paste) ) { mkdir ($dir2paste, 0777); }
					
					if( is_dir($dir2copy.$file) && $file != '..'  && $file != '.')
					{
						$this->copyDir ( $dir2copy.$file.'/' , $dir2paste.$file.'/' ); 
					}
					elseif( $file != '..' &&
							$file != '.' )
					{
						copy ( $dir2copy.$file , $dir2paste.$file ); 
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
	public static function copyDirWithoutType( $dir2copy, $dir2paste, array $extentions = null )
	{
		if ( $extentions === null ) { $extentions = array(); }
		
		if ( is_dir($dir2copy) )
		{

			if ( $dh = opendir($dir2copy) )
			{     
				while ( ($file = readdir($dh)) !== false )
				{
					if (!is_dir($dir2paste))
					{
						mkdir ($dir2paste, 0777);
					}
					
					if(is_dir($dir2copy.$file) && $file != '..'  && $file != '.')
					{
						self::copyDirWithoutPhpFiles ( $dir2copy.$file.'/' , $dir2paste.$file.'/' ); 
					}
					elseif( $file != '..' &&
							$file != '.' )
					{
						$ok = true;
						foreach ($extentions as $ext)
						{
							$l = count($ext);
							
							if ( strtolower( substr( strrchr( $file, '.' ), 1 ) ) === $ext ) { $ok = false; }
						}
						if( $ok ) { copy ( $dir2copy.$file , $dir2paste.$file ); }
					}
				}

				closedir($dh);
			}
		}
	}
}
