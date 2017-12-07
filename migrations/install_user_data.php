<?php
/**
 *
 * Ultimate Blog. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Mr. Goldy
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mrgoldy\ultimateteams\migrations;

class install_user_data extends \phpbb\db\migration\migration
{
	/**
	* @return void
	* @access public
	*/
	static public function depends_on()
	{
		return array('\mrgoldy\ultimateteams\migrations\install_acp_module');
	}

	/**
	* @return void
	* @access public
	*/
	public function update_data()
	{
		$data = array(
			# Add permissions
			array('permission.add', array('u_ut_view')),
			array('permission.add', array('u_ut_view_team')),
			array('permission.add', array('u_ut_add')),
			array('permission.add', array('u_ut_delete')),
			array('permission.add', array('u_ut_edit')),

			array('permission.add', array('m_ut_delete')),
			array('permission.add', array('m_ut_edit')),

			# Add view permission for the Guests group
			array('permission.permission_set', array('GUESTS', 'u_ut_view', 'group')),
		);

		# Assign permissions to roles
		if ($this->role_exists('ROLE_USER_STANDARD'))
		{
			$data[] = array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_ut_view'));
			$data[] = array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_ut_view_team'));
			$data[] = array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_ut_add'));
			$data[] = array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_ut_delete'));
			$data[] = array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_ut_edit'));
		}

		if ($this->role_exists('ROLE_USER_FULL'))
		{
			$data[] = array('permission.permission_set', array('ROLE_USER_FULL', 'u_ut_view'));
			$data[] = array('permission.permission_set', array('ROLE_USER_FULL', 'u_ut_view_team'));
			$data[] = array('permission.permission_set', array('ROLE_USER_FULL', 'u_ut_add'));
			$data[] = array('permission.permission_set', array('ROLE_USER_FULL', 'u_ut_delete'));
			$data[] = array('permission.permission_set', array('ROLE_USER_FULL', 'u_ut_edit'));
		}

		if ($this->role_exists('ROLE_MOD_STANDARD'))
		{
			$data[] = array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_ut_delete'));
			$data[] = array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_ut_edit'));
		}

		if ($this->role_exists('ROLE_MOD_FULL'))
		{
			$data[] = array('permission.permission_set', array('ROLE_MOD_FULL', 'm_ut_delete'));
			$data[] = array('permission.permission_set', array('ROLE_MOD_FULL', 'm_ut_edit'));
		}

		return $data;
	}

	/**
	 * # Check if permission role exists
	 *
	 * @param $role
	 * @return $role_id
	 * @access private
	 */
	private function role_exists($role)
	{
		$sql = 'SELECT role_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_name = '" . $this->db->sql_escape($role) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$role_id = $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);

		return $role_id;
	}
}
