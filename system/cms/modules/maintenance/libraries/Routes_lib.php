<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**	
 * Oxygen-CMS 
 *
 * @author Sal McDonald (2013-2016)
 *
 * @package OxygenCMS\Core\
 *
 *
 * @copyright  Copyright (c) 2013-2016
 * @copyright  Oxygen-CMS
 * @copyright  oxygen-cms.com
 * @copyright  Sal McDonald
 *
 * @contribs PyroCMS Dev Team, PyroCMS Community, Oxygen-CMS Community
 *
 */
class Routes_lib
{

	public function __construct()
	{

	}

	// Path to the route file to update
	private static function _getRoutePath()
	{
		// find the dir
		$dir = SITE_DIR.'config'.DIRECTORY_SEPARATOR;

		//make it if it aint
		is_dir($dir) OR @mkdir($dir,DIR_WRITE_MODE,true);

		is_file($dir.'routes.php') OR self::cproute($dir.'routes.php');

		return $dir.'routes.php';
	}

	/**
	 * copy the base user route file
	 */
	public static function cproute($loc)
	{
		copy('system/cms/config/routes_site.bak',$loc);
	}

	private static function _getBackupRoutePath()
	{
		// find the dir
		$dir = SITE_DIR.'backups'.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR;

		//make it if it aint
		is_dir($dir) OR @mkdir($dir,DIR_WRITE_MODE,true);

		return $dir;

	}	


	/**
	 * @returns ID of route after it is created otherwise false
	 */
	public static function AddRoute($route, $module='Custom',$build=false)
	{
		$ci = get_instance();

		$ci->db->trans_start();

		//get the count of the total routes
		$total_routes = 20;

		$newroute = 
		[
			'name' 			=> $route['name'],
			'uri' 			=> $route['uri'],
			'default_uri' 	=> $route['uri'],
			'dest' 			=> $route['dest'],
			'module' 		=> $module,
			'can_change' 	=> (isset($route['can_change']))?$route['can_change']:0,
			'created' 		=> date("Y-m-d H:i:s"),
			'updated' 		=> date("Y-m-d H:i:s"),
			'active' 		=> 'active', //all routes are always active UNLESS user specifies otherwise, but on install they are active
			'ordering_count'=> $total_routes,
		];

		// Remove the route if it exist
		self::RemoveRoute($route['uri']);

		//now add the new route
		if($ci->db->insert('routes',$newroute))
		{
			$id = $ci->db->insert_id();
			$newroute['id'] = $id;
		}

		//ok we are done
		$ci->db->trans_complete();

		$status = ($ci->db->trans_status() === false) ? false : $newroute;

		if( ($status==true) AND ($build==true))
		{
			self::Build();
		}

		return $status;
	}

	public static function InstallModule($module, $module_routes)
	{
		foreach($module_routes as $route)
		{
			self::AddRoute($route,$module,false);
		}

		self::Build();
	}


	public static function UninstallModule($module_name)
	{
		get_instance()->db->where('module',$module_name)->delete('routes');
		self::Build();		
	}

	public static function RemoveRoute($uri)
	{
		get_instance()->db->where('uri',$uri)->delete('routes');
	}

	public static function RemoveRouteByID($id)
	{
		get_instance()->db->where('id',$id)->delete('routes');
	}

	/**
	 * @todo: if CMS_EDITION is Standard, then write all data to routes, else write to cfg/routes,
	 * the problem is the data in db is currently only some routes and we are not writing others
	 */
	public static function Build()
	{
		get_instance()->load->helper('file');

		$site_routes = get_instance()->db->where('active','active')->order_by('ordering_count')->get('routes')->result();
		
		$file_data = self::build_content($site_routes);

		//delete existing file
		unlink(self::_getRoutePath());

		write_file( self::_getRoutePath() , $file_data); //, 'r+'); //wb is better

		return true;
	}	

	public static function ReBuild()
	{
		// take snapshot
		Routes_lib::Snapshot();

		//Now build
		Routes_lib::Build();
	}	


	public static function Snapshot()
	{

		$path = self::_getBackupRoutePath() . 'route_snapshot' . time() . '.php';
	
		//Check if we have a default route file
		if( ! file_exists( self::_getRoutePath() ))
		{
			copy( self::_getRoutePath() , $path );
			return true;
		}

		return false;		
	}



	private static function build_content($routes=[])
	{
		$file_data = "<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');\n\n/****************************************/\n\n";
		$file_data .= "/*\n";		
		$file_data .= " * Do not alter these routes directly within the file (unless something has gone seriously wrong).\n";
		$file_data .= " *\n"; 				
		$file_data .= " * File generated by Routes_lib System\n";
		$file_data .= " *\n"; 
		$file_data .= " * File generated on:".date('H:i:s', time())."\n"; 
		$file_data .= " *\n"; 						
		$file_data .= " *\n"; 
		$file_data .= " */\n\n"; 				

		if ($routes)
		{

			foreach ($routes as $route)
			{
				$file_data .= "\$route['{$route->uri}'] = '{$route->dest}';\n";
			}
		}

		$file_data .= "\n".'/* End of file routes.php */';

		return $file_data; 
	}

}