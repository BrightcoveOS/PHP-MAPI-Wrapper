About
=====

This project provides a starting point for integrating the Brightcove Media
API into your application. It provides simple ways to interact with the
API, as well as a long list of helper functions.

Compatibility Notice
====================

Please note that the PHP MAPI Wrapper v2.0 is **not** compatible with any
previous versions (when it was known as "Echove"). The class name has been
changed, numerous functions have been re-named, and methods have been
updated to take advantage of Brightcove API changes.

If you need assistance in determining what changes have been made, please
send an e-mail to opensource@brightcove.com with your request.

Requirements
============

PHP version 5.2 or greater, or you must have the JavaScript Object Notation
(JSON) PECL package. For more information on the JSON PECL package, please
visit the [PHP JSON](http://www.php.net/json) package website.

Using Cache Extension
=====================

The PHP MAPI Wrapper includes a caching extension. To use this feature,
include the file on your page along with the core PHP MAPI Wrapper file.

	require('bc-mapi.php');
	require('bc-mapi-cache.php');

Then, after instantiating the core class, you can instantiate the caching
extension.

	// Using flat files
	$bc = new BCMAPI(API_READ_TOKEN, API_WRITE_TOKEN);
	$bc_cache = new BCMAPICache('file', 600, '/var/www/myWebSite/cache/', '.cache');

	// Using Memcached
	$bc = new BCMAPI(API_READ_TOKEN, API_WRITE_TOKEN);
	$bc_cache = new BCMAPICache('memcached', 600, 'localhost', NULL, 11211);

The parameters for the constructor are:

*	[string] The type of caching method to use, either 'file' or 'memcached'
*	[int] How many seconds until cache files are considered cold
*	[string] The absolute path of the cache directory (file) or host (memcached)
*	[string] The file extension for cache items (file only)
*	[int] The port to use (Memcached only)