This library uses Brightcove’s legacy Media API (MAPI). A new library has been released which uses Brightcove’s new CMSAPI, DIAPI, and PMAPI: https://github.com/brightcove/PHP-API-Wrapper

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

* * *

Examples
========

Instantiation
-------------

This example shows how to instantiate, or start, the BCMAPI PHP class. The first token, which is for the Read API, is required. The second token is for the Write API and is optional.
	
	// Include the BCMAPI SDK
	require('bc-mapi.php');
	
	// Instantiate the class, passing it our Brightcove API tokens (read, then write)
	$bc = new BCMAPI(
		'READ_API_TOKEN',
		'WRITE_API_TOKEN'
	);
	
	// You may optionally include the caching extension provided with BCMAPI...
	require('bc-mapi-cache.php');
	
	// Using flat files
	$bc_cache = new BCMAPICache('file', 600, '/var/www/myWebSite/cache/', '.cache');
	
	// Using Memcached
	$bc_cache = new BCMAPICache('memcached', 600, 'localhost', NULL, 11211);


Properties
----------
This example shows how to set and retrieve some of the BCMAPI properties that can be used for debugging and additional settings.

	// Turn on HTTPS mode
	$bc->__set('secure', TRUE);
	
	// Make our API call
	$videos = $bc->find('allVideos');
	
	// Determine how many possible results there are
	echo 'Total Videos: ' . $bc->total_count . '&lt;br /&gt;';
	
	// Make our API call
	$videos = $bc->findAll();
	
	// Determine how many times we called the Brightcove API
	echo 'API Calls: ' . $bc->__get('api_calls');


Regional Support (Internationalization)
---------------------------------------
This example shows how to change the API URLs for supporting international regions.

	// Change our region to Japan
	$bc->__set('url_read', 'api.brightcove.co.jp/services/library?');
	$bc->__set('url_write', 'api.brightcove.co.jp/services/post');


Error Handling
--------------
This example shows how to utilize the built-in error handling in BCMAPI.
  
	// Create a try/catch
	try {
		// Make our API call
		$video = $bc->find('find_video_by_id', 123456789);
	} catch(Exception $error) {
		// Handle our error
		echo $error;
		die();
	}


Find Query
----------
This example shows how to retrieve a video from a Brightcove account.

	// Make our API call
	$video = $bc->find('find_video_by_id', 123456789);
	
	// Print the video name and ID
	echo $video->name . ' (' . $video->id . ')';


Find Query - Shorthand
----------------------
This example shows how you can use shorthand method names to make code easier to write and read.

	// Make our API call
	$video = $bc->find('videoById', 123456789);


Find Query - Additional Parameters
----------------------------------
This example shows how to define additional API call parameters using a key-value array.

	// Define our parameters
	$params = array(
		'video_id' => 123456789,
		'video_fields' => 'id,name,shortDescription'
	);
	
	// Make our API call
	$video = $bc->find('videoById', $params);


Find Query - True Find All
--------------------------
Brightcove limits the "find_all_videos" call to 100 results, requiring pagination and numerous API calls. This example shows how to use the findAll() method to do this automatically.
**WARNING: Use very carefully** *
	// Define our parameters
	$params = array(
		'video_fields' => 'id,name'
	);
	
	// Make our API call
	$videos = $bc->findAll('video', $params);


Search Query
------------
This example shows how to search for a video about "gates", but not "Bill Gates".

	// Define our parameters
	$params = array(
		'video_fields' => 'id,name,shortDescription'
	);
	
	// Set our search terms
	$terms = array(
		'all' => 'display_name:gates',
		'none' => 'display_name:bill'
	);
	
	// Make our API call
	$videos = $bc->search('video', $terms, $params);


Search Query (Multiple-Field Search)
------------------------------------
This example shows how to search for a video with "jobs" in the title AND tags.

	// Define our parameters
	$params = array(
		'video_fields' => 'id,name,shortDescription'
	);
	
	// Set our search terms
	$terms = array(
		'all' => 'display_name:jobs,tag:jobs'
	);
	
	// Make our API call
	$videos = $bc->search('video', $terms, $params);


