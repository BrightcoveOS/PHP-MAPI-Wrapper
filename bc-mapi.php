<?php

/**
 * Brightcove PHP MAPI Wrapper 2.0.4 (18 FEBRUARY 2011)
 * (Formerly known as Echove)
 *
 * REFERENCES:
 *	 Website: http://opensource.brightcove.com
 *	 Source: http://github.com/brightcoveos
 *
 * AUTHORS:
 *	 Matthew Congrove <mcongrove@brightcove.com>
 *	 Brian Franklin <bfranklin@brightcove.com>
 *
 * CONTRIBUTORS:
 *	 Luke Weber, Brandon Aaskov
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the “Software”),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, alter, merge, publish, distribute,
 * sublicense, and/or sell copies of the Software, and to permit persons to
 * whom the Software is furnished to do so, subject to the following conditions:
 *   
 * 1. The permission granted herein does not extend to commercial use of
 * the Software by entities primarily engaged in providing online video and
 * related services.
 *  
 * 2. THE SOFTWARE IS PROVIDED "AS IS", WITHOUT ANY WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, SUITABILITY, TITLE,
 * NONINFRINGEMENT, OR THAT THE SOFTWARE WILL BE ERROR FREE. IN NO EVENT
 * SHALL THE AUTHORS, CONTRIBUTORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY WHATSOEVER, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE, INABILITY TO USE, OR OTHER DEALINGS IN THE SOFTWARE.
 *  
 * 3. NONE OF THE AUTHORS, CONTRIBUTORS, NOR BRIGHTCOVE SHALL BE RESPONSIBLE
 * IN ANY MANNER FOR USE OF THE SOFTWARE.  THE SOFTWARE IS PROVIDED FOR YOUR
 * CONVENIENCE AND ANY USE IS SOLELY AT YOUR OWN RISK.  NO MAINTENANCE AND/OR
 * SUPPORT OF ANY KIND IS PROVIDED FOR THE SOFTWARE.
 */

class BCMAPI
{
	const ERROR_API_ERROR = 1;
	const ERROR_DEPRECATED = 99;
	const ERROR_DTO_DOES_NOT_EXIST = 12;
	const ERROR_ID_NOT_PROVIDED = 2;
	const ERROR_INVALID_FILE_TYPE = 5;
	const ERROR_INVALID_METHOD = 3;
	const ERROR_INVALID_PROPERTY = 4;
	const ERROR_INVALID_TYPE = 6;
	const ERROR_INVALID_UPLOAD_OPTION = 7;
	const ERROR_READ_API_TRANSACTION_FAILED = 8;
	const ERROR_READ_TOKEN_NOT_PROVIDED = 9;
	const ERROR_SEARCH_TERMS_NOT_PROVIDED = 13;
	const ERROR_WRITE_API_TRANSACTION_FAILED = 10;
	const ERROR_WRITE_TOKEN_NOT_PROVIDED = 11;

	public $page_number = NULL;
	public $page_size = NULL;
	public $total_count = NULL;

	private $api_calls = 0;
	private $bit32 = FALSE;
	private $media_delivery = 'default';
	private $secure = FALSE;
	private $show_notices = FALSE;
	private $timeout_attempts = 100;
	private $timeout_current = 0;
	private $timeout_delay = 1;
	private $timeout_retry = FALSE;
	private $token_read = NULL;
	private $token_write = NULL;
	private $url_read = 'api.brightcove.com/services/library?';
	private $url_write = 'api.brightcove.com/services/post';
	private $valid_types = array(
		'playlist',
		'video'
	);

	/**
	 * The constructor for the BCMAPI class.
	 * @access Public
	 * @since 0.1.0
	 * @param string [$token_read] The read API token for the Brightcove account
	 * @param string [$token_write] The write API token for the Brightcove account
	 */
	public function __construct($token_read = NULL, $token_write = NULL)
	{
		$this->token_read = $token_read;
		$this->token_write = $token_write;
		$this->bit32 = ((string)'99999999999999' == (int)'99999999999999') ? FALSE : TRUE;
	}

	/**
	 * Sets a property of the BCMAPI class.
	 * @access Public
	 * @since 1.0.0
	 * @param string [$key] The property to set
	 * @param mixed [$value] The new value for the property
	 * @return mixed The new value of the property
	 */
	public function __set($key, $value)
	{
		if(isset($this->$key) || is_null($this->$key))
		{
			$this->$key = $value;
		} else {
			throw new BCMAPIInvalidProperty($this, self::ERROR_INVALID_PROPERTY);
		}
	}

	/**
	 * Retrieves a property of the BCMAPI class.
	 * @access Public
	 * @since 1.0.0
	 * @param string [$key] The property to retrieve
	 * @return mixed The value of the property
	 */
	public function __get($key)
	{
		if(isset($this->$key) || is_null($this->$key))
		{
			return $this->$key;
		} else {
			throw new BCMAPIInvalidProperty($this, self::ERROR_INVALID_PROPERTY);
		}
	}

