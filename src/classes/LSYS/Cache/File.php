<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @license    http://kohanaframework.org/license
 */
namespace LSYS\Cache;
use LSYS\Exception;
use LSYS\Config;
use LSYS\Cache;

class File extends Cache {
	/**
	 * Creates a hashed filename based on the string. This is used
	 * to create shorter unique IDs for each cache filename.
	 * @param   string  $string  string to hash into filename
	 * @return  string
	 */
	protected static function filename($string)
	{
		return sha1($string).'.cache';
	}
	
	/**
	 * @var  string   the caching directory
	 */
	protected $_cache_dir;
	
	/**
	 * Constructs the file cache driver. This method cannot be invoked externally.
	 *
	 * @param   array  $config  config
	 * @throws  Exception
	 */
	protected function __construct(Config $config)
	{
		// Setup parent
		parent::__construct($config);
		try
		{
			$directory = $this->_config->get('cache_dir');
			if(empty($directory)) throw Exception(__("plase set config cache_dir param in cache.php"));
			$this->_cache_dir = new \SplFileInfo($directory);
		}
		// PHP < 5.3 exception handle
		catch (\ErrorException $e)
		{
			$this->_cache_dir = $this->_makeDirectory($directory, 0777, TRUE);
		}
		// PHP >= 5.3 exception handle
		catch (\UnexpectedValueException $e)
		{
			$this->_cache_dir = $this->_makeDirectory($directory, 0777, TRUE);
		}
	
		// If the defined directory is a file, get outta here
		if ($this->_cache_dir->isFile())
		{
			throw new Exception(__('Unable to create cache directory as a file already exists :path',array('path'=>$directory)));
		}
	
		// Check the read status of the directory
		if ( ! $this->_cache_dir->isReadable())
		{
			throw new Exception(__('Unable to read from the cache directory :path',array('path'=>$directory)));
		}
	
		// Check the write status of the directory
		if ( ! $this->_cache_dir->isWritable())
		{
			throw new Exception(__('Unable to write to the cache directory :path',array('path'=>$directory)));
		}
	}
	