Create - Video
--------------
This example details how to upload a video to a Brightcove account. This code is handling data that was passed from a form. Note that we re-name the uploaded movie to its original name rather than the random string generated when it's placed in the "tmp" directory; this is because the tmp_name does not include the file extension. The video name is a required field.

	// Create an array of meta data from our form fields
	$metaData = array(
		'name' => $_POST['videoName'],
		'shortDescription' => $_POST['videoShortDescription']
	);
	
	// Move the file out of 'tmp', or rename
	rename($_FILES['videoFile']['tmp_name'], '/tmp/' . $_FILES['videoFile']['name']);
	$file = '/tmp/' . $_FILES['videoFile']['name'];
	
	// Upload the video and save the video ID
	$id = $bc->createMedia('video', $file, $metaData);


Create - Image
--------------
This example details how to upload a image to a Brightcove account. This code is handling data that was passed from a form. Note that we re-name the uploaded image to its original name rather than the random string generated when it's placed in the "tmp" directory; this is because the tmp_name does not include the file extension.

	// Create an array of meta data from our form fields
	$metaData = array(
		'type' => 'VIDEO_STILL',
		'displayName' => $_POST['imageName']
	);
	
	// Move the file out of 'tmp', or rename
	rename($_FILES['bcImage']['tmp_name'], '/tmp/' . $_FILES['bcImage']['name']);
	$file = '/tmp/' . $_FILES['bcImage']['name'];
	
	// Upload the image, assign to a video, and save the image asset ID
	$id = $bc->createImage('video', $file, $metaData, 123456789);


Create - Playlist
-----------------
This example shows how to create a playlist in a Brightcove account. The code is handling data that was passed from a form. The name, video IDs, and playlist type are all required fields.

	// Take a comma-separated string of video IDs and explode into an array
	$videoIds = explode(',', $_POST['playlistVideoIds']);
	
	// Create an array of meta data from our form fields
	$metaData = array(
		'name' => $_POST['playlistName'],
		'shortDescription' => $_POST['playlistShortDescription'],
		'videoIds' => $videoIds,
		'playlistType' => 'explicit'
	);
	
	// Create the playlist and save the playlist ID
	$id = $bc->createPlaylist('video', $metaData);


Update - Video / Playlist
-------------------------
This example shows how to update a video, but the same method will work for a playlist.

	// Create an array of the new meta data
	$metaData = array(
		'id' => 123456789,
		'shortDescription' => 'Our new short description.'
	);
	
	// Update a video with the new meta data
	$bc->update('video', $metaData);
	

Delete - Video / Playlist
-------------------------
This example shows how to delete a video, but the same method will work for a playlist. Cascaded deletion means that the video will also be removed from all playlists and players.

	// Delete a 'video' by ID, and cascade the deletion
	$bc->delete('video', 123456789, NULL, TRUE);


Status - Video Upload
---------------------
This example shows how to determine the status of a video being uploaded to a Brightcove account.

	// Retrieve upload status
	$status = $bc->getStatus('video', 123456789);


Share Video
-----------
This example shows how to share a video with another Brightcove account. A list of the new video IDs will be returned. Note that sharing must be enabled between the two accounts.

	// List the accounts to share the video with
	$ids = array(
		123456789
	);
	
	// Share the videos, and save the new video IDs
	$new_ids = $bc->shareMedia('video', 123456789, $ids);


Add To / Remove From Playlist
-----------------------------
This example shows how to add an asset to a playlist, as well as how to remove an asset from a playlist. You may pass an array of video IDs, or a single video ID.

	// Add two videos to a playlist
	$bc->addToPlaylist(555555555, array(123456789, 987654321));
	
	// Remove a video from a playlist
	$bc->removeFromPlaylist(555555555, 987654321);


SEF URLs / Time Formatting
--------------------------
This example shows the BCMAPI convenience methods that convert video titles into a search-engine friendly format and video lengths into formatted strings.

	// Make our API call
	$video = $bc->find('videoById', 123456789);
	
	// Print the SEF video name and formatted duration
	echo 'Name: ' . $bc->sef($video->name) . '&lt;br /&gt;';
	echo 'Duration:' . $bc->time($video->length) . '&lt;br /&gt;';


Automatic Timestamp Conversion
------------------------------
To more seamlessly bridge the Brightcove API into PHP the 'from_date' parameter for the "find_modified_videos" call should be provided as seconds since Epoch (UNIX timestamp) instead of minutes since, as the Brightcove Media API documentation states. You can still pass minutes if you prefer.

	// Set timestamp to 7 days ago (in seconds)
	$time = time() - 604800;
	
	// Make our API call
	$videos = $bc->find('modifiedVideos', $time);
	
	// Set timestamp to 7 days ago (in minutes)
	$time = floor((time() - 604800) / 60);
	
	// Make our API call
	$videos = $bc->find('modifiedVideos', $time);