	/**
	 * Formats the request for any API 'Find' methods and retrieves the data.
	 * @access Public
	 * @since 0.1.0
	 * @param string [$call] The requested API method
	 * @param mixed [$params] A key-value array of API parameters, or a single value that matches the default
	 * @return object An object containing all API return data
	 */
	public function find($call, $params = NULL)
	{
		$call = strtolower(preg_replace('/(?:find|_)+/i', '', $call));

		switch($call)
		{
			case 'allvideos':
				$method = 'find_all_videos';
				$get_item_count = TRUE;
				break;
			case 'videobyid':
				$method = 'find_video_by_id';
				$default = 'video_id';
				$get_item_count = FALSE;
				break;
			case 'videobyidunfiltered':
				$method = 'find_video_by_id_unfiltered';
				$default = 'video_id';
				$get_item_count = FALSE;
				break;
			case 'videosbyids':
				$method = 'find_videos_by_ids';
				$default = 'video_ids';
				$get_item_count = FALSE;
				break;
			case 'videosbyidsunfiltered':
				$method = 'find_videos_by_ids_unfiltered';
				$default = 'video_ids';
				$get_item_count = FALSE;
				break;
			case 'videobyreferenceid':
				$method = 'find_video_by_reference_id';
				$default = 'reference_id';
				$get_item_count = FALSE;
				break;
			case 'videobyreferenceidunfiltered':
				$method = 'find_video_by_reference_id_unfiltered';
				$default = 'reference_id';
				$get_item_count = FALSE;
				break;
			case 'videosbyreferenceids':
				$method = 'find_videos_by_reference_ids';
				$default = 'reference_ids';
				$get_item_count = FALSE;
				break;
			case 'videosbyreferenceidsunfiltered':
				$method = 'find_videos_by_reference_ids_unfiltered';
				$default = 'reference_ids';
				$get_item_count = FALSE;
				break;
			case 'videosbycampaignid':
				$method = 'find_videos_by_campaign_id';
				$default = 'campaign_id';
				$get_item_count = TRUE;
				break;
			case 'videosbytags':
				$method = 'find_videos_by_tags';
				$default = 'or_tags';
				$get_item_count = TRUE;
				break;
			case 'videosbytext':
				$method = 'find_videos_by_text';
				$default = 'text';
				$get_item_count = TRUE;
				break;
			case 'videosbyuserid':
				$method = 'find_videos_by_user_id';
				$default = 'user_id';
				$get_item_count = TRUE;
				break;
			case 'modifiedvideos':
				$method = 'find_modified_videos';
				$default = 'from_date';
				$get_item_count = TRUE;
				break;
			case 'relatedvideos':
				$method = 'find_related_videos';
				$default = 'video_id';
				$get_item_count = TRUE;
				break;
			case 'allplaylists':
				$method = 'find_all_playlists';
				$get_item_count = TRUE;
				break;
			case 'playlistbyid':
				$method = 'find_playlist_by_id';
				$default = 'playlist_id';
				$get_item_count = FALSE;
				break;
			case 'playlistsbyids':
				$method = 'find_playlists_by_ids';
				$default = 'playlist_ids';
				$get_item_count = FALSE;
				break;
			case 'playlistbyreferenceid':
				$method = 'find_playlist_by_reference_id';
				$default = 'reference_id';
				$get_item_count = FALSE;
				break;
			case 'playlistsbyreferenceids':
				$method = 'find_playlists_by_reference_ids';
				$default = 'reference_ids';
				$get_item_count = FALSE;
				break;
			case 'playlistsforplayerid':
				$method = 'find_playlists_for_player_id';
				$default = 'player_id';
				$get_item_count = TRUE;
				break;
			default:
				throw new BCMAPIInvalidMethod($this, self::ERROR_INVALID_METHOD);
				break;
		}

		if(!isset($params))
		{
			$params = array();
		} else {
			if(!is_array($params))
			{
				$temp = $params;

				$params = array();
				$params[$default] = $temp;
			}
		}

		if(isset($params['from_date']))
		{
			$params['from_date'] = (string)$params['from_date'];

			if(strlen($params['from_date']) > 9)
			{
				$params['from_date'] = floor((int)$params['from_date'] / 60);
			}
		}

		if(!isset($params['get_item_count']) && $get_item_count)
		{
			$params['get_item_count'] = 'TRUE';
		}

		if(!isset($params['media_delivery']) && $this->media_delivery != 'default')
		{
			$params['media_delivery'] = $this->media_delivery;
		}

		$url = $this->appendParams($method, $params);

		$this->timeout_current = 0;

		return $this->getData($url);
	}

	/**
	 * Finds all media assets in account, ignoring pagination.
	 * @access Public
	 * @since 0.3.6
	 * @param string [$type] The type of object to retrieve
	 * @param array [$params] A key-value array of API parameters
	 * @return object An object containing all API return data
	 */
	public function findAll($type = 'video', $params = NULL)
	{
		$this->timeout_current = 0;

		$this->validType($type);

		if(!isset($params))
		{
			$params = array();
		}

		$params['get_item_count'] = 'TRUE';
		$params['page_number'] = 0;

		if(!isset($params['page_size']) || $params['page_size'] > 100)
		{
			$params['page_size'] = 100;
		}

		if(!isset($params['media_delivery']) && $this->media_delivery != 'default')
		{
			$params['media_delivery'] = $this->media_delivery;
		}

		$assets = array();
		$current_page = 0;
		$total_count = 0;
		$total_page = 1;

		while($current_page < $total_page)
		{
			$params['page_number'] = $current_page;

			$url = $this->appendParams(strtolower('find_all_' . $type . 's'), $params);

			$result = $this->getData($url);

			if($total_count < 1)
			{
				$total_count = $this->total_count;
				$total_page = ceil($total_count / $params['page_size']);
			}

			if(is_array($result))
			{
				foreach($result as $asset)
				{
					$assets[] = $asset;
				}
			}

			$current_page++;
		}
		
		$this->timeout_current = 0;

		return $assets;
	}

