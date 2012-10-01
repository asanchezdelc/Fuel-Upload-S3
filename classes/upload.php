<?php
namespace S3;
use \Fuel\Core\Upload as F;

require_once PKGPATH . 's3/s3-php5-curl/S3.php';

class Upload extends \Fuel\Core\Upload {

	public static function _init()
	{
		parent::_init();
		\Config::load('s3', true);
		static::$config = array_merge(static::$config, \Config::get('s3', array()));
	}

	/**
	 * save uploaded file(s)
	 *
	 * @param	mixed	if int, $files element to move. if array, list of elements to move, if none, move all elements
	 * @param	string	path to move to
	 * @return	void
	 */
	public static function save()
	{
		// files to save
		$files = array();
		
		// Straight from Upload.php code
		// check for parameters
		if (func_num_args())
		{
			foreach(func_get_args() as $param)
			{
				// string => new path to save to
				if (is_string($param))
				{
					$path = $param;
				}
				// array => list of $files indexes to save
				elseif(is_array($param))
				{
					$files = array();
					foreach($param as $key)
					{
						if (isset(static::$files[(int) $key]))
						{
							$files[(int) $key] = static::$files[(int) $key];
						}
					}
				}
				// integer => files index to save
				elseif(is_numeric($param))
				{
					if (isset(static::$files[$param]))
					{
						$files[$param] = static::$files[$param];
					}
				}
			}
		}
		else
		{
			// save all files
			$files = static::$files;
		}

		// get access keys
		$access_key_id = static::$config['access_key_id'];
		$secret_access_key = static::$config['secret_access_key'];
		$bucket_name = static::$config['bucket_name'];
		$enable_ssl = static::$config['enable_ssl'];
		
		// create s3 object
		$s3 = new \S3($access_key_id, $secret_access_key);
		
		foreach($files as $file)
		{
			//always randomize file
			$filename = md5(serialize($file).time());
			
			$save_as = array(
				static::$config['prefix'],
				$filename,
				static::$config['suffix'],
				'',
				'.',
				empty(static::$config['extension']) ? $file['extension'] : static::$config['extension']
			);
			
			$save_as = implode('', $save_as);
			
			//lets put the file!
			$s3->putObject(file_get_contents($file['file']), $bucket_name, $save_as, \S3::ACL_PUBLIC_READ);
		}
		
		
	}
}