Tags
----
This example demonstrates how a tag with a value of "abc=xyz" can easily be parsed into a key-value array pair.
	
	// Make our API call
	$video = $bc->find('videoById', 123456789);
	
	// Parse any key=value tags into array
	$video->tags = $bc->tags($video->tags);
	
	// Print out each tag
	foreach($video->tags as $key => $value)
	{
	echo $key . ': ' . $value . '&lt;br /&gt;';
	}


Tag Filter
----------
This example shows how to remove all videos that don't contain any of the listed tags.

	// Make our API call
	$videos = $bc->find('allVideos');
	
	// Remove all videos without specified tags
	$videos = $bc->filter($videos, 'published=true,include=true');

* * *

Methods
=======

BCMAPI
------
The constructor for the BCMAPI class.

### Arguments
- **token_read** *The read API token for the Brightcove account*

	Default:	NULL
	Type:		String

- **token_write** *The write API token for the Brightcove account*

	Default:	NULL
	Type:		String
				 

### Properties
- **api_calls** *Private - The total number of API calls that have been processed*

	Type:		Integer

- **media_delivery** *Private - What type of URL to return for UDS assets*

	Type:		String
				
- **page_number** *Public - The value of the last 'page_number' return*

	Type:		Integer
			
- **page_size** *Public - The value of the last 'page_size' return*

	Type:		Integer
			
- **secure** *Private - Whether BCMAPI is operating over HTTPS*

	Type:		Boolean
			
- **show_notices** *Private - Whether BCMAPI will send error notices*

	Type:		Boolean
			
- **timeout_attempts** *Private - The number of times to retry a call in case of API timeout*

	Type:		Integer
			
- **timeout_delay** *Private - Number of seconds to delay retry attempts*

	Type:		Integer
			
- **timeout_retry** *Private -  Whether to automatically retry calls that fail due to API timeout*

	Type:		Boolean
			
- **token_read** *Private - The read Brightcove token to use*

	Type:		String
			
- **token_write** *Private - The write Brightcove token to use*

	Type:		String
			
- **total_count** *Public - The value of the last 'total_count' return*

	Type:		Integer
			
__set
-----
Sets a property of the BCMAPI class.

### Arguments			
- **key** *The property to set*

	Type:		String
				
- **value** *The new value for the property*

	Type:		Mixed
				
			
### Return Value
The new value of the property

	Type:		Mixed
				
			
__get
-----
Retrieves a property of the BCMAPI class.

### Arguments			
- **key** *The property to retrieve*

	Type:		String

### Return Value
The value of the property

	Type:		Mixed

find
----
Formats the request for any API "Find" methods and retrieves the data. The requested call may be written in a shortened version (e.g. "allVideos" or "all_videos" instead of "find_all_videos"). If the call supports get_item_count, it is defaulted to TRUE.
		
### Arguments				
- **call** *The requested API method*

	Type:		String
				
- **params** *A key-value array of API parameters, or a single value that matches the default*

	Default:	NULL
	Type:		Mixed
		
### Return Value
An object containing all API return data

	Type:		Object
					
findAll
-------
Finds all media assets in account, ignoring pagination. This method should be used with extreme care as accounts with a large library of assets will require a high number of API calls. This could significantly affect performance and may result in additional charges from Brightcove.

### Arguments	
- **type** *The type of object to retrieve*

	Default:	video
	Type:		String
			
- **params** *A key-value array of API parameters*

	Default:	NULL
	Type:		Array
			
			
### Return Value
An object containing all API return data	
		
	Type:		Object
	
			
search
------
Performs a search of video meta data
	
### Arguments		
- **type** *The type of objects to retrieve*

	Default:	video
	Type:		String
				
- **terms** *The terms to use for the search*

	Default:	NULL
	Type:		Array
				
- **params** *A key-value array of API parameters*

	Default:	NULL
	Type:		Mixed
				
### Return Value
An object containing all API return data

	Type:		Object
				
			
createMedia
-----------
Uploads a media asset file to Brightcove. When creating an asset from an upload it is suggested that you first move the file out of the temporary directory where PHP placed it and rename the file to it's original name. An asset name and short description are both required; leaving these values blank will cause them to be populated with the current UNIX timestamp. Certain upload settings are not allowed depending upon what default have already been set, and depending on the type of file being uploaded. Setting the incorrect values for these parameters will trigger a notice.

### Arguments
- **type** *The type of object to upload*

	Default:	video
	Type:		String
				