	/**
	 * Performs a search of video meta data
	 * @access Public
	 * @since 1.1.1
	 * @param string [$type] The type of objects to retrieve
	 * @param array [$terms] The terms to use for the search
	 * @param mixed [$params] A key-value array of API parameters
	 * @return object An object containing all API return data
	 */
	public function search($type = 'video', $terms = NULL, $params = NULL)
	{
		if(!isset($terms) || !is_array($terms))
		{
			throw new BCMAPISearchTermsNotProvided($this, self::ERROR_SEARCH_TERMS_NOT_PROVIDED);
		}

		if(!isset($params))
		{
			$params = array();
		} else {
			if(!is_array($params))
			{
				$temp = $params;

				$params = array();
				$params[$default] = $temp;
			}
		}

		if(!isset($params['get_item_count']))
		{
			$params['get_item_count'] = 'TRUE';
		}

		foreach($terms as $key => $value)
		{
			if(strpos($value, ',') !== FALSE)
			{
				$i = 0;
				$parts = explode(',', $value);

				foreach($parts as $part)
				{
					if($i == 0)
					{
						$params[$key] = $part;
						$i++;
					} else {
						$params[$key] .= '%26' . $key . '%3D' . $part;
					}
				}
			} else {
				$params[$key] = $value;
			}
		}

		$url = str_replace(array('%2526', '%253D'), array('&', '='), $this->appendParams('search_' . $type . 's', $params));
		
		$this->timeout_current = 0;

		return $this->getData($url);
	}

	/**
	 * Uploads a media asset file to Brightcove.
	 * @access Public
	 * @since 1.0.0
	 * @param string [$type] The type of object to upload
	 * @param string [$file] The location of the temporary file
	 * @param array [$meta] The media asset information
	 * @param array [$options] Optional upload values
	 * @return string The media asset ID
	 */
	public function createMedia($type = 'video', $file = NULL, $meta, $options = NULL)
	{
		if(strtolower($type) == 'video')
		{
			if(isset($file))
			{
				preg_match('/(\.f4a|\.f4b|\.f4v|\.f4p|\.flv)*$/i', $file, $invalid_extensions);

				if(isset($invalid_extensions[1]))
				{
					if(isset($options['encode_to']))
					{
						unset($options['encode_to']);
						
						throw new BCMAPIInvalidUploadOption($this, self::ERROR_INVALID_UPLOAD_OPTION);
					}

					if(isset($options['create_multiple_renditions']))
					{
						$options['create_multiple_renditions'] = 'FALSE';
						
						throw new BCMAPIInvalidUploadOption($this, self::ERROR_INVALID_UPLOAD_OPTION);
					}

					if(isset($options['preserve_source_rendition']))
					{
						unset($options['preserve_source_rendition']);
						
						throw new BCMAPIInvalidUploadOption($this, self::ERROR_INVALID_UPLOAD_OPTION);
					}
				}

				if((isset($options['create_multiple_renditions']) && $options['create_multiple_renditions'] === TRUE) && (isset($options['H264NoProcessing']) && $options['H264NoProcessing'] === TRUE))
				{
					unset($options['H264NoProcessing']);
					
					throw new BCMAPIInvalidUploadOption($this, self::ERROR_INVALID_UPLOAD_OPTION);
				}
			}
		} else {
			throw new BCMAPIInvalidType($this, self::ERROR_INVALID_TYPE);
		}

		$request = array();
		$post = array();
		$params = array();
		$media = array();

		foreach($meta as $key => $value)
		{
			$media[$key] = $value;
		}

		if(!isset($media['name']) || is_null($media['name']) || $media['name'] == '')
		{
			$media['name'] = time();
		}

		if(!isset($media['shortDescription']) || is_null($media['shortDescription']) || $media['shortDescription'] == '')
		{
			$media['shortDescription'] = time();
		}

		if(isset($options))
		{
			foreach($options as $key => $value)
			{
				$params[$key] = $value;
			}
		}

		$params['token'] = $this->token_write;
		$params[strtolower($type)] = $media;

		$post['method'] = strtolower('create_' . $type);
		$post['params'] = $params;

		$request['json'] = json_encode($post);

		if(isset($file))
		{
			$request['file'] = '@' . $file;
		}

		return (string)$this->putData($request)->result;
	}