	/**
	 * Retrieve a cached value entry by id.
	 *
	 * @param   string   $id       id of cache to entry
	 * @param   string   $default  default value to return if cache miss
	 * @return  mixed
	 * @throws  Exception
	 */
	public function get($id, $default = NULL)
	{
		$filename = self::filename($this->_sanitizeId($id));
		$directory = $this->_resolveDirectory($filename);
	
		// Wrap operations in try/catch to handle notices
		try
		{
			// Open file
			$file = new \SplFileInfo($directory.$filename);
	
			// If file does not exist
			if ( ! $file->isFile())
			{
				// Return default value
				return $this->_getCallbackDefault($id, $default);
			}
			else
			{
				// Open the file and parse data
				$created  = $file->getMTime();
				$data     = $file->openFile();
				$lifetime = $data->fgets();
	
				// If we're at the EOF at this point, corrupted!
				if ($data->eof())
				{
					throw new Exception(__('corrupted cache file'));
				}
	
				$cache = '';
	
				while ($data->eof() === FALSE)
				{
					$cache .= $data->fgets();
				}
	
				// Test the expiry
				if (($created + (int) $lifetime) < time())
				{
					// Delete the file
					$this->_deleteFile($file, NULL, TRUE);
					return $this->_getCallbackDefault($id, $default);
				}
				else
				{
					return unserialize($cache);
				}
			}
	
		}
		catch (\ErrorException $e)
		{
			// Handle ErrorException caused by failed unserialization
			if ($e->getCode() === E_NOTICE)
			{
				throw new Exception(__('failed to unserialize cached object with message : :message',array("message"=>$e->getMessage())));
			}
	
			// Otherwise throw the exception
			throw new Exception($e->getMessage(),$e->getCode(),$e);
		}
	}
	/**
	 * check id in cache?
	 * @param string $id
	 */
	public function exist($id){
		$filename = self::filename($this->_sanitizeId($id));
		$directory = $this->_resolveDirectory($filename);
		try
		{
			$file = new \SplFileInfo($directory.$filename);
			if ( ! $file->isFile())
			{
				return false;
			}
			else
			{
				// Open the file and parse data
				$created  = $file->getMTime();
				$data     = $file->openFile();
				$lifetime = $data->fgets();
		
				// If we're at the EOF at this point, corrupted!
				if ($data->eof())
				{
					return false;
				}
		
				$cache = '';
		
				while ($data->eof() === FALSE)
				{
					$cache .= $data->fgets();
				}
		
				// Test the expiry
				if (($created + (int) $lifetime) < time())
				{
					// Delete the file
					$this->_deleteFile($file, NULL, TRUE);
					return false;
				}
				else
				{
					return true;
				}
			}
		}
		catch (\ErrorException $e)
		{
			return false;
		}
	}
	/**
	 * Set a value to cache with id and lifetime
	 *
	 * @param   string   $id        id of cache entry
	 * @param   string   $data      data to set to cache
	 * @param   integer  $lifetime  lifetime in seconds
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = NULL)
	{
		$filename = self::filename($this->_sanitizeId($id));
		$directory = $this->_resolveDirectory($filename);
	
		// If lifetime is NULL
		if ($lifetime === NULL)
		{
			// Set to the default expiry
			$lifetime = $this->_config->get("default_expire",Cache::$expire);
		}
	
		// Open directory
		$dir = new \SplFileInfo($directory);
	
		// If the directory path is not a directory
		if ( ! $dir->isDir())
		{
			// Create the directory
			if ( ! mkdir($directory, 0777, TRUE))
			{
				throw new Exception(__(':method unable to create directory :dir ',array("method"=>__METHOD__,'dir'=>$directory)));
			}
	
			// chmod to solve potential umask issues
			chmod($directory, 0777);
		}
	
		// Open file to inspect
		$resouce = new \SplFileInfo($directory.$filename);
		$file = $resouce->openFile('w');
	
		try
		{
			$data = $lifetime."\n".serialize($data);
			$file->fwrite($data, strlen($data));
			return (bool) $file->fflush();
		}
		catch (\ErrorException $e)
		{
			// If serialize through an error exception
			if ($e->getCode() === E_NOTICE)
			{
				// Throw a caching error
				throw new Exception(__(':method failed to serialize data for caching with message :msg ',array("method"=>__METHOD__,'msg'=>$e->getMessage())));
			}
	
			// Else rethrow the error exception
			throw new Exception($e->getMessage(),$e->getCode(),$e);
		}
	}
	
	/**
	 * Delete a cache entry based on id
	 *
	 * @param   string   $id  id to remove from cache
	 * @return  boolean
	 */
	public function delete($id)
	{
		$filename = self::filename($this->_sanitizeId($id));
		$directory = $this->_resolveDirectory($filename);
	
		return $this->_deleteFile(new \SplFileInfo($directory.$filename), NULL, TRUE);
	}
	
	/**
	 * Delete all cache entries.
	 *
	 * Beware of using this method when
	 * using shared memory cache systems, as it will wipe every
	 * entry within the system for all clients.
	 * @return  boolean
	 */
	public function deleteAll()
	{
		return $this->_deleteFile($this->_cache_dir, TRUE);
	}
	
	/**
	 * Garbage collection method that cleans any expired
	 * cache entries from the cache.
	 *
	 * @return  void
	 */
	public function garbageCollect()
	{
		$this->_deleteFile($this->_cache_dir, TRUE, FALSE, TRUE);
		return;
	}
	
