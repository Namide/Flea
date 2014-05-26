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
	 * @param string &$content
	 * @param string $fileName
	 */
	public static function writeFile( &$content, $fileName )
	{
		self::writeDirOfFile( $fileName );
		file_put_contents( $fileName, $content, LOCK_EX );
	}
	
	/**
	 * Writes recursively the directories of a files if it doesn't exist
	 * 
	 * @param string $fileName
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
	 * @param string $dir
	 */
	public static function writeDir( $dir )
	{
		$path = explode( '/', $dir );
		
		$dir = '';
		while ( count($path) > 0 )
		{
			$dir .= $path[0].'/';
			if ( !file_exists($dir) )
			{
				mkdir( $dir, 0777 );
			}
			array_shift($path);
		}
		
	}
	
	/**
	 * Size of the directory in octets
	 * 
	 * @param string $directory
	 * @return float
	 */
	public static function getDirSize($directory)
	{
		$size = 0;
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file)
		{
			$size += $file->getSize();
		}
		return $size;
	} 
	
	/**
	 * size of the directory in string with type (bytes, kilo-bytes...)
	 * 
	 * @param string $path
	 * @param bool $color
	 * @return string
	 */
	public static function getFormatedSize( $path, $round = 2 )
	{
		$size = self::getDirSize($path);
		
		//Size must be bytes!
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;

		return round($size,$round).' '.$sizes[$i];
	}
	
	/**
	 * Delete a directory and his content
	 * 
	 * @param string $dir
	 * @return int
	 */
	public static function delDirRecursively( $dir )
	{
		if ( !file_exists($dir) )
		{
			return 0;//echo 'error: No such file or directory "'.$dir.'"';
		}

		$files = array_diff( scandir($dir), array('.','..') );
		foreach ($files as $file)
		{
			if (is_dir($dir.'/'.$file))
			{
				self::delDirRecursively($dir.'/'.$file);
			}
			else
			{
				unlink($dir.'/'.$file);
			}
		}
		return rmdir($dir);
	}
	
	/**
	 * Delete all files and directories and return the number of file deleted
	 * 
	 * @param string $dir
	 * @return int
	 */
	public static function delEmptyDirRecursively( $dir )
	{
		$numChilds = 0;

		if ( !file_exists($dir) )	{ return 0; }
		if ( is_file($dir) )		{ return 1; }

		$files = array_diff( scandir($dir), array( '.', '..', '.DS_Store', 'Thumbs.db' ) );
		foreach ($files as $file)
		{
			if (is_dir($dir.'/'.$file))
			{
				$numChilds += self::delEmptyDirRecursively($dir.'/'.$file);
			}
			else
			{
				$numChilds++;
			}
		}

		if ( $numChilds < 1 )
		{
			rmdir($dir);
		}

		return $numChilds;
	}
	
	/**
	 * Copy the recursivly the directory ($dir2copy) to the directory ($dir_paste)
	 * 
	 * @param string $dir2copy
	 * @param string $dir_paste
	 */
	public static function copyDir( $dir2copy, $dir_paste )
	{
		if ( is_dir($dir2copy) )
		{

			if ( $dh = opendir($dir2copy) )
			{     
				while ( ($file = readdir($dh)) !== false )
				{
					if (!is_dir($dir_paste))
					{
						mkdir ($dir_paste, 0777);
					}
					
					if(is_dir($dir2copy.$file) && $file != '..'  && $file != '.')
					{
						copyDir ( $dir2copy.$file.'/' , $dir_paste.$file.'/' ); 
					}
					elseif( $file != '..' &&
							$file != '.' )
					{
						copy ( $dir2copy.$file , $dir_paste.$file ); 
					}
				}

				closedir($dh);
			}
		}
	}
	
	/**
	 * Copy the directory ($dir2copy) to the directory ($dir_paste) for type.
	 * Ex for copy without php:
	 * <code>copyDirWithoutType( 'original/dir', 'new/dir', array('php', 'php4', 'php5') );</code>
	 * 
	 * @param string $dir2copy
	 * @param string $dir_paste
	 */
	public static function copyDirWithoutType( $dir2copy, $dir_paste, array $extentions = null )
	{
		if ( $extentions === null ) { $extentions = array(); }
		
		if ( is_dir($dir2copy) )
		{

			if ( $dh = opendir($dir2copy) )
			{     
				while ( ($file = readdir($dh)) !== false )
				{
					if (!is_dir($dir_paste))
					{
						mkdir ($dir_paste, 0777);
					}
					
					if(is_dir($dir2copy.$file) && $file != '..'  && $file != '.')
					{
						self::copyDirWithoutPhpFiles ( $dir2copy.$file.'/' , $dir_paste.$file.'/' ); 
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
						if( $ok ) { copy ( $dir2copy.$file , $dir_paste.$file ); }
					}
				}

				closedir($dh);
			}
		}
	}
}