	/**
	 * Creates a playlist.
	 * @access Public
	 * @since 0.3.0
	 * @param string [$type] The type of playlist to create
	 * @param array [$meta] The playlist information
	 * @return string The playlist ID
	 */
	public function createPlaylist($type = 'video', $meta)
	{
		$request = array();
		$post = array();
		$params = array();
		$media = array();

		foreach($meta as $key => $value)
		{
			$media[$key] = $value;
		}

		if(strtolower($type) == 'video')
		{
			if(isset($media['videoIds']))
			{
				foreach($media['videoIds'] as $key => $value)
				{
					$media['videoIds'][$key] = (int)$value;
				}
			}

			$params['playlist'] = $media;
			$post['method'] = 'create_playlist';
		} else {
			throw new BCMAPIInvalidType($this, self::ERROR_INVALID_TYPE);
		}

		$params['token'] = $this->token_write;

		$post['params'] = $params;

		$request['json'] = json_encode($post);

		return (string)$this->putData($request)->result;
	}

	/**
	 * Uploads a media image file to Brightcove.
	 * @access Public
	 * @since 0.3.4
	 * @param string [$type] The type of object to upload image for
	 * @param string [$file] The location of the temporary file
	 * @param array [$meta] The image information
	 * @param int [$id] The ID of the media asset to assign the image to
	 * @param string [$ref_id] The reference ID of the media asset to assign the image to
	 * @param bool [$resize] Whether or not to resize the image on upload
	 * @return mixed The image asset
	 */
	public function createImage($type = 'video', $file = NULL, $meta, $id = NULL, $ref_id = NULL, $resize = TRUE)
	{
		$request = array();
		$post = array();
		$params = array();
		$media = array();

		if(strtolower($type) == 'video')
		{
			$post['method'] = 'add_image';
		} else {
			throw new BCMAPIInvalidType($this, self::ERROR_INVALID_TYPE);
		}

		foreach($meta as $key => $value)
		{
			$media[$key] = $value;
		}

		if(isset($id))
		{
			$params[strtolower($type) . '_id'] = $id;
		} elseif(isset($ref_id)) {
			$params[strtolower($type) . '_reference_id'] = $ref_id;
		} else {
			throw new BCMAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
		}

		if($resize)
		{
			$params['resize'] = 'TRUE';
		} else {
			$params['resize'] = 'FALSE';
		}

		$params['token'] = $this->token_write;
		$params['image'] = $media;

		$post['params'] = $params;

		$request['json'] = json_encode($post) . "\n";

		if(isset($file))
		{
			$request['file'] = '@' . $file;
		}

		return $this->putData($request)->result;
	}

	/**
	 * Uploads a logo overlay file to Brightcove.
	 * @access Public
	 * @since 1.1.0
	 * @param string [$file] The location of the temporary file
	 * @param array [$meta] The logo overlay information
	 * @param int [$id] The ID of the media asset to assign the logo overlay to
	 * @param string [$ref_id] The reference ID of the media asset to assign the logo overlay to
	 * @return mixed The logo overlay asset
	 */
	public function createOverlay($file = NULL, $meta, $id = NULL, $ref_id = NULL)
	{
		$request = array();
		$post = array();
		$params = array();
		$media = array();

		$post['method'] = 'add_logo_overlay';

		foreach($meta as $key => $value)
		{
			$media[$key] = $value;
		}

		if(isset($id))
		{
			$params['video_id'] = $id;
		} elseif(isset($ref_id)) {
			$params['video_reference_id'] = $ref_id;
		} else {
			throw new BCMAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
		}

		$params['token'] = $this->token_write;
		$params['logooverlay'] = $media;

		$post['params'] = $params;

		$request['json'] = json_encode($post) . "\n";

		if(isset($file))
		{
			$request['file'] = '@' . $file;
		}

		return $this->putData($request)->result;
	}

	/**
	 * Deletes a logo overlay.
	 * @access Public
	 * @since 1.1.0
	 * @param int [$id] The ID of the media asset
	 * @param string [$ref_id] The reference ID of the media asset
	 * @param array [$options] Optional values
	 */
	public function deleteOverlay($id = NULL, $ref_id = NULL, $options = NULL)
	{
		$request = array();
		$post = array();
		$params = array();

		$params['token'] = $this->token_write;

		if(isset($options))
		{
			foreach($options as $key => $value)
			{
				$params[$key] = $value;
			}
		}

		if(isset($id))
		{
			$params['video_id'] = $id;
		} elseif(isset($ref_id)) {
			$params['video_reference_id'] = $ref_id;
		} else {
			throw new BCMAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
		}

		$post['method'] = strtolower('remove_logo_overlay');
		$post['params'] = $params;

		$request['json'] = json_encode($post) . "\n";

		return $this->putData($request, FALSE);
	}

	/**
	 * Updates a media asset.
	 * @access Public
	 * @since 0.3.0
	 * @param string [$type] The type of object to update
	 * @param array [$meta] The information for the media asset
	 * @return object The new DTO
	 */
	public function update($type = 'video', $meta)
	{
		$this->validType($type);

		$request = array();
		$post = array();
		$media = array();
		$params = array();

		foreach($meta as $key => $value)
		{
			$media[$key] = $value;
		}

		$params['token'] = $this->token_write;
		$params[strtolower($type)] = $media;

		$post['method'] = strtolower('update_' . $type);
		$post['params'] = $params;

		$request['json'] = json_encode($post) . "\n";

		return $this->putData($request)->result;
	}

