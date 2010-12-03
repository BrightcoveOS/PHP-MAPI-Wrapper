<?php

/**
 * BC PHP MAPI CACHING LAYER 1.0.0 (12 OCTOBER 2010)
 * A caching layer for the Brightcove PHP Media API Wrapper
 *
 * REFERENCES:
 *	 Website: http://opensource.brightcove.com
 *	 Source: http://github.com/brightcoveos/
 *
 * AUTHORS:
 *	 Matthew Congrove, Professional Services Engineer, Brightcove
 *	 Brian Franklin, Professional Services Engineer, Brightcove
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
 * THE USE OR OTHER DEALINGS IN THE SOFTWARE. YOU AGREE TO RETAIN IN THE SOFTWARE AND ANY
 * MODIFICATIONS TO THE SOFTWARE THE REFERENCE URL INFORMATION, AUTHOR ATTRIBUTION AND
 * CONTRIBUTOR ATTRIBUTION PROVIDED HEREIN.
 */

class BCMAPICache
{
	public static $location = NULL;
	public static $time = 0;
	public static $extension = NULL;

	/**
	 * The constructor for the BCMAPICache class.
	 * @access Public
	 * @since 1.0
	 * @param string [$location] The absolute path of the cache directory
	 * @param string [$time] How many seconds until cache files are considered cold
	 * @param string [$extension] The file extension for cache items
	 */
	public function __construct($location, $time = 600, $extension = '.c')
	{
		self::$location = $location;
		self::$time = $time;
		self::$extension = $extension;
	}

	/**
	 * Checks for valid cached data.
	 * @access Public
	 * @since 1.0
	 * @param string [$key] The cache file key
	 * @return mixed The cached data if valid, otherwise FALSE
	 */
	public function check($key)
	{
		$file = self::$location . md5($key) . self::$extension;

		if(file_exists($file) && is_readable($file))
		{
			if((time() - filemtime($file)) <= self::$time)
			{
				return file_get_contents($file);
			}
		}

		return FALSE;
	}

	/**
	 * Creates a cache of data.
	 * @access Public
	 * @since 1.0.0
	 * @param string [$key] The cache file key
	 * @param mixed [$data] The data to cache
	 */
	public function set($key, $data)
	{
		$file = self::$location . md5($key) . self::$extension;

		if(is_writable(self::$location))
		{
			$handle = fopen($file, 'w');
			fwrite($handle, json_encode($data));
			fclose($handle);
		}
	}
}

?>