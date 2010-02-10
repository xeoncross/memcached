<?php
/*
 * Memcached Class
 *
 * This class handles caching of data using memcached servers
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright		Copyright (c) 2010 David Pennington
 * @license		http://micromvc.com/license.txt
 * @link		http://micromvc.com
 ********************************** 80 Columns *********************************
 */
class cache {

	public static $memcache = NULL;
	public static $compress = FALSE;

	/**
	 * Open the connection to the memcache server
	 */
	public static function connect()
	{
		if(self::$memcache)
		{
			return;
		}

		// Create and connecto to the memcache server(s)
		self::$memcache = new Memcache;

		//Get the config
		$config = config::get('cache_options');

		// Add each server
		foreach($config['servers'] as $server => $port)
		{
			self::$memcache->addServer($server, $port);
		}

		// Enable / Disable zlib compression
		self::$compress = $config['compress'];

		//print dump(self::stats());
	}


	/**
	 * Fetch an item from the cache
	 *
	 * @param $id the id of the cache item
	 * @param $cache_life the optional life of the item
	 * @return mixed
	 */
	public static function get($id, $cache_life = NULL)
	{
		// Make sure the database is connected
		self::$memcache or self::connect();

		//If no cache life was given - use default
		if( $cache_life === NULL )
		{
			$cache_life = config::get('cache_life');
		}

		// If caching is disabled
		if( ! $cache_life )
		{
			return FALSE;
		}

		return self::$memcache->get($id);
	}


	/**
	 * Store an item in the cache
	 *
	 * @param $id the id of the cache item
	 * @param $data the item to store
	 * @param $cache_life the optional life of the item
	 * @return boolean
	 */
	public static function set($id, $data, $cache_life = NULL)
	{
		// Make sure the database is connected
		self::$memcache or self::connect();

		//If no cache life was given - use default
		if( $cache_life === NULL )
		{
			$cache_life = config::get('cache_life');
		}

		// If caching is disabled
		if( ! $cache_life )
		{
			return FALSE;
		}

		// Store the object
		return self::$memcache->set($id, $data, self::$compress, $cache_life);
	}


	/**
	 * Delete an item from the cache
	 * @return boolean
	 */
	public static function delete($id)
	{
		// Make sure the database is connected
		self::$memcache or self::connect();

		return self::$memcache->delete($id);
	}


	/**
	 * Flush all existing caches
	 * @return	boolean
	 */
	public static function delete_all()
	{
		// Make sure the database is connected
		self::$memcache or self::connect();

		$result = self::$memcache->flush();

		// We must sleep after flushing, or overwriting will not work!
		// @see http://php.net/manual/en/function.memcache-flush.php#81420
		sleep(1);

		return $result;
	}


	/**
	 * Return status information about all servers
	 * @return array
	 */
	public static function stats()
	{
		// Make sure the database is connected
		self::$memcache or self::connect();

		// Return status
		return self::$memcache->getExtendedStats();
	}
}