	/**
	 * Deletes a media asset.
	 * @access Public
	 * @since 0.3.0
	 * @param string [$type] The type of item to delete
	 * @param int [$id] The ID of the media asset
	 * @param string [$ref_id] The reference ID of the media asset
	 * @param array [$options] Optional values
	 */
	public function delete($type = 'video', $id = NULL, $ref_id = NULL, $options = NULL)
	{
		$this->validType($type);

		$request = array();
		$post = array();
		$params = array();

		$params['token'] = $this->token_write;

		if(isset($options))
		{
			foreach($options as $key => $value)
			{
				$params[$key] = $value;
			}
		}

		if(isset($id))
		{
			$params[strtolower($type . '_id')] = $id;
		} elseif(isset($ref_id)) {
			$params['reference_id'] = $ref_id;
		} else {
			throw new BCMAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
		}

		$post['method'] = strtolower('delete_' . $type);
		$post['params'] = $params;

		$request['json'] = json_encode($post) . "\n";

		return $this->putData($request, FALSE);
	}

	/**
	 * Retrieves the status of a media asset upload.
	 * @access Public
	 * @since 0.3.9
	 * @param string [$type] The type of object to check
	 * @param int [$id] The ID of the media asset
	 * @param string [$ref_id] The reference ID of the media asset
	 * @return string The upload status
	 */
	public function getStatus($type = 'video', $id = NULL, $ref_id = TRUE)
	{
		if(!isset($id) && !isset($ref_id))
		{
			throw new BCMAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
		}

		$request = array();
		$post = array();
		$params = array();

		$params['token'] = $this->token_write;

		if(isset($id))
		{
			$params[strtolower($type) . '_id'] = $id;
		}

		if(isset($ref_id))
		{
			$params['reference_id'] = $ref_id;
		}

		if(strtolower($type) == 'video')
		{
			$post['method'] = 'get_upload_status';
		} else {
			throw new BCMAPIInvalidType($this, self::ERROR_INVALID_TYPE);
		}

		$post['params'] = $params;

		$request['json'] = json_encode($post) . "\n";

		return $this->putData($request)->result;
	}

	/**
	 * Shares a media asset with the selected accounts.
	 * @access Public
	 * @since 1.0.0
	 * @param string [$type] The type of object to check
	 * @param int [$id] The ID of the media asset
	 * @param array [$account_ids] An array of account IDs
	 * @param bool [$accept] Whether the share should be auto accepted
	 * @param bool [$force] Whether the share should overwrite existing copies of the media
	 * @return array The new media asset IDs
	 */
	public function shareMedia($type = 'video', $id, $account_ids, $accept = FALSE, $force = FALSE)
	{
		if(!isset($id))
		{
			throw new BCMAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
		}

		if(!is_array($account_ids))
		{
			$account_ids = array($account_ids);
		}

		$request = array();
		$post = array();
		$params = array();

		$params['token'] = $this->token_write;
		$params['sharee_account_ids'] = $account_ids;

		if($accept)
		{
			$params['auto_accept'] = 'TRUE';
		} else {
			$params['auto_accept'] = 'FALSE';
		}

		if($force)
		{
			$params['force_reshare'] = 'TRUE';
		} else {
			$params['force_reshare'] = 'FALSE';
		}

		if(strtolower($type) == 'video')
		{
			$params['video_id'] = $id;
			$post['method'] = 'share_video';
		} else {
			throw new BCMAPIInvalidType($this, self::ERROR_INVALID_TYPE);
		}

		$post['params'] = $params;

		$request['json'] = json_encode($post) . "\n";

		return $this->putData($request)->result;
	}

	/**
	 * Removes assets from a playlist
	 * @access Public
	 * @since 1.0.8
	 * @param int [$playlist_id] The ID of the playlist to modify
	 * @param array [$video_ids] An array of video IDs to delete from the playlist
	 * @return object The new playlist DTO
	 */
	public function removeFromPlaylist($playlist_id, $video_ids)
	{
		if(!isset($playlist_id))
		{
			throw new BCMAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
		}

		if(!is_array($video_ids))
		{
			$video_ids = array($video_ids);
		}

		$safe_videos = array();

		$meta = array(
			'playlist_id' => $playlist_id,
			'fields' => 'videoIds'
		);

		$playlist = $this->find('playlistById', $meta);

		if(!isset($playlist))
		{
			throw new BCMAPIDtoDoesNotExist($this, self::ERROR_DTO_DOES_NOT_EXIST);
		}

		foreach($playlist->videoIds as $video)
		{
			if(!in_array($video, $video_ids))
			{
				$safe_videos[] = $video;
			}
		}

		$new_meta = array(
			'id' => $playlist_id,
			'videoIds' => $safe_videos
		);

		return $this->update('playlist', $new_meta);
	}

