<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');
/**
 * OxygenCMS
 *
 * @author      OxygenCMS Dev Team 2015
 * @author      PyroCMS Dev Team 2008-2014
 * @copyright   Copyright (c) 2012, PyroCMS LLC
 * @copyright   Copyright (c) 2016, OxygenCMS 
 *
 */
/**
 * PyroCMS Module Definition
 *
 * This class should be extended to allow for module management.
 *
 * @package		PyroCMS\Core\Libraries
 * @author		PyroCMS Dev Team
 * @copyright   Copyright (c) 2012, PyroCMS LLC
 */
abstract class Module extends Component {

	/**
	 * The version of the module.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Info
	 *
	 * This function returns the details for a module. It should be overridden
	 * by the module.
	 * Expected return is an array:
	 *
	 * array(
	 *	   'name' => array(
	 *		   'en' => 'Example Module'
	 *	   ),
	 *	   'description' => array(
	 *		   'en' => 'Example Module Description'
	 *	   ),
	 *	   'frontend' => true,
	 *	   'backend'  => true,
	 *	   'menu'	  => true
	 *	   'controllers' => array(
	 *		   'admin' => array('index', 'edit', 'delete'),
	 *		   'example' => array('index', 'view')
	 *	   )
	 * );
	 *
	 * @return array The information about the module
	 */
	public abstract function info();

	/**
	 * Installs a module's tables and database tables and data.
	 *
	 * Called upon first install of the module. The typical case is that the
	 * module's tables are initially dropped from the database and subsequently
	 * are created again. But this is up to the module to implement.
	 *
	 * @return	bool	Whether the module was installed
	 */
	public abstract function install();

	/**
	 * Called upon the uninstall of the module.
	 *
	 * @return	bool	Whether the module was uninstalled
	 */
	public abstract function uninstall();

	/**
	 * Called when this is a newer version than currently installed.
	 *
	 * @param string $old_version The version to upgrade from
	 * @return bool Whether the module was installed
	 */
	public abstract function upgrade($old_version);


	/**
	 * Loads the database and dbforge libraries.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns the help text for a module.
	 *
	 * By default returns "No Help Provided".
	 *
	 * @return string
	 */
	public function help()
	{
		return lang('modules.no_help');
	}

}