	/**
	 * Deletes files recursively and returns FALSE on any errors
	 *
	 *     // Delete a file or folder whilst retaining parent directory and ignore all errors
	 *     $this->_deleteFile($folder, TRUE, TRUE);
	 *
	 * @param   \SplFileInfo  $file                     file
	 * @param   boolean      $retain_parent_directory  retain the parent directory
	 * @param   boolean      $ignore_errors            ignore_errors to prevent all exceptions interrupting exec
	 * @param   boolean      $only_expired             only expired files
	 * @return  boolean
	 * @throws  Exception
	 */
	protected function _deleteFile(\SplFileInfo $file, $retain_parent_directory = FALSE, $ignore_errors = FALSE, $only_expired = FALSE)
	{
		// Allow graceful error handling
		try
		{
			// If is file
			if ($file->isFile())
			{
				try
				{
					// Handle ignore files
					if (in_array($file->getFilename(), $this->_config->get('ignore_on_delete',array())))
					{
						$delete = FALSE;
					}
					// If only expired is not set
					elseif ($only_expired === FALSE)
					{
						// We want to delete the file
						$delete = TRUE;
					}
					// Otherwise...
					else
					{
						// Assess the file expiry to flag it for deletion
						$json = $file->openFile('r')->current();
						$data = json_decode($json);
						$delete = $data->expiry < time();
					}
	
					// If the delete flag is set delete file
					if ($delete === TRUE)
						return unlink($file->getRealPath());
					else
						return FALSE;
				}
				catch (\ErrorException $e)
				{
					// Catch any delete file warnings
					if ($e->getCode() === E_WARNING)
					{
						throw new Exception(__(':method failed to delete file :path',array('method'=>__METHOD__,'path'=>$file->getRealPath())));
					}
				}
			}
			// Else, is directory
			elseif ($file->isDir())
			{
				// Create new \DirectoryIterator
				$files = new \DirectoryIterator($file->getPathname());
	
				// Iterate over each entry
				while ($files->valid())
				{
					// Extract the entry name
					$name = $files->getFilename();
	
					// If the name is not a dot
					if ($name != '.' AND $name != '..')
					{
						// Create new file resource
						$fp = new \SplFileInfo($files->getRealPath());
						// Delete the file
						$this->_deleteFile($fp);
					}
	
					// Move the file pointer on
					$files->next();
				}
	
				// If set to retain parent directory, return now
				if ($retain_parent_directory)
				{
					return TRUE;
				}
	
				try
				{
					// Remove the files iterator
					// (fixes Windows PHP which has permission issues with open iterators)
					unset($files);
	
					// Try to remove the parent directory
					return rmdir($file->getRealPath());
				}
				catch (\ErrorException $e)
				{
					// Catch any delete directory warnings
					if ($e->getCode() === E_WARNING)
					{
						throw new Exception(__(':method failed to delete directory :path',array('method'=>__METHOD__,'path'=>$file->getRealPath())));
					}
					throw new Exception($e->getMessage(),$e->getCode(),$e);
				}
			}
			else
			{
				// We get here if a file has already been deleted
				return FALSE;
			}
		}
		// Catch all exceptions
		catch (\Exception $e)
		{
			// If ignore_errors is on
			if ($ignore_errors === TRUE)
			{
				// Return
				return FALSE;
			}
			// Throw exception
			throw new Exception($e->getMessage(),$e->getCode(),$e);
		}
	}
	
	/**
	 * Resolves the cache directory real path from the filename
	 *
	 *      // Get the realpath of the cache folder
	 *      $realpath = $this->_resolveDirectory($filename);
	 *
	 * @param   string  $filename  filename to resolve
	 * @return  string
	 */
	protected function _resolveDirectory($filename)
	{
		return $this->_cache_dir->getRealPath().DIRECTORY_SEPARATOR.$filename[0].$filename[1].DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Makes the cache directory if it doesn't exist. Simply a wrapper for
	 * `mkdir` to ensure DRY principles
	 *
	 * @link    http://php.net/manual/en/function.mkdir.php
	 * @param   string    $directory
	 * @param   integer   $mode
	 * @param   boolean   $recursive
	 * @param   resource  $context
	 * @return  \SplFileInfo
	 * @throws  Exception
	 */
	protected function _makeDirectory($directory, $mode = 0777, $recursive = FALSE, $context = NULL)
	{
		if ( ! mkdir($directory, $mode, $recursive, $context))
		{
			throw new Exception(__('Failed to create the defined cache directory : :dir',array("dir"=>$directory)));
		}
		chmod($directory, $mode);
	
		return new \SplFileInfo($directory);
	}
	/**
	 * Replaces troublesome characters with underscores.
	 *
	 *     // Sanitize a cache id
	 *     $id = $this->_sanitizeId($id);
	 *
	 * @param   string  $id  id of cache to sanitize
	 * @return  string
	 */
	protected function _sanitizeId($id)
	{
		// Change slashes and spaces to underscores
		return str_replace(array('/', '\\', ' '), '_', $id);
	}
}