	/**
	 * Adds assets to a playlist
	 * @access Public
	 * @since 1.0.8
	 * @param int [$playlist_id] The ID of the playlist to modify
	 * @param array [$video_ids] An array of video IDs to add to the playlist
	 * @return object The new playlist DTO
	 */
	public function addToPlaylist($playlist_id, $video_ids)
	{
		if(!isset($playlist_id))
		{
			throw new BCMAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
		}

		if(!is_array($video_ids))
		{
			$video_ids = array($video_ids);
		}

		$meta = array(
			'playlist_id' => $playlist_id,
			'fields' => 'videoIds'
		);

		$playlist = $this->find('playlistById', $meta);

		if(!isset($playlist))
		{
			throw new BCMAPIDtoDoesNotExist($this, self::ERROR_DTO_DOES_NOT_EXIST);
		}

		foreach($video_ids as $video)
		{
			$playlist->videoIds[] = $video;
		}

		$new_meta = array(
			'id' => $playlist_id,
			'videoIds' => $playlist->videoIds
		);

		return $this->update('playlist', $new_meta);
	}

	/**
	 * Converts milliseconds to formatted time or seconds.
	 * @access Public
	 * @since 0.2.1
	 * @param int [$ms] The length of the media asset in milliseconds
	 * @param bool [$seconds] Whether to return only seconds
	 * @return mixed The formatted length or total seconds of the media asset
	 */
	public function convertTime($ms, $seconds = FALSE)
	{
		$total_seconds = ($ms / 1000);

		if($seconds)
		{
			return $total_seconds;
		} else {
			$time = '';

			$value = array(
				'hours' => 0,
				'minutes' => 0,
				'seconds' => 0
			);

			if($total_seconds >= 3600)
			{
				$value['hours'] = floor($total_seconds / 3600);
				$total_seconds = $total_seconds % 3600;

				$time .= $value['hours'] . ':';
			}

			if($total_seconds >= 60)
			{
				$value['minutes'] = floor($total_seconds / 60);
				$total_seconds = $total_seconds % 60;

				$time .= $value['minutes'] . ':';
			} else {
				$time .= '0:';
			}

			$value['seconds'] = floor($total_seconds);

			if($value['seconds'] < 10)
			{
				$value['seconds'] = '0' . $value['seconds'];
			}

			$time .= $value['seconds'];

			return $time;
		}
	}