- **file** *The location of the temporary file*

	Default:	NULL
	Type:		String
				
- **meta** *The media asset information*

	Type:		Array
			
- **options** *Optional upload values*

	Default:	NULL
	Type:		Array
			
### Return Value
The media asset ID

	Type:		String
				
			
createPlaylist
--------------
Creates a playlist.

### Arguments			
- **type** *The type of playlist to create*

	Default:	video
	Type:		String
			
- **meta** *The playlist information*

	Type:		Array
			
### Return Value
The playlist ID

	Type:		String
				
				
update
------
Updates a media asset. Only the meta data that has changed needs to be passed along. Be sure to include the asset ID, though.

### Arguments			
- **type** *The type of object to update*

	Default:	video
	Type:		String
				
- **meta** *The information for the media asset*

	Type:		Array
				
### Return Value
The new DTO 

	Type:		DTO
			
			
createImage
-----------
Uploads a media image file to Brightcove. When creating an image it is suggested that you first move the file out of the temporary directory where PHP placed it and rename the file to it's original name.

### Arguments			
- **type** *The type of object to upload image for*

	Default:	video
	Type:		String
				
- **file** *The location of the temporary file*

	Default:	NULL
	Type:		String
				
- **meta** *The image information*

	Type:		Array
				
- **id** *The ID of the media asset to assign the image to*

	Default:	NULL
	Type:		Integer
				
- **ref_id** *The reference ID of the media asset to assign the image to*

	Default:	NULL
	Type:		String
				
- **resize** *Whether or not to resize the image on upload*

	Default:	TRUE
	Type:		Boolean
				
			
### Return Value
The image asset

	Type:		Mixed
				
			
createOverlay
-------------
Uploads a logo overlay file to Brightcove. When creating a logo overlay it is suggested that you first move the file out of the temporary directory where PHP placed it and rename the file to it's original name.

### Arguments			
- **file** *The location of the temporary file*

	Default:	NULL
	Type:		String
				
- **meta** *The logo overlay information*

	Type:		Array
				
- **id** *The ID of the media asset to assign the logo overlay to*

	Default:	NULL
	Type:		Integer
				
- **ref_id** *The reference ID of the media asset to assign the logo overlay to*

	Default:	NULL
	Type:		String
				
			
### Return Value
The logo overlay asset

	Type:		Mixed
				

deleteOverlay
-------------
Deletes a logo overlay.

### Arguments			
- **id** *The ID of the media asset*

	Default:	NULL
	Type:		Integer
				
- **ref_id** *The reference ID of the media asset*

	Default:	NULL
	Type:		String
				
- **options** *Optional values*

	Default:	NULL
	Type:		Array
				
			
delete
------
Deletes a media asset. Either an ID or Reference ID must be passed.

### Arguments	
- **type** *The type of the item to delete*

	Default:	video
	Type:		String
				
- **id** *The ID of the media asset*

	Default:	NULL
	Type:		Integer
				
- **ref_id** *The reference ID of the media asset*

	Default:	NULL
	Type:		String
				
- **options** *Optional values*

	Default:	NULL
	Type:		Array
				
				
getStatus
---------
Retrieves the status of a media asset upload.

### Arguments			
- **type** *The type of object to check*

	Default:	video
	Type:		String
				
- **id** *The ID of the media asset*

	Default:	NULL
	Type:		String
				
- **ref_id** *The reference ID of the media asset*

	Default:	TRUE
	Type:		String
				
### Return Value
The upload status

	Type:		String
				

shareMedia
----------
Shares a media asset with the selected accounts. Sharing must be enabled between the two accounts.

### Arguments			
- **type** *The type of object to share*

	Default:	video
	Type:		String
				
- **id** *The ID of the media asset*

	Type:		Integer
				
- **account_ids** *An array of account IDs*

	Type:		Array
				
- **accept** *Whether the share should be auto accepted*

	Default:	FALSE
	Type:		Boolean
				
- **force** *Whether the share should overwrite existing copies of the media*

	Default:	FALSE
	Type:		Boolean
			
### Return Value
The new media asset IDs 

	Type:		Array
				
			
removeFromPlaylist
------------------
Removes assets from a playlist.

### Arguments			
- **playlist_id** *The ID of the playlist to modify*

	Type:		Integer
				
- **video_ids** *An array of video IDs to delete from the playlist*

	Type:		Array
				
### Return Value
The new playlist DTO

	Type:		Array
				
			
