<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Loader extends CI_Loader {
	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, plugins, and helpers to be loaded automatically.
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _ci_autoloader()
	{	
		include_once(APPPATH.'config/autoload'.EXT);
		
		if ( ! isset($autoload))
		{
			return FALSE;
		}
		
		// Load any custom config file
		if (count($autoload['config']) > 0)
		{			
			$CI =& get_instance();
			foreach ($autoload['config'] as $key => $val)
			{
				$CI->config->load($val);
			}
		}		

		// Autoload plugins, helpers and languages
		foreach (array('helper', 'plugin', 'language') as $type)
		{			
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}		
		}

		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		if ( ! isset($autoload['libraries']))
		{
			$autoload['libraries'] = $autoload['core'];
		}
		
		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load scaffolding
			if (in_array('scaffolding', $autoload['libraries']))
			{
				$this->scaffolding();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('scaffolding'));
			}
		
			// Load all other libraries
			foreach ($autoload['libraries'] as $key => $item) {
                if(is_string($key))
                    $this->library($item, NULL, $key);
                else
                    $this->library($item);
            }
		}		

		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}

	}
}