	/**
	 * Parses media asset tags array into a key-value array.
	 * @access Public
	 * @since 0.3.2
	 * @param array [$tags] The tags array from a media asset DTO
	 * @param bool [$implode] Return array to Brightcove format
	 * @return array A key-value array of tags
	 */
	public function convertTags($tags, $implode = FALSE)
	{
		$return = array();

		if(count($tags) > 0)
		{
			if($implode)
			{
				$i = 0;

				foreach($tags as $key => $value)
				{
					if($key !== $i)
					{
						$return[] = $key . '=' . $value;
					} else {
						$return[] = $value;
					}

					$i++;
				}
			} else {
				foreach($tags as $tag)
				{
					if(strpos($tag, '=') === FALSE)
					{
						$return[] = $tag;
					} else {
						$group = explode('=', $tag);
						$key = trim($group[0]);
						$value = trim($group[1]);

						if(!isset($return[$key]))
						{
							$return[$key] = $value;
						} else {
							if(is_array($return[$key]))
							{
								$return[$key][] = $value;
							} else {
								$return[$key] = array($return[$key], $value);
							}
						}
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Removes assets that don't contain the appropriate tags.
	 * @access Public
	 * @since 0.3.6
	 * @param array [$assets] All the assets you wish to filter
	 * @param string [$tag] A comma-separated list of tags to filter on
	 * @return array The filtered list of assets
	 */
	public function tagsFilter($assets, $tags)
	{
		$filtered = array();
		$array = explode(',', strtolower($tags));

		foreach($assets as $asset)
		{
			foreach($asset->tags as $k => $v)
			{
				if(isset($asset->tags))
				{
					$asset->tags[$k] = strtolower($v);
				}
			}

			if(isset($asset->tags))
			{
				if(count(array_intersect($array, $asset->tags)) > 0)
				{
					$filtered[] = $asset;
				}
			}
		}

		return $filtered;
	}

	/**
	 * Formats a media asset name to be search-engine friendly.
	 * @access Public
	 * @since 0.2.1
	 * @param string [$name] The asset name
	 * @return string The SEF asset name
	 */
	public function sef($name)
	{
		$accent_match = array('Â', 'Ã', 'Ä', 'À', 'Á', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
		$accent_replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'B', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');

		$name = str_replace($accent_match, $accent_replace, $name);
		$name = preg_replace('/[^a-zA-Z0-9\s]+/', '', $name);
		$name = preg_replace('/\s/', '-', $name);

		return $name;
	}

	/**
	 * Retrieves the appropriate API URL
	 * @access Private
	 * @since 1.0.0
	 * @param string [$type] The type of URL to retrieve, read or write
	 * @return string The appropriate API URL
	 */
	private function getUrl($type = 'read')
	{
		if($this->secure)
		{
			$url = 'https://';
		} else {
			$url = 'http://';
		}

		if(strtolower($type) == 'read')
		{
			$url .= $this->url_read;
		} elseif(strtolower($type) == 'write') {
			$url .= $this->url_write;
		} else {
			throw new BCMAPIInvalidType($this, self::ERROR_INVALID_TYPE);
		}

		return $url;
	}

	/**
	 * Appends API parameters onto API request URL.
	 * @access Private
	 * @since 0.1.0
	 * @param string [$method] The requested API method
	 * @param array [$params] A key-value array of API parameters
	 * @param string [$default] The default API parameter if only 1 provided
	 * @return string The complete API request URL
	 */
	private function appendParams($method, $params = NULL, $default = NULL)
	{
		$url = $this->getUrl('read') . 'token=' . $this->token_read . '&command=' . $method;

		if(isset($params))
		{
			if(isset($default))
			{
				$url .= '&' . $default . '=' . urlencode($params);
			} else {
				foreach($params as $option => $value)
				{
					$url .= '&' . $option . '=' . urlencode($value);
				}
			}
		}

		return $url;
	}

	/**
	 * Retrieves API data from provided URL.
	 * @access Private
	 * @since 0.1.0
	 * @param string [$url] The complete API request URL
	 * @return object An object containing all API return data
	 */
	private function getData($url)
	{
		if(class_exists('BCMAPICache'))
		{
			$cache = BCMAPICache::get($url);

			if($cache !== FALSE)
			{
				$response_object = json_decode($cache);

				if(isset($response_object->items))
				{
					$data = $response_object->items;
				} else {
					$data = $response_object;
				}

				$this->page_number = isset($response_object->page_number) ? $response_object->page_number : NULL;
				$this->page_size = isset($response_object->page_size) ? $response_object->page_size : NULL;
				$this->total_count = isset($response_object->total_count) ? $response_object->total_count : NULL;

				return $data;
			}
		}

		$this->timeout_current++;

		if(!isset($this->token_read))
		{
			throw new BCMAPITokenError($this, self::ERROR_READ_TOKEN_NOT_PROVIDED);
		}

		$response = $this->curlRequest($url, TRUE);

		if($response && $response != 'NULL')
		{
			$response_object = json_decode(preg_replace('/[[:cntrl:]]/u', '', $response));;

			if(isset($response_object->error))
			{
				if($this->timeout_retry && $response_object->code == 103 && $this->timeout_current < $this->timeout_attempts)
				{
					if($this->timeout_delay > 0)
					{
						if($this->timeout_delay < 1)
						{
							usleep($this->timeout_delay * 1000000);
						} else {
							sleep($this->timeout_delay);
						}
					}

					return $this->getData($url);
				} else {
					throw new BCMAPIApiError($this, self::ERROR_API_ERROR, $response_object);
				}
			} else {
				if(class_exists('BCMAPICache'))
				{
					$cache = BCMAPICache::set($url, $response_object);
				}

				if(isset($response_object->items))
				{
					$data = $response_object->items;
				} else {
					$data = $response_object;
				}

				$this->page_number = isset($response_object->page_number) ? $response_object->page_number : NULL;
				$this->page_size = isset($response_object->page_size) ? $response_object->page_size : NULL;
				$this->total_count = isset($response_object->total_count) ? $response_object->total_count : NULL;

				return $data;
			}
		} else {
			throw new BCMAPIApiError($this, self::ERROR_API_ERROR);
		}
	}

	/**
	 * Sends data to the API.
	 * @access Private
	 * @since 1.0.0
	 * @param array [$request] The data to send
	 * @param bool [$return_json] Whether we should return any data or not
	 * @return object An object containing all API return data
	 */
	private function putData($request, $return_json = TRUE)
	{
		if(!isset($this->token_write))
		{
			throw new BCMAPITokenError($this, self::ERROR_WRITE_TOKEN_NOT_PROVIDED);
		}

		$response = $this->curlRequest($request, FALSE);

		if($return_json)
		{
			$response_object = json_decode(preg_replace('/[[:cntrl:]]/', '', $response));

			if(!isset($response_object->result))
			{
				throw new BCMAPIApiError($this, self::ERROR_API_ERROR, $response_object);
			}
			
			return $response_object;
		}
	}

	/**
	 * Makes a cURL request.
	 * @access Private
	 * @since 1.0.0
	 * @param mixed [$request] URL to fetch or the data to send via POST
	 * @param boolean [$get_request] If false, send POST params
	 * @return void
	 */
	private function curlRequest($request, $get_request = FALSE)
	{
		$curl = curl_init();

		if($get_request)
		{
			curl_setopt($curl, CURLOPT_URL, $request);
		} else {
			curl_setopt($curl, CURLOPT_URL, $this->getUrl('write'));
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		}

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);

		$this->api_calls++;

		$curl_error = NULL;

		if(curl_errno($curl))
		{
			$curl_error = curl_error($curl);
		}

		curl_close($curl);

		if($curl_error !== NULL)
		{
			if($get_request)
			{
				throw new BCMAPITransactionError($this, self::ERROR_READ_API_TRANSACTION_FAILED, $curl_error);
			} else {
				throw new BCMAPITransactionError($this, self::ERROR_WRITE_API_TRANSACTION_FAILED, $curl_error);
			}
		}

		return $this->bit32clean($response);
	}

	/**
	 * Cleans the response for 32-bit machine compliance.
	 * @access Private
	 * @since 1.0.0
	 * @param string [$response] The response from a cURL request
	 * @return string The cleansed string if using a 32-bit machine.
	 */
	private function bit32Clean($response)
	{
		if($this->bit32)
		{
			$response = preg_replace('/(?:((?:":\s*)(?:\[\s*)?|(?:\[\s*)|(?:\,\s*))+(\d{10,}))/', '\1"\2"', $response);
		}

		return $response;
	}

	/**
	 * Determines if provided type is valid
	 * @access Private
	 * @since 1.0.0
	 * @param string [$type] The type
	 */
	private function validType($type)
	{
		if(!in_array(strtolower($type), $this->valid_types))
		{
			throw new BCMAPIInvalidType($this, self::ERROR_INVALID_TYPE);
		} else {
			return TRUE;
		}
	}
	
	/**
	 * Dummy method for backwards compatability
	 * @todo Deprecate in > 2.1.0
	 */
	public function filter($assets, $tags)
	{
		return $this->tagsFilter($assets, $tags);
	}
	
	/**
	 * Dummy method for backwards compatability
	 * @todo Deprecate in > 2.1.0
	 */
	public function tags($tags, $implode = FALSE)
	{
		return $this->convertTags($tags, $implode);
	}
	
	/**
	 * Dummy method for backwards compatability
	 * @todo Deprecate in > 2.1.0
	 */
	public function time($ms, $seconds = FALSE)
	{
		return $this->convertTime($ms, $seconds);
	}

	/**
	 * Returns the JavaScript version of the player embed code.
	 * @access Public
	 * @since 0.2.2
	 * @deprecated 1.2.0
	 * @return string The embed code
	 */
	public function embed($a = NULL, $b = NULL, $c = NULL, $d = NULL, $e = NULL)
	{
		throw new BCMAPIDeprecated($this, self::ERROR_DEPRECATED);

		return FALSE;
	}

	/**
	 * Converts an error code into a textual representation.
	 * @access public
	 * @since 1.0.0
	 * @param int [$error_code] The code number of an error
	 * @return string The error text
	 */
	public function getErrorAsString($error_code)
	{
		switch($error_code)
		{
			case self::ERROR_API_ERROR:
				return 'API error';
				break;
			case self::ERROR_DTO_DOES_NOT_EXIST:
				return 'The requested object does not exist';
				break;
			case self::ERROR_ID_NOT_PROVIDED:
				return 'ID not provided';
				break;
			case self::ERROR_INVALID_FILE_TYPE:
				return 'Unsupported file type';
				break;
			case self::ERROR_INVALID_METHOD:
				return 'Requested method not found';
				break;
			case self::ERROR_INVALID_PROPERTY:
				return 'Requested property not found';
				break;
			case self::ERROR_INVALID_TYPE:
				return 'Type not specified';
				break;
			case self::ERROR_INVALID_UPLOAD_OPTION:
				return 'An invalid media upload parameter has been set';
				break;
			case self::ERROR_READ_API_TRANSACTION_FAILED:
				return 'Read API transaction failed';
				break;
			case self::ERROR_READ_TOKEN_NOT_PROVIDED:
				return 'Read token not provided';
				break;
			case self::ERROR_SEARCH_TERMS_NOT_PROVIDED:
				return 'Search terms not provided';
				break;
			case self::ERROR_WRITE_API_TRANSACTION_FAILED:
				return 'Write API transaction failed';
				break;
			case self::ERROR_WRITE_TOKEN_NOT_PROVIDED:
				return 'Write token not provided';
				break;
			case self::ERROR_DEPRECATED:
				return 'Access to this method or property has been deprecated';
				break;
		}
	}
}

class BCMAPIException extends Exception
{
	/**
	 * The constructor for the BCMAPIException class
	 * @access Public
	 * @since 1.0.0
	 * @param object [$obj] A pointer to the BCMAPI class
	 * @param int [$error_code] The error code
	 * @param string [$raw_error] Any additional error information
	 */
	public function __construct(BCMAPI $obj, $error_code, $raw_error = NULL)
	{
		$error = $obj->getErrorAsString($error_code);

		if(isset($raw_error))
		{
			if(isset($raw_error->error) && isset($raw_error->error->message) && isset($raw_error->error->code))
			{
				$raw_error = $raw_error->error;
			}
			
			$error .= "'\n";
			$error .= (isset($raw_error->message) && isset($raw_error->code)) ? '== ' . $raw_error->message . ' (' . $raw_error->code . ') ==' . "\n" : '';
			$error .= isset($raw_error->errors[0]) ? '== ' . $raw_error->errors[0]->error . ' (' . $raw_error->errors[0]->code . ') ==' . "\n" : '';
		}

		parent::__construct($error, $error_code);
	}
}

class BCMAPIApiError extends BCMAPIException{}
class BCMAPIDeprecated extends BCMAPIException{}
class BCMAPIDtoDoesNotExist extends BCMAPIException{}
class BCMAPIIdNotProvided extends BCMAPIException{}
class BCMAPIInvalidFileType extends BCMAPIException{}
class BCMAPIInvalidMethod extends BCMAPIException{}
class BCMAPIInvalidProperty extends BCMAPIException{}
class BCMAPIInvalidType extends BCMAPIException{}
class BCMAPIInvalidUploadOption extends BCMAPIException{}
class BCMAPISearchTermsNotProvided extends BCMAPIException{}
class BCMAPITokenError extends BCMAPIException{}
class BCMAPITransactionError extends BCMAPIException{}

?>