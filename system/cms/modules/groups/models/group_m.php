<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Group model
 *
 * @author		Phil Sturgeon
 * @author		PyroCMS Dev Team
 * @package		PyroCMS\Core\Modules\Groups\Models
 *
 */
class Group_m extends MY_Model
{
	protected $_table = 'users_groups';
	/**
	 * Check a rule based on it's role
	 *
	 * 
	 * @param string $role The role
	 * @param array $location
	 * @return mixed
	 */
	public function check_rule_by_role($role, $location)
	{
		// No more checking to do, admins win
		if ( $role == 1 )
		{
			return true;
		}

		// Check the rule based on whatever parts of the location we have
		if ( isset($location['module']) )
		{
			 $this->db->where('(module = "'.$location['module'].'" or module = "*")');
		}

		if ( isset($location['controller']) )
		{
			 $this->db->where('(controller = "'.$location['controller'].'" or controller = "*")');
		}

		if ( isset($location['method']) )
		{
			 $this->db->where('(method = "'.$location['method'].'" or method = "*")');
		}

		// Check which kind of user?
		$this->db->where('g.id', $role);

		$this->db->from('permissions p');
		$this->db->join('users_groups as g', 'g.id = p.group_id');

		$query = $this->db->get();

		return $query->num_rows() > 0;
	}

	/**
	 * Return an array of groups
	 *
	 * 
	 * @param array $params Optional parameters
	 * @return array
	 */

	public function get_all($params = [])
	{
		if ( isset($params['except']) )
		{
			$this->db->where_not_in('name', $params['except']);
		}

		return parent::get_all();
	}

	/*
	 * This needs more work before it can replace the original
	 * 
	public function get_all($params = [])
	{

		$users_table = $this->db->dbprefix('users');
		$groups_table = $this->db->dbprefix('users_groups');
		$sql = "select g.id,
				   count(*) as members_count
				   from
					   $groups_table g,
					   $users_table u
				   where 
					   g.id = u.group_id
				   group by
					   g.id";

		$result = $this->db->query( $sql );		       
			
		return $result->result();

	}
	*/



	/**
	 * Same as get_all() but includes data of no. of users in group
	 */
	public function get_all_admin($params = [])
	{
		if ( isset($params['except']) )
		{
			$this->db->where_not_in('name', $params['except']);
		}

		$groups = parent::get_all();

		foreach($groups as &$g)
		{
			$g->member_count = $this->db->where('group_id',$g->id)->from('users')->count_all_results();
		}

		return $groups;
	}


	public function get_all_select($params = [])
	{
		$groups = $this->get_all($params);	
		$return = [];
		foreach($groups as $group)
		{
			$return[$group->id] = $group->description;
		}
		return $return;
	}

	/**
	 * Add a group
	 *
	 * 
	 * @param array $input The data to insert
	 * @return array
	 */
	public function insert($input = [], $skip_validation = false)
	{
		//do not allow another admin
		if($input['name'] == 'admin')
			return false;

		return parent::insert(array(
			'name'			        => $input['name'],
			'description'	        => $input['description'],
			'authority'	        	=> isset($input['authority']) ? $input['authority']  : 10 ,
		));
	}

	/**
	 * Update a group
	 *
	 * 
	 * @param int $id The ID of the role
	 * @param array $input The data to update
	 * @return array
	 */
	public function update($id = 0, $input = [], $skip_validation = false)
	{
		//do not allow another admin
		if($input['name'] == 'admin')
			return false;
					
		$update = 
		[
			'name'					=> $input['name'],
			'description'			=> $input['description'],
		];

		if(isset($input['authority'])):
			$update['authority']  = $input['authority'];
		endif;

		return  parent::update($id, $update);

	}

	/**
	 * Delete a group
	 *
	 * 
	 * @param int $id The ID of the group to delete
	 * @return
	 */
	public function delete($id = 0)
	{
		$this->load->model('users/user_m');
		
		// don't delete the group if there are still users assigned to it
		if ($this->user_m->count_by(array('group_id' => $id)) > 0)
		{
			return false;
		}

		// Dont let them delete the "admin" group or the "user" group.
		// The interface does not have a delete button for these, this is just insurance
		$this->db->where_not_in('name', array('user', 'admin'));

		return parent::delete($id);
	}


}