addToPlaylist
-------------
Adds assets to a playlist.

### Arguments			
- **playlist_id** *The ID of the playlist to modify*

	Type:		Integer
			
- **video_ids** *An array of video IDs to add to the playlist*

	Type:		Array
				
### Return Value
The new playlist DTO

	Type:		Array
				

convertTime
-----------
Converts milliseconds to formatted time or seconds.

### Arguments			
- **ms** *The length of the media asset in milliseconds*

	Default:	
	Type:		Integer
			
- **seconds** *Whether to return only seconds*

	Default:	FALSE
	Type:		Boolean
				
			
### Return Value
The formatted length or total seconds of the media asset 

	Type:		Mixed
			
				
convertTags
-----------
Parses media asset tags array into a key-value array.

### Arguments			
- **tags** *The tags array from a media asset DTO*

	Default:	
	Type:		Array
				
- **implode** *Return array to Brightcove format*

	Default:	FALSE
	Type:		Boolean
			
### Return Value
A key-value array of tags, or a comma-separated string

	Type:		Mixed
				
				
tagsFilter
----------
Removes assets that don't contain the appropriate tags.

### Arguments			
- **videos** *All the assets you wish to filter*

	Default:	
	Type:		Array
			
- **tag** *A comma-separated list of tags to filter on*

	Default:	
	Type:		String
			
### Return Value
The filtered list of assets 

	Type:		Array
				
			
sef
---
Formats a media asset name to be search-engine friendly.

### Arguments			
- **name** *The asset name*

	Default:	
	Type:		String
			
### Return Value
The search-engine friendly asset name

	Type:		String
				

BCMAPICache
-----------
The constructor for the BCMAPICache class.

### Arguments			
- **type** *The type of caching method to use, either 'file' or 'memcached'*

	Default:	file
	Type:		String
				
- **time** *How many seconds until cache files are considered cold*

	Default:	600
	Type:		Integer
				
- **location** *The absolute path of the cache directory (file) or host (memcached)*

	Default:	
	Type:		String
				
- **extension** *The file extension for cache items (file only)*

	Default:	.c
	Type:		String
				
- **port** 

	Default:	11211
	Type:		Integer
				
			
### Properties
			
- **extension** *Public - The file extension for cache items (file only)*

	Type:		String
				
- **location** *Public - The absolute path of the cache directory (file) or host (memcached)*

	Type:		String
				
- **memcached** *Public - The Memcached object, if valid*

	Type:		Object
				
- **port** *Public - The port to use (Memcached only)*

	Type:		Integer
				
- **time** *Public - How many seconds until cache files are considered cold*

	Type:		Integer
				
- **type** *Public - The type of caching method to use, either 'file' or 'memcached'*

	Type:		String

* * *				

Errors
======

BCMAPIApiError
--------------
This is the most generic error returned from BCMAPI as it is thrown whenever the API returns unexpected data, or an error. The API return data will be included in the error to help you diagnose the problem.

BCMAPIDeprecated
----------------
The requested item is no longer supported by Brightcove and/or BCMAPI. Stop using this method as early as possible, as the item could be removed in any future release.

BCMAPIDtoDoesNotExist
---------------------
The specified asset does not exist in the Brightcove system. Ensure you're using the correct ID.

BCMAPIIdNotProvided
-------------------
An ID has not been passed to the method (usually a "delete" or "share" function). Include the ID parameter to resolve the error.

BCMAPIInvalidFileType
---------------------
The file being passed to the function is not supported. Try another file type to resolve the error.

BCMAPIInvalidMethod
-------------------
The "find" method being requested is not supported by BCMAPI, or does not exist in the Brightcove API. Remove the method call and check both the BCMAPI and Brightcove API documentation.

BCMAPIInvalidProperty
---------------------
The BCMAPI property you are trying to set or retrieve does not exist. Check the BCMAPI documentation.

BCMAPIInvalidType
-----------------
The DTO type (video, playlist, image, etc) you specified is not allowed for the method. Check both the BCMAPI and Brightcove API documentation.

BCMAPISearchTermsNotProvided
----------------------------
Please specify one or more search parameters. Verify you are passing the parameters in an array.

BCMAPITokenError
----------------
The read or write token you provided is not recognized by Brightcove. Verify you are using the correct token.

BCMAPITransactionError
----------------------
The API could not be accessed, or the API did not return any data. Verify the server has cURL installed, enabled, and able to retrieve remote data. Verify the Brightcove API is currently available.

